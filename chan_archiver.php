<?php
// enjoi -stk25
error_reporting( E_ALL );

include "config.php";

class chan_archiver
{
    public $mysql;
    public $threadurl = "http://boards.4chan.org/%s/res/%s"; // board, ID
    
    protected function connectDB()
    {
        global $archiver_config;
        if ( !$this->mysql )
        {
            $this->mysql = mysql_connect( $archiver_config[ 'mysql_host' ], $archiver_config[ 'mysql_user' ], $archiver_config[ 'mysql_pass' ] );
            if ( !$this->mysql )
                die( 'Could not connect: ' . mysql_error() );
            mysql_select_db( $archiver_config[ 'mysql_db' ], $this->mysql );
        }
    }
    
    protected function closeDB()
    {
        if ( $this->mysql )
        {
            mysql_close( $this->mysql );
            $this->mysql = null;
        }
    }
    
    protected function getSource( $url )
    {
        if ( ( $source = @file_get_contents( $url ) ) == false )
            return false;
        return $source;
    }
    
    protected function downloadFile( $url, $location )
    {
        $file = "";
        if ( ( $handle = @fopen( $url, "r" ) ) )
        {
            while ( $line = fread( $handle, 8192 ) )
                $file .= $line;
            fclose( $handle );
            $this->writeFile( $file, $location );
        }
    }
    
    protected function writeFile( $data, $location )
    {
        if ( ( $handle = fopen( $location, "w+" ) ) )
        {
            fwrite( $handle, $data );
            fclose( $handle );
            return true;
        }
        return false;
    }
    
    public function checkThreads()
    {
        $this->connectDB();
        $query = mysql_query( "SELECT * FROM `Threads` WHERE `Status` = '1'" );
        if ( !$query )
            die( 'Could not query database: ' . mysql_error() );
        $num = mysql_num_rows( $query );
        if ( $num <= 0 )
            return false;
        while ( $row = mysql_fetch_object( $query ) )
        {
            if ( time() - $row->LastChecked < 90 )
                continue;
            
            $this->updateThread( $row->ID, $row->Board );
        }
        $this->closeDB();
    }
    
    public function updateThread( $threadid, $board )
    {
        global $archiver_config;
        $this->connectDB();
        $thrquery = mysql_query( sprintf( "SELECT * FROM `Posts` WHERE `Board` = '%s' AND `ThreadID` = '%s'", $board, $threadid ) );
        $postarr  = array();
        while ( $post = mysql_fetch_object( $thrquery ) )
            array_push( $postarr, $post->ID );
        
        $url  = sprintf( $this->threadurl, $board, $threadid );
        $data = $this->getSource( $url );
        if ( !$data ) // must have 404'd
        {
            mysql_query( sprintf( "UPDATE `Threads` SET `Status` = '0' WHERE `Board` = '%s' AND `ID` = '%s'", $board, $threadid ) );
            mysql_query( sprintf( "DELETE FROM `Posts` WHERE `Board` = '%s' AND `ID` = '%s'", $board, $threadid ) );
            return;
        }
        $fixeddata = str_replace( "=\"//", "=\"http://", $data );
        $fixeddata = str_replace( "\"" . $threadid . "#", "\"" . $threadid . ".html#", $data );
        $fixeddata = str_replace( "text/rocketscript", "text/javascript", $data );
        $fixeddata = str_replace( "data-rocketsrc", "src", $data );
        if ( is_dir( $archiver_config[ 'storage' ] . $board . "/" ) === FALSE )
            mkdir( $archiver_config[ 'storage' ] . $board . "/" );
        if ( is_dir( $archiver_config[ 'storage' ] . $board . "/" . $threadid . "/" ) === FALSE )
            mkdir( $archiver_config[ 'storage' ] . $board . "/" . $threadid . "/" );
        if ( is_dir( $archiver_config[ 'storage' ] . $board . "/" . $threadid . "/thumbs/" ) === FALSE )
            mkdir( $archiver_config[ 'storage' ] . $board . "/" . $threadid . "/thumbs/" );
        
        $posts = explode( "class=\"postContainer", $data );
        for ( $i = 1; $i < count( $posts ); $i++ )
        {
            $post = explode( "</blockquote> </div>", $posts[ $i ] );
            $id   = explode( "title=\"Quote this post\">", $post[ 0 ] );
            $id   = explode( "</a>", $id[ 1 ] );
            $id   = $id[ 0 ];
            if ( in_array( $id, $postarr ) )
                continue;
            
            $posttime = explode( "data-utc=\"", $post[ 0 ] );
            $posttime = explode( "\"", $posttime[ 1 ] );
            $posttime = $posttime[ 0 ];
            
            $file = explode( "\">File:", $post[ 0 ] );
            if ( count( $file ) > 1 )
            {
                $file     = explode( "</a></div>", $file[ 1 ] );
                $file     = $file[ 0 ];
                $fileorig = explode( "<a href=\"", $file );
                $fileorig = explode( "\" target=\"_blank\"", $fileorig[ 1 ] );
                $fileorig = $fileorig[ 0 ];
                $fileurl  = "http:" . $fileorig;
                
                $filethum = explode( "<img src=\"", $file );
                $filethum = explode( "\" alt=", $filethum[ 1 ] );
                $filethum = $filethum[ 0 ];
                $thumurl  = "http:" . $filethum;
                
                $filestor    = $archiver_config[ 'storage' ] . $board . "/" . $threadid . "/" . basename( $fileurl );
                $thumstor    = $archiver_config[ 'storage' ] . $board . "/" . $threadid . "/thumbs/" . basename( $thumurl );
                $pubfilestor = $archiver_config[ 'pubstorage' ] . $board . "/" . $threadid . "/" . basename( $fileurl );
                $pubthumstor = $archiver_config[ 'pubstorage' ] . $board . "/" . $threadid . "/thumbs/" . basename( $thumurl );
                
                $this->downloadFile( $fileurl, $filestor );
                $this->downloadFile( $thumurl, $thumstor );
                $fixeddata = str_replace( $fileurl, $pubfilestor, $fixeddata );
                $fixeddata = str_replace( $thumurl, $pubthumstor, $fixeddata );
                //echo "<!-- " . $fileurl . " -->";
            }
            mysql_query( sprintf( "INSERT INTO `Posts` ( `ID`, `ThreadID`, `Board`, `PostTime` ) VALUES ( '%s', '%s', '%s', '%s' )", $id, $threadid, $board, $posttime ) );
        }
        echo sprintf( "Checked %s (/%s/) at %s<br />\r\n", $threadid, $board, time() );
        mysql_query( sprintf( "UPDATE `Threads` SET `LastChecked` = '%s' WHERE `Board` = '%s' AND `ID` = '%s'", time(), $board, $threadid ) );
        $this->writeFile( $fixeddata, $archiver_config[ 'storage' ] . $board . "/" . $threadid . ".html" );
    }
    
    public function addThread( $threadid, $board, $description )
    {
        $this->connectDB();
        // check if we already have it
        $query = mysql_query( sprintf( "SELECT * FROM `Threads` WHERE `ID` = '%s' AND Board = '%s'", $threadid, $board ) );
        if ( !$query )
            die( 'Could not query database: ' . mysql_error() );
        $num = mysql_num_rows( $query );
        if ( $num > 0 )
            return false;
        // guess we don't, lets add it
        $query = mysql_query( sprintf( "INSERT INTO `Threads` ( `ID`, `Board`, `Status`, `LastChecked`, `Description` ) VALUES ( '%s', '%s', '1', '0', '%s' )", $threadid, $board, $description ) );
        if ( !$query )
            die( 'Could not add thread: ' . mysql_error() );
        echo sprintf( "Added thread %s (/%s/)<br />\r\n", $threadid, $board );
        $this->closeDB();
        return true;
    }
    
    public function removeThread( $threadid, $board )
    {
        $this->connectDB();
        // check if we already have it
        $query = mysql_query( sprintf( "SELECT * FROM `Threads` WHERE `ID` = '%s' AND Board = '%s'", $threadid, $board ) );
        if ( !$query )
            die( 'Could not query database: ' . mysql_error() );
        $num = mysql_num_rows( $query );
        if ( $num <= 0 )
            return false;
        mysql_query( sprintf( "DELETE FROM `Threads` WHERE `ID` = '%s' AND Board = '%s'", $threadid, $board ) );
        mysql_query( sprintf( "DELETE FROM `Posts` WHERE `ThreadID` = '%s' AND Board = '%s'", $threadid, $board ) );
        echo sprintf( "Removed thread %s (/%s/)<br />\r\n", $threadid, $board );
        $this->closeDB();
        return true;
    }
    
    public function setThreadDescription( $threadid, $board, $description )
    {
        $this->connectDB();
        // check if we already have it
        $query = mysql_query( sprintf( "SELECT * FROM `Threads` WHERE `ID` = '%s' AND Board = '%s'", $threadid, $board ) );
        if ( !$query )
            die( 'Could not query database: ' . mysql_error() );
        $num = mysql_num_rows( $query );
        if ( $num <= 0 )
            return false;
        mysql_query( sprintf( "UPDATE `Threads` SET `Description` = '%s' WHERE `ID` = '%s' AND Board = '%s'", $description, $threadid, $board ) );
        echo sprintf( "Updated thread %s (/%s/)<br />\r\n", $threadid, $board );
        $this->closeDB();
        return true;
    }
    public function getThreads()
    {
        $this->connectDB();
        $query = mysql_query( "SELECT * FROM `Threads`" );
        if ( !$query )
            die( 'Could not query database: ' . mysql_error() );
        $thrarray = array();
        while ( $thr = mysql_fetch_object( $query ) )
        {
            $q2       = mysql_query( sprintf( "SELECT * FROM `Posts` WHERE `ThreadID` = '%s' AND `Board` = '%s' ORDER BY `PostTime` DESC", $thr->ID, $thr->Board ) );
            $lasttime = 0;
            if ( !$q2 )
                die( 'Could not query database: ' . mysql_error() );
            if ( mysql_num_rows( $q2 ) > 0 )
                $lasttime = mysql_fetch_object( $q2 )->PostTime;
            array_push( $thrarray, array(
                 $thr->ID,
                $thr->Board,
                $thr->Status,
                $thr->LastChecked,
                $thr->Description,
                $lasttime 
            ) );
        }
        $this->closeDB();
        return $thrarray;
    }
}
?>
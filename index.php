<html>
<head>
<title>4chan archiver - by anon e moose</title>
</head>
<body>
<a href="http://github.com/emoose/4chan-archiver/"><h2>4chan archiver - by anon e moose</h2></a>
<p>
<?php
include "chan_archiver.php";
$t = new chan_archiver();
if ( isset( $_REQUEST[ 'del' ] ) && isset( $_REQUEST[ 'id' ] ) && isset( $_REQUEST[ 'brd' ] ) )
    $t->removeThread( $_REQUEST[ 'id' ], $_REQUEST[ 'brd' ] );
    
if ( isset( $_REQUEST[ 'chk' ] ) && isset( $_REQUEST[ 'id' ] ) && isset( $_REQUEST[ 'brd' ] ) )
    $t->updateThread( $_REQUEST[ 'id' ], $_REQUEST[ 'brd' ] );

if ( isset( $_REQUEST[ 'upd' ] ) && isset( $_REQUEST[ 'id' ] ) && isset( $_REQUEST[ 'brd' ] ) )
    $t->setThreadDescription( $_REQUEST[ 'id' ], $_REQUEST[ 'brd' ], $_REQUEST[ 'desc' ] );

if ( isset( $_REQUEST[ 'url' ] ) && $c = preg_match_all( "/.*?(?:[a-z][a-z0-9_]*).*?(?:[a-z][a-z0-9_]*).*?(?:[a-z][a-z0-9_]*).*?(?:[a-z][a-z0-9_]*).*?((?:[a-z][a-z0-9_]*)).*?(\d+)/is", $_REQUEST[ 'url' ], $matches ) )
{
    $board = $matches[ 1 ][ 0 ];
    $id    = $matches[ 2 ][ 0 ];
    $t->addThread( $id, $board, $_REQUEST['desc'] );
}

$threads = $t->getThreads();
echo <<<ENDHTML
</p>
<table border="1" bordercolor="#FFCC00" style="background-color:#FFFFCC" width="750" cellpadding="3" cellspacing="3">
	<tr>
		<td>Thread ID</td>
		<td>Board</td>
		<td>Description</td>
		<td>Status</td>
		<td>Last Checked</td>
		<td>Actions</td>
	</tr>
ENDHTML;

foreach ( $threads as $thr )
{
    $thrlink     = sprintf( $t->threadurl, $thr[ 1 ], $thr[ 0 ] );
    $lastchecked = time() - $thr[ 3 ] . " seconds ago";
    if ( $thr[ 3 ] == 0 )
        $lastchecked = "never";
    $status = $thr[ 2 ] == 1 ? "Ongoing" : "404'd";
    $local  = $archiver_config[ 'pubstorage' ] . $thr[ 1 ] . "/" . $thr[ 0 ] . ".html";
    $link   = "<a href=\"$thrlink\">{$thr[0]}</a> <a href=\"$local\">(local)</a>";
    $check  = "<input type=\"submit\" name=\"chk\" value=\"Check\"/>";
    if ( $thr[ 2 ] == 0 )
    {
        $lastchecked = "";
        $link        = "<a href=\"$local\">{$thr[0]}</a>";
        $check       = "";
    }
    $check .= "<input type=\"submit\" name=\"del\" value=\"Remove\"/>";
    echo <<<ENDHTML
    <form action="" method="POST">
    <input type="hidden" name="id" value="{$thr[0]}"/>
    <input type="hidden" name="brd" value="{$thr[1]}"/>
	<tr>
		<td>$link</td>
		<td>{$thr[1]}</td>
		<td><input type="text" name="desc" value="{$thr[4]}"/><input type="submit" name="upd" value="Update"/></td>
		<td>$status</td>
		<td>$lastchecked</td>
		<td>$check</td>
	</tr>
    </form>
ENDHTML;
}
?>
</table>
<br />
<form action="" method="POST">
<table border="1" bordercolor="#FFCC00" style="background-color:#FFFFCC" width="610" cellpadding="3" cellspacing="3">
	<tr>
        <td><b>Add Thread</b></td>
    </tr>
    <tr>
        <td>Thread URL: <input type="text" name="url" size="60" /></td>
    </tr>
    <tr>
        <td>Thread Description: <input type="text" name="desc" size="60" /></td>
        <td><input type="submit" name="submit" value="Add"/></td>
    </tr>
</table>
</form>
<font size="1" family="Verdana">Downloaded from <a href="http://github.com/emoose/4chan-archiver/">github.com/emoose/4chan-archiver</a>. <a href="javascript:alert('nah just kidding');">check for updates?</a></font>
</body>
</html>
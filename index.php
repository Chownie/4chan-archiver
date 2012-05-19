<?php
session_start();
include "chan_archiver.php";
$t = new chan_archiver();
echo <<<ENDHTML
<html>
<head>
<title>4chan archiver - by anon e moose</title>
</head>
<body>
<a href="http://github.com/emoose/4chan-archiver/"><h2>4chan archiver - by anon e moose</h2></a>
<p>
ENDHTML;

// login stuff
if ( isset( $_REQUEST[ 'login' ] ) && isset( $_REQUEST[ 'user' ] ) && isset( $_REQUEST[ 'pass' ] ) )
{
    $_SESSION[ 'uname' ] = $_REQUEST[ 'user' ];
    $_SESSION[ 'pword' ] = $_REQUEST[ 'pass' ];
}

$isloggedin = ( isset( $_SESSION[ 'uname' ] ) && isset( $_SESSION[ 'pword' ] ) && $_SESSION[ 'uname' ] == $archiver_config[ 'login_user' ] && $_SESSION[ 'pword' ] == $archiver_config[ 'login_pass' ] ) || !$archiver_config[ 'login_enabled' ];
$delenabled = ( !$archiver_config[ 'login_del' ] || $isloggedin );
$chkenabled = ( !$archiver_config[ 'login_chk' ] || $isloggedin );
$addenabled = ( !$archiver_config[ 'login_add' ] || $isloggedin );

if ( $delenabled && isset( $_REQUEST[ 'del' ] ) && isset( $_REQUEST[ 'id' ] ) && isset( $_REQUEST[ 'brd' ] ) )
    $t->removeThread( $_REQUEST[ 'id' ], $_REQUEST[ 'brd' ] );

if ( $chkenabled && isset( $_REQUEST[ 'chk' ] ) && isset( $_REQUEST[ 'id' ] ) && isset( $_REQUEST[ 'brd' ] ) )
    $t->updateThread( $_REQUEST[ 'id' ], $_REQUEST[ 'brd' ] );

if ( $delenabled && isset( $_REQUEST[ 'upd' ] ) && isset( $_REQUEST[ 'id' ] ) && isset( $_REQUEST[ 'brd' ] ) )
    $t->setThreadDescription( $_REQUEST[ 'id' ], $_REQUEST[ 'brd' ], $_REQUEST[ 'desc' ] );

if ( $addenabled && isset( $_REQUEST[ 'add' ] ) && isset( $_REQUEST[ 'url' ] ) && $c = preg_match_all( "/.*?(?:[a-z][a-z0-9_]*).*?(?:[a-z][a-z0-9_]*).*?(?:[a-z][a-z0-9_]*).*?(?:[a-z][a-z0-9_]*).*?((?:[a-z][a-z0-9_]*)).*?(\d+)/is", $_REQUEST[ 'url' ], $matches ) )
    $t->addThread( $matches[ 2 ][ 0 ], $matches[ 1 ][ 0 ], $_REQUEST[ 'desc' ] );

echo "</p>";

if ( !$isloggedin )
{
    echo <<<ENDHTML
<form action="" method="POST">
<table border="1" bordercolor="#FFCC00" style="background-color:#FFFFCC" width="340" cellpadding="3" cellspacing="3">
	<tr>
        <td><b>Admin Login</b></td>
    </tr>
    <tr>
        <td>Username: <input type="text" name="user" size="20" /></td>
    </tr>
    <tr>
        <td>Password: <input type="password" name="pass" size="20" /></td>
        <td><input type="submit" name="login" value="Login"/></td>
    </tr>
</table>
</form>
ENDHTML;

}
else if ( $archiver_config[ 'login_enabled' ] )
{
    
    echo <<<ENDHTML
<form action="" method="POST">
<input type="hidden" name="user" value="" />
<input type="hidden" name="pass" value="" />
<input type="submit" name="login" value="Logout"/>
ENDHTML;
}

$threads = $t->getThreads();
echo <<<ENDHTML
<table border="1" bordercolor="#FFCC00" style="background-color:#FFFFCC" width="900" cellpadding="3" cellspacing="3">
	<tr>
		<td>Thread ID</td>
		<td>Board</td>
		<td>Description</td>
		<td>Status</td>
		<td>Last Checked</td>
		<td>Last Post</td>
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
    $check  = $chkenabled ? "<input type=\"submit\" name=\"chk\" value=\"Check\"/>" : "";
    $desc   = $delenabled ? "<input type=\"text\" name=\"desc\" value=\"{$thr[4]}\"/><input type=\"submit\" name=\"upd\" value=\"Update\"/>" : $thr[ 4 ];
    if ( $thr[ 2 ] == 0 )
    {
        $lastchecked = "";
        $link        = "<a href=\"$local\">{$thr[0]}</a>";
        $check       = "";
    }
    $check .= $delenabled ? "<input type=\"submit\" name=\"del\" value=\"Remove\"/>" : "";
    $lastpost = date( "m/d/y, g:i a", $thr[ 5 ] );
    if ( $thr[ 5 ] == "" || $thr[ 5 ] <= 0 )
        $lastpost = "N/A";
    echo <<<ENDHTML

    <form action="" method="POST">
    <input type="hidden" name="id" value="{$thr[0]}"/>
    <input type="hidden" name="brd" value="{$thr[1]}"/>
	<tr>
		<td>$link</td>
		<td>{$thr[1]}</td>
		<td>$desc</td>
		<td>$status</td>
		<td>$lastchecked</td>
		<td>$lastpost</td>
		<td>$check</td>
	</tr>
    </form>
ENDHTML;
}

echo "</table><br />";

if ( $addenabled )
{
    echo <<<ENDHTML
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
        <td><input type="submit" name="add" value="Add"/></td>
    </tr>
</table>
</form>
ENDHTML;
}
?>
<font size="1" family="Verdana">Downloaded from <a href="http://github.com/emoose/4chan-archiver/">github.com/emoose/4chan-archiver</a>. <a href="javascript:alert('nah just kidding');">check for updates?</a></font>
</body>
</html>
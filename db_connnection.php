<?php

function OpenCon()
{
$dbhost = "localhost"; //hostname
$dbuser = "root"; //username
$dbpass = ""; //pass
$db = "astrobot"; //database
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db) or die("Connect failed: %s\n". mysqli_error($conn));

return $conn;
}

function CloseCon($conn)
{
mysqli_close($conn);
}

?>
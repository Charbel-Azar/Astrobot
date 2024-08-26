<?php
function sanitizeInputVar($conn, $var){
	$var = stripslashes($var);
	$var = htmlentities($var);
	$var = strip_tags($var);
	$var = mysqli_real_escape_string($conn, $var);
	return $var;
}
?>
<?php



session_start();

$_SESSION = array();
session_unset();



header("Location: pdoweb.php");
exit();
?>
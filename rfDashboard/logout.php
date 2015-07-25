<?php
session_start();
$_SESSION["sess_username"]="";
session_destroy();
session_unset();
header("location: index.php");
exit;
?>
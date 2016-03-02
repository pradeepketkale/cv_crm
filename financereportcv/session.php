<?php
session_start();
$user_check=$_SESSION['login_user'];
if(!isset($user_check)){
/*	$writeCon->closeConnection();
	$readcon->closeConnection();*/ // Closing Connection
	header('Location: index.php'); // Redirecting To Home Page
}
?>
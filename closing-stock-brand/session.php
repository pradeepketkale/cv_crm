<?php
session_start();
if(!isset($_SESSION['login_user_vend'])){
/*	$writeCon->closeConnection();
	$readcon->closeConnection();*/ // Closing Connection
	header('Location: index.php'); // Redirecting To Home Page
}
?>

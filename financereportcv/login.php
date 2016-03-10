<?php
//require_once __DIR__.'/dbConnectionRead.php';
$rootPath = dirname(__DIR__);
var_dump($rootPath."/dbConnectionRead.php");exit;
require_once ($rootPath."/dbConnectionRead.php");
session_start(); // Starting Session
$error=''; // Variable To Store Error Message
if (isset($_POST['submit'])) {

	if (empty($_POST['username']) || empty($_POST['password'])) {
	$error = "Username or Password is invalid";
	}
	else
	{
	$username=$_POST['username'];
	$password=$_POST['password'];
	$password = md5("craftskufin2017".$password);
	$ses_sql = "SELECT `user_id` FROM `finance_login` WHERE `user_email` = '".$username."' AND `user_password` = '".$password."'";
	$query = mysql_query($ses_sql);
	$rows = mysql_num_rows($query);
	if ($rows == 1) {
		$user_id = mysql_fetch_assoc($query);
		$_SESSION['login_user']= $user_id['user_id']; // Initializing Session
		header("location: dashboard.php"); // Redirecting To Other Page
	} else {
		$error = "Username or Password is invalid";
	}
		//$readcon->closeConnection(); // Closing Connection
	}
}
?>
<?php
/*require_once __DIR__.'../app/Mage.php';
Mage::app();
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$writeCon = Mage::getSingleton('core/resource')->getConnection('core_write');*/
session_start();
$user_check=$_SESSION['login_user'];
/*$ses_sql = "SELECT `user_id` FROM `finance_login` WHERE `user_id` = '".$user_check."'";
//var_dump($ses_sql);exit;
$row = $readcon->query($ses_sql)->fetch();*/

//$login_session =$row['user_id'];
//var_dump($row);exit;
if(!isset($user_check)){
/*	$writeCon->closeConnection();
	$readcon->closeConnection();*/ // Closing Connection
	header('Location: index.php'); // Redirecting To Home Page
}

// $sfsStatementUpdate = "UPDATE `sales_flat_shipment` SET `statement_id`='".$stmtId."' WHERE `increment_id` = '".$incId."'";
// $writeCon->query($sfsStatementUpdate);


/*// Establishing Connection with Server by passing server_name, user_id and password as a parameter
$connection = mysql_connect("localhost", "root", "");
// Selecting Database
$db = mysql_select_db("company", $connection);
session_start();// Starting Session
// Storing Session
$user_check=$_SESSION['login_user'];
// SQL Query To Fetch Complete Information Of User
$ses_sql=mysql_query("select username from login where username='$user_check'", $connection);
$row = mysql_fetch_assoc($ses_sql);
$login_session =$row['username'];
if(!isset($login_session)){
mysql_close($connection); // Closing Connection
header('Location: index.php'); // Redirecting To Home Page
}*/
?>
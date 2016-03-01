<?php
	session_start();
	unset($_SESSION['ID']);
	 
	header("location: login.html");
		exit();
?>

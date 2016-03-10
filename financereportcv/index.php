
<?php
if(isset($_SESSION['login_user'])){
	header("location: dashboard.php");
}
require_once __DIR__.'/../dbConnectionRead.php';
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
	$query = mysql_query($ses_sql,$mainConnection);
	$rows = mysql_num_rows($query);
	if ($rows == 1) {
		$user_id = mysql_fetch_assoc($query);
		$_SESSION['login_user']= $user_id['user_id']; // Initializing Session
		header("location: dashboard.php"); // Redirecting To Other Page
	} else {
		$error = "Username or Password is invalid";
	}
		mysql_close($mainConnection); // Closing Connection
	}
} // Includes Login Script


?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title></title>
</head>
<link rel="stylesheet" href="css/style.css"/>
<body>
	
	<div class="wrapper_body">
		<div class="login_body">
				
			<div class="login-form">
				  <form action="" method='post'>
					
					<!---	<div class="logo-wrapper">
						
							<div class="logo"></div>
							
						</div> -->

							<div style="border-bottom: 1px solid #e1e1e1;">
							
							<div class="logo"></div>

						</div>	

							<div class="form-area">
						
								<div class="group">
									<input type="text" name='username' class="form-control" placeholder="Username">
								</div>
							<div class="group">
								<input type="password" name='password' class="form-control" placeholder="Password">
						  </div>
						  
						  <button type="submit" name='submit' class="btn btn-default btn-block btn_wdt">LOGIN</button>
						</div>
						<span style = "color:red";> <?php echo $error; ?></span>
				  </form>
		 
			</div>	
		</div>
	</div>
	
</body>
</html>

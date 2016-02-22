
<?php
include('login.php'); // Includes Login Script

if(isset($_SESSION['login_user'])){
header("location: dashboard.php");
}
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
					
						<div class="logo-wrapper">
						
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

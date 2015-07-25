<?php
//include('connection.php');
error_reporting(E_ALL & ~E_NOTICE);
session_start();
require_once '../app/Mage.php';

Varien_Profiler::enable();
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);

//umask(0);
Mage::app();

if(isset($_POST['formsubmit']))
{
	$read = Mage::getSingleton('core/resource')->getConnection('raf_login');
	//$sql = "SELECT * FROM raf_login where username='".mysql_real_escape_string($_POST["txtUsername"])."' and password='".mysql_real_escape_string($_POST["txtPassword"])."' LIMIT 1;";
	
	/*$result=mysql_query($sql);
	if(mysql_num_rows($result)>0)
	{
		$_SESSION["sess_username"]=$_POST["txtUsername"];
		header("Location:search.php");
		exit;
	}else{
		echo 'Wrong userid/Password';
		//header("location: index.php");
	}*/
	
	$where_cond = "username = '".$_POST["txtUsername"]."' AND password='".$_POST["txtPassword"]."'";
	$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('utm_admin');
			$select_1 = $read->select()->from('utm_admin')
								   ->where($where_cond);
	
	$result = $read->fetchRow($select_1);
	if($result)
	{
		$_SESSION["sess_username"]=$_POST["txtUsername"];
		header("Location:search.php");
		exit;
	}else{
		echo 'Wrong userid/Password';
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Craftsvilla</title>

<style type="text/css">
.contnt{ width:980px; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#444; margin:0 auto;}
.gride{ background:#ccc; width:100%;}
.gride tr td{ background:#fff; padding:3px 5px;}
.gride tr.altnet td{ background:#f8f8f8;}
.gride tr th{ background:#E9E8E8; padding:3px 5px;}
input,select{ color:#444; font-family:Arial, Helvetica, sans-serif; border:1px solid #ccc; width:214px; padding:3px;}
input.date{ width:70px; text-align:center;}
input[type="submit"]{ width:auto; background:#999; color:#fff; font-weight:bold; outline:1px solid #666; border-color:#fff; cursor:pointer;}
select{ width:118px;}
.rightaligne tr td{ text-align:right;}
.rightaligne tr td.left{ text-align:left;}
.logo {background: url(images/home-sprite.png) no-repeat scroll -20px -215px transparent; height: 134px;width: 223px; display:block; margin:0 auto 20px;}
.roller{ border-bottom:1px solid #ccc; margin-bottom:20px; text-align:right;}
.contnt a{ text-decoration:none; color:#444;}
.rightaligne{ margin:0 auto 20px;}
.pagination{ margin:0;}
.paddbot{ padding-bottom:10px;}
.login{background:#f8f8f8; padding:25px;-moz-border-radius: 15px;
border-radius: 15px; width:50%; margin:70px auto 100px;}
.login h2{border-bottom: 1px dotted #CCCCCC;
    font-weight: normal;
    margin: 0 0 12px;
    padding: 0 0 6px;}
</style>



</head>

<body>
<div class="contnt">

<a class="logo" href="#"></a>
<p class="roller">&nbsp;</p>

<div class="login">
<h2>Log in</h2>
<form method="post" name="fm" onsubmit="return valid()">
<table border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td>User Name:</td>
    <td><input type="text" name="txtUsername" /></td>
  </tr>
  <tr>
    <td>Password:</td>
    <td><input type="password" name="txtPassword"/></td>
  </tr>
<tr><td></td>
	<td><!--<a href="search.php">--><input type="submit" name="formsubmit" value="submit" /><!--</a>--></td></tr>
</table>
</form>


</div>





<p align="center">&copy; <a href="http://www.craftsvilla.com">craftsvilla.com.</a> All rights reserved.</p>
  
</div>

<script language="javascript" type="text/javascript">

function valid()
{
	var txtUsername=document.fm.txtUsername.value;
	if(txtUsername=="")
	{
		alert("Please Enter Valid UserName");
		document.fm.txtUsername.focus();
		return(false);
	}
	
	if(txtUsername.length>50)
	{
		alert("pls enter valid UserName");
		document.fm.txtUsername.focus();
		return(false);
	}
var txtPassword=document.fm.txtPassword.value;
//var rpassword=document.fm.rpassword.value;
	if(password=="")
	{
	 alert("Please Enter Valid Password");
   	 document.fm.txtPassword.focus();
   	 return(false);
	}
	 if(password.length<1)
	{
	 alert("Please Enter min some char password");
   	 document.fm.txtPassword.focus();
   	 return(false);
	}
	if(password.length>50)
	{
	 alert("Pls enter max 50 char");
   	 document.fm.txtPassword.focus();
   	 return(false);
	}
	//if(password!=rpassword)
	//{
	 //alert("Pls enter same password");
   	 //document.fm.rpassword.focus();
   	 //return(false);
   	 //}
	 return true;
}
</script>
</body>
</html>
<?php
//mysql_close();
?>
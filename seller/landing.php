<?php
$name=$_REQUEST["name"];
$email=$_REQUEST["email"];
$number=$_REQUEST["number"];
$city=$_REQUEST["city"];
$utmSource=$_REQUEST["utm_source"];
$utmMedium=$_REQUEST["utm_medium"];
$utmCampaign=$_REQUEST["utm_campaign"];
$sub=$_REQUEST["submit"];

$con = mysql_connect("newserver1.cpw1gtbr1jnd.ap-southeast-1.rds.amazonaws.com","poqfcwgwub","2gvrwogof9vnw");
$db=mysql_select_db("nzkrqvrxme");
if($con)
{
$qr="insert into selleraccount(`name`, `email`, `number`, `city`, `utm_source`, `utm_medium`, `utm_campaign`) values('$name','$email','$number','$city','$utmSource','$utmMedium','$utmCampaign')";
$res=mysql_query($qr,$con);
echo "Increment Id :". @mysql_insert_id(); //exit;
header("location: thank_you.html");
//email 
$to = "Craftsvilla Places<places@craftsvilla.com>"; 
$subject = "Lead Number-" . @mysql_insert_id(); 
//$email = $_REQUEST['email'] ; 
$message = "Email:	$email
			Name:	$name
			Number:	$number,
			City:	$city"; 
$headers = "From: $email"; 
//$headers = "From: $email" . "\r\n" .
//"CC: tirath@craftsvilla.com";
$sent = mail($to, $subject, $message, $headers) ; 
if($sent) 
	{
	print "Your mail was sent successfully"; 
	}
	 else 
	 {
		 print "We encountered an error sending your mail"; 
	 } 

}
mysql_close($con);
?>

<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$db = Mage::getSingleton('core/resource')->getConnection('core_read');

	$contents .= 'vendor_id,vendor_name,telephone,email';
          $contents .= "\n";
$sql = "select DISTINCT udropship_vendor from sales_flat_shipment where `udropship_status` = '11'";

$result = $db->query($sql)->fetchAll();
 $body .='<table border="1"><th>Vendor_Id</th><th>Vendor_Name</th><th>Telephone</th><th>EmailId</th>';
   
 //while($row = mysql_fetch_array($result))
  foreach($result as $row)
 {      
	   
	     $count = "select * from `sales_flat_shipment` where `udropship_vendor` = '".$row['udropship_vendor']."' AND `udropship_status` IN ('1','15', '16', '17')";

	  //$res = mysql_query($count);
 	  $res = $db->query($count);
	  $res1 = mysql_num_rows($res);
	   
	
	  if($res1 == '0')
	 {
		$vendorquery = "select * from `udropship_vendor` where `vendor_id` = '".$row['udropship_vendor']."'";
		  //$res2 = mysql_query($vendorquery);
		  $res2 = $db->query($vendorquery)->fetchAll();
		
		 //while($row1 = mysql_fetch_array($res2))
		  foreach($res2 as $row1)
		 {
			    $contents .=''.$row1['vendor_id'].',';
				$contents .=''.$row1['vendor_name'].',';
				$contents .=''.$row1['telephone'].',';
				$contents .=''.$row1['email'].',';
				$contents .= "\n";
		        $body .='<tr><td>'.$row1['vendor_id'].'</td><td>'.$row1['vendor_name'].'</td><td>'.$row1['telephone'].'</td><td>'.$row1['email'].'</td><td>';
				
		 
		 }
   
	 }
 }


	$fp=fopen('/var/www/html/media/misreport/firstshipment.csv','w');
		fputs($fp, $contents);
    	fclose($fp);

$message = "http://www.craftsvilla.com/media/misreport/firstshipment.csv";
 $body .='</table>';
	$body .= '<a href="http://www.craftsvilla.com/media/misreport/firstshipment.csv">click here to download the Excel</a>';
	$mail = Mage::getModel('core/email');
	$mail->setToName('Craftsvilla Places');
	$mail->setToEmail('monica@craftsvilla.com');
	$mail->setBody($body);
	$mail->setSubject($message);
	$mail->setFromEmail('places@craftsvilla.com');
	$mail->setFromName("Tech");
	$mail->setType('html');
	
	if($mail->send())
	{echo "Email sent to your emailid sucessfully";}
	else {echo "Failed to send Email";}

?>


	

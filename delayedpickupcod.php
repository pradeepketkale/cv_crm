<?php

require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$orderDetail = "SELECT sfs.created_at,sfs.entity_id,sfs.increment_id,sfs.udropship_vendor,sfst.number FROM sales_flat_shipment sfs LEFT JOIN sales_flat_shipment_track sfst ON sfs.entity_id = sfst.parent_id where sfs.udropship_status = 24 AND sfs.updated_at BETWEEN (DATE_SUB(NOW(),INTERVAL 5 DAY)) AND (DATE_SUB(NOW(),INTERVAL 4 DAY))";

$trackdetails =  $db->query($orderDetail)->fetchAll();
//print_r($trackdetails);
//exit;

$message1 =
'
<p> Total Shipments Accepted But Not Picked within 4 Days After Accepting COD Shipment</p>
<table border=1>
<tr bgcolor=#ccc>
<th width=200>Shipment #</th>
<th width=200>Created At</th>
<th width=200>Track</th>
<th width=200>Seller Name</th>
<th width=200>Phone Number</th>
<th width=200>Email</th>
<th width=200>Seller Address</th>
</tr>
';
$message2 = "";
$k = 0;
//while($result = mysql_fetch_array($trackdetails)){
foreach($trackdetails as $result)
$uvid = $result["udropship_vendor"];
$track = $result["number"];
$incrementid = $result["increment_id"];
$datecreated = $result["created_at"];
//echo $vid;


$vendorDetails = "SELECT vendor_id,vendor_name,email,telephone,street,city FROM udropship_vendor where vendor_id = '".$uvid."'";
$vendorInfo = $db->query($vendorDetails);
$row = $vendorInfo->fetch();
$vendorname = $row["vendor_name"];
//$address = $row["street"];
$address = $row["city"];
$email = $row["email"];
$phonenumber = $row["telephone"];
$message2 .= 
"
<tr bgcolor=#ccc>
<td width=200>".$incrementid."</td>
<td width=200>".$datecreated."</td>
<td width=200>".$track."</td>
<td width=200>".$vendorname."</td>
<td width=200>".$email."</td>
<td width=200>".$phonenumber."</td>
<td width=200>".$address."</td>
</tr>
";
$k++;
}
$message = $message1.$message2."</table>";

//echo "$message";
					$mail = Mage::getModel('core/email');

					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($message);
					$mail->setSubject('COD Pickup Delayed Report:: Shipments Delayed - '.$k);
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName("Craftsvilla Logistics");
					$mail->setType('html');
					//$mailerror = $mail->send();
					//print_r($mailerror); exit;

					if($mail->send())
	{
					$mail->setToEmail('monica@craftsvilla.com');
					$mail->send();
					//$mail->setToEmail('monica@craftsvilla.com');
					//$mail->send();
	echo "Email sent to your emailid sucessfully";
	}
	else {
	echo "Failed to send Email";
	}
echo "<br>";
//exit;
?> 

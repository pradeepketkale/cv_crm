<?php

require_once 'app/Mage.php';
Mage::app();

$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$orderDetail = "SELECT v.vendor_name,v.vendor_id,COUNT(s.entity_id) AS count FROM udropship_vendor AS v LEFT JOIN sales_flat_shipment AS s ON v.vendor_id = s.udropship_vendor AND s.udropship_status = 20 AND s.created_at > (DATE_SUB(NOW(),INTERVAL 90 DAY)) group by vendor_id order by count desc LIMIT 50";

$resultorderDetail =  $db->query($orderDetail)->fetchAll();

$totalDisputeQuery = "SELECT count(*) AS countdis FROM `sales_flat_shipment` WHERE `udropship_status` = '20'";
$totalDisputeQueryRes1 =  $db->query($totalDisputeQuery);
$totalDisputeQueryRes = $totalDisputeQueryRes1->fetch();
$totalDisputeNum = $totalDisputeQueryRes['countdis'];
echo "Total Disputed:".$totalDisputeNum;

$totalDisputeQuery30 = "SELECT count(*) AS countdis FROM `sales_flat_shipment` WHERE `udropship_status` = '20' AND created_at < (DATE_SUB(NOW(),INTERVAL 30 DAY))";
$totalDisputeQueryRes130 =  $db->query($totalDisputeQuery30);
$totalDisputeQueryRes30 = $totalDisputeQueryRes130->fetch();
$totalDisputeNum30 = $totalDisputeQueryRes30['countdis'];
echo "Total Disputed Older Than 30 Days:".$totalDisputeNum30;

$message1 =   
'<html>
<head>
<title>welcome</title>
</head>
<body>
<table border=1>
<tr bgcolor=#ccc>
<th width=300>Vendor Name</th>
<th width=200>Total Disputed Shipments</th>
<th width=200>Disputed Older Than 30 Days</th>
<th width=200>Total  Shipments</th>
<th width=200>Percentage (%)</th>
</tr>
</table>
</body>
</html>';
$message2 = "Total Disputed Older Than 30 Days:".$totalDisputeNum30."<br><br>";;
//while($row = mysql_fetch_array($resultorderDetail)){
 foreach($resultorderDetail as $row){
 $vendorId = $row["vendor_id"];

$totalshipment = "select COUNT(entity_id) AS count1 FROM sales_flat_shipment where udropship_vendor = '".$vendorId."' AND created_at > (DATE_SUB(NOW(),INTERVAL 90 DAY)) ";echo "<pre>";
$resultshipment =  $db->query($totalshipment);
$totalshipment30 = "select COUNT(entity_id) AS count1 FROM sales_flat_shipment where udropship_vendor = '".$vendorId."' AND created_at < (DATE_SUB(NOW(),INTERVAL 30 DAY)) AND udropship_status = 20 ";
$resultshipment30 =  $db->query($totalshipment30);

$row1 = $resultshipment->fetch();
$row11 = $resultshipment30->fetch();
$count1 = $row1["count1"];
$count = $row["count"];
$count2 = $row11["count1"];
$per = round(( $count/$count1)*100);
	$message2 .= "
<html>
<head>
<title>Processing</title>
</head>
<body>
<table border=1>
<tr>
<td width=300>".$row["vendor_name"]."</td>
<td align='center'width=200> ".$row["count"]."</td>
<td align='center'width=200> ".$row11["count1"]."</td>
<td align='center'width=200> ".$row1["count1"]."</td>
<td align='center'width=200> ".$per."%</td>
</table>
</body>
</html>";

}
$message = $message1.$message2; 
//echo "$message";
					$mail = Mage::getModel('core/email');

					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($message);
					$mail->setSubject("Stats:Top Sellers By Dispute Raised, Total Disputed:".$totalDisputeNum);
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName("Craftsvilla Stats");
					$mail->setType('html');
					//$mailerror = $mail->send();
					 

					if($mail->send())
					{
						echo 'Email has been send successfully';
					$mail->setToEmail('monica@craftsvilla.com');
					$mail->send();
					$mail->setToEmail('quality@craftsvilla.com');
					$mail->send();
					}
					else 
					echo "error";


?> 

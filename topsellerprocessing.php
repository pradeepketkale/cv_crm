<?php

require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$orderDetail = "SELECT v.vendor_name,v.vendor_id,COUNT(s.entity_id) AS count FROM udropship_vendor AS v LEFT JOIN sales_flat_shipment AS s ON v.vendor_id = s.udropship_vendor AND s.udropship_status = 11 AND s.created_at > (DATE_SUB(NOW(),INTERVAL 15 DAY)) group by vendor_id order by count desc LIMIT 50";

$resultorderDetail = $db->query($orderDetail)->fetchAll();

$totalDisputeQuery = "SELECT count(*) AS countdis FROM `sales_flat_shipment` WHERE `udropship_status` = '11'";
$totalDisputeQueryRes1 = $db->query($totalDisputeQuery);
$totalDisputeQueryRes = $totalDisputeQueryRes1->fetch();
$totalProcNum = $totalDisputeQueryRes['countdis'];
echo "Total Processing:".$totalProcNum;

$totalDisputeQuery30 = "SELECT count(*) AS countdis FROM `sales_flat_shipment` WHERE `udropship_status` = '11' AND created_at < (DATE_SUB(NOW(),INTERVAL 3 DAY))";
$totalDisputeQueryRes130 = $db->query($totalDisputeQuery30);
$totalDisputeQueryRes30 = $totalDisputeQueryRes130->fetch();
$totalProcNum3 = $totalDisputeQueryRes30['countdis'];
echo "Total Processing Older Than 3 Days:".$totalProcNum3;

$totalDisputeQuery30 = "SELECT count(*) AS countdis FROM `sales_flat_shipment` WHERE `udropship_status` = '11' AND created_at < (DATE_SUB(NOW(),INTERVAL 5 DAY))";
$totalDisputeQueryRes130 = $db->query($totalDisputeQuery30);
$totalDisputeQueryRes30 = $totalDisputeQueryRes130->fetch();
$totalProcNum5 = $totalDisputeQueryRes30['countdis'];
echo "Total Processing Older Than 5 Days:".$totalProcNum5;

$totalDisputeQuery30 = "SELECT count(*) AS countdis FROM `sales_flat_shipment` WHERE `udropship_status` = '11' AND created_at < (DATE_SUB(NOW(),INTERVAL 10 DAY))";
$totalDisputeQueryRes130 = $db->query($totalDisputeQuery30);
$totalDisputeQueryRes30 = $totalDisputeQueryRes130->fetch();
$totalProcNum10 = $totalDisputeQueryRes30['countdis'];
echo "Total Processing Older Than 10 Days:".$totalProcNum10;



$message3 =  "Total Processing Older Than 3 Days:".$totalProcNum3."<br>";
$message3 .=  "Total Processing Older Than 5 Days:".$totalProcNum5."<br>";
$message3 .=  "Total Processing Older Than 10 Days:".$totalProcNum10."<br>";
$message1 =   
'<table border=1>
<tr bgcolor=#ccc>
<th width=300>Vendor Name</th>
<th width=200>Total Processing Shipments</th>
<th width=200>Processing Older Than 3 Days</th>
<th width=200>Total  Shipments</th>
<th width=200>Percentage (%)</th>
<th width=200>Email</th>
<th width=200>Phone</th>
</tr>
';
$message2 =  "";
//while($row = mysql_fetch_array($resultorderDetail)){
foreach($resultorderDetail as $row){
$messageseller = "Please process your orders ASAP to avoid penalities and stoppage of marketing of your products on Craftsvilla<br><br>".$message1;
 $vendorId = $row["vendor_id"];

 $emailquery = "select * from udropship_vendor where `vendor_id` = '".$vendorId."' ";
$resultemail = $db->query($emailquery);
$row1 = $resultemail->fetch();
$email = $row1["email"];
$phone = $row1["telephone"];


$totalshipment = "select COUNT(entity_id) AS count1 FROM sales_flat_shipment where udropship_vendor = '".$vendorId."' AND created_at > (DATE_SUB(NOW(),INTERVAL 15 DAY)) ";
$resultshipment = $db->query($totalshipment);

$totalshipment3 = "select COUNT(entity_id) AS count1 FROM sales_flat_shipment where udropship_vendor = '".$vendorId."' AND created_at < (DATE_SUB(NOW(),INTERVAL 3 DAY)) AND udropship_status = 11 ";
$resultshipment3 = $db->query($totalshipment3);

$row1 = $resultshipment->fetch();
$row11 = $resultshipment3->fetch();
 $count1 = $row1["count1"];
 $count = $row["count"];
$per = round(( $count/$count1)*100);
	$message2 .= "
<tr>
<td width=300>".$row["vendor_name"]."</td>
<td align='center'width=200> ".$row["count"]."</td>
<td align='center'width=200> ".$row11["count1"]."</td>
<td align='center'width=200> ".$row1["count1"]."</td>
<td align='center'width=200> ".$per."%</td>
<td align='center'width=200> ".$email."</td>
<td align='center'width=200> ".$phone."</td>
</tr>
";

$messageseller .=  "
<tr>
<td width=300>".$row["vendor_name"]."</td>
<td align='center'width=200> ".$row["count"]."</td>
<td align='center'width=200> ".$row11["count1"]."</td>
<td align='center'width=200> ".$row1["count1"]."</td>
<td align='center'width=200> ".$per."%</td>
<td align='center'width=200> ".$email."</td>
<td align='center'width=200> ".$phone."</td>
</tr></table>
";

$mailseller = Mage::getModel('core/email');
$mailseller->setToName($row["vendor_name"]);
$mailseller->setToEmail($email);
$mailseller->setBody($messageseller);
$mailseller->setSubject("Stats:Total Craftsvilla Shipments In Processing Status:".$row["count"]);
$mailseller->setFromEmail('places@craftsvilla.com');
$mailseller->setFromName("Craftsvilla Stats");
$mailseller->setType('html');
$mailseller->send();
$mailseller->setToEmail('manoj@craftsvilla.com');
$mailseller->send();

}
$message2 .= "</table>";
$message = $message3.$message1.$message2;
//echo "$message";
					$mail = Mage::getModel('core/email');

					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($message);
					$mail->setSubject("Stats:Top Sellers By Processing Last 15 Days, Total Processing:".$totalProcNum);
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName("Craftsvilla Stats");
					$mail->setType('html');
					//$mailerror = $mail->send();
					//print_r($mailerror); exit;

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

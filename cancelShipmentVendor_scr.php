<?php
require_once 'app/Mage.php';
Mage::app();
$hlp = Mage::helper('generalcheck');

$mainconn = $hlp->getMaindbconnection();


$message1 ='<table border=1>
			<tr bgcolor=#ccc>
			<th width=166>Vendor Id</th>
			<th width=200>Total canceled Shipments</th>
			</tr>';

$shipment_query = mysql_query("SELECT `udropship_vendor`, COUNT(*) as `total_count` FROM `sales_flat_shipment` WHERE `udropship_status` = 6 AND `updated_at` > DATE_SUB(Now(),INTERVAL 1 DAY) GROUP BY `udropship_vendor` ORDER BY `total_count` DESC",$mainconn);
$shipment_cnt_query = mysql_query("SELECT COUNT(*) as `total_can_count` FROM `sales_flat_shipment` WHERE `udropship_status` = 6 AND `updated_at` > DATE_SUB(Now(),INTERVAL 1 DAY) ",$mainconn);

$totalCanCountres = mysql_fetch_array($shipment_cnt_query);
$totalCanCount = $totalCanCountres['total_can_count'];


$message2 = '';
	while($shipment_result = mysql_fetch_array($shipment_query))
	{

		$Total_canceled_Shipments = $shipment_result['total_count'];
		echo $vendor_id = $shipment_result['udropship_vendor'];
		$vnamequery = mysql_query("SELECT `vendor_name` as `vendorname` FROM `udropship_vendor` where `vendor_id` = ".$vendor_id,$mainconn);
		$vendorNameres = mysql_fetch_array($vnamequery);
		//print_r($vendorNameres); exit;
		echo $vendorName = $vendorNameres['vendorname'];

		$message2 .= "  <tr>
						<td width=200 align='center'> ". $vendorName." </td>
						<td width=200 align='center'> ".$Total_canceled_Shipments." </td>
						</tr>";

	}

mysql_close($mainconn);
	$message = $message1.$message2."</table>";
//	echo $message;


					$mail = Mage::getModel('core/email');
					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($message);
					$mail->setSubject('Total Canceled Shipments Last 24Hr: '.$totalCanCount);
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName('Craftsvilla Tech');
					$mail->setType('html');
					if($mail->send())
					{
						echo 'Email has been send successfully';
					$mail->setToEmail('monica@craftsvilla.com');
					$mail->send();
					}
					else 
					echo "error";
?>

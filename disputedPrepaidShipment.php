<?php
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$prepaidQuery = $db->query( "SELECT sfs.order_id, sfs.entity_id, sfs.increment_id, sfs.updated_at, sfop.parent_id, sfst.number,sfst.parent_id FROM `sales_flat_shipment` as sfs LEFT JOIN 
`sales_flat_order_payment` as sfop ON sfs.order_id = sfop.parent_id  LEFT JOIN 
`sales_flat_shipment_track` as sfst ON sfs.entity_id = sfst.parent_id
WHERE sfs.udropship_status = 20 AND sfop.method != 'cashondelivery' AND sfst.number != 'NULL' AND sfs.updated_at < DATE_SUB(NOW(),INTERVAL 1 MONTH) ORDER BY sfs.entity_id ASC"\)->fetchAll(); 

$count = 0;
$k = 0;
echo $totalDisputed = "Total Disputed Prepaid Over 30 Days:".mysql_num_rows($prepaidQuery); 
$emailBody = $totalDisputed.'<br>Shipments Converted from Dispute Raised to Shipped to Customer Are:<br>';
//while($result  = mysql_fetch_array($prepaidQuery))
 foreach($prepaidQuery as $result)
{
	if($k < 50)
	{
	 $trackId = $result['number'];
	 $parentId = $result['parent_id'];
echo	 $incrementid = $result['increment_id'];
	 $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
	 //print_r($shipment); exit;
	 $count++;
	 $emailBody .= $incrementid."<br>";
	 $shipment->setUdropshipStatus(1);
	 Mage::helper('udropship')->addShipmentComment($shipment,('Dispute Raised removed and changed to Shipped To Customer By System Script.'));
	 $shipment->save();	
	}
	$k++;			
	
}

					$mail = Mage::getModel('core/email');

					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($emailBody);
					$mail->setSubject('Total '.$count.' Shipments converted from Dispute Raised');
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName('Craftsvilla Places');
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

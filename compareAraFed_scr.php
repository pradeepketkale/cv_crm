<?php
require_once 'app/Mage.php';
Mage::app();
$hlp = Mage::helper('generalcheck');

$mainconn = $hlp->getMaindbconnection();


$aramex_cancel_query = mysql_query("SELECT COUNT(*) as `aramex_cancel_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 6 AND track.`courier_name`='Aramex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);

$aramex_refund_ini_query = mysql_query("SELECT COUNT(*) as `aramex_refund_ini_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 12 AND track.`courier_name`='Aramex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);

$aramex_dispute_raised_query = mysql_query("SELECT COUNT(*) as `aramex_dispute_raised_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 20 AND track.`courier_name`='Aramex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);

$aramex_delivered_query = mysql_query("SELECT COUNT(*) as `aramex_delivered_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 7 AND track.`courier_name`='Aramex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);

$aramex_RTO_COD_query = mysql_query("SELECT COUNT(*) as `aramex_RTO_COD_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 25 AND track.`courier_name`='Aramex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);



$fedex_cancel_query = mysql_query("SELECT COUNT(*) as `fedex_cancel_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 6 AND track.`courier_name`='Fedex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);

$fedex_refund_ini_query = mysql_query("SELECT COUNT(*) as `fedex_refund_ini_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 12 AND track.`courier_name`='Fedex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);

$fedex_dispute_raised_query = mysql_query("SELECT COUNT(*) as `fedex_dispute_raised_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 20 AND track.`courier_name`='Fedex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);

$fedex_delivered_query = mysql_query("SELECT COUNT(*) as `fedex_delivered_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 7 AND track.`courier_name`='Fedex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);

$fedex_RTO_COD_query = mysql_query("SELECT COUNT(*) as `fedex_RTO_COD_count` FROM `sales_flat_shipment` as `shipment` LEFT JOIN `sales_flat_shipment_track` as `track` ON shipment.`entity_id` = track.`parent_id` WHERE shipment.`udropship_status` = 25 AND track.`courier_name`='Fedex' AND shipment.`updated_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)",$mainconn);



mysql_close($mainconn);


$aramex_cancel_result = mysql_fetch_array($aramex_cancel_query);
$aramex_refund_ini_result = mysql_fetch_array($aramex_refund_ini_query);
$aramex_dispute_raised_result = mysql_fetch_array($aramex_dispute_raised_query);
$aramex_delivered_result = mysql_fetch_array($aramex_delivered_query);
$aramex_RTO_COD_result = mysql_fetch_array($aramex_RTO_COD_query);



$fedex_cancel_result = mysql_fetch_array($fedex_cancel_query);
$fedex_refund_ini_result = mysql_fetch_array($fedex_refund_ini_query);
$fedex_dispute_raised_result = mysql_fetch_array($fedex_dispute_raised_query);
$fedex_delivered_result = mysql_fetch_array($fedex_delivered_query);
$fedex_RTO_COD_result = mysql_fetch_array($fedex_RTO_COD_query);



echo $aramex_cancel = $aramex_cancel_result['aramex_cancel_count'];
echo $aramex_refund_ini = $aramex_refund_ini_result['aramex_refund_ini_count'];
echo $aramex_dispute_raised = $aramex_dispute_raised_result['aramex_dispute_raised_count'];
$aramex_RTO_COD = $aramex_RTO_COD_result['aramex_RTO_COD_count'];
$aramex_delivered = $aramex_delivered_result['aramex_delivered_count'];

$aramex_total = $aramex_cancel + $aramex_refund_ini + $aramex_dispute_raised + $aramex_RTO_COD + $aramex_delivered;
$aramex_RTO_prcnt  = round((($aramex_RTO_COD/$aramex_total)* 100),2);

$fedex_cancel = $fedex_cancel_result['fedex_cancel_count'];
$fedex_refund_ini = $fedex_refund_ini_result['fedex_refund_ini_count'];
$fedex_dispute_raised = $fedex_dispute_raised_result['fedex_dispute_raised_count'];
$fedex_RTO_COD = $fedex_RTO_COD_result['fedex_RTO_COD_count'];
$fedex_delivered = $fedex_delivered_result['fedex_delivered_count'];

$fedex_total = $fedex_cancel + $fedex_refund_ini + $fedex_dispute_raised + $fedex_RTO_COD + $fedex_delivered;
$fedex_RTO_prcnt  = round((($fedex_RTO_COD/$fedex_total)* 100),2);

$message ='<table border=1>
			<tr bgcolor=#ccc>
			<th width=200>Process Status</th>
			<th width=120>Aramex</th>
			<th width=120>Fedex</th>
			</tr>

			<tr>
			<th bgcolor=#ccc width=120>Cancel</th><td>'.$aramex_cancel.'</td><td>'.$fedex_cancel.'</td>
			</tr>
			<tr>
			<th bgcolor=#ccc width=120>Refund Initiated</th><td>'.$aramex_refund_ini.'</td><td>'.$fedex_refund_ini.'</td>
			</tr>
			<tr>
			<th bgcolor=#ccc width=120>Dispute Raised</th><td>'.$aramex_dispute_raised.'</td><td>'.$fedex_dispute_raised.'</td>
			</tr>
			<tr>
			<th bgcolor=#ccc width=120>RTO COD</th><td>'.$aramex_RTO_COD.'  ( '.$aramex_RTO_prcnt.' %)</td><td>'.$fedex_RTO_COD.'  ( '.$fedex_RTO_prcnt.' %)</td>
			</tr>
			<tr>
			<th bgcolor=#ccc width=120>Delivered</th><td>'.$aramex_delivered.'</td><td>'.$fedex_delivered.'</td>
			</tr></table>';
//echo $message;exit;


$mail = Mage::getModel('core/email');
					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($message);
					$mail->setSubject('Aramex VS Fedex Performance Comparison');
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


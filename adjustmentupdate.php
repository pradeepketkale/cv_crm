<?php
error_reporting(E_ALL ^ E_NOTICE);



$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$sql = "SELECT * FROM `shipmentpayout` as sp,`sales_flat_shipment` as sfs 
		WHERE sp.`shipment_id` = sfs.`increment_id` 
		AND sp.`shipmentpayout_status` = '1' and sfs.`udropship_status` = '12'";// cancelled = 6,Refund inited = 12
$results = $db->query($sql)->fetchAll();
$row = $results->fetch();
/*echo '<pre>';
print_r($row);exit;
echo $row[7];exit;
*/

//while ($row = mysql_fetch_array($results)) {
 foreach($results as $row){
	$insertQuery = "update `shipmentpayout` set `adjustment` = '".'-'.$row[7]."' where `shipment_id` = '".$row[1]."' ";
	$db->query($insertQuery)->fetch();

echo 'updatedddddddddd '.$row[1].'<br>';
	
}

?>

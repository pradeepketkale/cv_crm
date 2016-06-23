<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
Mage::app();

$pageDetail= $_GET['q'];

$statsConn = Mage::getSingleton('core/resource')->getConnection('core_read');
$selectQuery ="Select * from `seo_enable_anchor` where `page_type` = '".$pageDetail."'";
$existingResult = $statsConn->query($selectQuery)->fetchAll();
if($existingResult) {
	foreach($existingResult as $row) {
		$anchor_status= $row['is_active'];	
		$message='Record Found';
	}
} else {
	$anchor_status = 'N';
	$message='No record';
}

$responseData= array();

$responseData['m'] = $message;
$responseData['d'] = $anchor_status;
$responseData['s'] = 1;
echo json_encode($responseData);

?>

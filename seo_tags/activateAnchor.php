<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
Mage::app();

$pageDetail= $_GET['pageDetail'];
$activate_anchor= $_GET['activate_anchor'];

$statsConn = Mage::getSingleton('core/resource')->getConnection('core_read');
$selectQuery ="Select * from `seo_enable_anchor` where `page_type` = '".$pageDetail."'";
$existingResult = $statsConn->query($selectQuery)->fetchAll();
if($existingResult) {
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$sqlUpdateResult = "update `seo_enable_anchor` set `is_active`= '".$activate_anchor."' where `page_type`= '".$pageDetail."'";
	$updatetResult = $write->query($sqlUpdateResult);
	$write->closeConnection();
	$message='Anchor Updated Successfully';
	$data= $updatetResult;
} else {
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$sqlInsertResult = "INSERT INTO `seo_enable_anchor`(`page_type`, `is_active`) VALUES ('".$pageDetail."', '".$activate_anchor."')";
	$insertResult = $write->query($sqlInsertResult);
	$write->closeConnection();
	$message='Anchor Saved Successfully';
	$data= $insertResult;
}
$responseData= array();
$responseData['m'] = $message;
$responseData['d'] = $data;
$responseData['s'] = 1;
echo json_encode($responseData);



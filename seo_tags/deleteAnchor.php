<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
Mage::app();

$seo_anchor_id= $_GET['q'];
$pageDetail= $_GET['pageDetail'];
$anchor_type= $_GET['anchorType'];

$write = Mage::getSingleton('core/resource')->getConnection('core_write');
$sqlPaymentDetails = "Delete from `seo_anchor_tags` where `seo_anchor_id`=".$seo_anchor_id;
$write->query($sqlPaymentDetails);
$write->closeConnection();

$responseData= array();
$statsConn = Mage::getSingleton('core/resource')->getConnection('core_read');
$selectQuery ="Select * from `seo_anchor_tags` where `anchor_type` = '".$anchor_type."' and `page_detail` = '".$pageDetail."'";
$message='';//$selectQuery;
$result = $statsConn->query($selectQuery)->fetchAll();
$data='';
if ($result) {
	foreach($result as $row) {
		$anchor_tag= $row['anchor_tag'];	
		$link= $row['anchor_link'];
		$anchorId= $row['seo_anchor_id'];
		$data.='<li>
				<a href="'.$link.'"" title="'.$anchor_tag.'"">'.$anchor_tag.'</a> 
				<a style="cursor: pointer;font-size: 12px;vertical-align: super;font-weight: normal;color: #D40404;border-radius: 50%;border: 1px solid;letter-spacing: 4px;padding-left: 4px;" onclick="cancelConfirm('.$anchorId.')"> x</a>
			   </li> ';
	}
	
	$responseData['m'] = $message;
	$responseData['d'] = $data;
	$responseData['s'] = 1;
	echo json_encode($responseData);
} else {
	$data ="No details found";
	$responseData['m'] = $message;
	$responseData['d'] = $data;
	$responseData['s'] = 0;
	echo json_encode($responseData);
}
?>

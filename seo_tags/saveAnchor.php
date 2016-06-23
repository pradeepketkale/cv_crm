<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
Mage::app();

$pageDetail= $_GET['pageDetail'];
$anchorLink= $_GET['anchorLink'];
$anchorTitle= $_GET['anchorTitle'];
$anchor_type= $_GET['anchor_type'];
$anchorSequence= $_GET['anchorSequence'];

$statsConn = Mage::getSingleton('core/resource')->getConnection('core_read');
$selectQuery ="Select * from `seo_anchor_tags` where `anchor_type` = '".$anchor_type."' and `anchor_tag` = '".$anchorTitle."'";
$existingResult = $statsConn->query($selectQuery)->fetchAll();
if($existingResult) {
	$message='Anchor Already Exist';
} else {
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$sqlPaymentDetails = "INSERT INTO `seo_anchor_tags`(`anchor_type`, `page_detail`, `anchor_tag`, `anchor_link`, `sequence`) VALUES ('".$anchor_type."', '".$pageDetail."', '".$anchorTitle."', '".$anchorLink."', '".$anchorSequence."')";
	$insertResult = $write->query($sqlPaymentDetails);
	$write->closeConnection();
	$message='Anchor Saved Successfully';
}

$responseData= array();
$statsConn = Mage::getSingleton('core/resource')->getConnection('core_read');
$selectQuery ="Select * from `seo_anchor_tags` where `anchor_type` = '".$anchor_type."' and `page_detail` = '".$pageDetail."'";

$result = $statsConn->query($selectQuery)->fetchAll();
$data='';
if ($result) {
	foreach($result as $row) {
		$anchor_tag= $row['anchor_tag'];	
		$link= $row['anchor_link'];
		$anchorId= $row['seo_anchor_id'];
		$data.='<li>
				<a href="'.$link.'"" title="'.$anchor_tag.'"">"'.$anchor_tag.'"</a> 
				<a style="cursor:pointer;cursor: pointer;font-size: 9px;letter-spacing: 2px;vertical-align: super;font-weight: bold;" onclick="cancelConfirm('.$anchorId.')"> (x)</a>
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

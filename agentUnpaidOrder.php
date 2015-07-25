<?php 
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::app();



$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$getAgentIdQuery = "SELECT *,sfoa.`telephone` AS `telephone` FROM `sales_flat_order` as sfo,`sales_flat_order_address` as sfoa WHERE sfo.`entity_id` = sfoa.`parent_id` AND sfo.`coupon_code` LIKE '%CVAG%' AND sfo.`agent_id` IS NULL AND sfoa.`address_type` = 'billing 'AND sfo.`created_at` >= DATE_SUB( NOW( ) , INTERVAL 30 DAY )";
$resultCoupons = $db->query($getAgentIdQuery)->fetchAll();

$templateId = 'send_agent_order_detail';
$sender = Array('name'  => 'Craftsvilla',
				'email' => 'places@craftsvilla.com');
$_email = Mage::getModel('core/email_template');

//while($resultValues = mysql_fetch_array($resultCoupons)){
 foreach($resultCoupons as $resultValues)
	{
	$oCoupon = Mage::getModel('salesrule/coupon')->load($resultValues['coupon_code'], 'code');
						$oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
						//var_dump($oRule->getData());exit;
$couponcodeAgent = $oRule->getAgentid();
$ofagent = Mage::getModel('uagent/uagent')->load($couponcodeAgent);
$emailAgent =  $ofagent->getEmail();
echo $nameAgent = $ofagent->getAgentName();
$updateAgentId = "UPDATE `sales_flat_order` SET `agent_id` = '".$couponcodeAgent."',`agent_status` = 0 WHERE `coupon_code` = '".$resultValues['coupon_code']."'";
$updateResultagentid = $db->query($updateAgentId);
$vars = array('orderId' =>$resultValues['increment_id'],
			'couponcode' => $resultValues['coupon_code'],
			'agentName' => $nameAgent,
			'ordervalue'=>$resultValues['base_grand_total'],
			'email'=>$resultValues['customer_email'],
			'telephone'=>$resultValues['telephone'],
			'firstname' => $resultValues['customer_firstname']);

$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
				->sendTransactional($templateId, $sender, $emailAgent, '', $vars, $storeId);

$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
				->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', '', $vars, $storeId);

echo '<pre>'."Updated agentId: ".$couponcodeAgent.", couponcode: ".$resultValues['coupon_code'].",updated orderId: ".$resultValues['increment_id']."";
}

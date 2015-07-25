<?php 
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$shipmentDetails = "SELECT * FROM `sales_flat_shipment` as sfs WHERE  sfs.`created_at` >= DATE_SUB( NOW( ) , INTERVAL 5 DAY )";
$resultShipments = $db->query($shipmentDetails)->fetchAll();

$templateId = 'send_agent_paid_order_detail';
$sender = Array('name'  => 'Craftsvilla',
				'email' => 'places@craftsvilla.com');
$_email = Mage::getModel('core/email_template');


$lastShipmentOrderid = '';
$alreadyUpdated = false;
//while($resultValues = mysql_fetch_array($resultShipments))
foreach($resultShipments as $resultValues)
	{
		echo $shipmentId = $resultValues['increment_id'];
		$orderId  = $resultValues['order_id'];
		$shipagentid =  $resultValues['agent_id'];
		if(empty($shipagentid))
		{
			$orderDetailsCvag = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('coupon_code', array('like' => '%CVAG%'))
																			  ->addAttributeToFilter('entity_id',$orderId);
																			 
	//echo $orderDetailsCvag->getSelect()->__ToString();exit;
	//echo '<pre>'; print_r($orderDetailsCvag->getData());exit;
	//echo $orderDetailsCvag->count();exit;
	
					foreach($orderDetailsCvag as $orderDetails)
							{
							echo 'Order Id:'.$orderId;
							echo $couponCodeOrder =$orderDetails->getCouponCode();
							$oCoupon = Mage::getModel('salesrule/coupon')->load($couponCodeOrder, 'code');
							$oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
							//var_dump($oRule->getData());exit;
							
							$ordAgentId = $oRule->getAgentid();
							//echo $orderDetails->getAgentId();exit;
							$ofagent = Mage::getModel('uagent/uagent')->load($ordAgentId);
							$emailAgent =  $ofagent->getEmail();
							echo $nameAgent = $ofagent->getAgentName();
							
							if($orderDetails->getAgentId() == '')
								{
									echo 'Coupon: Not agent Id';
									//$updateAgentId = "UPDATE `sales_flat_order` SET `agent_id` = '".$ordAgentId."',`agent_status` = 4 WHERE `coupon_code` = '".$couponCodeOrder."'";
									$updateAgentId = "UPDATE `sales_flat_order` SET `agent_id` = '".$ordAgentId."',`agent_status` = 4 WHERE `entity_id` = '".$orderId."'";
									$db->query($updateAgentId)->fetch();
									$updateShipmentAgentId = "UPDATE `sales_flat_shipment` SET `agent_id` = '".$ordAgentId."' WHERE `increment_id` = '".$shipmentId."'";
									$db->query($updateShipmentAgentId)->fetch();

								 $vars = array('orderId' =>$orderDetails->getIncrementId(),
                                                                                'couponcode' => $couponCodeOrder,
                                                                                'agentName' => $nameAgent,
                                                                                'ordervalue'=>$orderDetails->getBaseGrandTotal(),
                                                                                'email'=>$orderDetails->getCustomerEmail(),
                                                                                'firstname' => $orderDetails->getCustomerFirstname());

                                                        $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                                        ->sendTransactional($templateId, $sender, $emailAgent, '', $vars, $storeId);

                                                        $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                                        ->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', '', $vars, $storeId);
								}
							else{
								echo 'Yes agent Id';
								$updateShipmentAgentId = "UPDATE `sales_flat_shipment` SET `agent_id` = '".$ordAgentId."' WHERE `increment_id` = '".$shipmentId."'";
								$db->query($updateShipmentAgentId)->fetch();
								echo 'updateddd else';
								}
							
							
						//	$vars = array('orderId' =>$orderDetails->getIncrementId(),
					//					'couponcode' => $couponCodeOrder,
				//						'agentName' => $nameAgent,
				//						'ordervalue'=>$orderDetails->getBaseGrandTotal(),
				//						'email'=>$orderDetails->getCustomerEmail(),
				//						'firstname' => $orderDetails->getCustomerFirstname());
				//			
				//			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
				//					->sendTransactional($templateId, $sender, $emailAgent, '', $vars, $storeId);
				//
				//			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
				//					->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', '', $vars, $storeId);
							
							echo '<pre>'."Updated agentId: ".$ordAgentId.", couponcode: ".$couponCodeOrder.",updated orderId: ".$orderDetails->getIncrementId()."";
							$alreadyUpdated = true;					
					}
					
				if(!$alreadyUpdated) {	
					$orderDetailsnocoupon = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('agent_id', array('notnull' => true))
																			  ->addAttributeToFilter('entity_id',$orderId);
																			 
	//echo $orderDetailsnocoupon->getSelect()->__ToString();exit;
	//echo '<pre>'; print_r($orderDetailsCvag->getData());exit;
	//echo $orderDetailsCvag->count();exit;
	
					foreach($orderDetailsnocoupon as $orderDetails)
							{
							echo 'Not Coupon Order Id:'.$orderId;
							$ordAgentId = $orderDetails->getAgentId();
							$couponCodeOrder = '';
							/*echo $couponCodeOrder =$orderDetails->getCouponCode();
							$oCoupon = Mage::getModel('salesrule/coupon')->load($couponCodeOrder, 'code');
							$oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
							//var_dump($oRule->getData());exit;
							
							echo $ordAgentId = $oRule->getAgentid();*/
							//echo $orderDetails->getAgentId();exit;
							$ofagent = Mage::getModel('uagent/uagent')->load($ordAgentId);
							echo $emailAgent =  $ofagent->getEmail();
							echo $nameAgent = $ofagent->getAgentName();
							
							if($orderDetails->getAgentId() == '')
								{
									echo 'No Coupon: Not agent Id';
									//$updateAgentId = "UPDATE `sales_flat_order` SET `agent_id` = '".$ordAgentId."',`agent_status` = 4 WHERE `coupon_code` = '".$couponCodeOrder."'";
									$updateAgentId = "UPDATE `sales_flat_order` SET `agent_id` = '".$ordAgentId."',`agent_status` = 4 WHERE `entity_id` = '".$orderId."'";
									$db->query($updateAgentId);
									$updateShipmentAgentId = "UPDATE `sales_flat_shipment` SET `agent_id` = '".$ordAgentId."' WHERE `increment_id` = '".$shipmentId."'";
									$db->query($updateShipmentAgentId);

									$vars = array('orderId' =>$orderDetails->getIncrementId(),
                                                                                'couponcode' => $couponCodeOrder,
                                                                                'agentName' => $nameAgent,
                                                                                'ordervalue'=>$orderDetails->getBaseGrandTotal(),
                                                                                'email'=>$orderDetails->getCustomerEmail(),
                                                                                'firstname' => $orderDetails->getCustomerFirstname());

                                                        $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                                        ->sendTransactional($templateId, $sender, $emailAgent, '', $vars, $storeId);

                                                        $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                                        ->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', '', $vars, $storeId);
								}
							else{
								//echo 'Yes agent Id';exit;
								$updateShipmentAgentId = "UPDATE `sales_flat_shipment` SET `agent_id` = '".$ordAgentId."' WHERE `increment_id` = '".$shipmentId."'";
								$db->query($updateShipmentAgentId);
							if ($lastShipmentOrderid != $orderId)
							{
							$vars = array('orderId' =>$orderDetails->getIncrementId(),
                                                                                'couponcode' => $couponCodeOrder,
                                                                                'agentName' => $nameAgent,
                                                                                'ordervalue'=>$orderDetails->getBaseGrandTotal(),
                                                                                'email'=>$orderDetails->getCustomerEmail(),
                                                                                'firstname' => $orderDetails->getCustomerFirstname());

                                                        $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                                        ->sendTransactional($templateId, $sender, $emailAgent, '', $vars, $storeId);

                                                        $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                                        ->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', '', $vars, $storeId);
							}
								echo 'updateddd else';
							}
							
							
					//		$vars = array('orderId' =>$orderDetails->getIncrementId(),
					//					'couponcode' => $couponCodeOrder,
					//					'agentName' => $nameAgent,
					//					'ordervalue'=>$orderDetails->getBaseGrandTotal(),
					//					'email'=>$orderDetails->getCustomerEmail(),
					//					'firstname' => $orderDetails->getCustomerFirstname());
					//		
					//		$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					//				->sendTransactional($templateId, $sender, $emailAgent, '', $vars, $storeId);
					//
					//		$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					//				->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', '', $vars, $storeId);
							
							echo '<pre>'."Updated agentId: ".$ordAgentId.", couponcode: ".$couponCodeOrder.",updated orderId: ".$orderDetails->getIncrementId()."";
							
					}
					
					
					
		}
		}
		$lastShipmentOrderid = $orderId;
		$alreadyUpdated = false;

	}

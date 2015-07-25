<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$sql = "select * from `disputeraised` where `status` = 3";
$result = $db->query($sql)->fetchAll();
// print_r($row = mysql_fetch_array($result));exit;
$shipmentNumdisputeDelayed = 0;
 //while($row = mysql_fetch_array($result))
 foreach($result as $row)
 {   
 	$shipmentid = $row['increment_id'];  
 	$_shippmentupdateDate = $row['updated_at'];
 	$vendorid = $row['vendor_id'];
 	$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
 	$vendorname = $vendor->getVendorName();
 	$vendoremail = $vendor->getEmail();
 	$newdate = strtotime($_shippmentupdateDate)+3*24*60*60;
	$newdate1 =  date('jS F Y', $newdate);
	$_todayDate = date('jS F Y');
	$_todayTime = strtotime($_todayDate);
	$delta = $_todayTime - $newdate;
	if ($delta <= 0)
	{
			$shipmentNumdisputeDelayed++;
    		$delay = (ceil($delta/(60*60*24))+3);
		 
				if ($delay >= 3)
				{	
				 		$storeId = Mage::app()->getStore()->getId();
						$templateId = 'disputeraisedresponse_reminderto_vendor_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Urgent Response Required For Dispute For Shipment No:'.$shipmentid;
						$vendorShipmentItemHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
											
								$_items1 = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentid);
								$allitems = $_items1->getAllItems();
								//echo '<pre>';print_r($allitems);exit;
								foreach ($allitems as $_item)
								{
									$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());
									$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
								 	$vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$shipmentid."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
								}
						$vendorShipmentItemHtml .= "</table>";		
				 		$vars = Array('vendorname' =>$vendorname,
											  'shipmentid' =>$shipmentid,
				 		'vendorItemHTML'=>$vendorShipmentItemHtml,
									  );
								//echo '<pre>';print_r($vars);
								$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
										->setTemplateSubject($mailSubject)
									->sendTransactional($templateId, $sender, 'gsonar8@gmail.com', $vendorname, $vars, $storeId);
									$translate->setTranslateInline(true);
									echo 'Email has been sent successfully to vendor';
							//print_r($_email);
				}
		
	}
	else
				{
					echo 'No delay dispute shipments available';
				}
				
		
			
 }


		?>


	

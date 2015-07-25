<?php
class Craftsvilla_Follow_Model_Cron
{
	public function sendfollow()
	{
		$_helpm = Mage::helper('umicrosite');
		$_helpv = Mage::helper('udropship');
		$_helpc = Mage::helper('catalog/image');
		$CatalogProduct = Mage::getModel('catalog/product');
		$Customer = Mage::getModel('customer/customer');
		$customerQuery = '';
		$storeId = Mage::app()->getStore()->getId();
		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$templateId = 'follow_email_template';
		$mailSubject = 'Craftsvilla.com: Weekly Updates from Your Favourite Shops!';
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'messages@craftsvilla.com');
		$read = Mage::getSingleton('core/resource')->getConnection('follow_read');
		$customerQuery = "SELECT customer_id FROM follow group by customer_id";
		$follows = $read->fetchAll($customerQuery);
		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$_email = Mage::getModel('core/email_template');
		$_vendors = array();
		$_vendorsNoProducts = array();
		foreach($follows as $follow){
			$customerFollowItemHtml = '';
			$vendorQuery = '';
			$vendorQuery = "SELECT vendor_id FROM follow where customer_id = '".$follow['customer_id']."' AND status='1'";
			$vendorData = $read->fetchAll($vendorQuery);
			foreach($vendorData as $vendor){
				$_latestProductData = '';
				$vendorIfo = '';
				$storeurl = '';
				$vendorLogo = '';
				$_vendorItemHtml = '';
				$_vendorId = $vendor['vendor_id'];
				if ((!array_key_exists($_vendorId, $_vendors)) and (!in_array($_vendorId, $_vendorsNoProducts))){
					$_latestProductQuery = "select entity_id from catalog_product_flat_1 WHERE `created_at` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE() AND udropship_vendor = '".$_vendorId."' AND visibility = '4' limit 0,4";
					$_latestProductData = $read->fetchAll($_latestProductQuery);
					if($_latestProductData){
						$vendorIfo = $_helpv->getVendor($_vendorId);
						$storeurl = $baseUrl.$vendorIfo->getUrlKey();
						$vendorLogo = $vendorIfo->getShopLogo()!='' ? $vendorIfo->getShopLogo() : 'vendor/noimage/noimage.jpg';
						$_vendorItemHtml .= '<div style="margin-bottom:10px;clear:both; overflow:hidden;">
                <div style="border:7px solid #F5F4F2;height:90px;width:241px;float:left;"><span style="display:block;float:left;margin-right: 10px;"> <a href="'.$storeurl.'" target="_blank" style="outline:0;"> <img src="'.$_helpm->getResizedUrl($vendorLogo,70).'" style="border:1px solid #DDDDDD;border-radius:2px 2px 2px 2px;box-shadow:1px 0 1px #BCBCBC;margin:3px;padding:6px;"> </a></span>
                  <p style="height:62px;overflow:hidden;margin-right:10px;"> <a href="'.$storeurl.'" target="_blank" style="color:#583F3B;margin-bottom:12px;display:block;font-size:15px;font-weight:bold;margin-top:2px;text-decoration:none;outline:0;">'.$vendorIfo->getVendorName().'</a> </p>
                </div>
                <div style="height:90px;width:417px; line-height:8px;float:left; margin-left:10px;">';
						foreach($_latestProductData as $_latestProduct){
							$_latestProduct = $CatalogProduct->load($_latestProduct['entity_id']);
							$_vendorItemHtml .= '<span style="display:block;float:left;margin-right: 10px;"> <a href="'.$baseUrl.$_latestProduct->getUrlPath().'" target="_blank" style="outline:0;"> <img src="'.$_helpc->init($_latestProduct, 'thumbnail')->resize(85).'" style="border:1px solid #DDDDDD;border-radius:2px 2px 2px 2px;box-shadow:1px 0 1px #BCBCBC;margin:3px 0 3px 0;"> </a> <a href="'.$baseUrl.$_latestProduct->getUrlPath().'" target="_blank" style="color:#583F3B;display:block;font-size:11px;margin-top:2px;overflow:hidden;text-decoration:none;width:87px;outline:0;line-height:16px;">'.substr($_latestProduct->getName(),0,14).'</a> 
				</span>';
						}
						$_vendorItemHtml .= '</div></div>';
						$_vendors[$_vendorId] = $_vendorItemHtml;
						$customerFollowItemHtml .= $_vendorItemHtml;
					}
					else
						$_vendorsNoProducts[] = $_vendorId;
				}
				elseif ((array_key_exists($_vendorId, $_vendors)) and (!in_array($_vendorId, $_vendorsNoProducts))){
					$customerFollowItemHtml .= $_vendors[$_vendorId];
				}
			}
			if($customerFollowItemHtml!=''){
				$_customerData = '';
				$_customerData = $Customer->load($follow['customer_id']);
				$vars = Array('customerFollowHTML' =>$customerFollowItemHtml,
					'customerName' =>$_customerData->getFirstname()." ".$_customerData->getLastname(),
				);
			
				$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->setTemplateSubject($mailSubject)
						->sendTransactional($templateId, $sender, $_customerData->getEmail(), $_customerData->getFirstname()." ".$_customerData->getLastname(), $vars, $storeId);
				$translate->setTranslateInline(true);
			}
		}
		$_vendors = array();
		$_vendorsNoProducts = array();
	}
}
?>

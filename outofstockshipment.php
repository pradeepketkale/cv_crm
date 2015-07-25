<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


 $db = Mage::getSingleton('core/resource')->getConnection('core_read');

$primary_cat="select st.`sku`, st.`name`, st.`qty`, st.`price`, st.`base_discount_amount`, sa.`increment_id`, ca.`category_id`, uv.`vendor_id`, uv.`email` from `sales_flat_shipment` as sa,`sales_flat_shipment_item` as st, 

`catalog_category_product` as ca, `udropship_vendor` as uv where st.`parent_id`=sa.`entity_id` AND st.`product_id`=ca.`product_id` AND 

uv.`primary_category`=ca.`category_id` AND sa.`udropship_status`=18 AND ca.`category_id`=74";

//$primary=mysql_query($primary_cat);
$primary=$db->query($primary_cat)->fetchAll();
$last_email = '';
$shipmentItemHtml1 = '';
$shipmentItemHtml2 = '';
$recname = '';

		//while($primary_catres=mysql_fetch_array($primary)){
		 foreach($primary as $primary_catres){
					$cat_id_pri = $primary_catres['category_id'];

					if($cat_id_pri != '74'){ echo 'Skipping Jewellery:'.$primary_catres['increment_id']; } else {
					$sku =$primary_catres['sku'];
					$increment = $primary_catres['increment_id'];
					/*$name=$primary_catres['name'];
					$qty=$primary_catres['qty'];
					$price=$primary_catres['price'];
					$discount=$primary_catres['base_discount_amount'];
					$amount=$primary_catres['price']-$primary_catres['base_discount_amount'];
					$increment = $primary_catres['increment_id'];
					$status = $primary_catres['udropship_status'];*/
        
					if($lastemail == $primary_catres['email'])
					{
						$shipmentItemHtml2 .= $shipmentItemHtml1;
						$lastemail = $primary_catres['email'];
						$recname = $primary_catres['vendor_name'];
					}
					else
					{	
						$shipmentItemHtml = $shipmentItemHtml2.'</table>';
						$shipmentItemHtml2 = '';
						if ($lastemail != ''){
							$storeId = Mage::app()->getStore()->getId();
					        	echo 'Sending Email to: '.$lastemail; 
							$templateId = 'shipmentoutofstock_email_template';
							$sender = Array('name'  => 'Craftsvilla',
							'email' => 'places@craftsvilla.com');
							$translate  = Mage::getSingleton('core/translate');
							$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
							$mailSubject = 'Claim These "Out of Stock" Shipments Today!';
							$vars = Array('shipmentItemHtml' =>$shipmentItemHtml);		
								
							$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
								->setTemplateSubject($mailSubject)
								->sendTransactional($templateId, $sender, $lastemail, $recname, $vars, $storeId);
					
							$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                        	->setTemplateSubject($mailSubject)
                                                        	->sendTransactional($templateId, $sender, 'monica@craftsvilla.com', $recname, $vars, $storeId);
							$translate->setTranslateInline(true);
						}
					
						$lastemail = $primary_catres['email'];
						$recname = $primary_catres['vendor_name'];
						$shipmentItemHtml = '';
					}	

					$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
					$productUrl = $product->getProductUrl(); 
					$image="<a href='".$productUrl."'><img src='".Mage::helper('catalog/image')->init($product, 'image')."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' /><br>Click Here To See Product</a>"; 
					$shipmentItemHtml1 = '<br> <br>SKU: '.$sku.' <br> <br>Shipment Id: '.$increment.'<br>';
					$shipmentItemHtml1 .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$primary_catres['name']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".number_format($primary_catres['qty'])."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".number_format($primary_catres['price'],2)."</td>
											 <td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".number_format($primary_catres['base_discount_amount'],2)."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".($primary_catres['price']-$primary_catres['base_discount_amount'])."</td>
											 <td><a href='http://www.craftsvilla.com/marketplace/vendor/claimproduct/'><img src='http://d1g63s1o9fthro.cloudfront.net/skin/frontend/default/craftsvilla2012/img/claim_button.png'></a></td></tr>";
					}

			} // End of While Loop
			$shipmentItemHtml = $shipmentItemHtml2.'</table>';
		 	if ($lastemail != ''){
                                  $storeId = Mage::app()->getStore()->getId();
                                  echo 'Sending Email to: '.$lastemail;
                                  $templateId = 'shipmentoutofstock_email_template';
                                  $sender = Array('name'  => 'Craftsvilla',
                       		           'email' => 'places@craftsvilla.com');
                                  $translate  = Mage::getSingleton('core/translate');
                                  $translate->setTranslateInline(false);
                                  $_email = Mage::getModel('core/email_template');
                                  $mailSubject = 'Claim These "Out of Stock" Shipments Today!';
                                  $vars = Array('shipmentItemHtml' =>$shipmentItemHtml);

                                  $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                         ->setTemplateSubject($mailSubject)
                                         ->sendTransactional($templateId, $sender, $lastemail, $recname, $vars, $storeId);

                                  $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                         ->setTemplateSubject($mailSubject)
                                         ->sendTransactional($templateId, $sender, 'monica@craftsvilla.com', $recname, $vars, $storeId);
                                  $translate->setTranslateInline(true);
                       }		

	echo "Out of stock shipment emails sent successfully";
	
?>

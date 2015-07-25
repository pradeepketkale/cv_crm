<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Abandoned Cart Email Script Started at Time:: ".$currentTime;
$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$collectionQuery = "SELECT entity_id, customer_firstname, customer_email, items_count, base_grand_total, created_at FROM sales_flat_quote WHERE items_count >0 AND base_grand_total > 1000 AND customer_email IS NOT NULL AND (created_at  BETWEEN DATE_SUB(NOW(),INTERVAL 1 DAY) AND DATE_SUB(NOW(),INTERVAL 3 HOUR)) ORDER BY `sales_flat_quote`.`created_at` DESC";
		$collection = $read->query($collectionQuery)->fetchAll();
		$collectionOrderQuery = "SELECT `customer_email` FROM `sales_flat_order` WHERE sales_flat_order.`created_at` > DATE_SUB(NOW(),INTERVAL 3 DAY)";
		$collectionOrder = $read->query($collectionOrderQuery)->fetchAll();
		
		$orderEmail = Array();
		foreach ($collectionOrder as $itemOrder) {
			$orderEmail[] = $itemOrder['customer_email'];
		}
		echo "Total Orders";
		echo sizeof($orderEmail);
		echo "Total Quotes";
		echo sizeof($collection); 
		$templateId = 'abandoned_cart_alert';
		$storeId = Mage::app()->getStore()->getId();
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'customercare@craftsvilla.com');
		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$_email = Mage::getModel('core/email_template');
		//$customeProductItem = '';
		//$customeProductItem .= '<table>';
		//$customeProductItem .="<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ProductImage</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Ordered Qty</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
		//echo '<pre>';
		//print_r($collection);exit;
		 
					
				foreach ($collection as $item) {
					//.'Customer Email';
				$custemail = $item['customer_email'];
				$custemailCustom = "maggax@hotmail.com";
				   $read = Mage::getSingleton('core/resource')->getConnection('core_read');
				 $custid = "select `entity_id` from `customer_entity` where `email` = '".$custemailCustom."'";
				$custidres = $read->query($custid)->fetchAll();
				foreach ($custidres as $_custidres) {
				 $customer = $_custidres['entity_id'];
				}
				
				$wishid = "select `wishlist_id` from `wishlist` where `customer_id` = '".$customer."'";
				$wishidres = $read->query($wishid)->fetchAll();
				foreach ($wishidres as $_wishidres)
				{
				  $wishlistid = $_wishidres['wishlist_id'];
				}
				$wishitem = "select `product_id` from `wishlist_item` where `wishlist_id` = '".$wishlistid."'";
				$wishitemres = $read->query($wishitem)->fetchAll();
				$count = count($skus);
		$cols = 2;
		$i=1;
		if(count($prid)<8)
		{
		$html = '<table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 690px;">
    <tbody>
   
         <tr>
                <td height="15">&nbsp;</td>
              </tr>              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 620px;">
                    <tbody>
                      
                      <tr>
                        <td colspan="3">&nbsp;</td>
                      </tr>';
				foreach($wishitemres as $_wishitemres)
				{
					$prid = $_wishitemres['product_id'];
			   $collections = Mage::getModel('catalog/product')->load($prid);
			$vendorid = $collections->getUdropshipVendor();
			$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
			$vendorName = $vendor->getVendorName();
			$vendorurl = $vendor->getUrlKey();
			$childId= $collections->getId();
			$purl = 'catalog/product/view/id/'.$childId;
			$_skuid = $collections['sku'];
			$name = $collections['name'];
			$price = $collections['price'];
			$shortdescription = $name.' in Rs. '.$price;
			$imageurl = Mage::getBaseUrl().$purl;
			$image = Mage::helper('catalog/image')->init($collections, 'image')->resize(300);
			$_target = Mage::getBaseDir('media') . DS . 'agenthtml' . DS;
			$newfilename = mt_rand(10000000, 99999999).'_Catalog.html';
			$_path = $_target.$newfilename;
			
			if($i%$cols==1)
			{
			  $html .= '<tr>';
			
			}
			$html .=     '<td height="15">
                  <table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 100px;">
                    <tbody>
                      <tr>
                        <td colspan="3" height="5">
                        </td>
                      </tr>
					  <tr>
<td height="206" style="border: #ECE2D5 1px solid; border-bottom: none;"width="302">
                          <table align="default" cellpadding="0" cellspacing="0" style="padding: 0px;"name="imgHolder">
                            <tbody>
                              <tr>
                                <td align="center"><a href="'.$imageurl.'" target="_blank"><img title="'.$name.'" caption="" src="'.$image.'" alt="'.$name.'" width="300" height="300" border="0" hspace="0" vspace="0" /></a>
                                </td>
                              </tr>
                              <tr>
                                <td align="center" class="CaptionText" style="font-family: Arial,Verdana; font-size: 12px;">
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td></tr>';
				
				$html .= '<tr><td style="border: #ECE2D5 1px solid; padding: 5px 0;" valign="top">
                          <table align="center" border="0" cellpadding="0" cellspacing="0" style="width: 95%;">
                            <tbody>
                              <tr>
                                <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding: 5px 0px;"><strong>'.$name.'&nbsp;- Rs. '.$price.'</strong><br /><br />
                                </td>
                                <td>
                                  <table align="default" cellpadding="0" cellspacing="0" style="padding: 0px;" name="imgHolder">
                                    <tbody>
                                      <tr>
                                        <td align="center"><a href="'.$imageurl.'"><img title="'.$name.'" caption=""src="https://images.benchmarkemail.com/client136252/image774099.gif" alt="'.$name.'" width="83" height="29" border="0" hspace="0" vspace="0" /></a>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td align="center" class="CaptionText" style="font-family: Arial,Verdana; font-size: 12px;">
                                        </td>
                                      </tr>    
                                    </tbody>
                                  </table>
                                </td>
                                 </tr>           
';
            $html .= ' <tr><td style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;">'.$name.'&nbsp;by <a href="'.Mage::getBaseUrl().$vendorurl.'"><strong>'.$vendorName.'</strong></a> &nbsp;in Rs. '.$price.' Only.</td>
                                <td style="font-family: Arial, Times New Roman, Times, serif; font-size: 13px; color: #d2152f; font-weight: bold; text-align: center;">&nbsp;</td>
                              </tr>
                             </tbody>
                  </table>
                  </td><td>&nbsp;</td></tr>
                    </tbody>
                  </table>
                </td>
                   ';
			if($i%$cols==0)
			{
			  $html .= '</tr>';
			
			}
			$i++;
					
				}
		$html .= '</table>';
		}
				
                     if(!in_array($custemail, $orderEmail) && (trim($custemail)!=""))
                                        {
					 $customeProductItem = '';
                $customeProductItem .= '<table>';
                $customeProductItem .="<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ProductImage</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Ordered Qty</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
					$qid = $item['entity_id'];
					$custName = $item['customer_firstname'];
					$parent_quote = Mage::getModel("sales/quote")->load($qid);
					$urlactionday1 = Mage::getBaseUrl().'umicrosite/vendor/addProductTocart?q='.$qid;
					
					//$itemQuote = Mage::getModel('sales/quote_item')->load($qid,'quote_id');
					//echo $itemQuote->getSku().'<br>';
					foreach($parent_quote->getAllItems() as $itemsQuote)
					{
						$productid = $itemsQuote->getProductId(); //product id
						$prdSku = $itemsQuote->getSku();
						$qty_value = $itemsQuote->getQty(); //ordered qty of item
						$prdName = $itemsQuote->getName();
						$price = $itemsQuote->getBasePrice();
						$product_model = Mage::getModel('catalog/product');
						$my_product = $product_model->load($productid);
						$image="<img src='".Mage::helper('catalog/image')->init($my_product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
						$checkbox = '<a href ="'.$urlactionday1.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="sndship"  > Go To Your Cart</button></a>';		
						$customeProductItem .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$itemsQuote->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$qty_value."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$price."</td></tr>";
						} //End of internal for each
					$customeProductItem .= '</table>';	
						$vars = Array('products' =>$customeProductItem,
							  'customer_name' =>$custName,
							  'recover_url' => $checkbox,
						      'otherproducts' => $html,
							  );
				//echo '<pre>';print_r($vars);exit;
					echo "Sending Email to ".$custName." With Email address:".$custemail;
					try{
						
						$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
			        	   	->sendTransactional($templateId, $sender, $custemail, '', $vars, $storeId);
						$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                           	->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', '', $vars, $storeId);
						if($_email){
							echo "email sent successfully ".$custName." With Email address".$custemail;
						}		   
					}
					catch(Exception $e) {
						echo 'Error occured for Email: '.$custemail;
					 	echo 'Message: ' .$e->getMessage();
					}
				}	
				else
				{
					echo 'Skipped Customer: '.$custemail;
				}
			}
			//End of for each
			
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Abandoned Cart Email Script Ended at Time:: ".$currentTime;
$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();
?>

<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();

//echo $bulkid = $argv[42]; 
echo $bulkid = $argv[1];
if ($bulkid == '') { echo "Please Provide Bulk Inventory Update Id.";exit;}
		$bulkinventoryupdate = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate')->load($bulkid);
		//$bulkinventoryupdate = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate')->load(44);
		$vendorid = $bulkinventoryupdate['vendor'];
		$filename = $bulkinventoryupdate['filename'];
		$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
		echo "Updating for Vendor:";
		echo $vendorname = $vendor['vendor_name'];
		$vendoremail = $vendor['email'];
		$_target = Mage::getBaseDir('media') . DS .'inventorycsv'. DS;
		$_path = $_target.$filename;
		$handle = fopen($filename, "r");
		$csvObject = new Varien_File_Csv();
        	$csvData = $csvObject->getData($_path);
		echo "\n Total to Update:";
		echo $count = sizeof($csvData);
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
		$catarray = array();
		$listarray[] = array("Craftsvilla SKU Id", "Vendor SKU", "Inventory", "Comments");
	    	$expectedCsvFields  = array(
			'sku' => 0,
			'vendorsku' => 1,
			'qty' => 2,
			
		);
		 $numfailed = 0;
		 $numsuccess = 0;
		$i = 0;
		 
		foreach ($csvData as $k => $v) 
		{
			if ($i == '0')
                        {
				if(!((strtolower($v[0]) == 'sku') || (strtolower($v[0]) == 'craftsvilla sku id')) || !((strtolower($v[1]) == 'vendor sku') || (strtolower($v[1]) == 'vendorsku')) || !((strtolower($v[2]) == 'qty') || (strtolower($v[2]) == 'quantity')))
                                {
                                        echo $error = "Invalid Format Of Bulk Upload Sheet!: Error 1";
                                        break;
                                }

			}
			if (count($v) <= 1 && !strlen($v[0])) {
				break;
			}
				
			if (count($v) < count($expectedCsvFields)) {
				echo 'Line %s format is invalid and has been ignored'. $k;
				break;
			 }

			if (($i> 0) && (strlen($v[0]) > 2) && ($i < 6000))
			{
			 
		   		echo $sku = $v[0];
		   		echo $vendorsku = $v[1];
		   		echo $qty = $v[2]; 
		   		$product = Mage::getModel('catalog/product');
		   		echo $productId = $product->getIdBySku($sku);
		   		//$prod = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
		  		// $_vendor = $prod->getUdropshipVendor();
		   		//$entity =  $prod['entity_id'];
		   		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
				$prod_query = "SELECT * FROM `catalog_product_entity_int` WHERE `entity_id` = '".$productId."' AND `attribute_id` = 531";
				$result = $read->query($prod_query)->fetchAll();	
				//var_dump($result);exit;
				foreach($result as $_result)
				{
					$_v = $_result['value'];
				}

		  		$error = 'No Error, Product Quantity Updated Successfully';

				if($sku == "sku")
				{
					$i++;
					continue;
				}
				if(empty($sku) || $qty=='' || !is_int($qty) || ($qty<0))
				{  
				echo	$error = "You did not fill out the required fields.";
					$numfailed++;
					$i++;
					continue;
				}
				elseif($_v != $bulkinventoryupdate['vendor'])
				{
				echo	$error = "This sku does not belong to you hence not updated.";
					$numfailed++;
					$i++;
					continue;
				}
				else
				{
				
					//		$product = Mage::getModel('catalog/product');
					$stockItem = Mage::getModel('cataloginventory/stock_item');
					//	    $productId = $product->getIdBySku($sku);
					if (!empty($productId) || ($productId !== false)) {
						//$product->load($productId);
			        		$stockItem->loadByProduct($productId);
						$stockItem->setData('qty', $qty);
						$stockItem->setData('is_in_stock', ($qty > 0) ? 1 : 0);
			    			try 
						{
							echo "\n Saving Product:";
							echo $sku;
							$numsuccess++;
							$stockItem->save();
						
						} 
						catch(Exception $e)
						{
				     			echo $e->getMessage();
						}
					}
					
				} 
			
				unset($product);
				unset($stockItem);
			}
			$listarray[] = array($sku, $vendorsku, $qty, $error);
			echo $i++;
		}//foreach closed 
			
			try{
				if($numsuccess>0)
				{
					echo $numsuccess.' Products Successfully Updated!!!';
				}
				if($numfailed>0)
				{
					echo $numfailed.' Products Upload Failed!!!';
				}
				
				if($numsuccess==0 && $numfailed==0)
				{
					echo 'No Products Were Uploaded. Please check your csv file!!!';
				}
				
			 } 
				catch(Exception $e){
				echo $e->getMessage();
				
			}
			
			$pathreport = Mage::getBaseDir('media'). DS . 'errorcsv'. DS;
			$file = $bulkid.'_inventory_report.csv';
			
				$errorcsvpath = $pathreport.$file;
				$fp = fopen($errorcsvpath, 'w');
				
			   	foreach ($listarray as $fields1) {
					fputcsv($fp, $fields1, ',', '"');
				}
				fclose($fp);
				$bulkinventoryupdate->setErrorreport($file)
								    ->setStatus(2)
								    ->setTotalproducts($count)
								    ->save();
									
				 $storeId = Mage::app()->getStore()->getId();	  
						$templateId = 'bulkinventoryupdate_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$vendorname = $vendor['vendor_name'];
		                $vendoremail = $vendor['email'];
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Product Inventory Update (ID: '.$bulkid.') Request On Craftsvilla.com Is Complete';
						$uploadHtml = '';
       
				$uploadHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$numsuccess."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$numfailed."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$count."</td></tr></table>";
						
            
					$vars = Array('uploadHtml' =>$uploadHtml);			
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, $vendoremail, $vendorname, $vars, $storeId);

					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                        ->setTemplateSubject($mailSubject)
                                                        ->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $vendorname, $vars, $storeId);
					
					$translate->setTranslateInline(true);
					echo "Email has been sent successfully";
?>

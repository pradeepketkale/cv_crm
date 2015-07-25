<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();

$bulkdownloadIds = Mage::getModel('productdownloadreq/productdownloadreq')->getCollection();
echo "Starting Bulk Download ";
$i = 0;
foreach($bulkdownloadIds as $_bulkdownloadIds)
{
	if($i < 10)
	{
        	$bulkid = $_bulkdownloadIds['productdownloadreq_id'];
		$bulkdownload = Mage::getModel('productdownloadreq/productdownloadreq')->load($bulkid);
		$vendorid = $bulkdownload['vendorname'];
		//$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
		//$vendorname = $vendor['vendor_name'];
		//$vendoremail = $vendor['email'];
		$vendorstatus = $bulkdownload['status'];
		$getactivity = Mage::getModel('productdownloadreq/productdownloadreq')->load($bulkid)->getActivity();
		//$products = Mage::getModel('catalog/product')->getCollection();
		//$products->addAttributeToFilter('udropship_vendor', $vendorid);
		//$products->addAttributeToSelect('*');
		//$products->load();  
		if($bulkdownload['status'] == 1)
		{
		$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
		$vendorname = $vendor['vendor_name'];
		$vendoremail = $vendor['email'];
		$products = Mage::getModel('catalog/product')->getCollection();
		$products->addAttributeToFilter('udropship_vendor', $vendorid);
		$products->addAttributeToSelect('*');
		$products->load();  
		$fromPart = $products->getSelect()->getPart(Zend_Db_Select::FROM);
                $fromPart['e']['tableName'] ='catalog_product_entity';
	        $products->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
		if($getactivity == 1)
		{
			echo 'Full Product Download Id: '.$bulkid; 
		   
			$cvsData ='Craftsvilla SKU Id,Vendor SKU,Product Name,Description,Price,Special Price,Image,Quantity'.PHP_EOL;
			foreach($products as $_prod) 
			{   
			     
			    $sku = $_prod->getSku();
				$vsku = $_prod->getVendorsku();
				$prname = $_prod->getName();
				$prnamerep = str_replace('"','',$prname);
				$price = $_prod->getPrice();
				$desc = $_prod->getDescription();
				$desc_rep = str_replace('"','',$desc);
				$sprice =  $_prod->getSpecialPrice();
				$image = $_prod->getMediaConfig()->getMediaUrl($_prod->getData('image'));
				$num= Mage::getModel('cataloginventory/stock_item')->loadByProduct($_prod)->getQty();
				$cvsData .= "\"$sku\",\"$vsku\",\"$prnamerep\",\"$desc_rep\",\"$price\",\"$sprice\",\"$image\",\"$num\"".PHP_EOL;
			}
		    $pathreport = Mage::getBaseDir('media'). DS . 'productcsv'. DS;
			$targetcsv = Mage::getBaseUrl('media').'productcsv/';
			$file = $bulkid.'_report.csv';
			$productcsvpath = $pathreport.$file;
			$productcsvpath1 = $targetcsv.$file;
			$fp = fopen("$productcsvpath", "a");
			if($fp)
				{
					fwrite($fp,$cvsData);
					fclose($fp);
				}	
        

		}
		
     if($getactivity == 2)
		{
			
			echo 'Inventory Download Id: '.$bulkid; 
			$cvsData ='Craftsvilla SKU Id,Vendor SKU,Quantity'.PHP_EOL;
			foreach($products as $_prod) {   
			     
			    $sku = $_prod->getSku();
				$vsku = $_prod->getVendorsku();
				$num= Mage::getModel('cataloginventory/stock_item')->loadByProduct($_prod)->getQty();
				$cvsData .= "\"$sku\",\"$vsku\",\"$num\"".PHP_EOL;
			}
		    $pathreport = Mage::getBaseDir('media'). DS . 'productcsv'. DS;
			$targetcsv = Mage::getBaseUrl('media').'productcsv/';
			$file = $bulkid.'_report.csv';
			$productcsvpath = $pathreport.$file;
			$productcsvpath1 = $targetcsv.$file;
			$fp = fopen("$productcsvpath", "a");
			if($fp)
				{
					fwrite($fp,$cvsData);
					fclose($fp);
				}	
        
  
	     }
		 
		 $bulkdownload->setCsvdownload($file)
				              ->setStatus(2)
							  ->save();
					
		$storeId = Mage::app()->getStore()->getId();	  
						$templateId = 'bulkdownload_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$vendorname = $vendor['vendor_name'];
		                $vendoremail = $vendor['email'];
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Product Download Request (ID:'.$bulkid.') On Craftsvilla.com Is Complete';
						
       
				$downloadReport =  "<a href=".$productcsvpath1.">".$file."</a>";
						
            
					$vars = Array('downloadReport' =>$downloadReport);		
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, $vendoremail, $vendorname, $vars, $storeId);

					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $vendorname, $vars, $storeId);
					
					$translate->setTranslateInline(true);
					
		echo 'Request Completed For Seller:'.$vendorname;
		echo $i++;
		}
		}

		
			
}

?>

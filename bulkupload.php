<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();

echo $bulkid = $argv[1]; 
if ($bulkid == '') { echo "Please Provide Bulk Upload Id."; exit;}
$bulkuploadcsv = Mage::getModel('bulkuploadcsv/bulkuploadcsv')->load($bulkid);
		$vendorid = $bulkuploadcsv['vendor'];
		$filename = $bulkuploadcsv['filename'];
		$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
	echo	$vendorname = $vendor['vendor_name'];
		$vendoremail = $vendor['email'];
		$_target = Mage::getBaseDir('media') . DS . 'vendorcsv' . DS;
		$_path = $_target.$filename;
		$csvObject = new Varien_File_Csv();
        $csvData = $csvObject->getData($_path);
		$count = sizeof($csvData)-1;
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
		$catarray = array();
		$path = Mage::getBaseDir('media') . DS . 'vendorimages' . DS;
//		$path = Mage::getBaseDir('media') . DS . 'vendorcsv' . DS . 'imgtest' . DS; 
		$listarray = array();
		$listarray[] = array("Craftsvilla SKU Id", "Vendor SKU", "Product Name", "Comments");
	   
				/**
		 * File expected fields
		 */
		$expectedCsvFields  = array(
			'name' => 0,
			'description' => 1,
			'short_description' => 2,
			'price' => 3,
			'special_price' => 4,
			'qty' => 5,
			'category' => 6,
			'shippingcost' => 7,
			'image' => 8,
			'weight' => 9,
			'vendorsku' => 10
			
		);
		
		/* $k is line number
		 * $v is line content array
		 */
		
		 $numfailed = 0;
		 $numsuccess = 0;
		 $i =0;
		
		echo 'Found Number of Products To Upload:'.$count;
		foreach ($csvData as $k => $v) 
		{
			echo "Product Upload in Progress ";
                	if ($i== 0)
			{
				$v[7] = str_replace(' ','',$v[7]); 
				if((strtolower($v[0]) != 'name') || (strtolower($v[1]) != 'description') || (strtolower($v[2]) != 'short_description') || (strtolower($v[3]) != 'price') || (strtolower($v[4]) != 'special_price')|| (strtolower($v[5]) != 'qty') || (strtolower($v[6]) != 'category') || (strtolower(trim($v[7])) != 'shippingcost') || (strtolower($v[8]) != 'image') || (strtolower($v[9]) != 'weight') || (strtolower($v[10]) != 'vendorsku'))
				{
					Echo "Invalid Format Of Bulk Upload Sheet!: Error 1";
					break;
				}
			}
				   /**
				 * End of file has more than one empty lines
				 */
			if (count($v) <= 1 && !strlen($v[0])) {
				Echo "Invalid Format Of Bulk Upload Sheet: Error 2!";
				break;
			}
				 /**
				 * Check that the number of fields is not lower than expected
				 */
			if (count($v) < count($expectedCsvFields)) {
				Echo "Invalid Format Of Bulk Upload Sheet!: Error 3";
				$this->_getSession()->addError($this->__('Line %s format is invalid and has been ignored', $k));
				break;
			 }
				/**
				 * Get fields content
				 */
		if (($i> 0) && (strlen($v[0]) > 2) && ($i < 500))
		{		
		//sleep(5);
		 $name = $v[0];
		  $description = $v[1];
		   $short_description = $v[2];
		   $price = $v[3];
		   $special_price = $v[4];
		   $qty = $v[5];
		   echo $category = $v[6];
		   $shippingcost = $v[7];
		   $image = $v[8];
		   $weight = $v[9];

			if(trim($shippingcost) == "-")
			{
				echo "Shipping Cost is - and replaced by zero";
				$shippingcost = 0;
			}
			else 
			{
				$shippingcostArray = explode(' ',$shippingcost);
				if (strtolower($shippingcostArray[0]) == 'free')
				{
					echo "Shipping Cost is mentioned FREE and therefore replaced by zero";
                                	$shippingcost = 0;
				}
			}
				
			
		 $vendorsku = $v[10];
			$error = 'No Error, Product Uploaded Successfully';
			$sku = "M".strtoupper(substr($vendorname,0,4)).rand(1111111111,9999999999)."0";
		$collection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('vendorsku', $vendorsku)
									->addFieldToFilter('udropship_vendor', $vendorid)
									->getFirstItem();
		  if($collection->getId()){

                          echo $error = "This product already exist: ";
                          $numfailed++;

                        }
		else
		{ 
		sleep(5);
		 $category_id = explode(',',$category);
		echo $category_id[1];
		if ($category_id[1] == "371")
		{
			$category_id[1] = 1161;
			$category = "74, 1161";
		}
		 $catagory_model = Mage::getModel('catalog/category');
		 $categories = $catagory_model->load($category_id[1]);
	     echo $catname = $categories->getName(); 
		 echo $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                         ->load($catname,'attribute_set_name')
						->getAttributeSetId();
						
			$image = str_replace('https://', 'http://', $image); 
			$imageArray = explode('.',$image);
			if ($imageArray[1] == 'dropbox')
			{
			echo	$image = str_replace('http://www.dropbox.com','http://dl.dropboxusercontent.com',$image);
			}
			echo $newimagename = $path.$sku.'-'.$catname.'-'.$vendorname.'-Craftsvilla'.'.jpg';
			if(!empty($image))
			{
			file_put_contents($newimagename, file_get_contents($image));
		 	$imgsize = filesize($newimagename);
			}
			else 
			{
			$imgsize = 0;
			echo $error = "You did not fill out the required field: Image Link is Missing.";
			}
			
			if(empty($short_description)){ $short_description = $name; }
			if(empty($weight)){ $weight = '1000'; }
			if($special_price < 10 ){ $special_price = '';}
			
			if(empty($name) || empty($description) || empty($short_description) || empty($price) || empty($qty) || empty($category) || empty($image) || empty($vendorsku) || empty($attributeSetId) || ($price < 10) || ($qty == 0))
			{  
				if (empty($attributeSetId))
				{
					echo $error = "Attribute Set Id is Missing for This Product. Please Inform Craftsvilla Team About This.";
				}
				if (empty($name))
				{
					echo $error = "You did not fill out the required field: Name of The Product is Missing.";
				}
				if (empty($description))
				{
					echo $error = "You did not fill out the required field: Description of The Product is Missing.";
				}
				if (empty($short_description))
				{
					echo $error = "You did not fill out the required field: Short Description of The Product is Missing.";
				}
				if (empty($price))
				{
					echo $error = "You did not fill out the required field: Price of The Product is Missing.";
				}
				if (empty($qty))
				{
					echo $error = "You did not fill out the required field: Inventory qty is Missing.";
				}
				if (empty($category))
				{
					echo $error = "You did not fill out the required field: Category Id is Missing.";
				}
				if (empty($image))
				{
					echo $error = "You did not fill out the required field: Image Link is Missing.";
				}
				if (empty($vendorsku))
				{
					echo $error = "You did not fill out the required field: Vendor SKU Id is Missing.";
				}
				if ($price < 10)
				{
					echo $error = "Price of The Product is Too Low, Zero or Incorrect. Please note prices have to be in Indian Currency Rs (INR)";
				}
				if ($qty == 0)
				{
					echo $error = "Qty Invetory is Zero. Qty cannot be zero. If you wish to do inventory update, please use bulk inventory update.";
				}
				$numfailed++;
				
			}
			elseif ($imgsize <= 0 || $imgsize < 1024)
			{
			      
				echo $error = "Image size should be more than 1KB!!!";
				$numfailed++;
				
			}
			
				
			elseif ($imgsize > 4097152)
			{
			      
				echo $error = "Image is Too Large. Please ensure image size is less than 4MB!!!";
				$numfailed++;
				
				
			}
			else
			{
				echo $productnamenew = $name.' - '.$catname.' by '.$vendorname;  
				
				$addproduct = Mage::getModel('catalog/product')
							->setDescription($description)
                            ->setSku($sku)
							->setName($productnamenew)
							->setShortDescription($short_description)
							->setWeight($weight)
							->setPrice($price)
							->setSpecialPrice($special_price)
							->setStockData(array(
										'is_in_stock' => 1,
										'qty' => $qty,
										'manage_stock' => 0,
									))
							->setShippingcost($shippingcost)
							->setTaxClassId(2) // taxable goods
                            ->setVisibility(4) // catalog, search
                            ->setStatus(1)
                            ->setTypeId('simple')
		      			    ->setStoreIDs(array(0))
							->setCategoryIds($category)
							->setAttributeSetId($attributeSetId);
		if($category){
		   //$data = explode(',', $category);
           $catIds = $category_id[0];
            if(strtolower($category_id[0]) == '6'):
                $international_shipping = 500;
                $international_shipping_morethan_one = 500;
            elseif(strtolower($category_id[0]) == '74'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($category_id[0]) == '9'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($category_id[0]) == '5'):
                $international_shipping = 1500;
                $international_shipping_morethan_one = 1500;
            elseif(strtolower($category_id[0]) == '4'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($category_id[1]) == '8'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($category_id[1]) == '1070'):
                $international_shipping = 1500;
                $international_shipping_morethan_one = 1500;
            elseif(strtolower($category_id[1]) == '284'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($category_id[1]) == '69'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($category_id[1]) == '614'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($category_id[1]) == '962'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            else:
                $international_shipping = 1500;
                $international_shipping_morethan_one = 1500;
            endif;
           
    
	   }
					$addproduct->setManufacturer($vendorname)
		                    ->setUdropshipVendor($bulkuploadcsv['vendor'])
							->setVendorsku($vendorsku)
							->setMetaDescription(substr($short_description,0,255))
		                    ->setMetaKeyword('Online Clothings, Salwar Suit')
							->setIntershippingcost($international_shipping)
                            ->setInterShippingTablerate($international_shipping_morethan_one)
                            ->setMediaGallery (array('images'=>array (), 'values'=>array ()))
		                    ->addImageToMediaGallery ($newimagename, array ('image','small_image','thumbnail'), false, false)
		                    ->setAddedFrom(1)
							->setMetaTitle(substr($name,0,35).' - by '.$vendorname.' - Buy Online Jewellery - '.$sku)
                            ->setWebsiteIds(array(1));
			
				$numsuccess++;	
				$addproduct->save();
				echo 'Added Product '.$sku.': '.$i.' of Total '.$count.' Products ';
				
			}
			}
			echo "Adding Error";
			
			$listarray[] = array($sku, $vendorsku, $name, $error);
			}
			else {
			echo "Skipped Row: ";
			if ($i > 500) break;
			}
			echo $i++;
			if ($i > 500) break;
			echo "Unset Collection";
			unset($collection);

			}//foreach closed 
			
			try{
				if($numsuccess>0)
				{
					echo $numsuccess.' Products Successfully Uploaded!!!';
				}
				if($numfailed>0)
				{
					echo $numfailed.' Products Upload Failed!!!';
				}
				
				if($numsuccess==0 && $numfailed==0)
				{
					echo 'No Products Were Uploaded. Please check your csv file!!!';
				}
				 
				//$this->_redirect('*/*/index');
			 } 
				catch(Exception $e){
				echo $e->getMessage();
				
			}
			$pathreport = Mage::getBaseDir('media') . DS . 'errorcsv' . DS;
			$file = $bulkid.'_report.csv';
			
			
				$errorcsvpath = $pathreport.$file;
				
				$fp = fopen($errorcsvpath, 'w');
				
			   	foreach ($listarray as $fields1) {
					fputcsv($fp, $fields1, ',', '"');
				}
				fclose($fp);
				$bulkuploadcsv->setErrorreport($file)
				              ->setStatus(2)
							  ->setProductsactiveted($numsuccess)
							  ->setProductsrejected($numfailed)
							  //->setTotalproducts($count)
							  ->save();
					
						$storeId = Mage::app()->getStore()->getId();	  
						$templateId = 'bulkupload_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$vendorname = $vendor['vendor_name'];
						$vendoremail = $vendor['email'];
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Pending Product Bulk Upload (ID '.$bulkid.') on Craftsvilla.com is Complete!';
						$uploadHtml = '';?>
       
				<?php $uploadHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$numsuccess."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$numfailed."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$count."</td></tr></table>";
						
            
					$vars = Array('uploadHtml' =>$uploadHtml);		
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, $vendoremail, $recname, $vars, $storeId);
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $recname, $vars, $storeId);
					
					$translate->setTranslateInline(true);
					echo "Email has been sent successfully";

	
?>

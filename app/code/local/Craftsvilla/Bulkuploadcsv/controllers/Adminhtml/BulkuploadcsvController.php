<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();
class Craftsvilla_Bulkuploadcsv_Adminhtml_BulkuploadcsvController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('bulkuploadcsv/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

/**

     * Product grid for AJAX request

     */

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('bulkuploadcsv/adminhtml_bulkuploadcsv_grid')->toHtml()
        );
    }
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('bulkuploadcsv/bulkuploadcsv')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('bulkuploadcsv_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('bulkuploadcsv/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('bulkuploadcsv/adminhtml_bulkuploadcsv_edit'))
				->_addLeft($this->getLayout()->createBlock('bulkuploadcsv/adminhtml_bulkuploadcsv_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bulkuploadcsv')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		
		if ($data = $this->getRequest()->getPost()) {
			print_r($data);
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
				        $vendorid = $data['vendor'];
						$data['filename'] = $_FILES['filename']['name'];
						$_target = Mage::getBaseDir('media') . DS . 'vendorcsv' . DS;
						$targetcsv = Mage::getBaseUrl('media').'vendorcsv/';
						$name = $data['filename'];
						$source_file_path = basename($name);
						$newfilename = mt_rand(10000000, 99999999).'_upload.csv';
						$path = $_target.$newfilename;
						$path1 = $targetcsv.$newfilename;
						$pathhtml = '<a href="'.$path1.'">'.$newfilename.'</a>';
						file_put_contents($path, file_get_contents($_FILES['filename']['tmp_name']));
						$csvObject = new Varien_File_Csv();
						$csvData = $csvObject->getData($path);
						$count = sizeof($csvData);
						$info = pathinfo($_FILES['filename']['name']);
						
						$bulk = Mage::getModel('bulkuploadcsv/bulkuploadcsv');
												$bulk->setVendor($vendorId)
												->setFilename($newfilename)
												->setStatus(3)
												->setProductsactiveted('0')
												->setProductsrejected('0')
												->setTotalproducts($count)
												->setUploaded(now())
												->setFilepath($pathhtml)
												->save();
					/* Starting upload */	
					/*$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
					$uploader->save($path, $_FILES['filename']['name'] );*/
					
				
	        
		     
	  			
	  			
			/*$model = Mage::getModel('bulkuploadcsv/bulkuploadcsv');	
			$id = $this->getRequest()->getParam('id');	
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'))
				->setComment($this->getRequest()->getParam('comment'));
			try {
				
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();*/
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('bulkuploadcsv')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
			}
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bulkuploadcsv')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('bulkuploadcsv/bulkuploadcsv');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('csv was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $bulkuploadcsvIds = $this->getRequest()->getParam('bulkuploadcsv');
        if(!is_array($bulkuploadcsvIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select csv(s)'));
        } else {
            try {
                foreach ($bulkuploadcsvIds as $bulkuploadcsvId) {
                    $bulkuploadcsv = Mage::getModel('bulkuploadcsv/bulkuploadcsv')->load($bulkuploadcsvId);
                    $bulkuploadcsv->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($bulkuploadcsvIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $bulkuploadcsvIds = $this->getRequest()->getParam('bulkuploadcsv');
        if(!is_array($bulkuploadcsvIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select csv(s)'));
        } else {
            try {
				
                foreach ($bulkuploadcsvIds as $bulkuploadcsvId) {
                  //echo $this->getRequest()->getParam('status');exit;
				   $bulkuploadcsv = Mage::getSingleton('bulkuploadcsv/bulkuploadcsv')
				   
                        ->load($bulkuploadcsvId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($bulkuploadcsvIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	public function uploadAction()
	{
		$bulkuploadcsvIds = $this->getRequest()->getParam('bulkuploadcsv');
		$bulkuploadcsv = Mage::getModel('bulkuploadcsv/bulkuploadcsv')->load(73369);
		$vendorid = $bulkuploadcsv['vendor'];
		$filename = $bulkuploadcsv['filename'];
		$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
		$vendorname = $vendor['vendor_name'];
		$vendoremail = $vendor['email'];
		$_target = Mage::getBaseDir('media') . DS . 'vendorcsv' . DS;
		$_path = $_target.$filename;
		$csvObject = new Varien_File_Csv();
        $csvData = $csvObject->getData($_path);
		$count = sizeof($csvData);
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
		$catarray = array();
		$path = Mage::getBaseDir('media') . DS . 'vendorimages' . DS;
		$listarray = array();
		$listarray[] = array("Craftsvilla SKU Id", "Vendor SKU", "Product Name", "Comments");
	   
				
	 // echo '<pre>';print_r($csvData);exit;
		/**
		 * File expected fields
		 */
		$expectedCsvFields  = array(
			0   => $this->__('name'),
			1   => $this->__('description'),
			2   => $this->__('short_description'),
			3  => $this->__('price'),
			4  => $this->__('special_price'),
			5  => $this->__('qty'),
			6  => $this->__('category'),
			7   => $this->__('shippingcost'),
			8  => $this->__('image'),
			9  => $this->__('weight'),
			10 => $this->__('vendorsku')
			
		);
		
		/* $k is line number
		 * $v is line content array
		 */
		
		 $numfailed = 0;
		 $numsuccess = 0;
		foreach ($csvData as $k => $v) 
		{
				   /**
				 * End of file has more than one empty lines
				 */
			if (count($v) <= 1 && !strlen($v[0])) {
				continue;
			}
				 /**
				 * Check that the number of fields is not lower than expected
				 */
			if (count($v) < count($expectedCsvFields)) {
				$this->_getSession()->addError($this->__('Line %s format is invalid and has been ignored', $k));
				continue;
			 }
				/**
				 * Get fields content
				 */
		    $name = $v[0];
		   $description = $v[1];
		   $short_description = $v[2];
		   $price = $v[3];
		   $special_price = $v[4];
		   $qty = $v[5];
		   $category = $v[6];
		   $shippingcost = $v[7];
		   $image = $v[8];
		   $weight = $v[9];
		   $vendorsku = $v[10];
		   
		 $category_id = explode(',',$category);
		 $catagory_model = Mage::getModel('catalog/category');
		 $categories = $catagory_model->load($category_id[1]);
	     $catname = $categories->getName();
		 $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                         ->load($catname,'attribute_set_name')
						->getAttributeSetId();
				
			$sku = "M".strtoupper(substr($vendorname,0,4)).rand(1111111111,9999999999)."0";
		    /*$newimagename = $path.$sku.'-'.$catname.'-'.$vendorname.'-Craftsvilla'.'.jpg';
			file_put_contents($newimagename, file_get_contents($image));*/
			$imgsize = filesize($newimagename);
			$image = str_replace('https://', 'http://', $image); 
			$newimagename = $path.$sku.'-'.$catname.'-'.$vendorname.'-Craftsvilla'.'.jpg';
			file_put_contents($newimagename, file_get_contents($image));
			$error = 'No Error, Product Uploaded Successfully';
			$collection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('vendorsku', $vendorsku)->getFirstItem();
			if(!($name) || empty($description) || empty($short_description) || empty($price) || empty($qty) || empty($category) || empty($image) || empty($weight) || empty($vendorsku) || empty($attributeSetId))
			{  
				$error = "You did not fill out the required fields.";
				$numfailed++;
			}
			elseif ($imgsize < 0 || $imgsize < 10240)
			{
			      
				$error = "Image size should be more than 10KB!!!";
				$numfailed++;
			}
			
				
			elseif ($imgsize > 2097152)
			{
			      
				$error = "Image is Too Large!!!";
				$numfailed++;
			}
			elseif($collection->getId()){
				
			  $error = "This product already exist: ";
			  $numfailed++;
			}
			else
			{
				
			$productnamenew = $name.' - Online Shopping for '.$catname.' by '.$vendorname;  
				
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
					$addproduct	->setManufacturer($vendorname)
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
							
			}
			
			$listarray[] = array($sku, $vendorsku, $name, $error);
			}//foreach closed 
			
			try{
				if($numsuccess>0)
				{
					$this->_getSession()->addSuccess($this->__('%s Products Successfully Uploaded!!!',$numsuccess));
				}
				if($numfailed>0)
				{
					$this->_getSession()->addError($this->__('%s Products Upload Failed!!!', $numfailed));
				}
				
				if($numsuccess==0 && $numfailed==0)
				{
					$this->_getSession()->addError($this->__('No Products Were Uploaded. Please check your csv file!!!'));
				}
				
				$this->_redirect('*/*/index');
			 } 
				catch(Exception $e){
				echo $e->getMessage();
				
			}
			$pathreport = Mage::getBaseDir('media') . DS . 'errorcsv' . DS;
			$file = $bulkuploadcsvIds[0].'_report.csv';
			
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
							//  ->setTotalproducts($count)
							  ->save();
						/*$storeId = Mage::app()->getStore()->getId();	  
						$templateId = 'bulkupload_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$vendorname = $vendor['vendor_name'];
						$vendoremail = $vendor['email'];
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your pending product bulk upload on craftsvilla.com is complete';
						$uploadHtml = '';?>
       
				<?php $uploadHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$numsuccess."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$numfailed."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$count."</td></tr></table>";
						
            
					$vars = Array('uploadHtml' =>$uploadHtml);		
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, $vendoremail, $recname, $vars, $storeId);
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, 'monica@craftsvilla.com', $recname, $vars, $storeId);
					
					$translate->setTranslateInline(true);
					echo "Email has been sent successfully";*/
					
	}
	
	public function rejectAction()
	{
		$bulkuploadcsvIds = $this->getRequest()->getParam('bulkuploadcsv');
		$bulkuploadcsv = Mage::getModel('bulkuploadcsv/bulkuploadcsv')->load($bulkuploadcsvIds);
		
		$vendorid = $bulkuploadcsv['vendor'];
		$filename = $bulkuploadcsv['filename'];
		$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
		$vendorname = $vendor['vendor_name'];
		$vendoremail = $vendor['email'];
		$_target = Mage::getBaseDir('media') . DS . 'vendorcsv' . DS;
		$_path = $_target.$filename;
        if(!is_array($bulkuploadcsvIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select csv(s)'));
        } else {
            try {
				$rejectionreason1 = $this->getRequest()->getParam('reject');
                foreach ($bulkuploadcsvIds as $bulkuploadcsvId) {
                  $bulkuploadcsv = Mage::getSingleton('bulkuploadcsv/bulkuploadcsv')

                        ->load($bulkuploadcsvId)
                        ->setRejectreason($rejectionreason1)
						->setStatus(4)
                        ->setIsMassupdate(true)
                        ->save();
				//$bulkuploadcsv = Mage::getModel('bulkuploadcsv/bulkuploadcsv')->load($bulkuploadcsvId);
		
		//echo $rejectionreason = $bulkuploadcsv['rejectreason'];exit;
		
		
		 $storeId = Mage::app()->getStore()->getId();	  
					   $templateId = 'bulkuploadrejection_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$vendorname = $vendor['vendor_name'];
						$vendoremail = $vendor['email'];
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Bulk Upload csv '.$filename.' (Id: '.$bulkuploadcsvId.') Is Rejected on craftsvilla.com';
						$rejectionreason = '';
						
            
					$vars = Array('rejectionreason' => $rejectionreason1, 'filename'=>$filename,'bulkuploadid'=>$bulkuploadcsvId);		
					
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, $vendoremail, $recname, $vars, $storeId);
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $recname, $vars, $storeId);
					
					$translate->setTranslateInline(true);
					
                $this->_redirect('*/*/index');
				}
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated and Email has been sent successfully to seller', count($bulkuploadcsvIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
			
        }
      
		
	}

	}

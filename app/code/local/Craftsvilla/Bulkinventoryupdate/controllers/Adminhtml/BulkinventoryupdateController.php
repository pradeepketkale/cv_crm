<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();
class Craftsvilla_Bulkinventoryupdate_Adminhtml_BulkinventoryupdateController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('bulkinventoryupdate/items')
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
            $this->getLayout()->createBlock('bulkinventoryupdate/adminhtml_bulkinventoryupdate_grid')->toHtml()
        );
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('bulkinventoryupdate_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('bulkinventoryupdate/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('bulkinventoryupdate/adminhtml_bulkinventoryupdate_edit'))
				->_addLeft($this->getLayout()->createBlock('bulkinventoryupdate/adminhtml_bulkinventoryupdate_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bulkinventoryupdate')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('filename');
					
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
					$uploader->save($path, $_FILES['filename']['name'] );
					
				} catch (Exception $e) {
		      
		        }
	        
		        //this way the name is saved in DB
	  			$data['filename'] = $_FILES['filename']['name'];
			}
	  			
	  			
			$model = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate');	
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
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('bulkinventoryupdate')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bulkinventoryupdate')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate');
				 
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
        $bulkinventoryupdateIds = $this->getRequest()->getParam('bulkinventoryupdate');
        if(!is_array($bulkinventoryupdateIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select csv(s)'));
        } else {
            try {
                foreach ($bulkinventoryupdateIds as $bulkinventoryupdateId) {
                    $bulkinventoryupdate = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate')->load($bulkinventoryupdateId);
                    $bulkinventoryupdate->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($bulkinventoryupdateIds)
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
        $bulkinventoryupdateIds = $this->getRequest()->getParam('bulkinventoryupdate');
        if(!is_array($bulkinventoryupdateIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select csv(s)'));
        } else {
            try {
				
                foreach ($bulkinventoryupdateIds as $bulkinventoryupdateId) {
                  //echo $this->getRequest()->getParam('status');exit;
				   $bulkinventoryupdate = Mage::getSingleton('bulkinventoryupdate/bulkinventoryupdate')
				   
                        ->load($bulkinventoryupdateId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($bulkinventoryupdateIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	public function inventoryuploadAction()
	{
		$bulkinventoryupdateIds = $this->getRequest()->getParam('bulkinventoryupdate');
		$bulkinventoryupdate = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate')->load($bulkinventoryupdateIds);
		$vendorid = $bulkinventoryupdate['vendor'];
		$filename = $bulkinventoryupdate['filename'];
		$vendor = Mage::getModel('udropship/vendor')->load($vendorid);
		$vendorname = $vendor['vendor_name'];
		$vendoremail = $vendor['email'];
		$_target = Mage::getBaseDir('media') . DS .'inventorycsv'. DS;
		$_path = $_target.$filename;
		$handle = fopen($filename, "r");
		$csvObject = new Varien_File_Csv();
        $csvData = $csvObject->getData($_path);
		$count = sizeof($csvData);
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
		$catarray = array();
		$listarray[] = array("Craftsvilla SKU Id", "Vendor SKU", "Inventory", "Comments");
	    $expectedCsvFields  = array(
			0   => $this->__('sku'),
			1   => $this->__('vendorsku'),
			2   => $this->__('qty'),
			
		);
		 $numfailed = 0;
		 $numsuccess = 0;
		 
		foreach ($csvData as $k => $v) 
		{
			if (count($v) <= 1 && !strlen($v[0])) {
				continue;
			}
				
			if (count($v) < count($expectedCsvFields)) {
				$this->_getSession()->addError($this->__('Line %s format is invalid and has been ignored', $k));
				continue;
			 }
			 
		   $sku = $v[0];
		   $vendorsku = $v[1];
		   $qty = $v[2];
		   $prod = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
		  // $_vendor = $prod->getUdropshipVendor();
		   $entity =  $prod['entity_id'];
		   $read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$prod_query = "SELECT * FROM `catalog_product_entity_int` WHERE `entity_id` = '".$entity ."' AND `attribute_id` = 531";
			$result = $read->query($prod_query)->fetchAll();	
			//var_dump($result);exit;
			foreach($result as $_result)
			{
				$_v = $_result['value'];
			}
		  	$error = 'No Error, Product Quantity Updated Successfully';
			if($sku == "sku")
			{
				continue;
			}
			if(empty($sku) || $qty=='')
			{  
				$error = "You did not fill out the required fields.";
				$numfailed++;
			}
			if($_v != $bulkinventoryupdate['vendor']){
				  $error = "This sku does not belong to you hence not updated.";
				  $numfailed++;
				 
					 }
			
		   else
			{
				
				$product = Mage::getModel('catalog/product');
				$stockItem = Mage::getModel('cataloginventory/stock_item');
			    $productId = $product->getIdBySku($sku);
				if ($productId !== false) {
					$product->load($productId);
			        $stockItem->loadByProduct($productId);
					$stockItem->setData('qty', $qty);
					$stockItem->setData('is_in_stock', ($qty > 0) ? 1 : 0);
			    try {
						$numsuccess++;
						$stockItem->save();
						
					} 
				catch(Exception $e){
				     echo $e->getMessage();
					}
				} 
			
				unset($product);
				unset($stockItem);
			   
			}
			$listarray[] = array($sku, $vendorsku, $qty, $error);
			}//foreach closed 
			
			try{
				if($numsuccess>0)
				{
					$this->_getSession()->addSuccess($this->__('%s Products Successfully Updated!!!',$numsuccess));
				}
				if($numfailed>0)
				{
					$this->_getSession()->addError($this->__('%s Products Update Failed!!!', $numfailed));
				}
				
				if($numsuccess==0 && $numfailed==0)
				{
					$this->_getSession()->addError($this->__('No Products Were Updated. Please check your csv file!!!'));
				}
				
				$this->_redirect('*/*/index');
			 } 
				catch(Exception $e){
				echo $e->getMessage();
				
			}
			
			$pathreport = Mage::getBaseDir('media'). DS . 'errorcsv'. DS;
			$file = $bulkinventoryupdateIds[0].'_report.csv';
			
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
						$templateId = 'bulkdownload_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
						$vendorname = $vendor['vendor_name'];
		                $vendoremail = $vendor['email'];
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Product Inventory Update Request On Craftsvilla.com Is Complete';
						$uploadHtml = '';?>
       
				<?php $uploadHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$numsuccess."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$numfailed."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$count."</td></tr></table>";
						
            
					$vars = Array('uploadHtml' =>$uploadHtml);			
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->addBcc('monica@craftsvilla.com')
							->sendTransactional($templateId, $sender, $vendoremail, $vendorname, $vars, $storeId);
					
					$translate->setTranslateInline(true);
									
	}
	
}

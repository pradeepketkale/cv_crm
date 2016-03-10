<?php

class Craftsvilla_Vendorneftcode_Adminhtml_VendorneftcodeController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('vendorneftcode/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('vendorneftcode/adminhtml_vendorneftcode_grid')->toHtml()
        );
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('vendorinfo_id');
		
		
		$model  = Mage::getModel('vendorneftcode/vendorneftcode')->load($id);
		
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			
			if (!empty($data)) {
			
				$model->setData($data);
			}
			
			Mage::register('vendorneftcode_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('vendorneftcode/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('vendorneftcode/adminhtml_vendorneftcode_edit'))
				->_addLeft($this->getLayout()->createBlock('vendorneftcode/adminhtml_vendorneftcode_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('vendorneftcode')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			/*if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
					/* Starting upload */	
					//$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		//$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					//$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					//$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					//$path = Mage::getBaseDir('media') . DS ;
					//$uploader->save($path, $_FILES['filename']['name'] );
					
				//} catch (Exception $e) {
		      
		       // }
	        
		        //this way the name is saved in DB
	  			//$data['filename'] = $_FILES['filename']['name'];
			//}*/
	  			
	  		$vendorId = $this->getRequest()->getParam('vendor_id');
			$vendorName = mysql_escape_string($this->getRequest()->getParam('vendor_name'));
			$merchantId = mysql_escape_string($this->getRequest()->getParam('merchant_id_city'));
			$catalog_privileges = mysql_escape_string($this->getRequest()->getParam('catalog_privileges'));
            $logistics_privileges = mysql_escape_string($this->getRequest()->getParam('logistics_privileges'));
            $payment_privileges = mysql_escape_string($this->getRequest()->getParam('payment_privileges'));
            $bulk_privileges = mysql_escape_string($this->getRequest()->getParam('bulk_privileges'));
	     $kyc_approved = mysql_escape_string($this->getRequest()->getParam('kyc_approved'));
		$read = Mage::getSingleton('core/resource')->getConnection('custom_db');
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $vendorInfoCraftsQuery = "SELECT * FROM `vendor_info_craftsvilla` WHERE `vendor_id` = '".$vendorId."'"; 
		$vendorInfoCraftsQueryRes = $read->query($vendorInfoCraftsQuery)->fetch();
		if($vendorInfoCraftsQueryRes) {
		
		$vendorInfoUpdateQuery = "UPDATE `vendor_info_craftsvilla` SET `merchant_id_city`='".$merchantId."',`vendor_name`='".$vendorName."',`catalog_privileges`='".$catalog_privileges."', `logistics_privileges`='".$logistics_privileges."', `payment_privileges`='".$payment_privileges."',`bulk_privileges`='".$bulk_privileges."',`kyc_approved`='".$kyc_approved."' WHERE `vendor_id` = '".$vendorId."'"; 
		$vendorInfoUpdateQueryRes = $write->query($vendorInfoUpdateQuery);
		
		} else {
		$vendorInfoInsertQuery = "INSERT INTO `vendor_info_craftsvilla`(`vendor_id`, `vendor_name`, `international_order`,`merchant_id_city`,`catalog_privileges`,`logistics_privileges`,`payment_privileges`,`bulk_privileges`, `kyc_approved`) VALUES ($vendorId,'$vendorName',1,'$merchantId','$catalog_privileges','$logistics_privileges','$payment_privileges',0,0)";   
		$vendorInfoInsertQueryRes = $write->query($vendorInfoInsertQuery);
		
		}
			$model = Mage::getModel('vendorneftcode/vendorneftcode');
			
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				//$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('vendorneftcode')->__('NEFTcode was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('vendorneftcode')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('vendorneftcode/vendorneftcode');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $vendorneftcodeIds = $this->getRequest()->getParam('vendorneftcode');
        
        if(!is_array($vendorneftcodeIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($vendorneftcodeIds as $vendorneftcodeId) {
                    $vendorneftcode = Mage::getModel('vendorneftcode/vendorneftcode')->load($vendorneftcodeId);
  
                    $vendorneftcode->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($vendorneftcodeIds)
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
        $vendorneftcodeIds = $this->getRequest()->getParam('vendorneftcode');
        if(!is_array($vendorneftcodeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($vendorneftcodeIds as $vendorneftcodeId) {
                    $vendorneftcode = Mage::getSingleton('vendorneftcode/vendorneftcode')
                        ->load($vendorneftcodeId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($vendorneftcodeIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

   public function catalogprivilegesAction()
	{
        $vendorneftcodeIds = $this->getRequest()->getParam('vendorneftcode');
        $model = Mage::getModel('vendorneftcode/vendorneftcode')->load($vendorneftcodeIds);
        $vendor_id = $model->getVendorId();
        $vendor_name = $model->getVendorName();
        $catalogprivileges_remark = mysql_escape_string($this->getRequest()->getParam('catalog_privileges'));
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $remarkinsertQuery = "INSERT INTO `vendoractivityremark` SET `vendorid`='".$vendor_id."',`vendorname`='".$vendor_name."',`vendoractivity`='".$catalogprivileges_remark."'"; 
        $remarkinsertrow = $write->query($remarkinsertQuery);
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('vendorneftcode')->__('Remark was successfully saved'));
      $this->_redirect('*/*/index');
        }

    public function logisticsprivilegesAction()
	{
         $vendorneftcodeIds = $this->getRequest()->getParam('vendorneftcode');
         $model = Mage::getModel('vendorneftcode/vendorneftcode')->load($vendorneftcodeIds);
         $vendor_id = $model->getVendorId();
         $vendor_name = $model->getVendorName();
         $logisticsprivileges_remark = mysql_escape_string($this->getRequest()->getParam('logistics_privileges'));
         $write = Mage::getSingleton('core/resource')->getConnection('core_write');
         $remarkinsertQuery = "INSERT INTO `vendoractivityremark` SET `vendorid`='".$vendor_id."',`vendorname`='".$vendor_name."',`vendoractivity`='".$logisticsprivileges_remark."'"; 
         $remarkinsertrow = $write->query($remarkinsertQuery);
         Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('vendorneftcode')->__('Remark was successfully saved'));
      $this->_redirect('*/*/index');
        }

public function paymentprivilegesAction()
	{
          $vendorneftcodeIds = $this->getRequest()->getParam('vendorneftcode');
          $model = Mage::getModel('vendorneftcode/vendorneftcode')->load($vendorneftcodeIds);
          $vendor_id = $model->getVendorId();
          $vendor_name = $model->getVendorName();
          $paymentprivileges_remark = mysql_escape_string($this->getRequest()->getParam('payment_privileges'));
          $write = Mage::getSingleton('core/resource')->getConnection('core_write');
          $remarkinsertQuery = "INSERT INTO `vendoractivityremark` SET `vendorid`='".$vendor_id."',`vendorname`='".$vendor_name."',`vendoractivity`='".$paymentprivileges_remark."'"; 
          $remarkinsertrow = $write->query($remarkinsertQuery);
          Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('vendorneftcode')->__('Remark was successfully saved'));
      $this->_redirect('*/*/index');
        }
}

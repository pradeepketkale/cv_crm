<?php

class Craftsvilla_Qualitycheckshipment_Adminhtml_QualitycheckshipmentController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('qualitycheckshipment/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Shipment Check'), Mage::helper('adminhtml')->__('Shipment Check'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id'); //shipment entityid
		$model  = Mage::getModel('sales/order_shipment_item')->load($id);
		//echo '<pre>';print_r($model->getData());exit;
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
//echo $model->getName();
			//Mage::register('qualitycheckshipment_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('qualitycheckshipment/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('QA check'), Mage::helper('adminhtml')->__('QA check'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('QA check'), Mage::helper('adminhtml')->__('QA check'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('qualitycheckshipment/adminhtml_qualitycheckshipment_edit'));
				//->_addLeft($this->getLayout()->createBlock('qualitycheckshipment/adminhtml_qualitycheckshipment_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('qualitycheckshipment')->__('Item does not exist'));
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
	  			
	  			
			$model = Mage::getModel('qualitycheckshipment/qualitycheckshipment');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('qualitycheckshipment')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('qualitycheckshipment')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('qualitycheckshipment/qualitycheckshipment');
				 
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
        $qualitycheckshipmentIds = $this->getRequest()->getParam('qualitycheckshipment');
        if(!is_array($qualitycheckshipmentIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($qualitycheckshipmentIds as $qualitycheckshipmentId) {
                    $qualitycheckshipment = Mage::getModel('qualitycheckshipment/qualitycheckshipment')->load($qualitycheckshipmentId);
                    $qualitycheckshipment->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($qualitycheckshipmentIds)
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
         $qualitycheckshipmentIds = $this->getRequest()->getParam('qualitycheckshipment');
		$statuses12 = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
		
        if(!is_array($qualitycheckshipmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Shipment(s)'));
        } else {
            try {
                foreach ($qualitycheckshipmentIds as $qualitycheckshipmentId) {
			
			$getStatustoUpdate = $this->getRequest()->getParam('status');
			$getCommenttoUpdate = $this->getRequest()->getParam('comment');
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$getShipmentquery = $read->query("select `sfs`.`entity_id`,`sfs`.`increment_id` from `sales_flat_shipment` as `sfs`,`sales_flat_shipment_item` as sfsi where `sfs`.`entity_id`= `sfsi`.`parent_id` AND `sfsi`.`entity_id`= '".$qualitycheckshipmentId."'")->fetch();
			$incrementId = $getShipmentquery['increment_id'];
			$getShipmentquery['entity_id'];
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$queryShipment = "update sales_flat_shipment set udropship_status='".$getStatustoUpdate."' WHERE increment_id = '".$incrementId."'";  
           $write->query($queryShipment);
		$shipment = Mage::getModel('sales/order_shipment')->load($getShipmentquery['entity_id']);
		$comment = Mage::getModel('sales/order_shipment_comment')
					->setParentId($getShipmentquery['entity_id'])
					->setIsVendorNotified(isset($getCommenttoUpdate['is_vendor_notified']))
                    ->setIsVisibleToVendor(isset($getCommenttoUpdate['is_visible_to_vendor']))
                    ->setComment($getCommenttoUpdate)
					->setCreatedAt(NOW())
					->setUdropshipStatus(@$statuses12[$getStatustoUpdate])
					->save();
					$shipment->addComment($comment);					


                    //$qualitycheckshipment = Mage::getSingleton('qualitycheckshipment/qualitycheckshipment')
                      //  ->load($qualitycheckshipmentId)
                        //->setStatus($this->getRequest()->getParam('status'))
                        //->setIsMassupdate(true)
                        //->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($incrementId))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}

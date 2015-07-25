<?php

class Craftsvilla_Wholesale_Adminhtml_WholesaleController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('wholesale/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Wholesale Details'), Mage::helper('adminhtml')->__('Wholesale Details'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('wholesale/wholesale')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('wholesale_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('wholesale/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Wholesale Details'), Mage::helper('adminhtml')->__('Wholesale Details'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('wholesale/adminhtml_wholesale_edit'))
				->_addLeft($this->getLayout()->createBlock('wholesale/adminhtml_wholesale_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('wholesale')->__('Item does not exist'));
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
	  			
	  			
			$model = Mage::getModel('wholesale/wholesale');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
				
			try {
				if ($model->getCreatedDate == NULL || $model->getUpdateDate() == NULL) {
					$model->setCreatedDate(now())
						->setUpdateDate(now());
				} else {
					$model->setUpdateDate(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('wholesale')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('wholesale')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
	
	
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('wholesale/wholesale');
				 
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

 /**
     * Export product grid to CSV format
     */
     public function exportCsvAction()
     {
        //For get email when someone xport 
			$user = Mage::getSingleton('admin/session');
			$userId = $user->getUser()->getUserId();
			$userEmail = $user->getUser()->getEmail();
			$userFirstname = $user->getUser()->getFirstname();
			$date = date("d-m-Y h:i:s",Mage::getModel('core/date')->timestamp(time()));
			$storeId = Mage::app()->getStore()->getId();
       		$translate  = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$vars = array();
			$templateId ='wholesale_export_csv';
					$translate  = Mage::getSingleton('core/translate');
					$mailSubject = 'Wholesale Csv exported By User:'.$userFirstname.' On dated:'.$date.' From ip add'.$_SERVER['REMOTE_ADDR'];
					$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
		
        	$mailTemplate=Mage::getModel('core/email_template');
        	$vars['firstname']=$userFirstname;
			$mailTemplate->setTemplateSubject($mailSubject)
						 ->sendTransactional($templateId, $sender,'manoj@craftsvilla.com',$vars,$storeId);
			$translate->setTranslateInline(true);
			$fileName   = 'wholesale.csv';
			$content    = $this->getLayout()->createBlock('wholesale/adminhtml_wholesale_grid')
							  ->getCsv();
			$this->_sendUploadResponse($fileName, $content);
	 }

     /**
     * Export product grid to XML format
     */
    /* public function exportXmlAction()
     {
        $fileName   = 'shipmentpayout.xml';
        $content    = $this->getLayout()->createBlock('shipmentpayout/adminhtml_shipmentpayout_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
     }
*/     
	 protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
     {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');

        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);

        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
     }     
     



    public function massDeleteAction() {
        $wholesaleIds = $this->getRequest()->getParam('wholesale');
        if(!is_array($wholesaleIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($wholesaleIds as $wholesaleId) {
                    $wholesale = Mage::getModel('wholesale/wholesale')->load($wholesaleId);
                    $wholesale->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($wholesaleIds)
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
        $wholesaleIds = $this->getRequest()->getParam('wholesale');
        if(!is_array($wholesaleIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($wholesaleIds as $wholesaleId) {
                    $wholesale = Mage::getSingleton('wholesale/wholesale')
                        ->load($wholesaleId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($wholesaleIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	public function emailAction(){
		
		$wholesaleIds = $this->getRequest()->getParam('wholesale');
		
		if(!is_array($wholesaleIds)) {
		
		       Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        
		}else {
		     try {
				 $storeId = Mage::app()->getStore()->getId();
        			$translate  = Mage::getSingleton('core/translate');
					$translate->setTranslateInline(false);
					$vars = array();
					$templateId ='wholesale_admin_email_template';
					$translate  = Mage::getSingleton('core/translate');
					$mailSubject = 'You Have enquiry For a Wholesale Product!';
					$sender = Array('name'  => 'Craftsvilla Wholesale',
						'email' => 'wholesale@craftsvilla.com');
		
        $mailTemplate=Mage::getModel('core/email_template');
                foreach ($wholesaleIds as $wholesaleId) {
                    $wholesale = Mage::getSingleton('wholesale/wholesale')->load($wholesaleId);
					$productname = $wholesale->getProductname();
					$quantity = $wholesale->getQuantity();
					$price = $wholesale->getOfferPrice();
					$vendorquote = $wholesale->getVendorquote();
					$deliverydate = $wholesale->getDeliverydate();
					$customeremail = $wholesale->getEmail();
					$customerName =$wholesale->getName();
										// Email set up for customer (WHOLESALE)
					       
        			//echo 'name=='.$_FILES['imgfile']['name'];exit;
        
       /* if(Mage::getSingleton('core/session')->getAttachImage()!=''){
           $image="<img src='".Mage::getSingleton('core/session')->getAttachImage()."' alt='' width='154' border='0' style='float:left; border:2px solid #7599AB; margin:0 20px 20px;' />";
        }
        else{
            $image='';
        }
*/        
        //echo "<br />hi".$data['imgfile'];exit;
	
		//$cc = 'wholesale@craftsvilla.com';
		
        /* Send mail To Customer*/
        //$vars = array('quantity' => $quantity,'offer_price' => $price,'productname' => $productname,'vendorquote' => $vendorquote,'deliverydate' => $deliverydate);
		$vars['quantity']=$quantity;
		$vars['offer_price']=$price;
		$vars['productname']=$productname;
		$vars['vendorquote']=$vendorquote;
		$vars['deliverydate']=$deliverydate;
		
		$mailTemplate->setTemplateSubject($mailSubject)
					 ->sendTransactional($templateId, $sender, $customeremail,$customerName, $vars, $storeId);
		
		}
		$translate->setTranslateInline(true);
        $this->_getSession()->addSuccess(
              $this->__('Total of %d record(s) were successfully Emailed', count($wholesaleIds))
        	  );
        } 
		catch (Exception $e) {
              $this->_getSession()->addError($e->getMessage('NOT SEND'));
          }
        }
        $this->_redirect('*/*/index');
	}
}
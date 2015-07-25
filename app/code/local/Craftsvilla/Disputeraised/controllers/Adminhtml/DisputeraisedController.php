<?php

class Craftsvilla_Disputeraised_Adminhtml_DisputeraisedController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('disputeraised/items')
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

           $this->getLayout()->createBlock('disputeraised/adminhtml_disputeraised_grid')->toHtml()

       );

   }
	public function viewAction() {
		$id = $this->getRequest()->getParam('increment_id');
		$model  = Mage::getModel('disputeraised/disputeraised')->getCollection()->addFieldToFilter('increment_id',$id)->setOrder('id', 'DESC');;
		$shipment = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('increment_id',$id);
		foreach ($shipment as $_shipment)
		{
		    $orderid = $_shipment['order_id'];
		}
		$order = Mage::getModel('sales/order')->load($orderid);
		$custfname = $order['customer_firstname'];
		$custlname =  $order['customer_lastname'];
		$html = '';
		$html .= '<strong>ShipmentId: '.$id.'</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Customer Name: '
		  .$custfname.' '.$custlname.'</strong><br><br><div><table border="1"><tr><th>Image</th><th>Content</th><th>Added By</th></tr>';
		foreach ($model as $_model)
		{
            $incrementid = $_model->getIncrementId();
            $html .= '<tr><td>'.$_model['image'].'</td><td width="600px">'.$_model['content'].'</td><td width="250px">'.$_model['addedby'].'</td></tr>';
		}
		 $html .= '</table></div>';
         echo $html;
         //$this->_redirect('*/*/');
		/*if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('disputeraised_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('disputeraised/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('disputeraised/adminhtml_disputeraised_edit'))
				->_addLeft($this->getLayout()->createBlock('disputeraised/adminhtml_disputeraised_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('disputeraised')->__('Item does not exist'));*/
			//$this->_redirect('*/*/');
		//}
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
	  			
	  			
			$model = Mage::getModel('disputeraised/disputeraised');		
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
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('disputeraised')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('disputeraised')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('disputeraised/disputeraised');
				 
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
        $disputeraisedIds = $this->getRequest()->getParam('disputeraised');
        if(!is_array($disputeraisedIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($disputeraisedIds as $disputeraisedId) {
                    $disputeraised = Mage::getModel('disputeraised/disputeraised')->load($disputeraisedId);
                    $disputeraised->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($disputeraisedIds)
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
        $disputeraisedIds = $this->getRequest()->getParam('disputeraised');
        if(!is_array($disputeraisedIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($disputeraisedIds as $disputeraisedId) {
                    $disputeraised = Mage::getSingleton('disputeraised/disputeraised')
                        ->load($disputeraisedId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($disputeraisedIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
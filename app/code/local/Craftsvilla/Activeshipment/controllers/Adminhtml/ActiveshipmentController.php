<?php

class Craftsvilla_Activeshipment_Adminhtml_ActiveshipmentController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('activeshipment/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Active Shipment Details'), Mage::helper('adminhtml')->__('Active Shipment Details'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('activeshipment/activeshipment')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('activeshipment_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('activeshipment/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Active Shipment'), Mage::helper('adminhtml')->__('Active Shipment'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Active Shipment'), Mage::helper('adminhtml')->__('Active Shipment'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('activeshipment/adminhtml_activeshipment_edit'))
				->_addLeft($this->getLayout()->createBlock('activeshipment/adminhtml_activeshipment_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('activeshipment')->__('Shipment ID does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			$model = Mage::getModel('activeshipment/activeshipment');		
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
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('activeshipment')->__('active shipment was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('activeshipment')->__('Unable to find activeshipment to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('activeshipment/activeshipment');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Active Shipment was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $activeshipmentIds = $this->getRequest()->getParam('activeshipment');
        if(!is_array($activeshipmentIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select shipment(s)'));
        } else {
            try {
                foreach ($activeshipmentIds as $activeshipmentId) {
                    $activeshipment = Mage::getModel('activeshipment/activeshipment')->load($activeshipmentId);
                    $activeshipment->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($activeshipmentIds)
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
        $activeshipmentIds = $this->getRequest()->getParam('activeshipment');
        if(!is_array($activeshipmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select shipment(s)'));
        } else {
            try {
                foreach ($activeshipmentIds as $activeshipmentId) {
                    $activeshipment = Mage::getSingleton('activeshipment/activeshipment')
                        ->load($activeshipmentId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($activeshipmentIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
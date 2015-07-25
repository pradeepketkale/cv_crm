<?php

class Craftsvilla_Uagent_Adminhtml_UagentController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('uagent/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Agent Details'), Mage::helper('adminhtml')->__('Agent Details'));
		
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
        $this->getLayout()->createBlock('uagent/adminhtml_uagent_grid')->toHtml()
        );
    }


	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('uagent/uagent')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('uagent_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('uagent/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Agent Details'), Mage::helper('adminhtml')->__('Agent Details'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Agent Details News'), Mage::helper('adminhtml')->__('Agent News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('uagent/adminhtml_uagent_edit'))
				->_addLeft($this->getLayout()->createBlock('uagent/adminhtml_uagent_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('uagent')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		
		if ($data = $this->getRequest()->getPost()) {
			$country = $data['country_id'];
			$region_name = $data['region'];
			$emailGet = $data['email'];
			$password = $data['password'];
			$regionModel = Mage::getModel('directory/region')->loadByName($region_name, $country);
			$regionId = $regionModel->getId();
		  	$model = Mage::getModel('uagent/uagent');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				$model->setRegion($region_name);
				$model->setRegionId($regionId);
				$model->setPassword($password);
				$model->setPasswordHash(Mage::helper('core')->getHash($password, 2));
				$model->save();
			
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('uagent')->__('Agent was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('uagent')->__('Unable to find agent to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('uagent/uagent');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Agent was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $uagentIds = $this->getRequest()->getParam('uagent');
        if(!is_array($uagentIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($uagentIds as $uagentId) {
                    $uagent = Mage::getModel('uagent/uagent')->load($uagentId);
                    $uagent->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($uagentIds)
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
        $uagentIds = $this->getRequest()->getParam('uagent');
        if(!is_array($uagentIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select agent(s)'));
        } else {
            try {
                foreach ($uagentIds as $uagentId) {
                    $uagent = Mage::getSingleton('uagent/uagent')
                        ->load($uagentId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($uagentIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	 
	 public function agentcommissionAction()
    {

        $uagentIds = $this->getRequest()->getParam('uagent');
		$updateComm = $this->getRequest()->getParam('agcommission');
		if(!is_array($uagentIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select agent(s)'));
        } else {
            try {
                foreach ($uagentIds as $uagentId) {
                    $uagent = Mage::getSingleton('uagent/uagent')
                        ->load($uagentId)
                        ->setAgentCommission($updateComm)
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated commission', count($uagentIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}

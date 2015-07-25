<?php

class Craftsvilla_Managemkt_Adminhtml_ManagemktController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('managemkt/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Marketing'), Mage::helper('adminhtml')->__('Manage Marketing'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('managemkt/managemkt')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('managemkt_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('managemkt/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Marketing'), Mage::helper('adminhtml')->__('Manage Marketing'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('marketing '), Mage::helper('adminhtml')->__('Marketing'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('managemkt/adminhtml_managemkt_edit'))
				->_addLeft($this->getLayout()->createBlock('managemkt/adminhtml_managemkt_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('managemkt')->__('Market item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('managemkt/managemkt');		
			//from here I have to start..
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			try {
				/*if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}*/	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('managemkt')->__('Marketing pk was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if($this->getRequest()->getParam('back')) {
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('managemkt')->__('Unable to find pk to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('managemkt/managemkt');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Mkting pk was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $managemktIds = $this->getRequest()->getParam('managemkt');
        if(!is_array($managemktIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Pk(s)'));
        } else {
            try {
                foreach ($managemktIds as $managemktId) {
                    $managemkt = Mage::getModel('managemkt/managemkt')->load($managemktId);
                    $managemkt->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($managemktIds)
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
	    $managemktIds = $this->getRequest()->getParam('managemkt');
		$pmstatus = $this->getRequest()->getParam('status');
		$getstatus = Mage::getModel('managemkt/managemkt')->load($managemktIds)->getStatus();
		$activityRate = array('250','2500','500','100','100','25000');
		
		if ($getstatus == $pmstatus || $getstatus == 3)
			{
			$this->_getSession()->addError(
			$this->__('Once status has been Executed ,Will not Update...', count($managemktIds)) );
			}
		else
			{
			if(!is_array($managemktIds)) 
				{
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
				} 
			else 
				{
					try {
						foreach ($managemktIds as $managemktId) {
						$managemkt = Mage::getSingleton('managemkt/managemkt')
									->load($managemktId)
									->setStatus($this->getRequest()->getParam('status'))
									->setIsMassupdate(true)
									->save();
						
						
						if ($managemkt->getStatus()== 3)
						{
							$mktvendorread = Mage::getSingleton('core/resource')->getConnection('mktvendors_read'); 
							$vendorMktpkgQuery = "SELECT * FROM `mktvendors` WHERE `vendor` = '".$managemkt->getVendorname()."'"; 
							$resultvendorMktpkg = $mktvendorread->fetchAll($vendorMktpkgQuery);
							$difference = (strtotime($managemkt->getEndDate()) - strtotime($managemkt->getStartDate()));
							$datediff   = floor($difference / (60*60*24))+1;
							if ($managemkt->getActivity() == 3 || $managemkt->getActivity() == 4 || $managemkt->getActivity() == 5)
								{
								$rateBalance = $activityRate[$managemkt->getActivity()-1]*$datediff;
								}
							else
								{
								$rateBalance = $activityRate[$managemkt->getActivity()-1];
								}	
							 
							 // For 'Cost Column'of activity marketing
							 $managemktwrite = Mage::getSingleton('core/resource')->getConnection('managemkt_write'); 
							 $updatecostQuery = "update `managemkt` set `cost` = '".$rateBalance."' WHERE `managemkt_id` = '".$managemktId."'";
           				     $resultCostBalance =$managemktwrite->query($updatecostQuery); 
							 
								$mktvendorwrite = Mage::getSingleton('core/resource')->getConnection('mktvendors_write');
								foreach($resultvendorMktpkg as $_resultvendorMktpkg)
									{
										if($_resultvendorMktpkg['balance'] >= $rateBalance)
											{
												$balance =  $_resultvendorMktpkg['balance'] - $rateBalance;
												$vendorMktpkgQuery = "update `mktvendors` set `balance` = '".$balance."' WHERE `mktvendors_id` = '".$_resultvendorMktpkg['mktvendors_id']."'";
												$resultBalance =$mktvendorwrite->query($vendorMktpkgQuery);
												$rateBalance = 0;
												break;
											}
											else
											{
												$rateBalance =  $rateBalance - $_resultvendorMktpkg['balance'];
												$balance = 0;
												$vendorMktpkgQuery = "update `mktvendors` set `balance` = '".$balance."' WHERE `mktvendors_id` = '".$_resultvendorMktpkg['mktvendors_id']."'";
												$resultBalance = $mktvendorwrite->query($vendorMktpkgQuery);
											}
									}
									if($rateBalance > 0 )
										{ 
										        $balance = -$rateBalance;
										        $vendorMktpkgQuery = "update `mktvendors` set `balance` = '".$balance."' WHERE `mktvendors_id` = '".$_resultvendorMktpkg['mktvendors_id']."'";
												$resultBalance =$mktvendorwrite->query($vendorMktpkgQuery);
										}
								
							$storeId = Mage::app()->getStore()->getId();
							$templateId = 'marketing_email_seller_template';
							$mailSubject = '';
							$sender = Array('name' => 'Craftsvilla Marketing',
									'email' => 'places@craftsvilla.com');
							$vendors = Mage::getModel('udropship/vendor')->getCollection()
									->addFilter('status', array('eq' => 'A'));
							$translate  = Mage::getSingleton('core/translate');
							$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
						
							if($managemkt->getActivity() == 1)
							{ $activitystat = "Facebook Post"; }
							
							if($managemkt->getActivity() == 2)
							{ $activitystat = "Emailer"; } 
							
							if($managemkt->getActivity() == 3)
							{ $activitystat = "Homepage Banner";}
							
							if($managemkt->getActivity() == 4) 
							{$activitystat = "Homepage Product";} 
							
							if($managemkt->getActivity() == 5) 
							{$activitystat ="Featured Seller";}
							
							if($managemkt->getActivity() == 6) 
							{$activitystat ="Guaranteed Sale";}
							
						
						
							foreach($vendors as $vendor)
							{
							$vendorInfo = $vendor->load($managemkt->getVendorname());
							$vars = Array('vendorEmail' =>$vendorInfo->getEmail(),
										  'vendorName' =>$vendorInfo->getVendorName(),
										  'activity' =>$activitystat,
										  'date' =>$managemkt->getStartDate(),
										 );
							//echo '<pre>';print_r($vars);exit;
							}	
							$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
									->setTemplateSubject($mailSubject)
									->sendTransactional($templateId, $sender, $vendorInfo->getEmail(), $vendorInfo->getVendorName(), $vars, $storeId);
							$translate->setTranslateInline(true);	
							$this->_getSession()->addSuccess(
							$this->__('Total of %d record(s) were successfully Updated & email has sent to seller', count($managemktIds)) );
						}
					else
						{	
						$this->_getSession()->addSuccess(
						$this->__('Total of %d record(s) were successfully updated', count($managemktIds)) );
						}
					}
					} catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
					}
				}
			}
        $this->_redirect('*/*/index');
    }
}
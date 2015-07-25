<?php
class Craftsvilla_ReferFriend_Block_Referral_Accept extends Mage_Core_Block_Template
{
	public $registration_status;
	public $purchase_status;
	
	public function _construct(){
		$this->setTemplate('referfriend/referral/accept.phtml');	
		
		$orders = Mage::getResourceModel('referfriend/referral_collection')
			->addFieldToFilter('referral_parent_id', Mage::getSingleton('customer/session')->getId())
			->addFieldToFilter('referral_register_status', '1')
			->addFieldToSelect('*');

		$this->setReferrals($orders);
		Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
	}
	
	protected function _prepareLayout()
	{
		parent::_prepareLayout();			
		$pager = $this->getLayout()->createBlock('page/html_pager', 'referfriend.referral.accept.pager')->setCollection($this->getReferrals());
		$this->setChild('pager', $pager);
		$this->getReferrals()->load();
		return $this;
	}
	
	public function getRegistrationStatus(){
		if($this->registration_status == '1'){
			return 'Yes';
		}else{
			return 'No';	
		}	
	}
	
	public function getPurchaseStatus(){
		if($this->purchase_status == '1'){
			return 'Yes';
		}else{
			return 'No';	
		}	
	}
	
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
}
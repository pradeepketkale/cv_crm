<?php
class Craftsvilla_Coupons_IndexController extends Mage_Core_Controller_Front_Action
{

	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}
	
    public function indexAction()
    {
		$session = $this->_getSession();
			if(!$session->isLoggedIn()){
				$current_url = Mage::getUrl().'coupons';
				$session->setData("after_auth_url", $current_url);
				$this->_redirectUrl(Mage::getUrl().'customer/account/login');				
				return;
			}
		 $this->loadLayout(array('default'));
		 $this->renderLayout();
    }
}
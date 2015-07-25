<?php

class Craftsvilla_Follow_IndexController extends Mage_Core_Controller_Front_Action
{
	public function savePostAction()
	{
		$Follow = '';
		$Follow = Mage::getModel('follow/follow');
		$r = $this->getRequest();
		$_customerId = $r->getParam('customerId');
        	$_vendorId = $r->getParam('vendorId');
		$_status = $r->getParam('status');
		$_followId = $r->getParam('followId');
		
		if($_followId)
			$Follow->load($_followId);	
		$Follow->setCustomerId($_customerId)
				->setVendorId($_vendorId)
				->setStatus($_status)
				->save();
		
		if($_status){
			$_helpv = Mage::helper('udropship');
			$vendorIfo = '';
			$customer = '';
			$customerData = '';
			$storeId = Mage::app()->getStore()->getId();
			$templateVendorId = 'follow_email_seller';
			$templateCustomerId = 'follow_email_customer';
			$sender = Array('name'  => 'Craftsvilla',
			'email' => 'messages@craftsvilla.com');
			$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			$vendorIfo = $_helpv->getVendor($_vendorId);
			$customer = Mage::getModel('customer/customer')->getCollection()
							->addNameToSelect()
							->addFieldToFilter('entity_id', $_customerId);
			$customerData = $customer->getData();
			$_vendorName = $vendorIfo->getVendorName();
			$_vendorEmail = $vendorIfo->getEmail();
			$_vendorShopName = $vendorIfo->getShopName();
			$_vendorShopUrl = $baseUrl.$vendorIfo->getUrlKey();
			$_customerName = $customerData[0]['name'];
			$_customerEmail = $customerData[0]['email'];
			$varsVendorEmail = Array('customerName' =>	$_customerName,
						'vendorName' => $_vendorName,
						'vendorShopName' => $_vendorShopName,
						'vendorShopUrl' =>	$_vendorShopUrl,
			);
			$varsCustomerEmail = Array('customerName' =>$_customerName,
						'vendorShopName' => $_vendorShopName,
						'vendorShopUrl' =>	$_vendorShopUrl,
			);
			$_email = Mage::getModel('core/email_template')->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId));
			$_email->sendTransactional($templateVendorId, $sender, $_vendorEmail, $_vendorName, $varsVendorEmail, $storeId);
			$_email->sendTransactional($templateCustomerId, $sender, $_customerEmail, $_customerName, $varsVendorEmail, $storeId);
			echo $_status.'##%%%%%%%%%##<a onclick="followseller('.$_customerId.','.$_vendorId.',0,'.$Follow->getId().');" class="spriteimg follow unfollw" style="cursor:pointer;">UnFollow Shop</a>';
		}
		else
			echo $_status.'##%%%%%%%%%##<a onclick="followseller('.$_customerId.','.$_vendorId.',1,'.$Follow->getId().');" class="spriteimg follow" style="cursor:pointer;">Follow Shop</a>';
	}
        
        public function followshopproductAction()
	{
            echo $this->getLayout()->createBlock('follow/follow')->setTemplate('follow/follow.phtml')->toHtml();
        }
}

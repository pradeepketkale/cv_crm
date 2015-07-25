<?php
class Craftsvilla_ReferFriend_Model_Referral extends Mage_Core_Model_Abstract
{
	public function _construct()
    {
        parent::_construct();
        $this->_init('referfriend/referral');
    }
	public function sendReferMail($name,$email,$refercode,$customer)
	{

		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'referfriend_test_email_template';
		$mailSubject = $customer->getName().' has invited you to craftsvilla.com';
		
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'raf@craftsvilla.com');
		
		$refer_base_url = Mage::getUrl();
		$refer_url= $refer_base_url.'customer/account/create/refercode/'.$refercode;
		
		$vars = Array('customeremail'=>$customer->getEmail(),
		'refercode' =>$refercode,
		'referurl' =>$refer_url
		);
		
		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		Mage::getModel('core/email_template')
		->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
		->setTemplateSubject($mailSubject)
		->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
		$translate->setTranslateInline(true);
	}

	public function sendVoucherMail($senderEmail,$senderName,$custEmail,$voucherCode,$voucherAmount,$expiry)
	{
		
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'referfriend_amit_email_template';
		$mailSubject = 'You got a Refer Voucher from Craftsvilla.com';
		
		$sender = Array('name'  => '',
		'email' => 'raf@craftsvilla.com');
		
		$vars = Array('vouchercode' =>$voucherCode,
		'referalEmail' =>$custEmail,
		'discount_amount' => $voucherAmount,
		'senderName' => $senderName,
		'expiry' => $expiry,
		);
		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		Mage::getModel('core/email_template')
		->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
		->setTemplateSubject($mailSubject)
		->sendTransactional($templateId, $sender, $senderEmail, $senderName, $vars, $storeId);
		$translate->setTranslateInline(true);
	}
}

<?php

class Craftsvilla_Uagent_Model_Uagent extends Mage_Core_Model_Abstract
{
	protected $_eventPrefix = 'uagent_agent';
    protected $_eventObject = 'agent';

    protected $_inAfterSave = false;

    protected function _construct()
    {
        $this->_init('uagent/uagent');
        parent::_construct();
        
    }

    public function authenticate($username, $password)
    {
		
        $collection = $this->getCollection();
        $collection->getSelect()->where('email=?', $username);
        foreach ($collection as $candidate) {
			
            if (!Mage::helper('core')->validateHash($password, $candidate->getPasswordHash())) {
                continue;
            }
			
            $this->load($candidate->getId());
            return true;
        }
        $this->load($username, 'email');
        if (!$this->getId()) {
            $this->unsetData();
            return false;
        }
        /*$masterPassword = Mage::getStoreConfig('udropship/vendor/master_password');
        if ($masterPassword && $password==$masterPassword) {
            return true;
        }*/
        return false;
    }
    
    public function validate()
    {
		$dhlp = Mage::helper('uagent');
        extract($this->getData());
		//echo "\$country_id = $country;";exit;
		if (!isset($agent_name) || !isset($telephone) || !isset($email) ||
            !isset($password) || !isset($password_confirm) || 
            !isset($street1) || !isset($city) || !isset($country)) {
           
            Mage::throwException($dhlp->__('Incomplete form data'));
        }
		
		if ($password!=$password_confirm) {
            Mage::throwException($dhlp->__('Passwords do not match'));
        }
        $collection = Mage::getModel('uagent/uagent')->getCollection()
            ->addFieldToFilter('email', $email);
        //echo '<pre>';print_r($collection->getData());exit;
		foreach ($collection as $dup) {
			
            if ($dup->getEmail() == $email) {
                Mage::throwException($dhlp->__('A agent with supplied email already exists.'));
            }
            if (Mage::helper('core')->validateHash($password, $dup->getPasswordHash())) {
                Mage::throwException($dhlp->__('A agent with supplied email and password already exists.'));
            }
        }
        
		$this->setCountryId($country);
        $this->setStreet($street1."\n".$street2);
        $this->setPasswordEnc(Mage::helper('core')->encrypt($password));
        $this->setPasswordHash(Mage::helper('core')->getHash($password, 2));
        $this->unsPassword();
        $this->setRemoteIp($_SERVER['REMOTE_ADDR']);
        $this->setCreatedTime(now());
        $this->setStoreId(Mage::app()->getStore()->getId());

	return $this;
    }
	
}
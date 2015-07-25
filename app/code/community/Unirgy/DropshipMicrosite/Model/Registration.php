<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipMicrosite
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipMicrosite_Model_Registration extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('umicrosite/registration');
        parent::_construct();
    }
/*
    protected function _afterLoad()
    {
        parent::_afterLoad();
        Mage::helper('udropship')->loadCustomData($this);
    }
*/
    public function validate()
    {
        $hlp = Mage::helper('umicrosite');
        $dhlp = Mage::helper('udropship');
        extract($this->getData());
		if (!isset($vendor_name) || !isset($telephone) || !isset($email) ||
            !isset($password) || !isset($password_confirm) || 
            !isset($street1) || !isset($city) || !isset($country_id)) {
            Mage::throwException($hlp->__('Incomplete form data'));
        }
        /*if (!isset($vendor_name) || !isset($telephone) || !isset($email) ||
            !isset($password) || !isset($password_confirm) || !isset($carrier_code) ||
            !isset($street1) || !isset($city) || !isset($country_id) ||
            !isset($url_key)) {
            Mage::throwException($hlp->__('Incomplete form data'));
        }*/
        if ($password!=$password_confirm) {
            Mage::throwException($hlp->__('Passwords do not match'));
        }
        $collection = Mage::getModel('udropship/vendor')->getCollection()
            ->addFieldToFilter('email', $email);
        foreach ($collection as $dup) {
            if (Mage::getStoreConfig('udropship/vendor/unique_email')) {
                Mage::throwException($dhlp->__('A vendor with supplied email already exists.'));
            }
            if (Mage::helper('core')->validateHash($password, $dup->getPasswordHash())) {
                Mage::throwException($dhlp->__('A vendor with supplied email and password already exists.'));
            }
        }
        $vendor = Mage::getModel('udropship/vendor')->load($url_key, 'url_key');
        if ($vendor->getId()) {
            Mage::throwException($hlp->__('This subdomain is already taken, please choose another.'));
        }
        $this->setStreet($street1."\n".$street2);
        $this->setPasswordEnc(Mage::helper('core')->encrypt($password));
        $this->setPasswordHash(Mage::helper('core')->getHash($password, 2));
        $this->unsPassword();
        $this->setRemoteIp($_SERVER['REMOTE_ADDR']);
        $this->setRegisteredAt(now());
        $this->setStoreId(Mage::app()->getStore()->getId());

        $dhlp->processCustomVars($this);

        return $this;
    }

    public function toVendor()
    {
        $vendor = Mage::getModel('udropship/vendor')->load(Mage::getStoreConfig('udropship/microsite/template_vendor'));
        $vendor->getShippingMethods();
        $vendor->addData($this->getData());
        Mage::helper('udropship')->loadCustomData($vendor);
        $vendor->setPassword(Mage::helper('core')->decrypt($this->getPasswordEnc()));
        $vendor->unsVendorId();
        $shipping = $vendor->getShippingMethods();
        foreach ($shipping as $sId=>&$s) {
            if ($s['carrier_code']==$vendor->getCarrierCode()) {
                $s['carrier_code'] = null;
            }
        }
        unset($s);
        $vendor->setShippingMethods($shipping);
        return $vendor;
    }
}
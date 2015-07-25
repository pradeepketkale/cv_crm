<?php

class Marketplace_Thoughtyard_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_currentVendor;
    protected $_origBaseUrl;
    protected $_parsedBaseUrl;
    protected $_vendorBaseUrl = array();

    
    public function getVendorBaseUrl($vendor=null)
    {
        $level = Mage::getStoreConfig('udropship/microsite/subdomain_level');
        if (is_null($vendor) || $vendor===true) {
            $vendor = $this->getCurrentVendor();
        } else {
            $vendor = Mage::helper('udropship')->getVendor($vendor);
        }
        if (!$level || !$vendor || !$vendor->getId() || !$vendor->getUrlKey()) {
            return $this->_origBaseUrl;
        }
        $vId = $vendor->getId();
        if (!isset($this->_vendorBaseUrl[$vId])) {
            $store = Mage::app()->getStore();
            if ($this->updateStoreBaseUrl() && $this->getCurrentVendor() && $this->getCurrentVendor()->getId()==$vendor->getId()) {
                $baseUrl = $store->getBaseUrl();
            } else {
                $url = $this->_parsedBaseUrl;
                $hostArr = explode('.', trim($url['host'], '.'));
                $l = sizeof($hostArr);
                if ($l-$level>=0) {
                    $hostArr[$l-$level] = $vendor->getUrlKey();
                } else {
                    array_unshift($hostArr, $vendor->getUrlKey());
                }
                $baseUrl = $url['scheme'].'://'.join('.', $hostArr).(isset($url['path']) ? $url['path'] : '/');
            }
            $this->_vendorBaseUrl[$vId] = $baseUrl;
        }
        return $this->_vendorBaseUrl[$vId];
    }

    public function withOrigBaseUrl($url, $prefix='')
    {
        $level = Mage::getStoreConfig('udropship/microsite/subdomain_level');
        if (!$level) {
            return $url;
        }
        $p = parse_url($url);
        $host = join('.', array_slice(explode('.', trim($p['host'], '.')), 1-$level));
        return $p['scheme'].'://'.$prefix.$host.$p['path']
            .(!empty($p['query'])?'?'.$p['query']:'')
            .(!empty($p['fragment'])?'?'.$p['fragment']:'');
/*
        $o = $this->_origBaseUrl;
        $v = Mage::getStoreConfig('web/unsecure/base_url');
        return $o!=$v ? str_replace($v, $o, $url) : $url;
*/
    }

    public function updateStoreBaseUrl()
    {
        return Mage::getStoreConfig('udropship/microsite/update_store_base_url');
    }

    /**
    * Get URL specific for vendor
    *
    * @param boolean|integer|Unirgy_Dropship_Model_Vendor $vendor
    * @param string|Mage_Catalog_Model_Product $orig original product or URL to be converted to vendor specific
    */
    public function getVendorUrl($vendor, $origUrl=null)
    {
        if ($vendor===true) {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return $origUrl;
            }
        } else {
            $vendor = Mage::helper('udropship')->getVendor($vendor);
        }
        $vendorBaseUrl = $this->getVendorBaseUrl($vendor);
        if (is_null($origUrl)) {
            return $vendorBaseUrl;
        }
        if ($origUrl instanceof Mage_Catalog_Model_Product) {
            $origUrl = $origUrl->getProductUrl();
        }
        if ($this->updateStoreBaseUrl() && ($curVendor = $this->getCurrentVendor())) {
            if ($curVendor->getId()==$vendor->getId()) {
                return $origUrl;
            }
            $origBaseUrl = $this->getVendorBaseUrl($curVendor);
        } else {
            $origBaseUrl = $this->_origBaseUrl;
        }
        return str_replace($origBaseUrl, $vendorBaseUrl, $origUrl);
    }

    public function getProductUrl($product)
    {
        return $this->getVendorUrl(Mage::helper('udropship')->getVendor($product), $product);
    }

    
    public function getDomainName()
    {
        $level = Mage::getStoreConfig('udropship/microsite/subdomain_level');
        if (!$level) {
            return '';
        }
        $baseUrl = Mage::getStoreConfig('web/unsecure/base_url');
        $url = parse_url($baseUrl);
        $hostArr = explode('.', $url['host']);
        return join('.', array_slice($hostArr, -($level-1)));
    }

    /**
    * Send new registration to store owner
    *
    * @param Mage_Sales_Model_Order_Shipment $shipment
    * @param string $comment
    */
    public function sendVendorRegistration($registration)
    {
        $store = Mage::app()->getStore($registration->getStoreId());
        $to = $store->getConfig('udropship/microsite/registration_receiver');
        $subject = $store->getConfig('udropship/microsite/registration_subject');
        $template = $store->getConfig('udropship/microsite/registration_template');
        $ahlp = Mage::getModel('adminhtml/url');

        if ($to && $subject && $template) {
            $data = $registration->getData();
            $data['store_name'] = $store->getName();
            $data['registration_url'] = $ahlp->getUrl('umicrositeadmin/adminhtml_registration/edit', array(
                'reg_id' => $registration->getId(),
                'key' => null,
            ));
            $data['all_registrations_url'] = $ahlp->getUrl('umicrositeadmin/adminhtml_registration', array(
                'key' => null,
            ));

            foreach ($data as $k=>$v) {
                $subject = str_replace('{{'.$k.'}}', $v, $subject);
                $template = str_replace('{{'.$k.'}}', $v, $template);
            }

            foreach (explode(',', $to) as $toEmail) {
                mail(trim($toEmail), $subject, $template, 'From: "'.$registration->getVendorName().'" <'.$registration->getEmail().'>');
            }
        }

        return $this;
    }

    public function addVendorFilterToProductCollection($collection)
    {
        $vendor = $this->getCurrentVendor();
        try {
            if ($vendor) {
                $collection->addAttributeToFilter('udropship_vendor', $vendor->getId());
            } else {
                $cond = "_table_udropship_status.vendor_id IS null OR _table_udropship_status.status='A'";
                $session = Mage::getSingleton('udropship/session');
                if ($session->isLoggedIn() && $session->getVendor()->getStatus()=='I') {
                    $cond .= " OR _table_udropship_status.vendor_id=".$session->getVendor()->getId();
                }
                $alreadyJoined = false;
                foreach ($collection->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $column) {
                    if ($column[2]=='udropship_vendor' || $column[2]=='udropship_status') {
                        $alreadyJoined = true;
                        break;
                    }
                }
                if (!$alreadyJoined) {
                    //$collection->joinAttribute('udropship_vendor', 'catalog_product/udropship_vendor', 'entity_id', null, 'left');
                    //$collection->joinField('udropship_status', 'udropship/vendor', 'status', 'vendor_id=udropship_vendor', $cond, 'left');
                }
            }
        } catch (Exception $e) {
            $skip = array(
                Mage::helper('eav')->__('Joined field with this alias is already declared'),
                Mage::helper('eav')->__('Invalid alias, already exists in joined attributes'),
                Mage::helper('eav')->__('Invalid alias, already exists in joint attributes.'),
            );
            if (!in_array($e->getMessage(), $skip)) {
                throw $e;
            }
        }
        return $this;
    }
}

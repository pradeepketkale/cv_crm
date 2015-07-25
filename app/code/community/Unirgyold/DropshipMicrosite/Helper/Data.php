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

class Unirgy_DropshipMicrosite_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_currentVendor;
    protected $_origBaseUrl;
    protected $_parsedBaseUrl;
    protected $_vendorBaseUrl = array();

    public function getLandingPageTitle($vendor=null)
    {
    	if ($vendor==null) {
    		if (!$this->getCurrentVendor()) return '';
    		$vendor = $this->getCurrentVendor();
    	}
    	$title = Mage::getStoreConfig('udropship/microsite/landing_page_title');
    	if ($vendor->getData('landing_page_title')) {
    		$title = $vendor->getData('landing_page_title');
    	}
    	$title = str_replace('[vendor_name]', $vendor->getVendorName(), $title);
    	return !empty($title) ? $title : $vendor->getVendorName();
    }
    
    public function getCurrentVendor()
    {
        if (is_null($this->_currentVendor)) {
            if (($vendor = $this->getFrontendVendor())) {
                $this->_currentVendor = $vendor; // it's a frontend (from subdomain)
            } elseif (Mage::app()->getStore()->isAdmin()) {
                if (($vendor = $this->getAdminhtmlVendor())) {
                    $this->_currentVendor = $vendor; // it's adminhtml (from user)
                } else {
                    $this->_currentVendor = false;
                }
            } elseif (($product = Mage::registry('current_product'))) {
                $this->_currentVendor = Mage::helper('udropship')->getVendor($product);
                if (!$this->_currentVendor->getId()) {
                    $this->_currentVendor = false;
                }
            } else {
                // if route known, make it permanent
                if (Mage::app()->getRequest()->getRouteName()) {
                    $this->_currentVendor = false;
                }
                // otherwise check next time
                return false;
            }
        }
        return $this->_currentVendor;
    }

    public function getFrontendVendor()
    {
        $this->_origBaseUrl = Mage::getStoreConfig('web/unsecure/base_link_url');
        $url = parse_url($this->_origBaseUrl);
        $this->_parsedBaseUrl = $url;

        if (empty($_SERVER["HTTP_HOST"])) {
            return false;
        }
        $level = Mage::getStoreConfig('udropship/microsite/subdomain_level');
        $host = $_SERVER["HTTP_HOST"];
        $hostArr = explode('.', trim($host, '.'));
        $i = sizeof($hostArr)-$level;

        if (empty($level) || empty($hostArr[$i])) {
            return false;
        }

        $vendor = Mage::getModel('udropship/vendor')->load($hostArr[$i], 'url_key');
        if (!$vendor->getId()) {
            return false;
        }
        if ($vendor->getStatus()!='A') {
            Mage::getSingleton('core/session', array('name'=>'frontend'))->start('frontend');
            $session = Mage::getSingleton('udropship/session');
#echo "<pre>"; print_r($session->debug()); echo "</pre>";
            if ($session->getId()!=$vendor->getId()) {
                return false;
            }
        }

        if ($this->updateStoreBaseUrl()) {
            $baseUrl = $url['scheme'].'://'.$host.(isset($url['path']) ? $url['path'] : '/');
            Mage::app()->getStore()->setConfig('web/unsecure/base_link_url', $baseUrl);
        }

        return $vendor;
    }

    public function getAdminhtmlVendor()
    {
        Mage::getSingleton('core/session', array('name'=>'adminhtml'))->start('adminhtml');
        $user = Mage::getSingleton('admin/session')->getUser();
        if (!$user) {
            return false;
        }
        $vId = $user->getUdropshipVendor();
        if ($vId) {
            $vendor = Mage::getModel('udropship/vendor')->load($vId);
            if ($vendor->getId()) {
                return $vendor;
            }
        }
        return false;
    }

    public function getManageProductsUrl()
    {
        $params = array();
        $hlp = Mage::getSingleton('adminhtml/url');
        if ($hlp->useSecretKey()) {
            $params[Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME] = $hlp->getSecretKey();
        }
        return $hlp->getUrl('adminhtml/catalog_product', $params);
    }

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

    public function getVendorRegisterUrl()
    {
        return Mage::getUrl('umicrosite/vendor/register');
    }

    public function sendVendorSignupEmail($registration)
    {
        $store = Mage::app()->getStore();
        Mage::helper('udropship')->setDesignStore($store);
        Mage::getModel('core/email_template')->sendTransactional(
            $store->getConfig('udropship/microsite/signup_template'),
            $store->getConfig('udropship/vendor/vendor_email_identity'),
            $registration->getEmail(),
            $registration->getVendorName(),
            array(
                'store_name' => $store->getName(),
                'vendor' => $registration,
            )
        );
        Mage::helper('udropship')->setDesignStore();

        return $this;
    }

    public function sendVendorWelcomeEmail($vendor)
    {
        $store = Mage::app()->getStore();
        Mage::helper('udropship')->setDesignStore($store);
        Mage::getModel('core/email_template')->sendTransactional(
            $store->getConfig('udropship/microsite/welcome_template'),
            $store->getConfig('udropship/vendor/vendor_email_identity'),
            $vendor->getEmail(),
            $vendor->getVendorName(),
            array(
                'store_name' => $store->getName(),
                'vendor' => $vendor,
            )
        );
        Mage::helper('udropship')->setDesignStore();

        return $this;
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
                    $collection->joinAttribute('udropship_vendor', 'catalog_product/udropship_vendor', 'entity_id', null, 'left');
                    $collection->joinField('udropship_status', 'udropship/vendor', 'status', 'vendor_id=udropship_vendor', $cond, 'left');
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
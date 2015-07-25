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

class Unirgy_DropshipMicrosite_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpc = Mage::helper('umicrosite');

        switch ($this->getPath()) {

        case 'udropship/microsite/subdomain_level':
            $options = array(
                0 => $hlpc->__('Disable'),
                1 => $hlpc->__('From URL Path (domain.com/vendor)'),
                2 => $hlpc->__('2nd level subdomain (vendor.com)'),
                3 => $hlpc->__('3rd level subdomain (vendor.domain.com)'),
                4 => $hlpc->__('4th level subdomain (vendor.subdomain.domain.com)'),
                5 => $hlpc->__('5th level subdomain (vendor.subdomain2.subdomain1.domain.com)'),
            );
            break;

        case 'udropship/stock/stick_microsite':
            $options = array(
                0 => $hlpc->__('No'),
                1 => $hlpc->__('Yes'),
                2 => $hlpc->__('Yes and display vendor'),
                3 => $hlpc->__('Yes (only when in stock)'),
                4 => $hlpc->__('Yes (only when in stock) and display vendor'),
            );
            break;

        case 'is_limit_categories':
            $options = array(
                0 => $hlpc->__('No'),
                1 => $hlpc->__('Enable Selected'),
                2 => $hlpc->__('Disable Selected'),
            );
            break;

        case 'udropship/microsite/registration_carriers':
            $options = Mage::getSingleton('udropship/source')->getCarriers();
            $selector = false;
            break;

        case 'udropship/microsite/template_vendor':
            $options = Mage::getSingleton('udropship/source')->getVendors(true);
            $selector = false;
            break;

        case 'udropship/microsite/registration_services': // not used
            $options = array();
            $collection = $hlp->getShippingMethods();
            foreach ($collection as $shipping) {
                $options[$shipping->getId()] = $shipping->getShippingTitle().' ['.$shipping->getShippingCode().']';
            }
            $selector = false;
            break;

        case 'limit_websites':
        case 'udropship/microsite/staging_website':
            $collection = Mage::getModel('core/website')->getResourceCollection();
            $options = array('' => $hlpc->__('* None'));
            foreach ($collection as $w) {
                $options[$w->getId()] = $w->getName();
            }
            break;

        case 'registration_carriers':
            $options = array();
            $carriers = explode(',', Mage::getStoreConfig('udropship/microsite/registration_carriers'));
            foreach ($carriers as $code) {
                $options[$code] = Mage::getStoreConfig("carriers/{$code}/title");
            }
            break;
            
        case 'udropship/microsite/hide_product_attributes':
            $options = $this->getVisibleProductAttributes();
            break;

        default:
            Mage::throwException($hlp->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$hlp->__('* Please select')) + $options;
        }

        return $options;
    }
    
    protected $_visibleProductAttributes;
    public function getVisibleProductAttributes()
    {
        if (!$this->_visibleProductAttributes) {
            $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');
            $attrs = $entityType->getAttributeCollection()
                ->addFieldToFilter('is_visible', 1)
                ->addFieldToFilter('frontend_label', array('nin'=>'', 'udropship_vendor'))
                ->setOrder('frontend_label', 'asc');
            $this->_visibleProductAttributes = array();
            foreach ($attrs as $a) {
                $this->_visibleProductAttributes[$a->getAttributeCode()] = $a->getFrontendLabel().' ['.$a->getAttributeCode().']';
            }
        }
        return $this->_visibleProductAttributes;
    }
}

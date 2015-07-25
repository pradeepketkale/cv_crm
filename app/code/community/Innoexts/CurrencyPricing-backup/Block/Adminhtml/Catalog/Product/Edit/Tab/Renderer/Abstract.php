<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the InnoExts Commercial License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://innoexts.com/commercial-license-agreement
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_CurrencyPricing
 * @copyright   Copyright (c) 2012 Innoexts (http://www.innoexts.com)
 * @license     http://innoexts.com/commercial-license-agreement  InnoExts Commercial License
 */

/**
 * Product currency special price tab renderer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Renderer_Abstract 
    extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface 
{
    /**
     * Form element
     *
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_element;
    /**
     * Store
     * 
     * @var Mage_Core_Model_Store
     */
    protected $_store;
    /**
     * Get currency pricing helper
     *
     * @return Innoexts_CurrencyPricing_Helper_Data
     */
    protected function getCurrencyPricingHelper()
    {
        return Mage::helper('currencypricing');
    }
    /**
     * Check if global price scope is active
     * 
     * @return bool
     */
    public function isGlobalPriceScope()
    {
        return $this->getCurrencyPricingHelper()->isGlobalPriceScope();
    }
    /**
     * Get currency codes
     * 
     * @return array
     */
    public function getCurrencyCodes()
    {
        return $this->getCurrencyPricingHelper()->getCurrencyCodes();
    }
    /**
     * Get store
     * 
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $storeId = (int) $this->getRequest()->getParam('store', 0);
            $this->_store = Mage::app()->getStore($storeId);
        }
        return $this->_store;
    }
    /**
     * Get base currency code
     * 
     * @return string
     */
    public function getStoreBaseCurrencyCode()
    {
        return $this->getStore()->getBaseCurrencyCode();
    }
    /**
     * Get website
     * 
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        return $this->getStore()->getWebsite();
    }
    /**
     * Set form element
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element; 
        return $this;
    }
    /**
     * Get form element
     * 
     * @return	Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->_element;
    }
    /**
     * Render block
     * 
     * @param 	Varien_Data_Form_Element_Abstract $element
     * @return	string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
    /**
     * Get registered product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }
    /**
     * Sort values function
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    protected function sortValues($a, $b)
    {
        if ($a['currency'] != $b['currency']) {
            return $a['currency'] < $b['currency'] ? -1 : 1;
        }
        return 0;
    }
    /**
     * Get values
     * 
     * @return array
     */
    public function getValues()
    {
        return array();
    }
}
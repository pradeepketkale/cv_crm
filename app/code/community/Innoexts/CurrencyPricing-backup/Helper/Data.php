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
 * Currency pricing helper
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Currency codes
     * 
     * @var array
     */
    protected $_currencyCodes;
    /**
     * Websites
     * 
     * @var array of Mage_Core_Model_Website
     */
    protected $_websites;
    /**
     * Get version helper
     * 
     * @return Innoexts_InnoCore_Helper_Version
     */
    public function getVersionHelper()
    {
        return Mage::helper('innocore')->getVersionHelper();
    }
    /**
     * Get config
     * 
     * @return Innoexts_CurrencyPricing_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('currencypricing/config');
    }
    /**
     * Check if currency pricing is enabled
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfig()->isEnabled();
    }
    /**
     * Get currency codes
     * 
     * @return array
     */
    public function getCurrencyCodes()
    {
        if (is_null($this->_currencyCodes)) {
            $currency = Mage::getModel('directory/currency');
            $currencyCodes = $currency->getConfigAllowCurrencies();
            sort($currencyCodes);
            if (count($currencyCodes)) {
                $this->_currencyCodes = $currencyCodes;
            }
        }
        return $this->_currencyCodes;
    }
    /**
     * Get website base currencies
     * 
     * @return array of Mage_Core_Model_Website
     */
    public function getWebsites()
    {
        if (is_null($this->_websites)) {
            $collection = Mage::getSingleton('core/website')->getCollection();
            foreach ($collection as $website) {
                $this->_websites[$website->getId()] = $website;
            }
        }
        return $this->_websites;
    }
    /**
     * Get website base currency codes
     * 
     * @return array
     */
    public function getWebsiteBaseCurrencyCodes()
    {
        $codes = array();
        foreach ($this->getWebsites() as $websiteId => $website) {
            $codes[$websiteId] = $website->getBaseCurrencyCode();
        }
        return $codes;
    }
    /**
     * Get base currency expression
     * 
     * @param string $website
     * @return string
     */
    public function getBaseCurrencyExpr($website)
    {
        $pieces = array();
        foreach ($this->getWebsiteBaseCurrencyCodes() as $websiteId => $currencyCode) {
            array_push($pieces, "WHEN ".$website." = ".$websiteId." THEN '".$currencyCode."'");
        }
        return '(CASE '.implode(' ', $pieces).' END)';
    }
    /**
     * Get currency price expression
     * 
     * @param string $price
     * @param string $currency
     * @return string
     */
    public function getCurrencyPriceExpr($price, $defaultPrice, $rate)
    {
        if ($price) {
            return "(IF (({$price} IS NOT NULL) AND ({$price} != '') AND ({$rate} IS NOT NULL) AND ({$rate} > 0), ROUND({$price} / {$rate}, 4), {$defaultPrice}))";
        } else {
            return $defaultPrice;
        }
    }
    /**
     * Get currency price website identifier expression
     * 
     * @return string
     */
    public function getCurrencyPriceWebsiteIdExpr()
    {
        if (!$this->isGlobalPriceScope()) {
            $resource = Mage::getSingleton('core/resource');
            $websiteIdSelect = $resource->getConnection('core_write')->select()
                ->from(array('_pcp' => $resource->getTableName('catalog/product_currency_price')), 'website_id')
                ->where("(_pcp.product_id = e.entity_id) AND (_pcp.currency = cr.currency_to) AND 
                    ((_pcp.website_id = cs.website_id) OR (_pcp.website_id = '0'))")
                ->order('_pcp.website_id DESC')->limit(1);
            return "({$websiteIdSelect->assemble()})";
        } else {
            return '0';
        }
    }
    /**
     * Get currency special price website identifier expression
     * 
     * @return string
     */
    public function getCurrencySpecialPriceWebsiteIdExpr()
    {
        if (!$this->isGlobalPriceScope()) {
            $resource = Mage::getSingleton('core/resource');
            $websiteIdSelect = $resource->getConnection('core_write')->select()
                ->from(array('_pcsp' => $resource->getTableName('catalog/product_currency_special_price')), 'website_id')
                ->where("(_pcsp.product_id = e.entity_id) AND (_pcsp.currency = cr.currency_to) AND 
                    ((_pcsp.website_id = cs.website_id) OR (_pcsp.website_id = '0'))")
                ->order('_pcsp.website_id DESC')->limit(1);
            return "({$websiteIdSelect->assemble()})";
        } else {
            return '0';
        }
    }
    /**
     * Check if admin store is active
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin();
    }
    /**
     * Check if create order request is active
     * 
     * @return bool
     */
    public function isCreateOrderRequest()
    {
        if ($this->isAdmin()) {
            $controllerName = Mage::app()->getRequest()->getControllerName();
            if (in_array($controllerName, array('sales_order_edit', 'sales_order_create'))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * Get current store
     * 
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        if ($this->isAdmin() && $this->isCreateOrderRequest()) {
            return Mage::getSingleton('adminhtml/session_quote')->getStore();
        } else {
            return Mage::app()->getStore();
        }
    }
    /**
     * Get current currency
     * 
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrentCurrency()
    {
        return $this->getCurrentStore()->getCurrentCurrency();
    }
    /**
     * Get current currency code
     * 
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->getCurrentCurrency()->getCode();
    }
    /**
     * Get base currency
     * 
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrency()
    {
        return $this->getCurrentStore()->getBaseCurrency();
    }
    /**
     * Get base currency code
     * 
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrencyCode()
    {
        return $this->getBaseCurrency()->getCode();
    }
    /**
     * Set product price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return Innoexts_CurrencyPricing_Helper_Data
     */
    public function setProductPrice($product)
    {
        if (!$product->getData('_edit_mode')) {
            $baseCurrency = $this->getBaseCurrency();
            $currency = $this->getCurrentCurrency();
            $currencyPrices = $product->getWebsiteCurrencyPrices();
            if ($currency) {
                $currencyCode = $currency->getCode();
                if (isset($currencyPrices[$currencyCode])) {
                    $currencyPrice = $currencyPrices[$currencyCode];
                    $rate = $baseCurrency->getRate($currency);
                    $price = $currencyPrice / $rate;
                    $product->setPrice($price);
                }
            }
        }
        return $this;
    }
    /**
     * Set product website currency prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return Innoexts_AdvancedPricing_Helper_Data
     */
    public function setProductWebsiteCurrencyPrices($product)
    {
        $websiteId = $this->getProductWebsiteId($product);
        $currencies = $this->getCurrencyCodes();
        if (count($currencies)) {
            $currencyPrices = $product->getCurrencyPrices();
            if (count($currencyPrices)) {
                $websiteCurrencyPrices = array();
                foreach ($currencies as $currency) {
                    if ($websiteId) {
                        if (isset($currencyPrices[$websiteId]) && isset($currencyPrices[$websiteId][$currency])) {
                            $websiteCurrencyPrices[$currency] = $currencyPrices[$websiteId][$currency];
                        } else if (isset($currencyPrices[0]) && isset($currencyPrices[0][$currency])) {
                            $websiteCurrencyPrices[$currency] = $currencyPrices[0][$currency];
                        }
                    } else {
                        if (isset($currencyPrices[0]) && isset($currencyPrices[0][$currency])) {
                            $websiteCurrencyPrices[$currency] = $currencyPrices[0][$currency];
                        }
                    }
                }
                $product->setWebsiteCurrencyPrices($websiteCurrencyPrices);
            }
        }
        return $this;
    }
    /**
     * Set product special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return Innoexts_CurrencyPricing_Helper_Data
     */
    public function setProductSpecialPrice($product)
    {
        if (!$product->getData('_edit_mode')) {
            $baseCurrency = $this->getBaseCurrency();
            $currency = $this->getCurrentCurrency();
            $currencySpecialPrices = $product->getWebsiteCurrencySpecialPrices();
            if ($currency) {
                $currencyCode = $currency->getCode();
                if (isset($currencySpecialPrices[$currencyCode])) {
                    $currencySpecialPrice = $currencySpecialPrices[$currencyCode];
                    $rate = $baseCurrency->getRate($currency);
                    $price = $currencySpecialPrice / $rate;
                    $product->setSpecialPrice($price);
                }
            }
        }
        return $this;
    }
    /**
     * Set product website currency special prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return Innoexts_AdvancedPricing_Helper_Data
     */
    public function setProductWebsiteCurrencySpecialPrices($product)
    {
        $websiteId = $this->getProductWebsiteId($product);
        $currencies = $this->getCurrencyCodes();
        if (count($currencies)) {
            $currencySpecialPrices = $product->getCurrencySpecialPrices();
            if (count($currencySpecialPrices)) {
                $websiteCurrencySpecialPrices = array();
                foreach ($currencies as $currency) {
                    if ($websiteId) {
                        if (isset($currencySpecialPrices[$websiteId]) && isset($currencySpecialPrices[$websiteId][$currency])) {
                            $websiteCurrencySpecialPrices[$currency] = $currencySpecialPrices[$websiteId][$currency];
                        } else if (isset($currencySpecialPrices[0]) && isset($currencySpecialPrices[0][$currency])) {
                            $websiteCurrencySpecialPrices[$currency] = $currencySpecialPrices[0][$currency];
                        }
                    } else {
                        if (isset($currencySpecialPrices[0]) && isset($currencySpecialPrices[0][$currency])) {
                            $websiteCurrencySpecialPrices[$currency] = $currencySpecialPrices[0][$currency];
                        }
                    }
                }
                $product->setWebsiteCurrencySpecialPrices($websiteCurrencySpecialPrices);
            }
        }
        return $this;
    }
    /**
     * Get escaped price
     * 
     * @param float $price
     * @return float
     */
    public function getEscapedPrice($price)
    {
        if (!is_numeric($price)) {
            return null;
        }
        return number_format($price, 2, null, '');
    }
    /**
     * Get product price scope
     * 
     * @return int
     */
    public function getPriceScope()
    {
        return Mage::helper('catalog')->getPriceScope();
    }
    /**
     * Check if global price scope is active
     * 
     * @return bool
     */
    public function isGlobalPriceScope()
    {
        return Mage::helper('catalog')->isPriceGlobal();
    }
    /**
     * Get product website identifier
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getProductWebsiteId($product)
    {
        if (!$this->isGlobalPriceScope()) {
            $storeId = (int) $product->getStoreId();
            return Mage::app()->getStore($storeId)->getWebsiteId();
        } else {
            return 0;
        }
    }
    /**
     * Get currency rate
     * 
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public function getCurrencyRate($fromCurrency, $toCurrency)
    {
        $rate = Mage::getModel('directory/currency')->load($fromCurrency)->getRate($toCurrency);
        if (!$rate) {
            $rate = Mage::getModel('directory/currency')->load($toCurrency)->getRate($fromCurrency);
            if (!$rate) {
                $baseCurrency = $this->getBaseCurrency();
                $fromRate = $baseCurrency->getRate($fromCurrency);
                $toRate = $baseCurrency->getRate($toCurrency);
                if (!$fromRate) {
                    $fromRate = 1;
                }
                if (!$toRate) {
                    $toRate = 1;
                }
                $rate = $toRate / $fromRate;
            } else {
                $rate = 1 / $rate;
            }
        }
        return $rate;
    }
    /**
     * Add price index currency filter
     * 
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function addPriceIndexCurrencyFilter($collection)
    {
        $select = $collection->getSelect();
        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['price_index'])) {
            $oldJoinCond = $fromPart['price_index']['joinCondition'];
            if (strpos($oldJoinCond, 'currency') === false) {
                $connection = $collection->getConnection();
                if (!$collection->getFlag('currency')) {
                    $currencyCode = $this->getCurrentCurrencyCode();
                } else {
                    $currencyCode = $collection->getFlag('currency');
                }
                $currencyCode = $connection->quote($currencyCode);
                $joinCond = $oldJoinCond." AND ((price_index.currency IS NULL) OR (price_index.currency = {$currencyCode}))";
                $fromPart['price_index']['joinCondition'] = $joinCond;
                $select->setPart(Zend_Db_Select::FROM, $fromPart);
            }
        }
        return $collection;
    }
}
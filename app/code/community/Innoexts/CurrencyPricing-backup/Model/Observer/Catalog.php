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
 * Catalog observer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Observer_Catalog
{
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
     * Save product currency price
     * 
     * @param 	Varien_Event_Observer $observer
     * @return 	Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function saveProductCurrencyPrice(Varien_Event_Observer $observer)
    {
        $helper = $this->getCurrencyPricingHelper();
        $product = $observer->getEvent()->getProduct();
        if ($product && ($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product->getId();
            $resource = $product->getResource();
            $websiteId = $helper->getProductWebsiteId($product);
            $productCurrencyPriceTable = $resource->getTable('catalog/product_currency_price');
            $adapter = $resource->getWriteConnection();
            $_currencyPrices = $product->getCurrencyPrices();
            if (count($_currencyPrices)) {
                $currencyPrices = array();
                $oldCurrencyPrices = array();
                foreach ($_currencyPrices as $currencyPrice) {
                    if (
                        isset($currencyPrice['currency']) && isset($currencyPrice['price'])
                    ) {
                        $currency = $currencyPrice['currency'];
                        $price = ($currencyPrice['price'] && ($currencyPrice['price'] > 0)) ? 
                            round(floatval($currencyPrice['price']), 2) : 0;
                        $currencyPrices[$currency] = array(
                            'product_id' => $productId, 'currency' => $currency, 'website_id' => $websiteId, 'price' => $price, 
                        );
                    }
                }
                $select = $adapter->select()->from($productCurrencyPriceTable)
                    ->where("(product_id = {$adapter->quote($productId)}) AND (website_id = {$adapter->quote($websiteId)})");
                $query = $adapter->query($select);
                while ($currencyPrice = $query->fetch()) {
                    $currency = $currencyPrice['currency'];
                    $oldCurrencyPrices[$currency] = $currencyPrice;
                }
                foreach ($oldCurrencyPrices as $currencyPrice) {
                    $currency = $currencyPrice['currency'];
                    if (!isset($currencyPrices[$currency])) {
                        $adapter->delete($productCurrencyPriceTable, array(
                            $adapter->quoteInto('product_id = ?', $productId), 
                            $adapter->quoteInto('currency = ?', $currency), 
                            $adapter->quoteInto('website_id = ?', $websiteId)
                        ));
                    }
                }
                foreach ($currencyPrices as $currencyPrice) {
                    $currency = $currencyPrice['currency'];
                    if (!isset($oldCurrencyPrices[$currency])) {
                        $adapter->insert($productCurrencyPriceTable, $currencyPrices[$currency]);
                    } else {
                        $adapter->update($productCurrencyPriceTable, $currencyPrices[$currency], array(
                            $adapter->quoteInto('product_id = ?', $productId), 
                            $adapter->quoteInto('currency = ?', $currency), 
                            $adapter->quoteInto('website_id = ?', $websiteId), 
                        ));
                    }
                }
            }
        }
        return $this;
    }
    /**
     * Load product currency price
     * 
     * @param Varien_Event_Observer $observer
     * @return Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function loadProductCurrencyPrice(Varien_Event_Observer $observer)
    {
        $helper = $this->getCurrencyPricingHelper();
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = $product->getId();
            $resource = $product->getResource();
            $productCurrencyPriceTable = $resource->getTable('catalog/product_currency_price');
            $adapter = $resource->getWriteConnection();
            $select = $adapter->select()->from($productCurrencyPriceTable)->where('product_id = ?', $productId);
            $query = $adapter->query($select);
            $currencyPrices = array();
            while ($currencyPrice = $query->fetch()) {
                $currency = $currencyPrice['currency'];
                $price = $currencyPrice['price'];
                $websiteId = (int) $currencyPrice['website_id'];
                $currencyPrices[$websiteId][$currency] = $price;
            }
            $product->setCurrencyPrices($currencyPrices);
            $helper->setProductWebsiteCurrencyPrices($product);
            $helper->setProductPrice($product);
        }
        return $this;
    }
    /**
     * Load product collection currency price
     * 
     * @param 	Varien_Event_Observer $observer
     * @return 	Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function loadProductCollectionCurrencyPrice(Varien_Event_Observer $observer)
    {
        $helper = $this->getCurrencyPricingHelper();
        $collection = $observer->getEvent()->getCollection();
        if ($collection) {
            $productIds = array();
            foreach ($collection as $product) {
                array_push($productIds, $product->getId());
            }
            if (count($productIds)) {
                $productCurrencyPriceTable = $collection->getTable('catalog/product_currency_price');
                $adapter = $collection->getConnection();
                $select = $adapter->select()->from($productCurrencyPriceTable)->where($adapter->quoteInto('product_id IN (?)', $productIds));
                $query = $adapter->query($select);
                $productCurrencyPrices = array();
                while ($currencyPrice = $query->fetch()) {
                    $currency = $currencyPrice['currency'];
                    $price = $currencyPrice['price'];
                    $websiteId = (int) $currencyPrice['website_id'];
                    $productCurrencyPrices[$currencyPrice['product_id']][$websiteId][$currency] = $price; 
                }
                foreach ($collection as $product) {
                    $productId = $product->getId();
                    $currencyPrices = (isset($productCurrencyPrices[$productId])) ? $productCurrencyPrices[$productId] : array();
                    $product->setCurrencyPrices($currencyPrices);
                    $helper->setProductWebsiteCurrencyPrices($product);
                    $helper->setProductPrice($product);
                }
            }
        }
        return $this;
    }
    /**
     * Remove product currency price
     * 
     * @param Varien_Event_Observer $observer
     * @return Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function removeProductCurrencyPrice(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product->unsCurrencyPrices();
        }
        return $this;
    }
    /**
     * Before product collection load
     * 
     * @param Varien_Event_Observer $observer
     * @return Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function beforeProductCollectionLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($collection) {
            $this->getCurrencyPricingHelper()->addPriceIndexCurrencyFilter($collection);
        }
        return $this;
    }
    /**
     * After product collection apply limitations
     * 
     * @param Varien_Event_Observer $observer
     * @return Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function afterProductCollectionApplyLimitations(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($collection) {
            $this->getCurrencyPricingHelper()->addPriceIndexCurrencyFilter($collection);
        }
        return $this;
    }
    /**
     * Save product currency special price
     * 
     * @param 	Varien_Event_Observer $observer
     * @return 	Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function saveProductCurrencySpecialPrice(Varien_Event_Observer $observer)
    {
        $helper = $this->getCurrencyPricingHelper();
        $product = $observer->getEvent()->getProduct();
        if ($product && ($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product->getId();
            $resource = $product->getResource();
            $websiteId = $helper->getProductWebsiteId($product);
            $productCurrencySpecialPriceTable = $resource->getTable('catalog/product_currency_special_price');
            $adapter = $resource->getWriteConnection();
            $_currencySpecialPrices = $product->getCurrencySpecialPrices();
            if (count($_currencySpecialPrices)) {
                $currencySpecialPrices = $oldCurrencySpecialPrices = array();
                foreach ($_currencySpecialPrices as $currencySpecialPrice) {
                    if (
                        isset($currencySpecialPrice['currency']) && isset($currencySpecialPrice['price'])
                    ) {
                        $currency = $currencySpecialPrice['currency'];
                        $price = ($currencySpecialPrice['price'] && ($currencySpecialPrice['price'] > 0)) ? 
                            round(floatval($currencySpecialPrice['price']), 2) : 0;
                        $currencySpecialPrices[$currency] = array(
                            'product_id' => $productId, 'currency' => $currency, 'website_id' => $websiteId, 'price' => $price, 
                        );
                    }
                }
                $select = $adapter->select()->from($productCurrencySpecialPriceTable)
                    ->where("(product_id = {$adapter->quote($productId)}) AND (website_id = {$adapter->quote($websiteId)})");
                $query = $adapter->query($select);
                while ($currencyPrice = $query->fetch()) {
                    $currency = $currencyPrice['currency'];
                    $oldCurrencySpecialPrices[$currency] = $currencyPrice;
                }
                foreach ($oldCurrencySpecialPrices as $currencySpecialPrice) {
                    $currency = $currencySpecialPrice['currency'];
                    if (!isset($currencySpecialPrices[$currency])) {
                        $adapter->delete($productCurrencySpecialPriceTable, array(
                            $adapter->quoteInto('product_id = ?', $productId), 
                            $adapter->quoteInto('currency = ?', $currency), 
                            $adapter->quoteInto('website_id = ?', $websiteId)
                        ));
                    }
                }
                foreach ($currencySpecialPrices as $currencySpecialPrice) {
                    $currency = $currencySpecialPrice['currency'];
                    if (!isset($oldCurrencySpecialPrices[$currency])) {
                        $adapter->insert($productCurrencySpecialPriceTable, $currencySpecialPrices[$currency]);
                    } else {
                        $adapter->update($productCurrencySpecialPriceTable, $currencySpecialPrices[$currency], array(
                            $adapter->quoteInto('product_id = ?', $productId), 
                            $adapter->quoteInto('currency = ?', $currency), 
                            $adapter->quoteInto('website_id = ?', $websiteId), 
                        ));
                    }
                }
            }
        }
        return $this;
    }
    /**
     * Load product currency special price
     * 
     * @param Varien_Event_Observer $observer
     * @return Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function loadProductCurrencySpecialPrice(Varien_Event_Observer $observer)
    {
        $helper = $this->getCurrencyPricingHelper();
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = $product->getId();
            $resource = $product->getResource();
            $productCurrencySpecialPriceTable = $resource->getTable('catalog/product_currency_special_price');
            $adapter = $resource->getWriteConnection();
            $select = $adapter->select()->from($productCurrencySpecialPriceTable)->where('product_id = ?', $productId);
            $query = $adapter->query($select);
            $currencySpecialPrices = array();
            while ($currencySpecialPrice = $query->fetch()) {
                $currency = $currencySpecialPrice['currency'];
                $price = $currencySpecialPrice['price'];
                $websiteId = (int) $currencySpecialPrice['website_id'];
                $currencySpecialPrices[$websiteId][$currency] = $price; 
            }
            $product->setCurrencySpecialPrices($currencySpecialPrices);
            $helper->setProductWebsiteCurrencySpecialPrices($product);
            $helper->setProductSpecialPrice($product);
        }
        return $this;
    }
    /**
     * Load product collection currency special price
     * 
     * @param 	Varien_Event_Observer $observer
     * @return 	Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function loadProductCollectionCurrencySpecialPrice(Varien_Event_Observer $observer)
    {
        $helper = $this->getCurrencyPricingHelper();
        $collection = $observer->getEvent()->getCollection();
        if ($collection) {
            $productIds = array();
            foreach ($collection as $product) {
                array_push($productIds, $product->getId());
            }
            if (count($productIds)) {
                $productCurrencySpecialPriceTable = $collection->getTable('catalog/product_currency_special_price');
                $adapter = $collection->getConnection();
                $select = $adapter->select()->from($productCurrencySpecialPriceTable)->where($adapter->quoteInto('product_id IN (?)', $productIds));
                $query = $adapter->query($select);
                $productCurrencySpecialPrices = array();
                while ($currencySpecialPrice = $query->fetch()) {
                    $productId = $currencySpecialPrice['product_id'];
                    $currency = $currencySpecialPrice['currency'];
                    $price = $currencySpecialPrice['price'];
                    $websiteId = (int) $currencySpecialPrice['website_id'];
                    $productCurrencySpecialPrices[$productId][$websiteId][$currency] = $price; 
                }
                foreach ($collection as $product) {
                    $productId = $product->getId();
                    $currencySpecialPrices = (isset($productCurrencySpecialPrices[$productId])) ? $productCurrencySpecialPrices[$productId] : array();
                    $product->setCurrencySpecialPrices($currencySpecialPrices);
                    $helper->setProductWebsiteCurrencySpecialPrices($product);
                    $helper->setProductSpecialPrice($product);
                }
            }
        }
        return $this;
    }
    /**
     * Remove product currency special price
     * 
     * @param Varien_Event_Observer $observer
     * @return Innoexts_CurrencyPricing_Model_Observer_Catalog
     */
    public function removeProductCurrencySpecialPrice(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product->unsCurrencySpecialPrices();
        }
        return $this;
    }
}
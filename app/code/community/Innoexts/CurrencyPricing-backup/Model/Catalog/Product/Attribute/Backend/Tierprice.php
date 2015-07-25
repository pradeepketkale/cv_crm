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
 * Product tier price backend attribute
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Catalog_Product_Attribute_Backend_Tierprice 
    extends Mage_Catalog_Model_Product_Attribute_Backend_Tierprice 
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
     * Validate tier price data
     *
     * @param Mage_Catalog_Model_Product $object
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function validate($object)
    {
        $helper = $this->getCurrencyPricingHelper();
        $attribute = $this->getAttribute();
        $tiers = $object->getData($attribute->getName());
        if (empty($tiers)) { return true; }
        $duplicates = array();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) { continue; }
            $compare = join('-', array($tier['website_id'], $tier['cust_group'], $tier['price_qty'] * 1, $tier['currency']));
            if (isset($duplicates[$compare])) {
                Mage::throwException($helper->__('Duplicate website tier price customer group, quantity and currency.'));
            }
            $duplicates[$compare] = true;
        }
        if (!$attribute->isScopeGlobal() && $object->getStoreId()) {
            $origTierPrices = $object->getOrigData($attribute->getName());
            foreach ($origTierPrices as $tier) {
                if ($tier['website_id'] == 0) {
                    $compare = join('-', array($tier['website_id'], $tier['cust_group'], $tier['price_qty'] * 1, $tier['currency']));
                    $duplicates[$compare] = true;
                }
            }
        }
        $baseCurrency = Mage::app()->getBaseCurrencyCode();
        $rates = $this->_getWebsiteRates();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) { continue; }
            if ($tier['website_id'] == 0) { continue; }
            $compare = join('-', array($tier['website_id'], $tier['cust_group'], $tier['price_qty'], $tier['currency']));
            $globalCompare = join('-', array(0, $tier['cust_group'], $tier['price_qty'] * 1, $tier['currency']));
            $websiteCurrency = $rates[$tier['website_id']]['code'];
            if ($baseCurrency == $websiteCurrency && isset($duplicates[$globalCompare])) {
                Mage::throwException(Mage::helper('catalog')->__('Duplicate website tier price customer group, quantity and currency.'));
            }
        }
        return true;
    }
    /**
     * Prepare tier prices data for website
     * 
     * @param array $priceData
     * @param string $productTypeId
     * @param int $websiteId
     * @param string $currency
     * @return array
     */
    public function preparePriceData2(array $priceData, $productTypeId, $websiteId, $currency)
    {
        $helper = $this->getCurrencyPricingHelper();
        $data = array();
        $price = Mage::getSingleton('catalog/product_type')->priceFactory($productTypeId);
        $website = Mage::app()->getWebsite($websiteId);
        foreach ($priceData as $v) {
            $v['currency'] = (isset($v['currency']) && $v['currency']) ? $v['currency'] : null;
            if (is_null($v['currency'])) {
                if ($v['website_id'] == 0) {
                    $v['currency'] = Mage::app()->getBaseCurrencyCode();
                } else {
                    $v['currency'] = Mage::app()->getWebsite($v['website_id'])->getBaseCurrencyCode();
                }
            }
            $key = join('-', array($v['cust_group'], $v['price_qty']));
            if (
                (($v['website_id'] == $websiteId) || ($v['website_id'] == 0 && !isset($data[$key]))) && 
                (($v['currency'] == $currency) || (is_null($v['currency']) && !isset($data[$key])))
            ) {
                $data[$key] = $v;
                $data[$key]['website_id'] = $websiteId;
                $data[$key]['currency'] = $currency;
                if ($price->isTierPriceFixed()) {
                    $websiteCurrency = $website->getBaseCurrencyCode();
                    if ($websiteCurrency != $v['currency']) {
                        $rate = $helper->getCurrencyRate($websiteCurrency, $v['currency']);
                        $data[$key]['price'] = $v['price'] / $rate;
                        $data[$key]['website_price'] = $v['price'] / $rate;
                    }
                }
            }
        }
        return $data;
    }
    /**
     * Assign tier prices to product data
     * 
     * @param Mage_Catalog_Model_Product $object
     * @return Innoexts_CurrencyPricing_Model_Catalog_Product_Attribute_Backend_Tierprice
     */
    public function afterLoad($object)
    {
        $helper = $this->getCurrencyPricingHelper();
        $storeId   = $object->getStoreId();
        $websiteId = null;
        $store = Mage::app()->getStore($storeId);
        if ($this->getAttribute()->isScopeGlobal()) {
            $websiteId = 0;
        } else if ($storeId) {
            $websiteId = $store->getWebsiteId();
        }
        $currency = $store->getCurrentCurrencyCode();
        if (!$object->getData('_edit_mode')) {
            $data = $this->_getResource()->loadPriceData2($object->getId(), $websiteId, $currency);
        } else {
            $data = $this->_getResource()->loadPriceData2($object->getId(), $websiteId, null);
        }
        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }
        }
        if (!$object->getData('_edit_mode') && $websiteId) {
            $data = $this->preparePriceData2($data, $object->getTypeId(), $websiteId, $currency);
        }
        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);
        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);
        return $this;
    }
    /**
     * After save
     *
     * @param Mage_Catalog_Model_Product $object
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Tierprice
     */
    public function afterSave($object)
    {
        $helper = $this->getCurrencyPricingHelper();
        $websiteId = Mage::app()->getStore($object->getStoreId())->getWebsiteId();
        $isGlobal = $this->getAttribute()->isScopeGlobal() || $websiteId == 0;
        $tierPrices = $object->getData($this->getAttribute()->getName());
        if (empty($tierPrices)) {
            if ($isGlobal) {
                $this->_getResource()->deletePriceData($object->getId());
            } else {
                $this->_getResource()->deletePriceData($object->getId(), $websiteId);
            }
            return $this;
        }
        $old = array();
        $new = array();
        $origTierPrices = $object->getOrigData($this->getAttribute()->getName());
        if (!is_array($origTierPrices)) { $origTierPrices = array(); }
        foreach ($origTierPrices as $data) {
            if ($data['website_id'] > 0 || ($data['website_id'] == '0' && $isGlobal)) {
                $key = join('-', array($data['website_id'], $data['cust_group'], $data['price_qty'] * 1, $data['currency']));
                $old[$key] = $data;
            }
        }
        foreach ($tierPrices as $data) {
            if (empty($data['price_qty']) || !isset($data['cust_group']) || empty($data['currency']) || !empty($data['delete'])) {
                continue;
            }
            if ($this->getAttribute()->isScopeGlobal() && $data['website_id'] > 0) { continue; }
            if (!$isGlobal && (int)$data['website_id'] == 0) { continue; }
            $key = join('-', array($data['website_id'], $data['cust_group'], $data['price_qty'] * 1, $data['currency']));
            $useForAllGroups = $data['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL;
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;
            $new[$key] = array(
                'website_id'        => $data['website_id'],
                'all_groups'        => $useForAllGroups ? 1 : 0,
                'customer_group_id' => $customerGroupId,
                'qty'               => $data['price_qty'],
                'value'             => $data['price'], 
                'currency'          => $data['currency'], 
            );
        }
        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);
        $isChanged  = false;
        $productId  = $object->getId();
        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->_getResource()->deletePriceData($productId, null, $data['price_id']);
                $isChanged = true;
            }
        }
        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new Varien_Object($data);
                $price->setEntityId($productId);
                $this->_getResource()->savePriceData($price);
                $isChanged = true;
            }
        }
        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value']) {
                    $price = new Varien_Object(array('value_id' => $old[$k]['price_id'], 'value' => $v['value']));
                    $this->_getResource()->savePriceData($price);
                    $isChanged = true;
                }
            }
        }
        if ($isChanged) {
            $valueChangedKey = $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
        }
        return $this;
    }
}
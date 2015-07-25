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
 * Product collection
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Catalog_Product_Collection 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection 
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
     * Add tier price data to loaded items
     *
     * @return Innoexts_CurrencyPricing_Model_Mysql4_Catalog_Product_Collection
     */
    public function addTierPriceData()
    {
        $helper = $this->getCurrencyPricingHelper();
        if ($this->getFlag('tier_price_added')) {
            return $this;
        }
        $tierPrices = array();
        $productIds = array();
        foreach ($this->getItems() as $item) {
            $productIds[] = $item->getId();
            $tierPrices[$item->getId()] = array();
        }
        if (!$productIds) {
            return $this;
        }
        $store       = Mage::app()->getStore($this->getStoreId());
        $currency    = $store->getCurrentCurrencyCode();
        $attribute   = $this->getAttribute('tier_price');
        if ($attribute->isScopeGlobal()) {
            $websiteId = 0;
        } else if ($this->getStoreId()) {
            $websiteId = $store->getWebsiteId();
        }
        $adapter   = $this->getConnection();
        $columns   = array(
            'price_id'      => 'value_id', 
            'website_id'    => 'website_id', 
            'all_groups'    => 'all_groups', 
            'cust_group'    => 'customer_group_id', 
            'price_qty'     => 'qty', 
            'price'         => 'value', 
            'product_id'    => 'entity_id', 
            'currency'      => 'currency', 
        );
        $select  = $adapter->select()
            ->from($this->getTable('catalog/product_attribute_tier_price'), $columns)
            ->where('entity_id IN(?)', $productIds)
            ->order(array('entity_id','qty'));
        if ($websiteId == '0') {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where('website_id IN(?)', array('0', $websiteId));
        }
        $select->where('(currency = ?) OR (currency IS NULL)', $currency);
        foreach ($adapter->fetchAll($select) as $row) {
            $tierPrices[$row['product_id']][] = array(
                'website_id'     => $row['website_id'], 
                'cust_group'     => $row['all_groups'] ? Mage_Customer_Model_Group::CUST_GROUP_ALL : $row['cust_group'], 
                'price_qty'      => $row['price_qty'], 
                'price'          => $row['price'], 
                'website_price'  => $row['price'], 
                'currency'       => $row['currency'], 
            );
        }
        $backend = $attribute->getBackend();
        foreach ($this->getItems() as $item) {
            $data = $tierPrices[$item->getId()];
            if (!empty($data) && $websiteId) {
                $data = $backend->preparePriceData2($data, $item->getTypeId(), $websiteId, $currency);
            }
            $item->setData('tier_price', $data);
        }
        $this->setFlag('tier_price_added', true);
        return $this;
    }
}
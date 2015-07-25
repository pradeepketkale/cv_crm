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
 * Price indexer resource
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Catalog_Product_Indexer_Price 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price 
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
     * Get version helper
     * 
     * @return Innoexts_InnoCore_Helper_Version
     */
    protected function getVersionHelper()
    {
        return $this->getCurrencyPricingHelper()->getVersionHelper();
    }
    /**
     * Get base currency expression
     * 
     * @param string $website
     * @return string
     */
    protected function getBaseCurrencyExpr($website)
    {
        return $this->getCurrencyPricingHelper()->getBaseCurrencyExpr($website);
    }
    /**
     * Get currency price expression
     * 
     * @param string $price
     * @param string $currency
     * @return string
     */
    protected function getCurrencyPriceExpr($price, $defaultPrice, $rate)
    {
        return $this->getCurrencyPricingHelper()->getCurrencyPriceExpr($price, $defaultPrice, $rate);
    }
    /**
     * Prepare tier price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @return Innoexts_CurrencyPricing_Model_Mysql4_Catalog_Product_Indexer_Price
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $helper = $this->getCurrencyPricingHelper();
        $write = $this->_getWriteAdapter();
        $table = $this->_getTierPriceIndexTable();
        $write->delete($table);
        $rate  = 'cr.rate';
        if ($this->getVersionHelper()->isGe1600()) {
            $price = $write->getCheckSql('tp.website_id = 0', 'ROUND(tp.value * cwd.rate, 4)', 'tp.value');
        } else {
            $price = new Zend_Db_Expr('IF(tp.website_id=0, ROUND(tp.value * cwd.rate, 4), tp.value)');
        }
        $price = $this->getCurrencyPriceExpr((($helper->isEnabled()) ? $price : null), $price, $rate);
        $currency = "IF (cr.currency_to IS NULL, {$this->getBaseCurrencyExpr('cw.website_id')}, cr.currency_to)";
        $select = $write->select()
            ->from(
                array('tp' => $this->getValueTable('catalog/product', 'tier_price')), 
                array('entity_id'))
            ->join(
                array('cg' => $this->getTable('customer/customer_group')), 
                '(tp.all_groups = 1) OR ((tp.all_groups = 0) AND (tp.customer_group_id = cg.customer_group_id))', 
                array('customer_group_id'))
            ->join(
                array('cw' => $this->getTable('core/website')),
                'tp.website_id = 0 OR tp.website_id = cw.website_id',
                array('website_id'))
            ->join(
                array('cwd' => $this->_getWebsiteDateTable()),
                'cw.website_id = cwd.website_id',
                array())
            ->joinLeft(array('cr' => $this->getTable('directory/currency_rate')), 
                '(cr.currency_from = '.$this->getBaseCurrencyExpr('cw.website_id').') AND (tp.currency = cr.currency_to)', 
                array('currency' => $currency))
            ->where('cw.website_id != 0')
            ->columns(new Zend_Db_Expr("MIN({$price})"))
            ->group(array('tp.entity_id', 'cg.customer_group_id', 'cw.website_id', $currency));
        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }
        $query = $select->insertFromSelect($table);
        $write->query($query);
        return $this;
    }
}
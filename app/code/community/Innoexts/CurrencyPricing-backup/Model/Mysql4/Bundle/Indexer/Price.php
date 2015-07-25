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
 * Bundle products price indexer resource
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Bundle_Indexer_Price extends Mage_Bundle_Model_Mysql4_Indexer_Price
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
     * Prepare temporary price index data for bundle products by price type
     *
     * @param int $priceType
     * @param int|array $entityIds the entity ids limitatation
     * @return Mage_Bundle_Model_Mysql4_Indexer_Price
     */
    protected function _prepareBundlePriceByType($priceType, $entityIds = null)
    {
        $helper = $this->getCurrencyPricingHelper();
        $write = $this->_getWriteAdapter();
        $table = $this->_getBundlePriceTable();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(array('cg' => $this->getTable('customer/customer_group')), '', array('customer_group_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns('website_id', 'cw')
            ->join(array('cwd' => $this->_getWebsiteDateTable()), 'cw.website_id = cwd.website_id', array())
            ->joinLeft(
                array('cr' => $this->getTable('directory/currency_rate')), 
                '(cr.currency_from = '.$this->getBaseCurrencyExpr('cw.website_id').')', array())
            ->joinLeft(
                array('pcp' => $this->getTable('catalog/product_currency_price')), 
                "(pcp.product_id = e.entity_id) AND (pcp.currency = cr.currency_to) AND ".
                    "(pcp.website_id = {$helper->getCurrencyPriceWebsiteIdExpr()})", array())
            ->joinLeft(
                array('pcsp' => $this->getTable('catalog/product_currency_special_price')), 
                "(pcsp.product_id = e.entity_id) AND (pcsp.currency = cr.currency_to) AND ".
                    "(pcsp.website_id = {$helper->getCurrencySpecialPriceWebsiteIdExpr()})", array()
            )->joinLeft(
                array('tp' => $this->_getTierPriceIndexTable()), 
                '(tp.entity_id = e.entity_id) AND (tp.website_id = cw.website_id) AND '.
                '(tp.customer_group_id = cg.customer_group_id) AND (tp.currency = cr.currency_to)', 
                array()
            )->where('e.type_id=?', $this->getTypeId());
        $statusCond = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);
        if ($this->getVersionHelper()->isGe1600()) {
            if (Mage::helper('core')->isModuleEnabled('Mage_Tax')) {
                $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
            } else {
                $taxClassId = new Zend_Db_Expr('0');
            }
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC) {
                $select->columns(array('tax_class_id' => new Zend_Db_Expr('0')));
            } else {
                $select->columns(array('tax_class_id' => $write->getCheckSql($taxClassId . ' IS NOT NULL', $taxClassId, 0)));
            }
        } else {
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC) {
                $select->columns(array('tax_class_id' => new Zend_Db_Expr('0')));
            } else {
                $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
                $select->columns(array('tax_class_id' => new Zend_Db_Expr("IF($taxClassId IS NOT NULL, $taxClassId, 0)")));
            }
        }
        
        $priceTypeCond = $write->quoteInto('=?', $priceType);
        $this->_addAttributeToSelect($select, 'price_type', 'e.entity_id', 'cs.store_id', $priceTypeCond);
        $rate           = 'cr.rate';
        $price          = $this->_addAttributeToSelect($select, 'price', 'e.entity_id', 'cs.store_id');
        $price          = $this->getCurrencyPriceExpr('pcp.price', $price, $rate);
        $specialPrice   = $this->_addAttributeToSelect($select, 'special_price', 'e.entity_id', 'cs.store_id');
        $specialPrice   = $this->getCurrencyPriceExpr('pcsp.price', $specialPrice, $rate);
        $specialFrom    = $this->_addAttributeToSelect($select, 'special_from_date', 'e.entity_id', 'cs.store_id');
        $specialTo      = $this->_addAttributeToSelect($select, 'special_to_date', 'e.entity_id', 'cs.store_id');
        if ($this->getVersionHelper()->isGe1600()) {
            $curentDate     = new Zend_Db_Expr('cwd.website_date');
        } else {
            $curentDate     = new Zend_Db_Expr('cwd.date');
        }
        $specialExpr    = new Zend_Db_Expr("IF(IF({$specialFrom} IS NULL, 1, "
            . "IF({$specialFrom} <= {$curentDate}, 1, 0)) > 0 AND IF({$specialTo} IS NULL, 1, "
            . "IF({$specialTo} >= {$curentDate}, 1, 0)) > 0 AND {$specialPrice} > 0, $specialPrice, 0)");

        $tierExpr       = new Zend_Db_Expr("tp.min_price");
        $tierExpr      = $this->getCurrencyPriceExpr(null, $tierExpr, $rate);
        
        if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
            $finalPrice     = new Zend_Db_Expr("IF({$specialExpr} > 0, ROUND($price * ({$specialExpr} / 100), 4), {$price})");
            $tierPrice      = new Zend_Db_Expr("IF({$tierExpr} IS NOT NULL, ROUND({$price} - ({$price} * ({$tierExpr} / 100)), 4), NULL)");
        } else {
            $finalPrice     = new Zend_Db_Expr("0");
            $tierPrice      = new Zend_Db_Expr("IF({$tierExpr} IS NOT NULL, 0, NULL)");
        }
        $origPrice = new Zend_Db_Expr("IF({$price} IS NULL, 0, {$price})");
        $select->columns(array(
            'price_type'    => new Zend_Db_Expr($priceType),
            'special_price' => $specialExpr,
            'tier_percent'  => $tierExpr,
            'orig_price'    => $origPrice,
            'price'         => $finalPrice,
            'min_price'     => $finalPrice,
            'max_price'     => $finalPrice,
            'tier_price'    => $tierPrice,
            'base_tier'     => $tierPrice, 
            'currency'      => new Zend_Db_Expr('cr.currency_to'), 
        ));
        
        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        $eventData = array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id'), 
        );
        Mage::dispatchEvent('catalog_product_prepare_index_select', $eventData);
        $query = $select->insertFromSelect($table);
        $write->query($query);
        return $this;
    }
    /**
     * Prepare percentage tier price for bundle products
     *
     * @see Mage_Catalog_Model_Resource_Product_Indexer_Price::_prepareTierPriceIndex
     *
     * @param int|array $entityIds
     * @return Mage_Bundle_Model_Resource_Indexer_Price
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $adapter = $this->_getWriteAdapter();
        $select  = $adapter->select()
            ->from(array('i' => $this->_getTierPriceIndexTable()), null)
            ->join(array('e' => $this->getTable('catalog/product')), 'i.entity_id=e.entity_id', array())
            ->where('e.type_id=?', $this->getTypeId());
        $query = $select->deleteFromSelect('i');
        $adapter->query($query);
        $rate  = 'cr.rate';
        $price = 'tp.value';
        $price = $this->getCurrencyPriceExpr($price, $price, $rate);
        $currency = "IF (cr.currency_to IS NULL, {$this->getBaseCurrencyExpr('cw.website_id')}, cr.currency_to)";
        $select = $adapter->select()
            ->from(array('tp' => $this->getValueTable('catalog/product', 'tier_price')), array('entity_id'))
            ->join(array('e' => $this->getTable('catalog/product')), 'tp.entity_id = e.entity_id', array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
                array('customer_group_id')
            )->join(
                array('cw' => $this->getTable('core/website')),
                'tp.website_id = 0 OR tp.website_id = cw.website_id',
                array('website_id')
            )->joinLeft(array('cr' => $this->getTable('directory/currency_rate')), 
                '(cr.currency_from = '.$this->getBaseCurrencyExpr('cw.website_id').') AND (tp.currency = cr.currency_to)', 
                array('currency' => $currency))
            ->where('cw.website_id != 0')
            ->where('e.type_id=?', $this->getTypeId())
            ->columns(new Zend_Db_Expr("MIN({$price})"))
            ->group(array('tp.entity_id', 'cg.customer_group_id', 'cw.website_id', $currency));
        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }
        $query   = $select->insertFromSelect($this->_getTierPriceIndexTable());
        $adapter->query($query);
        return $this;
    }
    /**
     * Calculate bundle product selections price by product type
     *
     * @param int $priceType
     * @return Mage_Bundle_Model_Mysql4_Indexer_Price
     */
    protected function _calculateBundleSelectionPrice($priceType)
    {
        $write = $this->_getWriteAdapter();
        if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
            $selectionPriceValue = new Zend_Db_Expr(
                'IF (bsp.selection_price_value IS NULL, bs.selection_price_value, bsp.selection_price_value)'
            );
            $selectionPriceType = new Zend_Db_Expr(
                'IF (bsp.selection_price_type IS NULL, bs.selection_price_type, bsp.selection_price_type)'
            );
            $priceExpr = new Zend_Db_Expr(
                new Zend_Db_Expr(
                    'IF ('.$selectionPriceType.' = 1, ROUND(i.price * ('.$selectionPriceValue.' / 100), 4), '.(
                        new Zend_Db_Expr(
                            'IF (i.special_price > 0 AND i.special_price < 100, ROUND('.$selectionPriceValue.' * (i.special_price / 100), 4), '.
                                $selectionPriceValue.')'
                        )
                    .')')
                ).' * bs.selection_qty'
            );
            $tierExpr = new Zend_Db_Expr(
                'IF (i.base_tier IS NOT NULL, '.
                (new Zend_Db_Expr(
                    'IF ('.$selectionPriceType .' = 1, '.
                    'ROUND(i.base_tier - (i.base_tier * ('.$selectionPriceValue.' / 100)), 4), '. 
                    (new Zend_Db_Expr('IF (i.tier_percent > 0, ROUND('.$selectionPriceValue.' - ('.
                        $selectionPriceValue.' * (i.tier_percent / 100)), 4), '.$selectionPriceValue)))).')) * bs.selection_qty, NULL)');
        } else {
            $priceExpr = new Zend_Db_Expr(
                new Zend_Db_Expr(
                    'IF (i.special_price > 0 AND i.special_price < 100, ROUND(idx.min_price * (i.special_price / 100), 4), idx.min_price)'
                ).' * bs.selection_qty'
            );
            $tierExpr = new Zend_Db_Expr(
                'IF (i.base_tier IS NOT NULL, ROUND(idx.min_price * (i.base_tier / 100), 4)* bs.selection_qty, NULL)'
            );
        }
        $groupType = new Zend_Db_Expr("IF(bo.type = 'select' OR bo.type = 'radio', '0', '1')");
        $select = $write->select()
            ->from(array('i' => $this->_getBundlePriceTable()), array('entity_id', 'customer_group_id', 'website_id'))
            ->join(array('bo' => $this->getTable('bundle/option')), 'bo.parent_id = i.entity_id', array('option_id'))
            ->join(array('bs' => $this->getTable('bundle/selection')), 'bs.option_id = bo.option_id', array('selection_id'))
            ->joinLeft(array('bsp' => $this->getTable('bundle/selection_price')), 
                'bs.selection_id = bsp.selection_id AND bsp.website_id = i.website_id', array(''))
            ->join(array('idx' => $this->getIdxTable()), '(bs.product_id = idx.entity_id) AND (i.customer_group_id = idx.customer_group_id) AND '.
                '(i.website_id = idx.website_id) AND (i.currency = idx.currency)', array())
            ->join(array('e' => $this->getTable('catalog/product')), 'bs.product_id = e.entity_id AND e.required_options=0', array())
            ->where('i.price_type=?', $priceType)
            ->columns(array(
                'group_type'   => $groupType,
                'is_required'  => 'bo.required', 
                'price'        => $priceExpr, 
                'tier_price'   => $tierExpr, 
                'currency'     => 'i.currency', 
            ));
        $query = $select->insertFromSelect($this->_getBundleSelectionTable());
        $write->query($query);
        return $this;
    }
    /**
     * Calculate fixed bundle product selections price
     *
     * @return Mage_Bundle_Model_Mysql4_Indexer_Price
     */
    protected function _calculateBundleOptionPrice()
    {
        $write = $this->_getWriteAdapter();
        $this->_prepareBundleSelectionTable();
        $this->_calculateBundleSelectionPrice(Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED);
        $this->_calculateBundleSelectionPrice(Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC);
        $this->_prepareBundleOptionTable();
        if ($this->getVersionHelper()->isGe1600()) {
            $select = $write->select()
                ->from(
                    array('i' => $this->_getBundleSelectionTable()),
                    array('entity_id', 'customer_group_id', 'website_id', 'option_id')
                )
                ->group(array('entity_id', 'customer_group_id', 'website_id', 'option_id', 'currency', 'is_required', 'group_type'))
                ->columns(array(
                    'min_price' => $write->getCheckSql('i.is_required = 1', 'MIN(i.price)', '0'),
                    'alt_price' => $write->getCheckSql('i.is_required = 0', 'MIN(i.price)', '0'),
                    'max_price' => $write->getCheckSql('i.group_type = 1', 'SUM(i.price)', 'MAX(i.price)'),
                    'tier_price' => $write->getCheckSql('i.is_required = 1', 'MIN(i.tier_price)', '0'),
                    'alt_tier_price' => $write->getCheckSql('i.is_required = 0', 'MIN(i.tier_price)', '0'), 
                    'currency' => 'i.currency', 
                ));
        } else {
            $select = $write->select()
                ->from(array('i' => $this->_getBundleSelectionTable()),
                    array('entity_id', 'customer_group_id', 'website_id', 'option_id'))
                ->group(array('entity_id', 'customer_group_id', 'website_id', 'option_id', 'currency'))
                ->columns(array(
                    'min_price' => new Zend_Db_Expr("IF(i.is_required = 1, MIN(i.price), 0)"),
                    'alt_price' => new Zend_Db_Expr("IF(i.is_required = 0, MIN(i.price), 0)"),
                    'max_price' => new Zend_Db_Expr("IF(i.group_type = 1, SUM(i.price), MAX(i.price))"),
                    'tier_price' => new Zend_Db_Expr("IF(i.is_required = 1, MIN(i.tier_price), 0)"),
                    'alt_tier_price' => new Zend_Db_Expr("IF(i.is_required = 0, MIN(i.tier_price), 0)"), 
                    'currency' => 'i.currency', 
                ));
        }
        $query = $select->insertFromSelect($this->_getBundleOptionTable());
        $write->query($query);
        $this->_prepareDefaultFinalPriceTable();
        if ($this->getVersionHelper()->isGe1600()) {
            $minPrice  = new Zend_Db_Expr($write->getCheckSql('SUM(io.min_price) = 0', 'MIN(io.alt_price)', 'SUM(io.min_price)').' + i.price');
            $maxPrice  = new Zend_Db_Expr("SUM(io.max_price) + i.price");
            $tierPrice = $write->getCheckSql(
                'MIN(i.tier_percent) IS NOT NULL', 
                $write->getCheckSql('SUM(io.tier_price) = 0', 'SUM(io.alt_tier_price)', 'SUM(io.tier_price)').' + MIN(i.tier_price)', 
                'NULL');
            $select = $write->select()
                ->from(array('io' => $this->_getBundleOptionTable()), array('entity_id', 'customer_group_id', 'website_id'))
                ->join(array('i' => $this->_getBundlePriceTable()),
                    'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id'.
                        ' AND i.website_id = io.website_id AND (i.currency = io.currency)', array())
                ->group(array('io.entity_id', 'io.customer_group_id', 'io.website_id', 
                    'io.currency', 'i.tax_class_id', 'i.orig_price', 'i.price'))
                ->columns(array('i.tax_class_id', 
                    'orig_price'    => 'i.orig_price', 
                    'price'         => 'i.price', 
                    'min_price'     => $minPrice, 
                    'max_price'     => $maxPrice, 
                    'tier_price'    => $tierPrice, 
                    'base_tier'     => 'MIN(i.base_tier)', 
                    'currency'      => 'i.currency', 
                ));
        } else {
            $minPrice  = new Zend_Db_Expr("IF(SUM(io.min_price) = 0, MIN(io.alt_price), SUM(io.min_price)) + i.price");
            $maxPrice  = new Zend_Db_Expr("SUM(io.max_price) + i.price");
            $tierPrice = new Zend_Db_Expr("IF(i.tier_percent IS NOT NULL, IF(SUM(io.tier_price) = 0, ".
                "SUM(io.alt_tier_price), SUM(io.tier_price)) + i.tier_price, NULL)");
            $select = $write->select()
                ->from(array('io' => $this->_getBundleOptionTable()),
                    array('entity_id', 'customer_group_id', 'website_id'))
                ->join(array('i' => $this->_getBundlePriceTable()),
                    '(i.entity_id = io.entity_id) AND (i.customer_group_id = io.customer_group_id) AND '.
                        '(i.website_id = io.website_id) AND (i.currency = io.currency)', array())
                ->group(array('io.entity_id', 'io.customer_group_id', 'io.website_id', 'io.currency'))
                ->columns(array('i.tax_class_id',
                    'orig_price'    => 'i.orig_price',
                    'price'         => 'i.price',
                    'min_price'     => $minPrice,
                    'max_price'     => $maxPrice,
                    'tier_price'    => $tierPrice,
                    'base_tier'     => 'i.base_tier', 
                    'currency'      => 'i.currency'));
        }
        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);
        return $this;
    }
    /**
     * Apply custom option minimal and maximal price to temporary final price index table
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
     */
    protected function _applyCustomOption()
    {
        $write      = $this->_getWriteAdapter();
        $coaTable   = $this->_getCustomOptionAggregateTable();
        $copTable   = $this->_getCustomOptionPriceTable();
        $this->_prepareCustomOptionAggregateTable();
        $this->_prepareCustomOptionPriceTable();
        $select = $write->select()
            ->from(array('i' => $this->_getDefaultFinalPriceTable()), array('entity_id', 'customer_group_id', 'website_id'))
            ->join(array('cw' => $this->getTable('core/website')), 'cw.website_id = i.website_id', array())
            ->join(array('csg' => $this->getTable('core/store_group')), 'csg.group_id = cw.default_group_id', array())
            ->join(array('cs' => $this->getTable('core/store')), 'cs.store_id = csg.default_store_id', array())
            ->join(array('o' => $this->getTable('catalog/product_option')), 'o.product_id = i.entity_id', array('option_id'))
            ->join(array('ot' => $this->getTable('catalog/product_option_type_value')), 'ot.option_id = o.option_id', array())
            ->join(array('otpd' => $this->getTable('catalog/product_option_type_price')), 
                'otpd.option_type_id = ot.option_type_id AND otpd.store_id = 0',array())
            ->joinLeft(
                array('otps' => $this->getTable('catalog/product_option_type_price')), 
                'otps.option_type_id = otpd.option_type_id AND otpd.store_id = cs.store_id', array())
            ->joinLeft(
                array('cr' => $this->getTable('directory/currency_rate')), 
                '(cr.currency_from = '.$this->getBaseCurrencyExpr('cw.website_id').') AND (cr.currency_to = i.currency)', array()
            )->group(array('i.entity_id', 'i.customer_group_id', 'i.website_id', 'o.option_id', 'i.currency'));
        $rate = 'cr.rate';
        $optPriceType   = new Zend_Db_Expr('IF (otps.option_type_price_id > 0, otps.price_type, otpd.price_type)');
        $optPriceValue  = new Zend_Db_Expr('IF (otps.option_type_price_id > 0, otps.price, otpd.price)');
        $optPriceValue  = $this->getCurrencyPriceExpr(null, $optPriceValue, $rate);
        $minPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $minPriceExpr   = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$minPriceRound})");
        $minPriceMin    = new Zend_Db_Expr("MIN({$minPriceExpr})");
        $minPrice       = new Zend_Db_Expr("IF (MIN(o.is_require) = 1, {$minPriceMin}, 0)");
        $tierPriceRound = new Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 4)");
        $tierPriceExpr  = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$tierPriceRound})");
        $tierPriceMin   = new Zend_Db_Expr("MIN($tierPriceExpr)");
        $tierPriceValue = new Zend_Db_Expr("IF (MIN(o.is_require) > 0, {$tierPriceMin}, 0)");
        $tierPrice      = new Zend_Db_Expr("IF (MIN(i.base_tier) IS NOT NULL, {$tierPriceValue}, NULL)");
        $maxPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $maxPriceExpr   = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$maxPriceRound})");
        $maxPrice       = new Zend_Db_Expr("IF ((MIN(o.type)='radio' OR MIN(o.type)='drop_down'), MAX({$maxPriceExpr}), SUM({$maxPriceExpr}))");
        $select->columns(array(
            'min_price'   => $minPrice, 
            'max_price'   => $maxPrice, 
            'tier_price'  => $tierPrice, 
            'currency'    => 'i.currency', 
        ));
        $query = $select->insertFromSelect($coaTable);
        $write->query($query);

        $select = $write->select()
            ->from(array('i' => $this->_getDefaultFinalPriceTable()), array('entity_id', 'customer_group_id', 'website_id'))
            ->join(array('cw' => $this->getTable('core/website')), 'cw.website_id = i.website_id', array())
            ->join(array('csg' => $this->getTable('core/store_group')), 'csg.group_id = cw.default_group_id', array())
            ->join(array('cs' => $this->getTable('core/store')), 'cs.store_id = csg.default_store_id', array())
            ->join(array('o' => $this->getTable('catalog/product_option')), 'o.product_id = i.entity_id', array('option_id'))
            ->join(array('opd' => $this->getTable('catalog/product_option_price')), 'opd.option_id = o.option_id AND opd.store_id = 0', array())
            ->joinLeft(array('ops' => $this->getTable('catalog/product_option_price')), 
                'ops.option_id = opd.option_id AND ops.store_id = cs.store_id', array())
            ->joinLeft(
                array('cr' => $this->getTable('directory/currency_rate')), 
                '(cr.currency_from = '.$this->getBaseCurrencyExpr('cw.website_id').') AND (cr.currency_to = i.currency)', array()
            );
        $rate = 'cr.rate';
        $optPriceType   = new Zend_Db_Expr('IF (ops.option_price_id > 0, ops.price_type, opd.price_type)');
        $optPriceValue  = new Zend_Db_Expr('IF (ops.option_price_id > 0, ops.price, opd.price)');
        $optPriceValue  = $this->getCurrencyPriceExpr(null, $optPriceValue, $rate);
        $minPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $priceExpr      = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$minPriceRound})");
        $minPrice       = new Zend_Db_Expr("IF ({$priceExpr} > 0 AND o.is_require > 1, {$priceExpr}, 0)");
        $maxPrice       = $priceExpr;
        $tierPriceRound = new Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 4)");
        $tierPriceExpr  = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$tierPriceRound})");
        $tierPriceValue = new Zend_Db_Expr("IF ({$tierPriceExpr} > 0 AND o.is_require > 0, {$tierPriceExpr}, 0)");
        $tierPrice      = new Zend_Db_Expr("IF (i.base_tier IS NOT NULL, {$tierPriceValue}, NULL)");    
        $select->columns(array(
            'min_price' => $minPrice, 
            'max_price' => $maxPrice, 
            'tier_price' => $tierPrice, 
            'currency' => 'i.currency', 
        ));
        $query = $select->insertFromSelect($coaTable);
        $write->query($query);
        $select = $write->select()
            ->from(array($coaTable), array('entity_id', 'customer_group_id', 'website_id', 
                'min_price' => 'SUM(min_price)', 'max_price' => 'SUM(max_price)', 'tier_price' => 'SUM(tier_price)', 'currency'))
            ->group(array('entity_id', 'customer_group_id', 'website_id', 'currency'));
        $query = $select->insertFromSelect($copTable);
        $write->query($query);
        $table  = array('i' => $this->_getDefaultFinalPriceTable());
        $select = $write->select()
            ->join(array('io' => $copTable), '(i.entity_id = io.entity_id) AND (i.customer_group_id = io.customer_group_id) AND '.
                '(i.website_id = io.website_id) AND (i.currency = io.currency)', array());
        $tierPrice = new Zend_Db_Expr('IF(i.tier_price IS NOT NULL, i.tier_price + io.tier_price, NULL)');
        $select->columns(array(
            'min_price'  => new Zend_Db_Expr('i.min_price + io.min_price'),
            'max_price'  => new Zend_Db_Expr('i.max_price + io.max_price'),
            'tier_price' => $tierPrice, 
        ));
        $query = $select->crossUpdateFromSelect($table);
        $write->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $write->delete($coaTable);
            $write->delete($copTable);
        } else {
            if ($this->getVersionHelper()->isGe1610()) {
                if ($this->useIdxTable() && $this->_allowTableChanges) {
                    $write->truncate($coaTable);
                    $write->truncate($copTable);
                } else {
                    $write->delete($coaTable);
                    $write->delete($copTable);
                }
            } else {
                if ($this->useIdxTable()) {
                    $write->truncate($coaTable);
                    $write->truncate($copTable);
                } else {
                    $write->delete($coaTable);
                    $write->delete($copTable);
                }
            }
        }
        
        return $this;
    }
    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
     */
    protected function _movePriceDataToIndexTable()
    {
        $columns = array(
            'entity_id'         => 'entity_id', 
            'customer_group_id' => 'customer_group_id', 
            'website_id'        => 'website_id', 
            'tax_class_id'      => 'tax_class_id', 
            'price'             => 'orig_price', 
            'final_price'       => 'price', 
            'min_price'         => 'min_price', 
            'max_price'         => 'max_price', 
            'tier_price'        => 'tier_price', 
            'currency'			=> 'currency', 
        );
        $write  = $this->_getWriteAdapter();
        $table  = $this->_getDefaultFinalPriceTable();
        $select = $write->select()->from($table, $columns);
        $query = $select->insertFromSelect($this->getIdxTable());
        $write->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $write->delete($table);
        } else {
            if ($this->getVersionHelper()->isGe1610()) {
                if ($this->useIdxTable() && $this->_allowTableChanges) {
                    $write->truncate($table);
                } else {
                    $write->delete($table);
                }
            } else {
                if ($this->useIdxTable()) {
                    $write->truncate($table);
                } else {
                    $write->delete($table);
                }
            }
        }
        
        return $this;
    }
    /**
     * Prepare temporary index price for bundle products
     *
     * @param int|array $entityIds  the entity ids limitation
     * @return Mage_Bundle_Model_Mysql4_Indexer_Price
     */
    protected function _prepareBundlePrice($entityIds = null)
    {
        $this->_prepareTierPriceIndex($entityIds);
        $this->_prepareBundlePriceTable();
        $this->_prepareBundlePriceByType(Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED, $entityIds);
        $this->_prepareBundlePriceByType(Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC, $entityIds);
        $select = $this->_getWriteAdapter()->select()
            ->join(
                array('wd' => $this->_getWebsiteDateTable()),
                'i.website_id = wd.website_id',
                array()
            );
        if ($this->getVersionHelper()->isGe1600()) {
            $websiteDate = 'wd.website_date';
        } else {
            $websiteDate = 'wd.date';
        }
		// Craftsvilla Comment By Amit Pitre On 10-07-2012 for the indexing issue after product save.
        Mage::dispatchEvent('prepare_catalog_product_price_index_table', array(
            'index_table'       => array('i' => $this->_getBundlePriceTable()),
            'select'            => $select,
            'entity_id'         => 'i.entity_id',
            'customer_group_id' => 'i.customer_group_id',
            'website_id'        => 'i.website_id',
            'website_date'      => $websiteDate, 
            'currency'          => 'i.currency', 
            'update_fields'     => array('price', 'min_price', 'max_price')
        ));
        $this->_calculateBundleOptionPrice();
        $this->_applyCustomOption();
        $this->_movePriceDataToIndexTable();
        return $this;
    }
}

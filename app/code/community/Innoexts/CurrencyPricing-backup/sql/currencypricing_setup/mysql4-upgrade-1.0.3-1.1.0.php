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

$installer                                = $this;
$connection                               = $installer->getConnection();

$installer->startSetup();

/* Product tier price */
$productTierPriceTableName                = 'catalog/product_attribute_tier_price';
$productTierPriceTable                    = $installer->getTable($productTierPriceTableName);

$connection->addColumn($productTierPriceTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productTierPriceTable, 'IDX_CATALOG_PRODUCT_ENTITY_TIER_PRICE_CURRENCY', array('currency'), 'index');

if (Mage::helper('innocore/version')->isGe1600()) {
    $productTierPriceIndexes = $connection->getIndexList($productTierPriceTable);
    foreach ($productTierPriceIndexes as $index) {
        if ($index['INDEX_TYPE'] == Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE) {
            $connection->dropIndex($productTierPriceTable, $index['KEY_NAME']);
        }
    }
    $connection->addIndex(
        $productTierPriceTable, 
        $installer->getIdxName(
            $productTierPriceTableName, 
            array('entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'currency'), 
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'currency'), 
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
} else {
    $connection->addKey($productTierPriceTable, 'UNQ_CATALOG_PRODUCT_TIER_PRICE', array(
        'entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'currency', 
    ), 'unique');
}
/* Product index tier price */
$productIndexTierPriceTableName           = 'catalog/product_index_tier_price';
$productIndexTierPriceTable               = $installer->getTable($productIndexTierPriceTableName);

$connection->addColumn($productIndexTierPriceTable, 'currency', 'varchar(3) null default null after `website_id`');
$connection->addKey($productIndexTierPriceTable, 'IDX_CATALOG_PRODUCT_INDEX_TIER_PRICE_CURRENCY', array('currency'), 'index');
$connection->addKey($productIndexTierPriceTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'currency'), 'primary');
/* Product currency price */
$productCurrencyPriceTable                = $installer->getTable('catalog/product_currency_price');
$websiteTable                             = $installer->getTable('core/website');

$connection->addColumn($productCurrencyPriceTable, 'website_id', 'smallint(5) unsigned not null default 0 after `currency`');
$connection->addKey($productCurrencyPriceTable, 'IDX_CATALOG_PRODUCT_CURRENCY_PRICE_WEBSITE_ID', array('website_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_CURRENCY_PRICE_WEBSITE_ID', $productCurrencyPriceTable, 'website_id', $websiteTable, 'website_id');
$connection->addKey($productCurrencyPriceTable, 'PRIMARY', array('product_id', 'currency', 'website_id'), 'primary');
/* Product currency special price */
$productCurrencySpecialPriceTable         = $installer->getTable('catalog/product_currency_special_price');
$websiteTable                             = $installer->getTable('core/website');

$connection->addColumn($productCurrencySpecialPriceTable, 'website_id', 'smallint(5) unsigned not null default 0 after `currency`');
$connection->addKey($productCurrencySpecialPriceTable, 'IDX_CATALOG_PRODUCT_CURRENCY_SPECIAL_PRICE_WEBSITE_ID', array('website_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_CURRENCY_SPECIAL_PRICE_WEBSITE_ID', $productCurrencySpecialPriceTable, 'website_id', $websiteTable, 'website_id');
$connection->addKey($productCurrencySpecialPriceTable, 'PRIMARY', array('product_id', 'currency', 'website_id'), 'primary');

$installer->endSetup();

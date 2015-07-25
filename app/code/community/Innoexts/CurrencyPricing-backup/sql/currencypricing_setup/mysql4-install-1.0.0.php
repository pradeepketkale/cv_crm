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

$installer = $this;
$connection = $installer->getConnection();

$productTable = $installer->getTable('catalog/product');
$productCurrencyPriceTable = $installer->getTable('catalog/product_currency_price');

$installer->startSetup();
 
$installer->run("
CREATE TABLE `{$productCurrencyPriceTable}` (
  `product_id` int(10) unsigned not null, 
  `currency` varchar(3) not null, 
  `price` decimal(12,4) null default null, 
  PRIMARY KEY  (`product_id`, `currency`), 
  KEY `FK_CATALOG_PRODUCT_CURRENCY_PRICE_PRODUCT` (`product_id`), 
  KEY `IDX_CURRENCY` (`currency`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_CURRENCY_PRICE_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$productTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
/**
 * Price index
 */
$productIndexPriceTable = $installer->getTable('catalog/product_index_price');
$connection->addColumn($productIndexPriceTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceTable, 'IDX_CURRENCY', array('currency'), 'index');
$connection->addKey($productIndexPriceTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'currency'), 'primary');

$productIndexPriceIdxTable = $installer->getTable('catalog/product_price_indexer_idx');
$connection->addColumn($productIndexPriceIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceIdxTable, 'IDX_CURRENCY', array('currency'), 'index');
$connection->addKey($productIndexPriceIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'currency'), 'primary');

$productIndexPriceTmpTable = $installer->getTable('catalog/product_price_indexer_tmp');
$connection->addColumn($productIndexPriceTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceTmpTable, 'IDX_CURRENCY', array('currency'), 'index');
$connection->addKey($productIndexPriceTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'currency'), 'primary');
/**
 * Final price index
 */
$productIndexPriceFinalIdxTable = $installer->getTable('catalog/product_price_indexer_final_idx');
$connection->addColumn($productIndexPriceFinalIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceFinalIdxTable, 'IDX_CURRENCY', array('currency'), 'index');
$connection->addKey($productIndexPriceFinalIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'currency'), 'primary');

$productIndexPriceFinalTmpTable = $installer->getTable('catalog/product_price_indexer_final_tmp');
$connection->addColumn($productIndexPriceFinalTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceFinalTmpTable, 'IDX_CURRENCY', array('currency'), 'index');
$connection->addKey($productIndexPriceFinalTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'currency'), 'primary');
/**
 * Bundle price index
 */
$productIndexPriceBundleIdxTable = $installer->getTable('bundle/price_indexer_idx');
$connection->addColumn($productIndexPriceBundleIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceBundleIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'currency'), 'primary');

$productIndexPriceBundleTmpTable = $installer->getTable('bundle/price_indexer_tmp');
$connection->addColumn($productIndexPriceBundleTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceBundleTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'currency'), 'primary');

$productIndexPriceBundleSelectionIdxTable = $installer->getTable('bundle/selection_indexer_idx');
$connection->addColumn($productIndexPriceBundleSelectionIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceBundleSelectionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'selection_id', 'currency'
), 'primary');

$productIndexPriceBundleSelectionTmpTable = $installer->getTable('bundle/selection_indexer_tmp');
$connection->addColumn($productIndexPriceBundleSelectionTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceBundleSelectionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'selection_id', 'currency'
), 'primary');

$productIndexPriceBundleOptionIdxTable = $installer->getTable('bundle/option_indexer_idx');
$connection->addColumn($productIndexPriceBundleOptionIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceBundleOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'currency'
), 'primary');

$productIndexPriceBundleOptionTmpTable = $installer->getTable('bundle/option_indexer_tmp');
$connection->addColumn($productIndexPriceBundleOptionTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceBundleOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'currency'
), 'primary');
/**
 * Option price index
 */
$productIndexPriceOptionAggregateIdxTable = $installer->getTable('catalog/product_price_indexer_option_aggregate_idx');
$connection->addColumn($productIndexPriceOptionAggregateIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceOptionAggregateIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'currency'
), 'primary');

$productIndexPriceOptionAggregateTmpTable = $installer->getTable('catalog/product_price_indexer_option_aggregate_tmp');
$connection->addColumn($productIndexPriceOptionAggregateTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceOptionAggregateTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'currency'
), 'primary');

$productIndexPriceOptionIdxTable = $installer->getTable('catalog/product_price_indexer_option_idx');
$connection->addColumn($productIndexPriceOptionIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'currency'
), 'primary');

$productIndexPriceOptionTmpTable = $installer->getTable('catalog/product_price_indexer_option_tmp');
$connection->addColumn($productIndexPriceOptionTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'currency'
), 'primary');
/**
 * Downloadable price index
 */
$productIndexPriceDownloadableIdxTable = $installer->getTable('downloadable/product_price_indexer_idx');
$connection->addColumn($productIndexPriceDownloadableIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceDownloadableIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'currency'
), 'primary');

$productIndexPriceDownloadableTmpTable = $installer->getTable('downloadable/product_price_indexer_tmp');
$connection->addColumn($productIndexPriceDownloadableTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceDownloadableTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'currency'
), 'primary');
/**
 * Configurable option price index
 */
$productIndexPriceCfgOptionAggregateIdxTable = $installer->getTable('catalog/product_price_indexer_cfg_option_aggregate_idx');
$connection->addColumn($productIndexPriceCfgOptionAggregateIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceCfgOptionAggregateIdxTable, 'PRIMARY', array(
    'parent_id', 'child_id', 'customer_group_id', 'website_id', 'currency'
), 'primary');

$productIndexPriceCfgOptionAggregateTmpTable = $installer->getTable('catalog/product_price_indexer_cfg_option_aggregate_tmp');
$connection->addColumn($productIndexPriceCfgOptionAggregateTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceCfgOptionAggregateTmpTable, 'PRIMARY', array(
    'parent_id', 'child_id', 'customer_group_id', 'website_id', 'currency'
), 'primary');

$productIndexPriceCfgOptionIdxTable = $installer->getTable('catalog/product_price_indexer_cfg_option_idx');
$connection->addColumn($productIndexPriceCfgOptionIdxTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceCfgOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'currency'
), 'primary');

$productIndexPriceCfgOptionTmpTable = $installer->getTable('catalog/product_price_indexer_cfg_option_tmp');
$connection->addColumn($productIndexPriceCfgOptionTmpTable, 'currency', 'varchar(3) null default null');
$connection->addKey($productIndexPriceCfgOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'currency'
), 'primary');

$installer->endSetup();

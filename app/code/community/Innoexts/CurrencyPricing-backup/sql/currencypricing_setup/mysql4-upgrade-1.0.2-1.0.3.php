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

$productTable                             = $installer->getTable('catalog/product');
$productCurrencySpecialPriceTable         = $installer->getTable('catalog/product_currency_special_price');

$installer->startSetup();

$installer->run("
CREATE TABLE `{$productCurrencySpecialPriceTable}` (
  `product_id` int(10) unsigned not null, 
  `currency` varchar(3) not null, 
  `price` decimal(12,4) null default null, 
  PRIMARY KEY  (`product_id`, `currency`), 
  KEY `FK_CATALOG_PRODUCT_CURRENCY_SPECIAL_PRICE_PRODUCT` (`product_id`), 
  KEY `IDX_CURRENCY` (`currency`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_CURRENCY_SPECIAL_PRICE_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$productTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

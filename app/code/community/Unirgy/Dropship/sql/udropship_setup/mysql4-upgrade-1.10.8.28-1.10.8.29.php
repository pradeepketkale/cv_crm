<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$this->startSetup();

    $setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $setup->addAttribute('shipment_item', 'order_id', array('type' => 'int'));
    $setup->addAttribute('shipment_item', 'qty_ordered', array('type' => 'decimal'));
    $setup->addAttribute('shipment_item', 'store_id', array('type' => 'smallint'));
    $setup->addAttribute('shipment_item', 'base_original_price', array('type' => 'decimal'));
    $setup->addAttribute('shipment_item', 'base_discount_amount', array('type' => 'decimal'));
    $setup->addAttribute('shipment_item', 'base_amount_refunded', array('type' => 'decimal'));
    $setup->addAttribute('shipment_item', 'shippingcost', array('type' => 'decimal'));
    $setup->addAttribute('shipment_item', 'total_shippingcost', array('type' => 'decimal'));

$this->endSetup();

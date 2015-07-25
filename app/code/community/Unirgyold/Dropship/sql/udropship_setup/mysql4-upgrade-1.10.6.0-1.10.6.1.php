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

if (Mage::helper('udropship')->isSalesFlat()) {
    $this->_conn->addColumn($this->getTable('sales_flat_shipment_track'), 'courier_name', 'varchar(255)');
} else {
    $eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
    $eav->addAttribute('shipment_track', 'courier_name', array('type' => 'varchar'));
}

$this->endSetup();

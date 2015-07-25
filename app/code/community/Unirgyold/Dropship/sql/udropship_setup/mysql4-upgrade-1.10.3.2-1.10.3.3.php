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

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
$eav->updateAttribute('catalog_product', 'udropship_vendor', 'frontend_input', 'select');
$eav->updateAttribute('catalog_product', 'udropship_vendor', 'frontend_input_renderer', 'udropship/vendor_htmlselect');

$this->endSetup();

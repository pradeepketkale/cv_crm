<?php

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

if (version_compare(Mage::getVersion(), '1.4.0', '>=')) {
    $eav->updateAttribute('catalog_product', 'udropship_vendor', 'is_used_for_price_rules', 1);
}
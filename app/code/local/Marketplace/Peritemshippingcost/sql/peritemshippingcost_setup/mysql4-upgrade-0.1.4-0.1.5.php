<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$setup->updateAttribute('catalog_product', 'shippingcost', 'is_html_allowed_on_front', 1);
$installer->endSetup();

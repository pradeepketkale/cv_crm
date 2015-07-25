<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_product', 'ship_handling_time', array(
	'group'     	=> 'Policies',
	'input'         => 'select',
        'type'          => 'text',
        'label'         => 'Shipping handling Time',
	'backend'       => '',
	'source'        => 'thoughtyard/source_Handling',
	'visible'       => 1,
	'required'	=> 1,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));


$setup->addAttribute('catalog_product', 'item_location', array(
	'group'     	=> 'Policies',
	'input'         => 'select',
        'type'          => 'text',
        'label'         => 'Item Location',
        'source'        => 'Marketplace_Thoughtyard_Model_Source_Location',
	'visible'       => 1,
	'required'	=> 1,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'item_return_in_days', array(
	'group'     	=> 'Policies',
	'input'         => 'select',
        'type'          => 'text',
        'label'         => 'Item to be Returned in',
        'source'  => 'Marketplace_Thoughtyard_Model_Source_Return',
	'backend'       => '',
	'visible'       => 1,
	'required'	=> 1,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));


$setup->addAttribute('catalog_product', 'refund_made_as', array(
	'group'     	=> 'Policies',
	'input'         => 'select',
        'type'          => 'text',
        'label'         => 'Refund will be given as',
        'source'  => 'Marketplace_Thoughtyard_Model_Source_Refund',
	'backend'       => '',
	'visible'       => 1,
	'required'	=> 1,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'refund_cost_bearer', array(
	'group'     	=> 'Policies',
	'input'         => 'select',
        'type'          => 'text',
        'label'         => 'Return cost paid by',
        'source'  => 'Marketplace_Thoughtyard_Model_Source_Bearer',
	'backend'       => '',
	'visible'       => 1,
	'required'	=> 1,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'return_policy_detail', array(
	'group'     	=> 'Policies',
	'input'         => 'textarea',
        'type'          => 'text',
        'label'         => 'Return Policy Details',
        'is_wysiwyg_enabled', 1,
	'backend'       => '',
	'visible'       => 1,
	'required'	=> 1,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
$installer->endSetup();

<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

// adding attribute group
// You dont need to add group, its attribute set -> Group -> Attribute
// Use only if you want to have a group like prices
//$setup->addAttributeGroup('catalog_product', 'Default', 'Special Attributes', 1000);

// the attribute added will be displayed under the group/tab Special Attributes in product edit page
$setup->addAttribute('catalog_product', 'intershippingcost', array(
	'group'     	=> 'Prices',
	'input'         => 'price',
        'type'          => 'decimal',
        'label'         => 'International Shipping Cost',
	'backend'       => '',
	'visible'       => 1,
	'required'	=> 0,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'inter_shipping_tablerate', array(
	'group'     	=> 'Prices',
	'input'         => 'price',
        'type'          => 'decimal',
        'label'         => 'International Shipping Cost (Qty more than 1)',
	'backend'       => '',
	'visible'       => 1,
	'required'	=> 0,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 0,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
$installer->endSetup();

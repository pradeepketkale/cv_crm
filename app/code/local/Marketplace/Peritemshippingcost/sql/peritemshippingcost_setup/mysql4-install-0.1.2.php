<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

// adding attribute group
// You dont need to add group, its attribute set -> Group -> Attribute
// Use only if you want to have a group like prices
//$setup->addAttributeGroup('catalog_product', 'Default', 'Special Attributes', 1000);

// the attribute added will be displayed under the group/tab Special Attributes in product edit page
$setup->addAttribute('catalog_product', 'shippingcost', array(
	'group'     	=> 'Prices',
	'input'         => 'price',
        'type'          => 'decimal',
        'label'         => 'Shipping Cost',
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

$setup->addAttribute('catalog_product', 'shipping_tablerate', array(
	'group'     	=> 'Prices',
	'input'         => 'price',
        'type'          => 'decimal',
        'label'         => 'Shipping Cost (Qty more than 1)',
	'backend'       => '',
	'visible'       => 1,
	'required'	=> 1,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 0,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttribute('catalog_product', 'option_tablerate', array(
	'group'     	=> 'Prices',
	'input'         => 'select',
        'type'          => 'text',
        'label'         => 'Allow shipping tablerates',
	'backend'       => '',
        'source'        => 'peritemshippingcost/peritemshippingcost_attribute_source_unit',
	'visible'       => 1,
	'required'	=> 1,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 0,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->_conn->addColumn($this->getTable('sales_flat_quote_item'), 'shippingcost', 'decimal');
$setup->addAttribute('quote_item', 'shippingcost', array('type' => 'decimal'));

$installer->_conn->addColumn($this->getTable('sales_flat_order_item'), 'shippingcost', 'decimal');
$setup->addAttribute('order_item', 'shippingcost', array('shippingcost' => 'decimal'));

$setup->addAttribute('shipment_item', 'shippingcost', array('type' => 'decimal'));

$setup->addAttribute('invoice_item', 'shippingcost', array('type' => 'decimal'));

$setup->addAttribute('creditmemo_item', 'shippingcost', array('type' => 'decimal'));

$installer->endSetup();

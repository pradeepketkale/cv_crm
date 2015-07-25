<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_product', 'added_from', array(
	'group'     	=> 'General',
	'input'         => 'boolean',
        'type'          => 'int',
        'label'         => 'Added By Self',
	'backend'       => '',
	'visible'       => 1,
	'required'	=> 0,
        'default'	=> 0,
	'user_defined'  => 0,
	'searchable'    => 0,
	'filterable'    => 0,
	'comparable'	=> 0,
	'visible_on_front' => 0,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 1,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();


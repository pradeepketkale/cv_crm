<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('customer', 'wishlist_share', array(
						'label'		=> 'Wishlist Sharing',
						'type'		=> 'int',
						'input'		=> 'select',
                                                'source'    =>  'eav/entity_attribute_source_boolean',
                                                'default'	=> 0,
                                                'visible'	=> true,
						'required'	=> false,
						'user_defined' => true,
                                   ));
$installer->endSetup();
//$installer->installEntities();
?>
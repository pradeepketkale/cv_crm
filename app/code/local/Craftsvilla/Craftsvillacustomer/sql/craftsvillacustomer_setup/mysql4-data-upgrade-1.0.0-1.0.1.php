<?php
/*Author: Saurabh Sharma - 20th dec .2011
* For Adding Attribute Bonafide Customer
*/
$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('customer', 'customer_photo', array(
						'label'		=> 'Customer Photo',
						'type'		=> 'varchar',
						'input'		=> 'file',
						'default'	=> '',
                                                'visible'	=> true,
						'required'	=> false,
						'user_defined' => true,
                                   ));
$installer->endSetup();
//$installer->installEntities();
?>
<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

// adding attribute group
// You dont need to add group, its attribute set -> Group -> Attribute
// Use only if you want to have a group like prices
//$setup->addAttributeGroup('catalog_product', 'Default', 'Special Attributes', 1000);

// the attribute added will be displayed under the group/tab Special Attributes in product edit page

$this->run("
CREATE TABLE `{$this->getTable('feedback_vendor_shipping')}` (
`feedback_id` int(11) unsigned NOT NULL auto_increment,
`feedback_at` timestamp,
`update_at` timestamp,
`image_path` varchar(255),
`shipment_id` int(11) NOT NULL,
`ship_item_id` int(11) NOT NULL,
`order_item_id` int(11) NOT NULL,
`vendor_id` int(11) unsigned NOT NULL ,
`cust_id` int(11) NOT NULL,
`feedback` char(2) NOT NULL,
`feedback_comments` varchar(255) NOT NULL,
`status` char(1) NOT NULL,
`hold` char(1) NOT NULL,
`customer_comments` varchar(255) default NULL,
PRIMARY KEY  (`feedback_id`),
KEY `FK_feedback_vendor_shipping` (`vendor_id`),
KEY `ID_STATUS` (`status`),
KEY `ID_SHIPID` (`ship_item_id`),
KEY `ID_FEEDBACK_AT` (`feedback_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 
");
//CONSTRAINT `FK_feedback_vendor_shipping` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
$this->_conn->addColumn($this->getTable('sales_flat_shipment_item'), 'feedback_id', 'int unsigned');

$setup->addAttribute('shipment_item', 'udropship_vendor', array('type' => 'int'));

$setup->addAttribute('invoice_item', 'udropship_vendor', array('type' => 'int'));

$setup->addAttribute('creditmemo_item', 'udropship_vendor', array('type' => 'int'));

$setup->addAttribute('shipment_item', 'feedback_at', array('type' => 'datetime'));

$setup->addAttribute('shipment_item', 'cust_id', array('type' => 'int'));

$setup->addAttribute('shipment_item', 'feedback', array('type' => 'varchar'));

$setup->addAttribute('shipment_item', 'status', array('type' => 'int')); //active inactive feedback

$setup->addAttribute('shipment_item', 'feedback', array('type' => 'text'));

$setup->addAttribute('shipment_item', 'customer_comments', array('type' => 'text')); //only if feedback is negative or neutral 

$installer->endSetup();

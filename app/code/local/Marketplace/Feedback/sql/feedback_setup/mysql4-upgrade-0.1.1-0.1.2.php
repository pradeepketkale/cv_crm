<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('feedback_reminder')};
CREATE TABLE {$this->getTable('feedback_reminder')} (
  `entity_id` int(11) unsigned NOT NULL auto_increment,
  `shipment_id` int(10) unsigned NOT NULL,
  `follow_reminder_date` date,
  `status` smallint(1) NOT NULL default '0',
  PRIMARY KEY (`entity_id`),
KEY `FK_feedback_reminder_shipment` (`shipment_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
$installer->endSetup(); 

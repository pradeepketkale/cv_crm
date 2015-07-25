<?php
//echo "hiii";
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('referfriend_referral')};
CREATE TABLE IF NOT EXISTS `referfriend_referral` (
  `referfriend_referral_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `referral_parent_id` int(11) unsigned NOT NULL,
  `referral_child_id` int(11) unsigned DEFAULT NULL,
  `referral_email` varchar(255) NOT NULL DEFAULT '',
  `refer_id` int(11) NOT NULL,
  `referral_name` varchar(255) DEFAULT NULL,
  `referral_code` varchar(100) NOT NULL,
  `referral_register_status` tinyint(1) DEFAULT '0',
  `referral_purchase_status` tinyint(4) NOT NULL DEFAULT '0',
  `referral_reminder` int(3) NOT NULL DEFAULT '1',
  `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`referfriend_referral_id`),
  UNIQUE KEY `email` (`referral_email`),
  UNIQUE KEY `son_id` (`referral_child_id`),
  KEY `FK_customer_entity` (`referral_parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

");

$installer->endSetup(); 
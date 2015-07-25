<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('uagent'),'agent_commission','varchar(50)');
$installer->getConnection()->addColumn($this->getTable('uagent'),'closing_balance','varchar(50)');
$installer->endSetup();

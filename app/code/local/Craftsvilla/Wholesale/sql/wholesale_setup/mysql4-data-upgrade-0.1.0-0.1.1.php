<?php

$this->startSetup();

$this->_conn->addColumn($this->getTable('wholesale'), 'vendorquote', 'varchar(255)');
$this->_conn->addColumn($this->getTable('wholesale'), 'deliverydate', 'datetime');

$this->endSetup();

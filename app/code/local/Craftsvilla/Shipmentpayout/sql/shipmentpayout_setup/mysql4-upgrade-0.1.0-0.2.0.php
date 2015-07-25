<?php
$this->startSetup();

$this->_conn->addColumn($this->getTable('shipmentpayout'), 'payment_amount', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('shipmentpayout'), 'type', 'varchar(50)');

$this->endSetup();
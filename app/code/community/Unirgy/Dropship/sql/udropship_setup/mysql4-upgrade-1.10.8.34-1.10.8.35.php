<?php

$this->startSetup();

$this->_conn->addColumn($this->getTable('udropship_vendor'), 'merchant_id_city', "varchar(60) not null after vendor_name");

$this->endSetup();

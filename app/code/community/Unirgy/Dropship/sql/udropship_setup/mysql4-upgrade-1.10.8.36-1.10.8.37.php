<?php

$this->startSetup();

$this->_conn->addColumn($this->getTable('udropship_vendor'), 'allow_international_shipping', "int(11) default 0 after merchant_id_city");

$this->endSetup();




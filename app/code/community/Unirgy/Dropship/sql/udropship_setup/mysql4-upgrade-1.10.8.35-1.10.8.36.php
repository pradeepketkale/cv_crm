<?php

$this->startSetup();

$this->_conn->addColumn($this->getTable('udropship_vendor'), 'meta_description', "varchar(170) after random_hash");

$this->endSetup();

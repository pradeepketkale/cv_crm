<?php

$this->startSetup();

$this->_conn->addColumn($this->getTable('udropship/vendor'), 'seller_priority', 'int(11)' );

$this->endSetup();

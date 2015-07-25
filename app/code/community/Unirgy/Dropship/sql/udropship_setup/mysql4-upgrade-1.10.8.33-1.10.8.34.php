<?php

$this->startSetup();

$this->_conn->dropColumn($this->getTable('udropship/vendor'), 'seller_priority');
$this->_conn->addColumn($this->getTable('udropship/vendor'), 'seller_priority', 'int(11)not null default 1');

$this->endSetup();

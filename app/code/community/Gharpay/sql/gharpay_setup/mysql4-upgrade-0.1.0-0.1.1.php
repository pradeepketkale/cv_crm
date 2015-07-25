<?php

$installer = $this;

$installer->startSetup();

$installer->run("
alter table gharpay add unique (order_id)
");

$installer->endSetup(); 
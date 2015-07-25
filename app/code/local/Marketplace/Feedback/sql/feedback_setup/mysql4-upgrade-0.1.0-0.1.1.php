<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
$this->startSetup();

$this->_conn->dropColumn($this->getTable('feedback_vendor_shipping'), 'update_at');
$this->_conn->dropColumn($this->getTable('feedback_vendor_shipping'), 'ship_item_id');
$this->_conn->dropColumn($this->getTable('feedback_vendor_shipping'), 'order_item_id');
$this->_conn->dropColumn($this->getTable('feedback_vendor_shipping'), 'feedback_comments');
$this->_conn->dropColumn($this->getTable('feedback_vendor_shipping'), 'status');
$this->_conn->dropColumn($this->getTable('feedback_vendor_shipping'), 'hold');
$this->_conn->dropColumn($this->getTable('feedback_vendor_shipping'), 'customer_comments');

$this->_conn->addColumn($this->getTable('feedback_vendor_shipping'), 'received', "TINYINT( 2 ) NULL DEFAULT NULL COMMENT '0:Not-Recevied 1:Received'");
$this->_conn->addColumn($this->getTable('feedback_vendor_shipping'), 'rating', "TINYINT( 2 ) NOT NULL COMMENT '0:Negative 1:Neutral 2:Positive'");
$this->_conn->addColumn($this->getTable('feedback_vendor_shipping'), 'feedback_type', "TINYINT( 2 ) NOT NULL DEFAULT '1' COMMENT '1:Customer 2:Vendor'");

$this->_conn->changeColumn($this->getTable('feedback_vendor_shipping'), 'feedback', 'feedback','TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
$this->endSetup();

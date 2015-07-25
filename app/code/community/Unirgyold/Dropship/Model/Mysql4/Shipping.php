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

class Unirgy_Dropship_Model_Mysql4_Shipping extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/shipping', 'shipping_id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);

        $id = $object->getId();
        if (!$id) {
            return;
        }

        $read = $this->_getReadAdapter();

        $table = $this->getTable('udropship/shipping_website');
        $select = $read->select()->from($table)->where($table.'.shipping_id=?', $id);
        if ($result = $read->fetchAll($select)) {
            foreach ($result as $row) {
                $websites = $object->getWebsiteIds();
                if (!$websites) $websites = array();
                $websites[] = $row['website_id'];
                $object->setWebsiteIds($websites);
            }
        }

        $table = $this->getTable('udropship/shipping_method');
        $select = $read->select()->from($table)->where($table.'.shipping_id=?', $id);
        if ($result = $read->fetchAll($select)) {
            foreach ($result as $row) {
                $methods = $object->getSystemMethods();
                if (!$methods) $methods = array();
                $methods[$row['carrier_code']] = $row['method_code'];
                $object->setSystemMethods($methods);
            }
        }
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        $write = $this->_getWriteAdapter();

        $table = $this->getTable('udropship/shipping_website');
        $write->delete($table, $write->quoteInto('shipping_id=?', $object->getId()));
        $websiteIds = $object->getWebsiteIds();
        if (in_array(0, $websiteIds)) {
            $websiteIds = array(0);
        }
        foreach ($websiteIds as $wId) {
            $write->insert($table, array('shipping_id'=>$object->getId(), 'website_id'=>(int)$wId));
        }

        $table = $this->getTable('udropship/shipping_method');
        $write->delete($table, $write->quoteInto('shipping_id=?', $object->getId()));
        foreach ($object->getSystemMethods() as $c=>$m) {
            if (!$m) continue;
            $write->insert($table, array('shipping_id'=>$object->getId(), 'carrier_code'=>$c, 'method_code'=>$m));
        }
    }

}
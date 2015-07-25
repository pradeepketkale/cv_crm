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

class Unirgy_Dropship_Model_Mysql4_Vendor_Statement extends Unirgy_Dropship_Model_Mysql4_Vendor_Statement_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_statement', 'vendor_statement_id');
    }
    
    protected function _getAdjustmentTable()
    {
        return $this->getTable('udropship/vendor_statement_adjustment');
    }
    protected function _getRowTable()
    {
        return $this->getTable('udropship/vendor_statement_row');
    }
    
    protected function _prepareRowSave($statement, $row)
    {
        $row['statement_id'] = $statement->getId();
        return parent::_prepareRowSave($statement, $row);
    }
    
    public function initAdjustmentsCollection($statement)
    {
        $statement->setAdjustmentsCollection(
            Mage::getResourceModel('udropship/vendor_statement_adjustment_collection')
                ->addFieldToFilter('statement_id', $statement->getStatementId())
        );
        return $this;
    }
    
    protected function _cleanRowTable($statement)
    {
        $conn = $this->_getWriteAdapter();
        $conn->delete(
            $this->_getRowTable(), 
            $conn->quoteInto('statement_id=?', $statement->getId())
            .$conn->quoteInto(' AND (po_id not in (?)', array_keys($statement->getOrders()))
            .$conn->quoteInto(' OR po_type!=? OR po_id is NULL)', $statement->getPoType())
        );
        return $this;
    }
    
    protected function _cleanAdjustmentTable($statement)
    {
        $conn = $this->_getWriteAdapter();
        $conn->delete(
            $this->_getAdjustmentTable(), 
            $conn->quoteInto('statement_id=?', $statement->getStatementId())
            .$conn->quoteInto(' AND adjustment_id not like ?', Mage::helper('udropship')->getAdjustmentPrefix('statement').'%')
        );
        return $this;
    }
    
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        if (Mage::helper('udropship')->isUdpayoutActive()) {
            $ptCollection = Mage::getResourceModel('udpayout/payout_collection')
                ->addFieldToFilter('statement_id', $object->getStatementId())
                ->addFieldToFilter('payout_status', Unirgy_DropshipPayout_Model_Payout::STATUS_HOLD);
            foreach ($ptCollection as $pt) {
                $pt->setPayoutStatus($pt->getData('before_hold_status'))->save();
            }
        }
        $this->_cleanStatement($object);
        return parent::_beforeDelete($object);
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
       
        parent::_afterSave($object);
        
        $this->_saveRows($object);
        $this->_saveAdjustments($object);
        
        if ($object->getOrders()) {
            $this->_changePosAttribute(array_keys($object->getOrders()), $object->getPoType(), 'statement_id', $object->getStatementId());
        }
        
        return $this;
    }
    
    protected function _getCleanExcludePoSelect(Mage_Core_Model_Abstract $object)
    {
        $conn = $this->_getWriteAdapter();
        $excludePoSelect = $conn->select()->union(array(
            $conn->select()
                ->from(array('sr' => $this->getTable('udropship/vendor_statement_row')), array())
                ->where('sr.po_type=?', $object->getPoType())
                ->where('sr.statement_id!=?', $object->getId())
                ->columns('sr.po_id')
        ));
        if (Mage::helper('udropship')->isUdpayoutActive()) {
            $excludePoSelect->union(array(
                $conn->select()
                    ->from(array('pr' => $this->getTable('udpayout/payout_row')), array())
                    ->where('pr.po_type=?', $object->getPoType())
                    ->columns('pr.po_id')
            ));
        }
        return $excludePoSelect;
    }
    
    protected function _cleanStatement(Mage_Core_Model_Abstract $object)
    {
        $conn = $this->_getWriteAdapter();
        $conn->delete(
            $this->getTable('udropship/vendor_statement_row'), 
            $conn->quoteInto('statement_id=?', $object->getId())
        );
        $excludePoSelect = $conn->select()->union(array(
            $conn->select()
                ->from(array('sr' => $this->getTable('udropship/vendor_statement_row')), array())
                ->where('sr.po_type=?', $object->getPoType())
                ->where('sr.statement_id!=?', $object->getId())
                ->columns('sr.po_id')
        ));
        $this->_changePosAttribute(array_keys($object->getOrders()), $object->getPoType(), 'statement_id', NULL, $excludePoSelect);
        parent::_cleanStatement($object);
        return $this;
    }
    
    public function markPosHold($statement)
    {
        return $this->_changePosAttribute(array_keys($statement->getUnpaidOrders()), $statement->getPoType(), 'udropship_payout_status', Unirgy_DropshipPayout_Model_Payout::STATUS_HOLD);
    }
    
    public function markPosPaid($statement)
    {
        return $this->_changePosAttribute(array_keys($statement->getUnpaidOrders()), $statement->getPoType(), 'udropship_payout_status', Unirgy_DropshipPayout_Model_Payout::STATUS_PAID);
    }
    
    protected function _prepareAdjustmentSave($statement, $adjustment)
    {
        $adjustment['statement_id'] = $statement->getStatementId();
        return parent::_prepareAdjustmentSave($statement, $adjustment);
    }
}

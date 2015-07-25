<?php

class Craftsvilla_Shipmentpayout_Model_Shipmentpayout extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('shipmentpayout/shipmentpayout');
    }
    
    public function loadByOrderIncrementId($incrementId)
    {
        $ids = $this->getCollection()
            ->addFieldToFilter('order_id', $incrementId)
            ->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->load(current($ids));
        }
        return $this;
    }
	 public function loadByPayoutId($id)
    {
        $ids = $this->getCollection()
            ->addFieldToFilter('shipmentpayout_id ', $id)
            ->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->load(current($ids));
        }
        return $this;
    } 
}

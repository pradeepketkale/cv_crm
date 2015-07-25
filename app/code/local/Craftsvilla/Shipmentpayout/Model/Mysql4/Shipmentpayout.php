<?php

class Craftsvilla_Shipmentpayout_Model_Mysql4_Shipmentpayout extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the shipmentpayout_id refers to the key field in your database table.
        $this->_init('shipmentpayout/shipmentpayout', 'shipmentpayout_id');
    }
}

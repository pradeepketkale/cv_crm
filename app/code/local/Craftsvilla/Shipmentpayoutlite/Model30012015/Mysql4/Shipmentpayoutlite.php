<?php

class Craftsvilla_Shipmentpayoutlite_Model_Mysql4_Shipmentpayoutlite extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the shipmentpayoutlite_id refers to the key field in your database table.
        $this->_init('shipmentpayoutlite/shipmentpayoutlite', 'shipmentpayoutlite_id');
    }
}

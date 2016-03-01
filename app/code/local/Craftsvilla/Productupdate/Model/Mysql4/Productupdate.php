<?php

class Craftsvilla_Productupdate_Model_Mysql4_Productupdate extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        
        $this->_init('productupdate/productupdate', 'entity_id');
    }
}
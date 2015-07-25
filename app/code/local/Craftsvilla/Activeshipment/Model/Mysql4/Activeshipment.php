<?php

class Craftsvilla_Activeshipment_Model_Mysql4_Activeshipment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the activeshipment_id refers to the key field in your database table.
        $this->_init('activeshipment/activeshipment', 'activeshipment_id');
    }
}
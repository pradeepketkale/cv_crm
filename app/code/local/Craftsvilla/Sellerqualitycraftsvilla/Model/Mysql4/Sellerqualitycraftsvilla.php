<?php

class Craftsvilla_Sellerqualitycraftsvilla_Model_Mysql4_Sellerqualitycraftsvilla extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the id refers to the key field in your database table.
        $this->_init('sellerqualitycraftsvilla/sellerqualitycraftsvilla', 'sellerquality_id');
    }
}

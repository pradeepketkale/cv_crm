<?php

class Craftsvilla_Mktngproducts_Model_Mysql4_Mktngproducts extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the mktngproducts_id refers to the key field in your database table.
        $this->_init('mktngproducts/mktngproducts', 'mktngproducts_id');
    }
}

<?php

class Craftsvilla_Refundrequestedcustomer_Model_Mysql4_Refundrequestedcustomer extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the refundrequestedcustomer_id refers to the key field in your database table.
        $this->_init('refundrequestedcustomer/refundrequestedcustomer', 'refundrequestedcustomer_id');
    }
}

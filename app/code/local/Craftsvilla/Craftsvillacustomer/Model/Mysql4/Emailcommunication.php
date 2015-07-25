<?php

class Craftsvilla_Craftsvillacustomer_Model_Mysql4_Emailcommunication extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the craftsvillacustomer_id refers to the key field in your database table.
        $this->_init('craftsvillacustomer/emailcommunication', 'emailcommunication_id');
    }
}
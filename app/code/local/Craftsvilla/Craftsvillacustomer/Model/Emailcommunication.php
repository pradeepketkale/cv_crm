<?php

class Craftsvilla_Craftsvillacustomer_Model_Emailcommunication extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('craftsvillacustomer/emailcommunication');
    }
}
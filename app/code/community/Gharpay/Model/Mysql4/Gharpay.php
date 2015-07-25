<?php

class Gharpay_Model_Mysql4_Gharpay extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {    
        $this->_init('gharpay/gharpay', 'id');
    }
}
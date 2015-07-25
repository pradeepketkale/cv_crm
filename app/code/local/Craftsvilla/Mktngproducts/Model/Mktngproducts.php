<?php

class Craftsvilla_Mktngproducts_Model_Mktngproducts extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mktngproducts/mktngproducts');
    }
}

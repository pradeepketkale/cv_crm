<?php

class Craftsvilla_Codshipments_Model_Codshipments extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('codshipments/codshipments');
    }
}
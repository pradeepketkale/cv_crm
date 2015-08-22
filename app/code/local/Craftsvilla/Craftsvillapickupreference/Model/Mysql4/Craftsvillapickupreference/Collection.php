<?php

class Craftsvilla_Craftsvillapickupreference_Model_Mysql4_Craftsvillapickupreference_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('craftsvillapickupreference/craftsvillapickupreference');
    }
}

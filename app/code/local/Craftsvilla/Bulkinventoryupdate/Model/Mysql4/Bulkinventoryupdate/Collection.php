<?php

class Craftsvilla_Bulkinventoryupdate_Model_Mysql4_Bulkinventoryupdate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('bulkinventoryupdate/bulkinventoryupdate');
    }
}
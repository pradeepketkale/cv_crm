<?php

class Craftsvilla_ReferFriend_Model_Mysql4_Count extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('referfriend/count', 'count_id');
    }
}

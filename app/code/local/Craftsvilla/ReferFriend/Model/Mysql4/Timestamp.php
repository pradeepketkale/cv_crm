<?php

class Craftsvilla_ReferFriend_Model_Mysql4_Timestamp extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('referfriend/timestamp', 'time_stamp_id');
    }
}

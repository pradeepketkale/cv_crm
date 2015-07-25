<?php

class Craftsvilla_ReferFriend_Model_Mysql4_Referral extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('referfriend/referral', 'referfriend_referral_id');
    }
}

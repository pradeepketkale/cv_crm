<?php

class Craftsvilla_ReferFriend_Model_Mysql4_Referral_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('referfriend/referral');
    }
}

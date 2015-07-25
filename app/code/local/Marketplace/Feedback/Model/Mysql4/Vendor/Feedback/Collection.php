<?php
class Marketplace_Feedback_Model_Mysql4_Vendor_Feedback_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('feedback/vendor_feedback');
    }
}

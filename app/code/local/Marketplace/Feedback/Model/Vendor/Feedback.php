<?php
class Marketplace_Feedback_Model_Vendor_Feedback extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('feedback/vendor_feedback','feedback_id');
        parent::_construct();
    }
    

}

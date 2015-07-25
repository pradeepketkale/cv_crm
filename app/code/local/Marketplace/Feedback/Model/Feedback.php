<?php
class Marketplace_Feedback_Model_Feedback extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('feedback/feedback','feedback_id');
        parent::_construct();
    }
    

}

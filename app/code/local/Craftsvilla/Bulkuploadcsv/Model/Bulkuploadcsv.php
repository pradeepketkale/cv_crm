<?php

class Craftsvilla_Bulkuploadcsv_Model_Bulkuploadcsv extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('bulkuploadcsv/bulkuploadcsv');
    }
}
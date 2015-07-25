<?php

class Craftsvilla_Bulkuploadcsv_Model_Mysql4_Bulkuploadcsv extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the bulkuploadcsvid refers to the key field in your database table.
        $this->_init('bulkuploadcsv/bulkuploadcsv', 'bulkuploadid');
    }
}
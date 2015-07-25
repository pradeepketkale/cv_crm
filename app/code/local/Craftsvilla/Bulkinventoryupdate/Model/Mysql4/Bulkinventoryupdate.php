<?php

class Craftsvilla_Bulkinventoryupdate_Model_Mysql4_Bulkinventoryupdate extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the bulkuploadcsvid refers to the key field in your database table.
        $this->_init('bulkinventoryupdate/bulkinventoryupdate', 'bulkinventoryupdateid');
    }
}
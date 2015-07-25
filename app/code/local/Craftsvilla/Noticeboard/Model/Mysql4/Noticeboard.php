<?php

class Craftsvilla_Noticeboard_Model_Mysql4_Noticeboard extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the bulkuploadcsvid refers to the key field in your database table.
        $this->_init('noticeboard/noticeboard', 'noticeid');
    }
}
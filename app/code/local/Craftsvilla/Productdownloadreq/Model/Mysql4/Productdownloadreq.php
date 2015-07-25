<?php

class Craftsvilla_Productdownloadreq_Model_Mysql4_Productdownloadreq extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('productdownloadreq/productdownloadreq', 'productdownloadreq_id');
    }
}
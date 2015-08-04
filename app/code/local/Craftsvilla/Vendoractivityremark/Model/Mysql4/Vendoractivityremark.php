<?php

class Craftsvilla_Vendoractivityremark_Model_Mysql4_Vendoractivityremark extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('vendoractivityremark/vendoractivityremark', 'vendoractivityremark_id');
    }
}

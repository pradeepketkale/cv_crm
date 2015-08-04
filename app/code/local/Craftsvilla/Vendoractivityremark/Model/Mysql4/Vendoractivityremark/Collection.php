<?php

class Craftsvilla_Vendoractivityremark_Model_Mysql4_Vendoractivityremark_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendoractivityremark/vendoractivityremark');
    }
}

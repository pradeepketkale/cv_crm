<?php
class Craftsvilla_Vendorseo_Model_Mysql4_Vendorseo extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("vendorseo/vendorseo", "vendor_id");
    }
}
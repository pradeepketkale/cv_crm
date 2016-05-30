<?php
class Craftsvilla_Seovendor_Model_Mysql4_Seovendor extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("seovendor/seovendor", "vendor_id");
    }
}
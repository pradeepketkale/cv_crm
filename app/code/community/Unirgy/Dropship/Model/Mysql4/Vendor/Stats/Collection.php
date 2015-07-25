<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Unirgy_Dropship_Model_Mysql4_Vendor_Stats_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_stats');
        parent::_construct();
    }
}
?>

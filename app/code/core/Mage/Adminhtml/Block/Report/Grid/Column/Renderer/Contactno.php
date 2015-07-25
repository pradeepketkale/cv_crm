<?php
class Mage_Adminhtml_Block_Report_Grid_Column_Renderer_Contactno extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    { 
        foreach($row as $k=>$v) { 
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(array(Mage::app()->getStore(true)->getWebsite()->getId()));
            $customer->loadByEmail($v['email']);
            $contactNo = Mage::getModel('customer/address')->load($customer->getDefaultBilling())->getTelephone();
            if($contactNo != ''):
                return $contactNo;
            else:
                return " ";
            endif;
        }
    }
}
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

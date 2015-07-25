<?php
class Craftsvilla_Coupons_Block_Adminhtml_Customers_View
 extends Mage_Adminhtml_Block_Template
 implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct() {
        $this->setTemplate('template/coupons/view.phtml');
    }

} 


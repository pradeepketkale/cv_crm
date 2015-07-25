<?php

class Marketplace_Feedback_IndexController extends Mage_Core_Controller_Front_Action
{

    public function IndexAction()
    {
        $this->loadLayout();
        $this->renderlayout();
    }
    

    public function getVendorShipmentCollection()
    {
        return Mage::helper('udropship')->getVendorShipmentCollection();
    }
}

<?php

class Marketplace_Thoughtyard_ProfileController extends Mage_Core_Controller_Front_Action
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

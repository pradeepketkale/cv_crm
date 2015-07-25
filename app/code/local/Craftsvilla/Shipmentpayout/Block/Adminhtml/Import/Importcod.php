<?php
/**
 * Shipment Tracking module
 * Author : Saurabh Sharma*/
class Craftsvilla_Shipmentpayout_Block_Adminhtml_Import_Importcod extends Mage_Adminhtml_Block_Widget
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('shipmenttracking/import/importcod.phtml');
    }

}

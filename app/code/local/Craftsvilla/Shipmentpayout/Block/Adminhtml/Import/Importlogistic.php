<?php
/**
 * Shipment Tracking module
 * Author : Saurabh Sharma*/
class Craftsvilla_Shipmentpayout_Block_Adminhtml_Import_Importlogistic extends Mage_Adminhtml_Block_Widget
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('shipmenttracking/import/importlogistic.phtml');
    }

}

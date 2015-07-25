<?php
/**
 * Shipment Tracking module
 * Author : Saurabh Sharma*/
class Craftsvilla_Agentpayout_Block_Adminhtml_Import_Formpaytype extends Mage_Adminhtml_Block_Widget
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('agentpaytype/import/formpaytypeagent.phtml');
    }

}

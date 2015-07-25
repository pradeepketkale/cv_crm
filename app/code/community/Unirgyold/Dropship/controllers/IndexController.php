<?php

class Unirgy_Dropship_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->_forward('index', 'vendor');
    }
    public function vendorAutocompleteAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('udropship/vendor_autocomplete')
                ->setVendorPrefix($this->getRequest()->getParam('vendor_name'))
                ->toHtml()
        );
    }
}
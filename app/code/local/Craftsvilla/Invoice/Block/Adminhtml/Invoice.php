<?php
class Craftsvilla_Invoice_Block_Adminhtml_Invoice extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amit/invoice/invoice.phtml');
    }
	
	protected function _prepareLayout()
    {
	
    }

	protected function getInvoice()
	{
		$invoice_id=$this->getRequest()->getParam('invoice_id');
		return Mage::getModel('sales/order_invoice')->load($invoice_id);
	}	
	protected function getOrder()
	{
		$order_id=$this->getRequest()->getParam('order_id');
		return Mage::getModel('sales/order')->load($order_id);
		 //return Mage::registry('sales_order');
	}	
}
?>
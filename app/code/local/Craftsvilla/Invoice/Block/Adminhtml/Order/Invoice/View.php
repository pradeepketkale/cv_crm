<?php
class Craftsvilla_Invoice_Block_Adminhtml_Order_Invoice_View extends Mage_Adminhtml_Block_Sales_Order_Invoice_View
{
	public function __construct() {
		//echo "here"; exit;
		
		parent::__construct();
		$this->removeButton('print');
		if ($this->getInvoice()->getId()){
		$this->_addButton('order_reorder', array(
		    'label'     => Mage::helper('sales')->__('Print'),
		    'onclick'   => 'window.open(\'' . $this->getCustomUrl() . '\')',
		));
		}
	}
	
	public function getCustomUrl()
    {
        return $this->getUrl('invoice/adminhtml_invoice/index', array(
            'order_id'  => $this->getInvoice()->getOrder()->getId(),
	'invoice_id' => $this->getInvoice()->getId(),
        ));
    }
}
?>

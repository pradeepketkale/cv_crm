<?php  

class Craftsvilla_Seovendor_Block_Adminhtml_Seovendorbackend extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
			 $this->_controller = "adminhtml_seovendor";
			 $this->_blockGroup = "seovendor";
			 $this->_headerText = Mage::helper("seovendor")->__("Vendor Seo Manager");
			 $this->_addButtonLabel = Mage::helper("seovendor")->__("Add New Item");
			 parent::__construct();
	}
}

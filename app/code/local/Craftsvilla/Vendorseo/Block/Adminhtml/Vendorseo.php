<?php


class Craftsvilla_Vendorseo_Block_Adminhtml_Vendorseo extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_vendorseo";
	$this->_blockGroup = "vendorseo";
	$this->_headerText = Mage::helper("vendorseo")->__("Vendorseo Manager");
	$this->_addButtonLabel = Mage::helper("vendorseo")->__("Add New Item");
	parent::__construct();
	
	}
}
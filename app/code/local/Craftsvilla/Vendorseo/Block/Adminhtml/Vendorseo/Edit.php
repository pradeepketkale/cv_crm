<?php
	
class Craftsvilla_Vendorseo_Block_Adminhtml_Vendorseo_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "vendor_id";
				$this->_blockGroup = "vendorseo";
				$this->_controller = "adminhtml_vendorseo";
				$this->_updateButton("save", "label", Mage::helper("vendorseo")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("vendorseo")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("vendorseo")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);
				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("vendorseo_data") && Mage::registry("vendorseo_data")->getId() ){
				return Mage::helper("vendorseo")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("vendorseo_data")->getVendorName()));
				} 
				else{
				return Mage::helper("vendorseo")->__("Add Item");
				}
		}
		protected function _prepareLayout()
		{
				// Load Wysiwyg on demand and Prepare layout
				if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head')))
				{
				$block->setCanLoadTinyMce(true);
				}
				parent::_prepareLayout();
        }
}
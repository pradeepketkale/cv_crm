<?php
	
class Craftsvilla_Seovendor_Block_Adminhtml_Seovendor_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "vendor_id";
				$this->_blockGroup = "seovendor";
				$this->_controller = "adminhtml_seovendor";
				$this->_updateButton("save", "label", Mage::helper("seovendor")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("seovendor")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("seovendor")->__("Save And Continue Edit"),
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
				if( Mage::registry("seovendor_data") && Mage::registry("seovendor_data")->getId() ){
				return Mage::helper("seovendor")->__("Edit SeoData");
				} 
				else{
				return Mage::helper("seovendor")->__("Add Item");
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
<?php
class Craftsvilla_Vendorseo_Block_Adminhtml_Vendorseo_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("vendorseo_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("vendorseo")->__("VendorSeo Settings"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("vendorseo")->__("VendorSeo Settings"),
				"title" => Mage::helper("vendorseo")->__("VendorSeo Settings"),
				"content" => $this->getLayout()->createBlock("vendorseo/adminhtml_vendorseo_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}

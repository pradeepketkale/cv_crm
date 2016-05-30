<?php
class Craftsvilla_Seovendor_Block_Adminhtml_Seovendor_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("seovendor_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("seovendor")->__("VendorSeo Settings"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("seovendor")->__("VendorSeo Settings"),
				"title" => Mage::helper("seovendor")->__("VendorSeo Settings"),
				"content" => $this->getLayout()->createBlock("seovendor/adminhtml_seovendor_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}

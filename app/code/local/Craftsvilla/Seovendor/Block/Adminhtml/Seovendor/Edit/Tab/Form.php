<?php
class Craftsvilla_Seovendor_Block_Adminhtml_Seovendor_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{
				
				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("seovendor_form", array("legend"=>Mage::helper("seovendor")->__("Seo Information")));
				$vendor_name = Mage::helper('seovendor')->getVendorList();                 
				if($this->getRequest()->getParam('id')) {
								$flag = true;
				 } else {
								$flag = false;
				 }
				
				$vendorDropdown = $fieldset->addField('vendor_id', 'select', array(
				  'label' => Mage::helper('seovendor')->__('Vendor Name'),
				  'name' => 'vendor_name',
				  'values' => $vendor_name,
				  'onchange'  => 'onchangeShowData(this)',
				  'disabled' => $flag,
				));
				
				
				
				$selectScript = '
				      var vendorId = ele.value ;
						var reloadurl = "'.$this->getUrl("*/*/getSeoData").'";
						new Ajax.Request(reloadurl, {
										method: "get",
										requestHeaders: {Accept: "application/json"},
										parameters: {v_id:vendorId},
										onLoading: function (transport) {
										 
										},
										onSuccess: function(transport) {
										  var response = transport.responseText.evalJSON();
										
										  if(response.empty == "Y"){
										  document.getElementById("meta_title").value = "";
										  document.getElementById("meta_description").value = "";
										  document.getElementById("meta_keywords").value = "";
										  tinyMCE.activeEditor.setContent("");		
										  } else {   
										  document.getElementById("meta_title").value = response.meta_title;
										  document.getElementById("meta_description").value = response.meta_description;
										  document.getElementById("meta_keywords").value = response.meta_keywords;
										  tinyMCE.activeEditor.setContent(response.vendor_description);
										  }
										},
										onFailure: function(transport) {
										  document.getElementById("meta_title").value = "";
										  document.getElementById("meta_description").value = "";
										  document.getElementById("meta_keywords").value = "";
										  tinyMCE.activeEditor.setContent("");		
										}
					    });
						
				    ';
				$vendorDropdown->setAfterElementHtml('
				<script type = "text/javascript">
						function onchangeShowData(ele) { '.$selectScript.'}
				</script>
				');
			
				$fieldset->addField("meta_title", "text", array(
				"label" => Mage::helper("seovendor")->__("Meta Title"),
				"name" => "meta_title",
				));
			
				$fieldset->addField("meta_description", "textarea", array(
				"label" => Mage::helper("seovendor")->__("Meta Description"),
				"name" => "meta_description",
				));
				
				$fieldset->addField("meta_keywords", "text", array(
				"label" => Mage::helper("seovendor")->__("Meta Keywords"),
				"name" => "meta_keywords",
				));
				
				//$fieldset->addField("vendor_description", "textarea", array(
				//"label" => Mage::helper("seovendor")->__("Description"),
				//"name" => "vendor_description",
				//));
				
				$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config');
				$fieldset->addField('vendor_description', 'editor', array(
				    'name'      => 'vendor_description',
				    'label'     => Mage::helper('seovendor')->__('Description'),
				    'title'     => Mage::helper('seovendor')->__('Description'),
				    'style'     => 'height: 15em;',
				    'wysiwyg'   => true,
				    'required'  => false,
				    'config'    => $wysiwygConfig
				));
				
				if (Mage::getSingleton("adminhtml/session")->getVendorseoData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getVendorseoData());
					Mage::getSingleton("adminhtml/session")->setVendorseoData(null);
				} 
				elseif(Mage::registry("seovendor_data")) {
				    $form->setValues(Mage::registry("seovendor_data")->getData());
				}
				return parent::_prepareForm();
		}
		
		protected function _prepareLayout()
		{
		    parent::_prepareLayout();
		    if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		    }
		}
}

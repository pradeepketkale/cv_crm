<?php
class Craftsvilla_Vendorseo_Block_Adminhtml_Vendorseo_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{
				
				$form = new Varien_Data_Form();
				$this->setForm($form);

				$read = Mage::getSingleton('core/resource')->getConnection('core_read');
				$query = "select vendor_id, vendor_name from vendor_info_craftsvilla where vendor_name is not null and vendor_name <> '' order by vendor_name asc";
				$result = $read->query($query)->fetchAll();
                                $read->closeConnection();
				$vendor_name = array('-1' => "Please select vendor.");

				foreach ($result as $key => $value) {
					$vendor_name[$value['vendor_id']] = $value['vendor_name'];
				}

				$fieldset = $form->addFieldset("vendorseo_form", array("legend"=>Mage::helper("vendorseo")->__("Item information")));

				//$fieldset->addField("vendor_name", "text", array(
				//"label" => Mage::helper("vendorseo")->__("Name"),
				//"name" => "vendor_name",
				//
				//));
				
				
				$vendorDropdown = $fieldset->addField('vendor_id', 'select', array(
				  'label' => Mage::helper('vendorseo')->__('Vendor Name'),
				  'name' => 'vendor_name',
				  'values' => $vendor_name,
				  'onchange'  => 'onchangeShowData(this)',
				));

				$selectScript = '
						var reloadurl = "'.$this->getUrl("getdata").'vendor_id/" + document.getElementById("meta_title").value;
						reloadurl = "'.Mage::helper("adminhtml")->getUrl("admin_vendorseo/adminhtml_vendorseo/getdata").'";
						new Ajax.Request(reloadurl, {
				            method: "get",
				            onLoading: function (transport) {
				                
				            },
				            onComplete: function(transport) {				            	
				            	document.getElementById("meta_title").value = "Pradeep";
                            	document.getElementById("meta_description").value = "Pradeep";
                            	document.getElementById("meta_keywords").value = "Pradeep";
				            }
				        });				                        
                            
				';
				$vendorDropdown->setAfterElementHtml('
                        <script type = "text/javascript">
                        function onchangeShowData(ele) { '.$selectScript.'			        
                        }
                        </script>
                    ');
			
				$fieldset->addField("meta_title", "text", array(
				"label" => Mage::helper("vendorseo")->__("Meta Title"),
				"name" => "meta_title",
				));
			
				$fieldset->addField("meta_description", "text", array(
				"label" => Mage::helper("vendorseo")->__("Meta Description"),
				"name" => "meta_description",
				));
			
				$fieldset->addField("meta_keywords", "text", array(
				"label" => Mage::helper("vendorseo")->__("Meta Keywords"),
				"name" => "meta_keywords",
				));
					

				if (Mage::getSingleton("adminhtml/session")->getVendorseoData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getVendorseoData());
					Mage::getSingleton("adminhtml/session")->setVendorseoData(null);
				} 
				elseif(Mage::registry("vendorseo_data")) {
				    $form->setValues(Mage::registry("vendorseo_data")->getData());
				}
				return parent::_prepareForm();
		}
}

<?php
class Marketplace_Peritemshippingcost_Model_Peritemshippingcost_Attribute_Source_Unit extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions(){
		if(!$this->_options){
			$this->_options = array (
				array(
					'value'=>'1',
					'label'=>'Yes',
				),
				array(
					'value'=>'0',
					'label'=>'No',
				),
				
			);
		}
		return $this->_options;
	}
}

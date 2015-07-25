<?php
class Marketplace_Thoughtyard_Model_Source_Location extends Varien_object
{
public function getAllOptions(){
		if(!$this->_options){
			$this->_options = array (
				array(
					'value'=>'0',
					'label'=>'No',
				),
				array(
					'value'=>'1',
					'label'=>'Yes',
				),
			);
		}
		return $this->_options;
	}
	
}

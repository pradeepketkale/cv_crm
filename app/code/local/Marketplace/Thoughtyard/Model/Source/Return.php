<?php
class Marketplace_Thoughtyard_Model_Source_Return extends Varien_object
{
public function getAllOptions(){
		if(!$this->_options){
			$this->_options = array(
                array(
                    'value' => '24 hours',
                    'label' => '24 hours',
                ),
                array(
                    'value' => '48 hours',
                    'label' => '48 hours',
                ),
                array(
                    'value' => '72 hours',
                    'label' => '72 hours',
                ),
                array(
                    'value' => '5 days',
                    'label' => '5 days',
                ),
                array(
                    'value' => '7 days',
                    'label' => '7 days',
                ),
                array(
                    'value' => '10 days',
                    'label' => '10 days',
                ),
                
            );
		}
		return $this->_options;
	}
	
}

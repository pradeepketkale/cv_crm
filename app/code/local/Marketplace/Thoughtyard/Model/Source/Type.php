<?php
class Marketplace_Thoughtyard_Model_Source_Type extends Varien_object
{
public function getAllOptions(){
		if(!$this->_options){
			$this->_options = array(
                array(
                    'value' => 'individual',
                    'label' => 'individual',
                ),
                array(
                    'value' => 'proprietorship',
                    'label' => 'Proprietorship',
                    ),
                array(
                    'value' => 'privateltd',
                    'label' => 'Pvt. Ltd. Firm',
                    ),
                array(
                    'value' => 'publicltd',
                    'label' => 'Public Ltd. Firm',
                ),
            );
		}
		return $this->_options;
	}
	
}


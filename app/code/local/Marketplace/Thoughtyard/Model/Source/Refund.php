<?php
class Marketplace_Thoughtyard_Model_Source_Refund extends Varien_object
{
public function getAllOptions(){
		if(!$this->_options){
			$this->_options = array(
                array(
                    'value' => 'voucher',
                    'label' => 'Voucher',
                ),
                array(
                    'value' => 'moneyback',
                    'label' => 'Moneyback',
                ),
                array(
                    'value' => 'other',
                    'label' => 'Others',
                ),
            );
		}
		return $this->_options;
	}
	
}

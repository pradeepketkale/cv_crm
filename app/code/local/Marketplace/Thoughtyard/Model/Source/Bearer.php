<?php
class Marketplace_Thoughtyard_Model_Source_Bearer extends Varien_object
{
public function getAllOptions(){
		if(!$this->_options){
			$this->_options = 
			array( 
			array('value' =>'buyer' , 'label' =>'Buyer',),
                        array('value' =>'seller', 'label' =>'Seller',)
                        );
		}
		return $this->_options;
	}
	
}

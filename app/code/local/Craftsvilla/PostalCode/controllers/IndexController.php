<?php
  class Craftsvilla_PostalCode_IndexController extends Mage_Core_Controller_Front_Action
  {
  	public function indexAction()
    {
	    try{
	    	$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('checkout_pobox');
			$select_1 = $read->select()->from('checkout_pobox')
								   ->where("pincode = $_GET[postcode]");
			//Mage::log($select_1->__toString());					   
			$row_1 = $read->fetchRow($select_1);
			$where = "pincode = {$_GET[postcode]} AND is_cod = 0";
			$select_2 = $read->select()->from('checkout_cod')
								 
								   ->where($where);
			//Mage::log($select_2->__toString());exit();					   
			$row_1 = $read->fetchRow($select_1);
			$row_2 = $read->fetchRow($select_2);
			if($row_1):
				$row_one_value = 1;
			else: 
				$row_one_value = 0;
			endif;

			if($row_2):
				$row_two_value = 1;
			else: 
				$row_two_value = 0;
			endif;
			
			echo $row_one_value."|".$row_two_value;
				
		}catch(Exception $e){
			echo $e->getMessage();
		}
    	
    }	
  }
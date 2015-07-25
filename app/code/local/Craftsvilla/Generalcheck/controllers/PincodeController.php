<?php


class Craftsvilla_Generalcheck_PincodeController extends Mage_Core_Controller_Front_Action{
    
	public function pincodecheckAction()
	{
		//$pincode1 = $_POST['pincode'];
		$pincode1 = $this->getRequest()->getParam('pincode');

		$pincode = $pincode1;
		//$pincode = mysql_real_escape_string($pincode1);
		  $html_value = '';
		if(!is_numeric($pincode))  
	          {
			$html_value .= '<div id="display"><div id="pincode"><div class="index">Pin Code You Have Given Is Not Valid</div></div></div>';
		  }
                  else { 
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$delhivery = Mage::getStoreConfig('courier/general/delhivery'); 
		if($delhivery=='1')
		{
			
			$pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$pincode."' AND `carrier` like '%Delhivery%'";
		
		}
		else
		{
			
			$pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$pincode."' AND `carrier` like '%Aramex%'";
		
		}
			$rquery = $read->query($pincodeQuery)->fetch();
		  $cod = $rquery['is_cod'];
		  if($cod=='0')
		  {
			$html_value .= '<div id="display"><div id="pincode"><div class="index">COD For This Pincode '.$pincode.' Is Available</div></div></div>';
		  }
		   else
		  {
			$html_value .= '<div id="display"><div id="pincode"><div class="index2">COD For This Pincode '.$pincode.' Is Not Available</div></div></div>';
		  }
		  }
						
		
	      
		   echo $html_value;
							
		}
		

	
}

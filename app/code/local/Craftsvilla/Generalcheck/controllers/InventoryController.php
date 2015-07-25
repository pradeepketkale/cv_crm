<?php


class Craftsvilla_Generalcheck_InventoryController extends Mage_Core_Controller_Front_Action{
    
	public function inventorycheckAction()
	{
		$pid = $_POST['pidvalue'];
		$checkStatusQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($pid);
		$checkType = $checkStatusQty->getTypeId();
//		echo '<pre>';print_r($checkStatusQty);exit;		
		$qty = (int)$checkStatusQty->getQty();
//		echo '<pre>';print_r($qty);exit;
		$isinstock = (int)$checkStatusQty->getIsInStock();
		$html_value = '';
		if($checkType != "configurable")
			{
				if($pid == '')
				{
					$html_value = '';
				}
		
				elseif($qty<=3 && $qty>0)
				{
					$html_value .= '<div id="craftsvilla_quantity"><div class="active">Hurry Only '.$qty.' Left!</div></div>';
				}
				elseif($qty==0 || $isinstock == 0)
				{
					$html_value .= '<div id="craftsvilla_quantity"><div class="active">Out Of Stock!</div></div>';
				}
		
				 echo $html_value;
			}	
	}
		

	
}

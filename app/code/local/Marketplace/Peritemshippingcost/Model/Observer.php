<?php
class Marketplace_Peritemshippingcost_Model_Observer{
	public function sales_order_item_save_before($observer)
	{
		$item = $observer->getEvent()->getItem();
		$item_sku = $item->getProductId();
		$item_info = Mage::getModel('catalog/product')->load($item_sku);
		if(!$item->getShippingcost()){
		    /*if($item->getOrder()->getPayment()->getMethod()=='ddpayment')
		    {
			$item->setShippingcost($item_info->getDdShippingamount());
			$orderedShippingcost = ($item_info->getDdShippingamount()*$item->getQtyOrdered());
		    }
		else 
		    {*/
				$item->setShippingcost($item_info->getShippingcost());
				if($item->getOptionTablerate())
					{
                                        $orderedShippingcost = ($item_info->getShippingcost()*$item->getQtyOrdered());
					if($item->getQtyOrdered()>1)
                                           {
					     $orderedShippingcost = ($item_info->getShippingcost()+$item->getShippingTablerate()*($item->getQtyOrdered()-1));
					   }
                                         }
				else    {
					     $orderedShippingcost = ($item_info->getShippingcost()*$item->getQtyOrdered());
		    			}




//}
		    $item->setTotalShippingcost($orderedShippingcost);
		}
	}
}

//added for DD special prices By Vipul

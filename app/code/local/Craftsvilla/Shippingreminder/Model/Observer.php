<?php
class Craftsvilla_Shippingreminder_Model_Observer
{	
	public function hookToShippmentSaveEvent($observer)
	{       $_order = $observer->getOrder();
		$orderId = $_order->getId();
		$shipment = Mage::getModel('sales/order_shipment');
		if($_order->getPayment()->getMethod() == 'cashondelivery'){
			$_helpv = Mage::helper('udropship');
			$shippmentCollection = $shipment->getCollection()
										->addAttributeToFilter('order_id', $orderId);
			foreach($shippmentCollection as $_shipment){
				$vendorIfo = '';
				$_vendorCODFee = '';
				$vendorIfo = $_helpv->getVendor($_shipment->getUdropshipVendor());
				$_vendorCODFee = $vendorIfo->getCodFee();
				$_shipment->setCodFee($_vendorCODFee)->setbaseCodFee($_vendorCODFee)->save();
			}
		}
		/***************** Added by Mandar for Some order item data put into shipment item data *********************/ 	
		$orderItem = Mage::getModel('sales/order_item')->getCollection()->addAttributeToFilter('order_id',$orderId);
		foreach($orderItem as $items):
			$shipmentItem = Mage::getModel('sales/order_shipment_item')->getCollection()->addAttributeToFilter('order_item_id',$items->getItemId());
			foreach($shipmentItem as $shipItems):
				$shipItems->setOrderId($items->getOrderId());
				$shipItems->setQtyOrdered($items->getQtyOrdered());
				$shipItems->setStoreId($items->getStoreId());
				$shipItems->setBaseOriginalPrice($items->getBaseOriginalPrice());
				$shipItems->setBaseDiscountAmount($items->getBaseDiscountAmount());
				$shipItems->setBaseAmountRefunded($items->getBaseAmountRefunded());
				$shipItems->setShippingcost($items->getShippingcost());
				$shipItems->setTotalShippingcost($items->getTotalShippingcost());
				$shipItems->save();
			endforeach;
		endforeach;
		/********************************************************************************************************/
                
                /***************** Added by Mandar for shipment itemised total cost in sales flat shipment on 28-05-2012 *********************/ 	
                $totalItemisedShipments = Mage::getModel('sales/order_shipment_item')->getCollection()->addFieldToFilter('order_id',$orderId);
                $totalItemisedShipments->addExpressionFieldToSelect('total', 'SUM({{total_shippingcost}})', 'total_shippingcost');
                $totalItemisedShipments->getSelect()->group('parent_id');
                foreach($totalItemisedShipments as $shipItemised):
                    if($shipItemised->getTotal() > 0):
                        $setTotalItemisedShipments = Mage::getModel('sales/order_shipment')->load($shipItemised->getParentId());
                        $setTotalItemisedShipments->setItemisedTotalShippingcost($shipItemised->getTotal());
                        $setTotalItemisedShipments->save();
                    endif;
                endforeach;
         }
  }

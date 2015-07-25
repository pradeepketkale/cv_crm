<?php 
class Gharpay_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	public function update($orderId, $orderStatus)
	{
		$gharpay = Mage::getModel('gharpay/gharpay')->getCollection()->addFieldToFilter('gharpay_order_id',$orderId);
		$data = $gharpay->getData();
		if (!$data[0]['id']) {
			$this->_fault('not_exists');
			// No order found
		}
		$dateTime = date("Y-m-d H:i:s");
		$model = Mage::getModel('gharpay/gharpay')->load($data[0]['id']);
		$model->setOrderStatus($orderStatus);
		$model->setUpdateTime($dateTime);
		$model->save();
		if($orderStatus == 'Delivered')
		{
			if ($data[0]['order_id'] != null) {
				$salesModel = Mage::getModel('sales/order');
				$salesModel->loadByIncrementId($data[0]['order_id']);
			}
			if($salesModel->getStatus() == 'canceled')
			{
				$message = "Secont attempt to order canceled to Processing. For Order id==".$data[0]['order_id']." and Gharpay Order Id is==".$data[0]['gharpay_order_id'];
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: raf@craftsvilla.com' . "\r\n";
				mail('mandar.datar@craftsvilla.com', 'Secont attempt to order canceled to Processing', $message, $headers);
			}
			else{
				$salesModel->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true,'',false);
				$salesModel->save();
			}
		}
		if($orderStatus == 'Failed' || $orderStatus == 'Cancelled by Customer' || $orderStatus == 'Invalid')
		{
			if ($data[0]['order_id'] != null) {
				$salesModel = Mage::getModel('sales/order');
				$salesModel->loadByIncrementId($data[0]['order_id']);
			}
			if($salesModel->getStatus() == 'processing')
			{
				$message = "Secont attempt to order Processing to canceled. For Order id==".$data[0]['order_id']." and Gharpay Order Id is==".$data[0]['gharpay_order_id'];
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: raf@craftsvilla.com' . "\r\n";
				mail('mandar.datar@craftsvilla.com', 'Secont attempt to order Processing to canceled', $message, $headers);
			}else{
				$salesModel->cancel();
				$salesModel->save();
			}
		}
		return true;
	}
}

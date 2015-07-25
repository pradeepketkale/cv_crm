<?php
class Mage_Sales_Block_Order_Pending extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/pending.phtml');

        $res = Mage::getSingleton('core/resource');
		$_todayDate = date('jS F Y');
		$_todayTime = strtotime($_todayDate);
		$startDate = date('Y-m-d', $_todayTime); 
		 

		$_todayTime1 = strtotime($_todayDate)-30*24*60*60;
	    $endDate = date('Y-m-d', $_todayTime1); 
	
		
        $collection = Mage::getModel('sales/order')->getCollection();
		$collection->addAttributeToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId());
        $collection->addAttributeToFilter('status',array('pending','canceled','pending_payment','holded'));
	//	$collection->addAttributeToFilter('created_at', array('gteq' =>$endDate));
    	//$collection->addAttributeToFilter('created_at', array('lteq' => $startDate));
		$collection->addAttributeToSort('created_at', 'desc');
		
		//echo $collection->getSelect()->__toString();exit;

        $this->setOrders($collection);
		Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('Pending Orders'));
		
    }

    protected function _prepareLayout()
    {
		
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales.order.pending.pager')
            ->setCollection($this->getOrders());
        $this->setChild('pager', $pager);
        $this->getOrders()->load();
        
		return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($order)
    {
        return $this->getUrl('*/*/view', array('order_id' => $order->getId()));
    }

   
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
    
   
}

<?php

class Ess_M2ePro_Block_Adminhtml_Orders_View_Logs extends Mage_Adminhtml_Block_Widget
{
    protected $_orderModel = null;

    public function __construct($attributes)
    {
        parent::__construct($attributes);
        $this->setTemplate('M2ePro/orders/logs.phtml');

        if (isset($attributes['order'])) {
            $this->_orderModel = $attributes['order'];
        }
    }

    /**
     * Return items from logs collection connected to current view order
     * When order model not loaded, empty array returned
     *
     * @return array()
     */
    public function getLogsList()
    {
        if ($this->_orderModel == null || !$this->_orderModel->getId()) {
            return array();
        }

        $collection = Mage::getModel('M2ePro/EbayOrdersLogs')->getCollection()->addFieldToFilter('order_id', $this->_orderModel->getId());
        $collection->setOrder('create_date', 'DESC');
        $collection->setOrder('id', 'DESC');

        return $collection->getItems();
    }
}
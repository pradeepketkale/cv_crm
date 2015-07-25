<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebaySalesEventsGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('created_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/EbayOrders')->getCollection();

        $collection->getSelect()
                   ->joinLeft(
                       array('ma' => Mage::getResourceModel('M2ePro/Accounts')->getMainTable()),
                       '(ma.id = `main_table`.account_id)',
                       array('account_mode' => 'mode'))
                   ->joinLeft(
                       array('so' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
                       '(so.entity_id = `main_table`.magento_order_id)',
                       array('magento_order_num' => 'increment_id'));
        
        // Add Filter By Account
        //------------------------------
        if ($accountId = $this->getRequest()->getParam('account')) {
            $collection->addFieldToFilter('account_id', $accountId);
        }
        //------------------------------

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('created_date', array(
            'header' => Mage::helper('M2ePro')->__('Sale Date'),
            'align'  => 'left',
            'type'   => 'datetime',
            'index'  => 'created_date',
            'width'  => '170px'
        ));

        $this->addColumn('magento_order_num', array(
            'header' => Mage::helper('M2ePro')->__('Magento Order #'),
            'align'  => 'left',
            'index'  => 'so.increment_id',
            'width'  => '110px',
            'frame_callback' => array($this, 'callbackColumnMagentoOrder')
        ));

        $this->addColumn('ebay_order_id', array(
            'header' => Mage::helper('M2ePro')->__('eBay Order #'),
            'align'  => 'left',
            'width'  => '110px',
            'index'  => 'ebay_order_id',
            'frame_callback' => array($this, 'callbackColumnEbayOrder')
        ));

        $this->addColumn('ebay_order_items', array(
            'header' => Mage::helper('M2ePro')->__('Items'),
            'align'  => 'left',
            'index'  => 'ebay_order_items',
            'sortable' => false,
            'width'  => '*',
            'frame_callback' => array($this, 'callbackColumnItems'),
            'filter_condition_callback' => array($this, 'callbackFilterItems')
        ));

        $this->addColumn('buyer', array(
            'header' => Mage::helper('M2ePro')->__('Buyer'),
            'align'  => 'left',
            'index'  => 'buyer_userid',
            'frame_callback' => array($this, 'callbackColumnBuyer'),
            'filter_condition_callback' => array($this, 'callbackFilterBuyer'),
            'width'  => '120px'
        ));

        $this->addColumn('amount_paid', array(
            'header' => Mage::helper('M2ePro')->__('Total'),
            'align'  => 'left',
            'width'  => '110px',
            'index'  => 'amount_paid',
            'type'   => 'number',
            'frame_callback' => array($this, 'callbackColumnTotal')
        ));

        $this->addColumn('checkout_status', array(
            'header' => Mage::helper('M2ePro')->__('Checkout'),
            'align'  => 'left',
            'width'  => '50px',
            'index'  => 'checkout_status',
            'type'   => 'options',
            'options' => array(
                0 => Mage::helper('M2ePro')->__('No'),
                1 => Mage::helper('M2ePro')->__('Yes')
            )
        ));

        $this->addColumn('payment_status_m2e_code', array(
            'header' => Mage::helper('M2ePro')->__('Paid'),
            'align'  => 'left',
            'width'  => '50px',
            'index'  => 'payment_status_m2e_code',
            'type'   => 'options',
            'options' => array(
                0 => Mage::helper('M2ePro')->__('No'),
                1 => Mage::helper('M2ePro')->__('Yes')
            ),
            'frame_callback' => array($this, 'callbackColumnPayment'),
            'filter_condition_callback' => array($this, 'callbackFilterPaymentCondition')
        ));

        $this->addColumn('shipping_status', array(
            'header' => Mage::helper('M2ePro')->__('Shipped'),
            'align'  => 'left',
            'width'  => '50px',
            'index'  => 'shipping_status',
            'type'   => 'options',
            'options' => array(
                0 => Mage::helper('M2ePro')->__('No'),
                1 => Mage::helper('M2ePro')->__('Yes')
            ),
            'frame_callback' => array($this, 'callbackColumnShipping'),
            'filter_condition_callback' => array($this, 'callbackFilterShippingCondition')
        ));

        $this->addColumn('action', array(
            'header'  => Mage::helper('M2ePro')->__('Action'),
            'width'   => '80px',
            'type'    => 'action',
            'getter'  => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('M2ePro')->__('View'),
                    'url'     => array('base' => '*/*/view'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Create Order'),
                    'url'     => array('base' => '*/*/createOrder'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Create Invoice'),
                    'url'     => array('base' => '*/*/doShipPaid/action/paid'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Create Shipment'),
                    'url'     => array('base' => '*/*/doShipPaid/action/ship'),
                    'field'   => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true
        ));

        return parent::_prepareColumns();
    }

    //##############################################################

    public function callbackColumnMagentoOrder($value, $row, $column, $isExport)
    {
        $magentoOrderId = $row['magento_order_id'];

        if (!$magentoOrderId) {
            $returnString = Mage::helper('M2ePro')->__('N/A');
        } else {
            $orderUrl = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view/order_id/' . $magentoOrderId, null);

            $returnString = '<a href="' . $orderUrl . '" target="_blank">' . $row['magento_order_num'] . '</a>';
        }

        return $returnString.$this->getViewLogIconHtml($row->getId());
    }

    private function getViewLogIconHtml($orderId)
    {
        // Prepare collection
        // --------------------------------
        $orderLogsCollection = Mage::getModel('M2ePro/EbayOrdersLogs')->getCollection()
                                                                      ->addFieldToFilter('order_id', (int)$orderId)
                                                                      ->setOrder('id', 'DESC');
        $orderLogsCollection->getSelect()
                            ->limit(3);
        // --------------------------------

        // Prepare logs data
        // --------------------------------
        $orderLogs = $orderLogsCollection->toArray();
        if (!count($orderLogs['items'])) {
            return '';
        }

        $logRows = array();
        foreach ($orderLogs['items'] as $log) {
            $logRows[] = array(
                'type' => $log['code'],
                'text' => Mage::helper('M2ePro')->__($log['message']),
                'date' => Mage::helper('M2ePro')->gmtDateToTimezone($log['create_date'])
            );
        }

        $lastLogRow = $logRows[0];
        // --------------------------------

        // Get log icon
        // --------------------------------
        $icon = 'normal';
        $iconTip = Mage::helper('M2ePro')->__('Last order action was completed successfully.');

        if ($lastLogRow['type'] == Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR) {
            $icon = 'error';
            $iconTip = Mage::helper('M2ePro')->__('Last order action was completed with error(s).');
        }
        if ($lastLogRow['type'] == Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_WARNING) {
            $icon = 'warning';
            $iconTip = Mage::helper('M2ePro')->__('Last order action was completed with warning(s).');
        }

        $iconSrc = $this->getSkinUrl('M2ePro').'/images/log_statuses/'.$icon.'.png';
        // --------------------------------

        $html = '<span style="float:right;">';
        $html .= '<a title="'.$iconTip.'" id="orders_grid_help_icon_open_'.(int)$orderId.'" href="javascript:void(0);" onclick="OrdersHandlersObj.viewItemHelp('.(int)$orderId.',\''.base64_encode(json_encode($logRows)).'\');"><img src="'.$iconSrc.'" /></a>';
        $html .= '<a title="'.$iconTip.'" id="orders_grid_help_icon_close_'.(int)$orderId.'" style="display:none;" href="javascript:void(0);" onclick="OrdersHandlersObj.hideItemHelp('.(int)$orderId.');"><img src="'.$iconSrc.'" /></a>';
        $html .= '</span>';

        return $html;
    }

    //--------------------------------------------------------------

    public function callbackColumnEbayOrder($value, $row, $column, $isExport)
    {
        return str_replace('-', '-<br />', $value);
    }

    public function callbackColumnItems($value, $row, $column, $isExport)
    {
        $originData = $row->getData();

        $orderItems = Mage::getModel('M2ePro/EbayOrdersItems')
                                ->getCollection()
                                ->addFieldToFilter('ebay_order_id', $originData['id'])->getItems();

        $returnString = '';
        foreach ($orderItems as $singleItem) {
            if ($returnString != '') {
                $returnString .= '<br />';
            }

            $url = Mage::helper('M2ePro/Ebay')->getEbayItemUrl($singleItem->getItemId(), $originData['account_mode']);
            $returnString .= '<b>'.Mage::helper('M2ePro')->__('Item').': #</b> <a href="'.$url.'" target="_blank">'.$singleItem->getItemId().'</a><br />';

            $returnString .= Mage::helper('M2ePro')->escapeHtml($singleItem->getItemTitle()) . '<br />';

            $returnString .= '<small>';

            if ($variationInfo = $singleItem->getVariations()) {
                $returnString .= '<span style="padding-left: 10px;"><b>'.Mage::helper('M2ePro')->__('Options').':</b></span><br />';

                foreach ($variationInfo as $optionName => $optionValue) {
                    $returnString .= '<span style="padding-left: 20px; font-style: italic; font-weight: bold;">'.Mage::helper('M2ePro')->escapeHtml($optionName).': </span>';
                    $returnString .= Mage::helper('M2ePro')->escapeHtml($optionValue) . '<br />';
                }
            }

            $returnString .= '<span style="padding-left: 10px;"><b>QTY:</b> '.$singleItem->getQtyPurchased().'</span><br />';
            if (($transactionId = $singleItem->getTransactionId()) != 0) {
                $returnString .= '<span style="padding-left: 10px;"><b>Transaction:</b> '.$transactionId.'</span><br />';
            }

            $returnString .= '</small>';
        }

        return $returnString;
    }

    public function callbackColumnBuyer($value, $row, $column, $isExport)
    {
        return $row->getBuyerInfoHtml();
    }

    public function callbackColumnTotal($value, $row, $column, $isExport)
    {
        $originData = $row->getData();
        return Mage::helper('M2ePro')->convertCurrencyNameToCode($originData['currency'], $originData['amount_paid']);
    }

    public function callbackColumnShipping($value, $row, $column, $isExport)
    {
        if ($row->getData('shipping_status') == Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_COMPLETED) {
            return Mage::helper('M2ePro')->__('Yes');
        } else {
            return Mage::helper('M2ePro')->__('No');
        }
    }

    public function callbackColumnPayment($value, $row, $column, $isExport)
    {
        if ($row->getData('payment_status_m2e_code') == Ess_M2ePro_Helper_Sales::PAYMENT_STATUS_COMPLETED) {
            return Mage::helper('M2ePro')->__('Yes');
        } else {
            return Mage::helper('M2ePro')->__('No');
        }
    }

    //##############################################################

    protected function callbackFilterItems($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $orderItemsCollection = Mage::getModel('M2ePro/EbayOrdersItems')->getCollection();

        $orderItemsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $orderItemsCollection->getSelect()->columns('ebay_order_id');
        $orderItemsCollection->getSelect()->distinct(true);

        $orderItemsCollection->getSelect()->orWhere('item_id LIKE ?', '%'.$value.'%');
        $orderItemsCollection->getSelect()->orWhere('item_title LIKE ?', '%'.$value.'%');
        $orderItemsCollection->getSelect()->orWhere('transaction_id LIKE ?', '%'.$value.'%');

        $totalResult = $orderItemsCollection->getColumnValues('ebay_order_id');
        $collection->addFieldToFilter('`main_table`.id', array('in' => $totalResult));
    }

    protected function callbackFilterBuyer($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->orWhere('buyer_email LIKE ?', '%' . $value . '%');
        $collection->getSelect()->orWhere('buyer_userid LIKE ?', '%' . $value . '%');
        $collection->getSelect()->orWhere('buyer_name LIKE ?', '%' . $value . '%');
    }

    protected function callbackFilterPaymentCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value === null) {
            return;
        }
        if ($value == 1) {
            $this->getCollection()->addFieldToFilter('payment_status_m2e_code', Ess_M2ePro_Helper_Sales::PAYMENT_STATUS_COMPLETED);
        } else {
            $this->getCollection()->addFieldToFilter('payment_status_m2e_code', array('neq' => Ess_M2ePro_Helper_Sales::PAYMENT_STATUS_COMPLETED));
        }
    }

    protected function callbackFilterShippingCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value === null) {
            return;
        }
        if ($value == 1) {
            $this->getCollection()->addFieldToFilter('shipping_status', Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_COMPLETED);
        } else {
            $this->getCollection()->addFieldToFilter('shipping_status', array('neq' => Ess_M2ePro_Helper_Sales::SHIPPING_STATUS_COMPLETED));
        }
    }

    //##############################################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridOrders', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    //##############################################################
}
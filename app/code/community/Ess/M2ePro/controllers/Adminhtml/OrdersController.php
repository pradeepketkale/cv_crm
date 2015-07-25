<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_OrdersController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/sales')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Sales'))
             ->_title(Mage::helper('M2ePro')->__('eBay Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/OrdersHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/sales/ebay_orders');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_orders'));

        $this->renderLayout();
    }

    public function gridOrdersAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_orders_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function viewAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = Mage::getModel('M2ePro/EbayOrders')->load($id);
            if (!$model->getId()) {
                $error = Mage::helper('M2ePro')->__('Order with ID: %id% is not found.');
                $this->_getSession()->addError(str_replace('%id%', $id, $error));
                return $this->_redirect('*/*/');
            }
            $this->_initAction();
            // $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $orderViewBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view', null, array('model' => $model));

            $this->_addContent($orderViewBlock);
            $this->renderLayout();

        } else {
            $error = Mage::helper('M2ePro')->__('Order with ID: %id% is not found.');
            $this->_getSession()->addError(str_replace('%id%', $id, $error));
            return $this->_redirect('*/*/');
        }
    }

    //#############################################

    public function doShipPaidAction()
    {
        $id = $this->getRequest()->getParam('id');
        $action = $this->getRequest()->getParam('action');

        if ($action != 'ship' && $action != 'paid') {
            $action = 'ship';
        }

        $eBayOrderInfo = Mage::getModel('M2ePro/EbayOrders')->load($id);
        if (!$eBayOrderInfo->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('M2ePro')->__('Order is not found.'));
            $this->_redirect('*/*/view/id/' . $id);
            return;
        }

        switch ($action) {
            case 'paid':
                $result = $eBayOrderInfo->markEbaySalesPaid();
                break;
            case 'ship':
                $result = $eBayOrderInfo->markEbaySalesShipped();
                break;
            default:
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('M2ePro')->__('Unknown action on order.'));
                $this->_redirect('*/*/view/id/' . $id);
                return;
        }

        // Show success and errors
        foreach ($result['messages'] as $message) {
           Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('M2ePro')->__($message));
        }

        foreach ($result['errors'] as $error) {
           Mage::getSingleton('adminhtml/session')->addError(Mage::helper('M2ePro')->__($error));
        }

        $this->_redirect('*/*/view/id/' . $id);
    }

    /**
     * Manual Create Order based on information from transaction table
     *
     * @return
     */
    public function createOrderAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/EbayOrders')->load($id);
        
        if (!$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('M2ePro')->__('Order is not found.'));
            $this->_redirect('*/*/view/id/' . $id);
            return;
        }

        if ($model->getMagentoOrderId() > 0) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('M2ePro')->__('Magento order is already created for this eBay order.'));
            $this->_redirect('*/*/view/id/' . $id);
            return;
        }

        $eBayOrderInfoAsArray = $model->convertToInfoArray();

        $createOrderResult = Mage::getModel('M2ePro/EbayOrders')->createMagentoOrder($eBayOrderInfoAsArray, (int)$eBayOrderInfoAsArray['account_id']);

        // Order success created
        if (isset($createOrderResult['messages'])) {
            foreach ($createOrderResult['messages'] as $message) {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('M2ePro')->__($message));
            }
        }

        if (isset($createOrderResult['errors'])) {
            foreach ($createOrderResult['errors'] as $error) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('M2ePro')->__($error));
            }
        }

        if ($createOrderResult['success']) {
            $model->setData('magento_order_id', $createOrderResult['id'])->save();
        }

        $this->_redirect('*/*/view/id/'.$id);
    }

    //#############################################
}
<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Invoice
{
    //####################################

    /**
     * Observer calling when created invoice for order.
     * Work only for order imported from eBay Sale
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function salesOrderInvoicePay(Varien_Event_Observer $observer)
    {
        try {

            if (Mage::registry("m2epro_skip_invoice_observer") != null) {
                // Not process invoice observer when set such flag
                Mage::unregister("m2epro_skip_invoice_observer");
                return;
            }

            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();
            $orderId = $order['entity_id'];

            if ($orderId === null) {
                return;
            }

            $collection = Mage::getModel('M2ePro/EbayOrders')->getCollection()->addFieldToFilter("magento_order_id", $orderId);
            if ($collection->getSize() < 1) {
                return;
            }

            // first item from collection
            $loadedEbayOrder = $collection->getFirstItem();
            if (!$loadedEbayOrder || !$loadedEbayOrder->getId()) {
                return;
            }

            $result = $loadedEbayOrder->markEbaySalesPaid();

            foreach ($result['messages'] as $message) {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('M2ePro')->__($message));
            }

            foreach ($result['errors'] as $error) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('M2ePro')->__($error));
            }

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return;
        }
    }

    //####################################
}
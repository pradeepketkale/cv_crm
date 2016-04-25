<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('view', 'index');

    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Orders'), $this->__('Orders'));
        return $this;
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    /**
     * Orders grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'));

        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Order grid
     */
    public function gridAction()
    {
        //$this->loadLayout(false);
        //$this->renderLayout();
		$this->loadLayout();
		$this->getResponse()->setBody(
		$this->getLayout()->createBlock('adminhtml/sales_order_grid')->toHtml());
    }

    /**
     * View order detale
     */
    public function viewAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'));

        if ($order = $this->_initOrder()) {
            $this->_initAction();

            $this->_title(sprintf("#%s", $order->getRealOrderId()));

            $this->renderLayout();
        }
    }

    /**
     * Notify user
     */
    public function emailAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->sendNewOrderEmail();
                $this->_getSession()->addSuccess($this->__('The order email has been sent.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Failed to send the order email.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }
    /**
     * Cancel order
     */
    public function cancelAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->cancel()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('The order has been cancelled.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Hold order
     */
    public function holdAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->hold()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('The order has been put on hold.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order was not put on hold.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Unhold order
     */
    public function unholdAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->unhold()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('The order has been released from holding status.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order was not unheld.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Manage payment state
     *
     * Either denies or approves a payment that is in "review" state
     */
    public function reviewPaymentAction()
    {
        try {
            if (!$order = $this->_initOrder()) {
                return;
            }
            $action = $this->getRequest()->getParam('action', '');
            switch ($action) {
                case 'accept':
                    $order->getPayment()->accept();
                    $message = $this->__('The payment has been accepted.');
                    break;
                case 'deny':
                    $order->getPayment()->deny();
                    $message = $this->__('The payment has been denied.');
                    break;
                case 'update':
                    $order->getPayment()
                        ->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_UPDATE, true);
                    $message = $this->__('Payment update has been made.');
                    break;
                default:
                    throw new Exception(sprintf('Action "%s" is not supported.', $action));
            }
            $order->save();
            $this->_getSession()->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Failed to update the payment.'));
            Mage::logException($e);
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     * Add order comment action
     */
    public function addCommentAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $response = false;
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
				
				//getting username
				 //$session = Mage::getSingleton('admin/session');
                //$username = $session->getUser()->getUsername();
                //$append = " [name](by ".$username.")[/name]";

				  //appending username with markup to comment		
                $order->addStatusHistoryComment($data['comment'].$append, $data['status'])
                    ->setIsVisibleOnFront($visible)
                    ->setIsCustomerNotified($notify);

                $comment = trim(strip_tags($data['comment']));

                $order->save();
                $order->sendOrderUpdateEmail($notify, $comment);

                $this->loadLayout('empty');
                $this->renderLayout();
            }
            catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot add order history.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }

    /**
     * Generate invoices grid for ajax request
     */
    public function invoicesAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_invoices')->toHtml()
        );
    }

    /**
     * Generate shipments grid for ajax request
     */
    public function shipmentsAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_shipments')->toHtml()
        );
    }

    /**
     * Generate creditmemos grid for ajax request
     */
    public function creditmemosAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_creditmemos')->toHtml()
        );
    }

    /**
     * Generate order history for ajax request
     */
    public function commentsHistoryAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_history')->toHtml()
        );
    }

    /**
     * Cancel selected orders
     */
    public function massCancelAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canCancel()) {
                $order->cancel()
                    ->save();
                $countCancelOrder++;
            } else {
                $countNonCancelOrder++;
            }
        }
        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->_getSession()->addError($this->__('%s order(s) cannot be canceled', $countNonCancelOrder));
            } else {
                $this->_getSession()->addError($this->__('The order(s) cannot be canceled'));
            }
        }
        if ($countCancelOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been canceled.', $countCancelOrder));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Hold selected orders
     */
    public function massHoldAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countHoldOrder = 0;
        $countNonHoldOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canHold()) {
                $order->hold()
                    ->save();
                $countHoldOrder++;
            } else {
                $countNonHoldOrder++;
            }
        }
        if ($countNonHoldOrder) {
            if ($countHoldOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not put on hold.', $countNonHoldOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were put on hold.'));
            }
        }
        if ($countHoldOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been put on hold.', $countHoldOrder));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Unhold selected orders
     */
    public function massUnholdAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countUnholdOrder = 0;
        $countNonUnholdOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canUnhold()) {
                $order->unhold()
                    ->save();
                $countUnholdOrder++;
            } else {
                $countNonUnholdOrder++;
            }
        }
        if ($countNonUnholdOrder) {
            if ($countUnholdOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not released from holding status.', $countNonUnholdOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were released from holding status.'));
            }
        }
        if ($countUnholdOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been released from holding status.', $countUnholdOrder));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Change status for selected orders
     */
    public function massStatusAction()
    {

    }

    /**
     * Print documents for selected orders
     */
    public function massPrintAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $document = $this->getRequest()->getPost('document');
    }

    /**
     * Print invoices for selected orders
     */
    public function pdfinvoicesAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($invoices->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Print shipments for selected orders
     */
    public function pdfshipmentsAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($shipments->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Print creditmemos for selected orders
     */
    public function pdfcreditmemosAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'creditmemo'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Print all documents for selected orders
     */
    public function pdfdocsAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($invoices->getSize()){
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }

                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($shipments->getSize()){
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }

                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'docs'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                    $pdf->render(), 'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Atempt to void the order payment
     */
    public function voidPaymentAction()
    {
        if (!$order = $this->_initOrder()) {
            return;
        }
        try {
            $order->getPayment()->void(
                new Varien_Object() // workaround for backwards compatibility
            );
            $order->save();
            $this->_getSession()->addSuccess($this->__('The payment has been voided.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Failed to void the payment.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/view', array('order_id' => $order->getId()));
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'hold':
                $aclResource = 'sales/order/actions/hold';
                break;
            case 'unhold':
                $aclResource = 'sales/order/actions/unhold';
                break;
            case 'email':
                $aclResource = 'sales/order/actions/email';
                break;
            case 'cancel':
                $aclResource = 'sales/order/actions/cancel';
                break;
            case 'view':
                $aclResource = 'sales/order/actions/view';
                break;
            case 'addcomment':
                $aclResource = 'sales/order/actions/comment';
                break;
            case 'creditmemos':
                $aclResource = 'sales/order/actions/creditmemo';
                break;
            case 'reviewpayment':
                $aclResource = 'sales/order/actions/review_payment';
                break;
             
            default:
                $aclResource = 'sales/order';
                break;

        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';
        $grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /**
     * Order transactions grid ajax action
     *
     */
    public function transactionsAction()
    {
        $this->_initOrder();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Edit order address form
     */
    public function addressAction()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $address = Mage::getModel('sales/order_address')
            ->getCollection()
            ->getItemById($addressId);
        if ($address) {
            Mage::register('order_address', $address);
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/');
        }
    }
    
    public function paymentmethodAction()
    {
        $paymentId = $this->getRequest()->getParam('payment_id');
        $payment = Mage::getModel('sales/order_payment')
            ->load($paymentId);
        if ($payment) {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/');
        }
    }
    
    public function paymentmethodsaveAction()
    {
        $payMethod = $this->getRequest()->getParam('paymethod');
        $paymentId = $this->getRequest()->getParam('payid');
        $txtValue = $this->getRequest()->getParam('txtval');
        $payment = Mage::getModel('sales/order_payment')->load($paymentId);
        $payment
        ->setMethod($payMethod)
        ->setPoNumber($txtValue) 
        ->save();
        echo "saved!";
    }
    
    /**
     * Save order address
     */
    public function addressSaveAction()
    {
        $addressId  = $this->getRequest()->getParam('address_id');
        $address    = Mage::getModel('sales/order_address')->load($addressId);
        $data       = $this->getRequest()->getPost();
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $address->implodeStreetAddress()
                    ->save();
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The order address has been updated.'));
                $this->_redirect('*/*/view', array('order_id'=>$address->getParentId()));
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('sales')->__('An error occurred while updating the order address. The address has not been changed.')
                );
            }
            $this->_redirect('*/*/address', array('address_id'=>$address->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }
	
	public function UndoAction() {
        $flag = true;
        $pName = "";
        if ($order = $this->_initOrder()) {
            try {
                foreach ($order->getItemsCollection() as $item) {
                    if ($item->getQtyCanceled() > 0) {
                        $itemId = $item->getProductId();
                        $productQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($itemId)->getQty();
                        if ($productQty > 0) {
                            //do nothiing
                        } else {
                            $productType = Mage::getModel('cataloginventory/stock_item')->loadByProduct($itemId)->getTypeId();
                            if ($productType == "simple") {
                                $flag = false;
                                $prName = Mage::getModel('catalog/product')->load($itemId)->getName();
                                $pName .="<br/>" . $prName;
                            }
                        }
                    }
                }


                if ($flag == true) {

                    foreach ($order->getItemsCollection() as $item) {
                        if ($item->getQtyCanceled() > 0) {
                            $item->setQtyCanceled(0)->save();
                        }
                    }

                    $order
                            ->setBaseDiscountCanceled(0)
                            ->setBaseShippingCanceled(0)
                            ->setBaseSubtotalCanceled(0)
                            ->setBaseTaxCanceled(0)
                            ->setBaseTotalCanceled(0)
                            ->setDiscountCanceled(0)
                            ->setShippingCanceled(0)
                            ->setSubtotalCanceled(0)
                            ->setTaxCanceled(0)
                            ->setTotalCanceled(0);

                    $state = 'new';
                    $status = 'pending';

                    $order
                            ->setStatus($status)
                            ->setState($state)
                            ->save();

                    $this->_getSession()->addSuccess(
                            $this->__('Order was successfully uncancelled.')
                    );
                } else {
                    $errMessage = "Order can not be restored due to unavailibility of following items.\n" . $pName . "</br> kindly generate fresh order";
                    $this->_getSession()->addError($this->__($errMessage));
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Order was not uncancelled.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }
	
	public function rip_tags($string) {

    // ----- remove HTML TAGs -----
    $string = preg_replace ('/<[^>]*>/', ' ', $string);

    // ----- remove control characters -----
    $string = str_replace("\r", '', $string);    // --- replace with empty space
    $string = str_replace("\n", ' ', $string);   // --- replace with space
    $string = str_replace("\t", ' ', $string);   // --- replace with space
        $string = str_replace("*", ' ', $string);   // --- replace with space
        $string = str_replace("&", ' ', $string);   // --- replace with space
        $string = str_replace("#", ' ', $string);   // --- replace with space
        $string = str_replace("%", ' ', $string);   // --- replace with space
        $string = str_replace("$", ' ', $string);   // --- replace with space
        $string = str_replace("@", ' ', $string);   // --- replace with space
        $string = str_replace(".com", ' ', $string);   // --- replace with space
        $string = str_replace("?", ' ', $string);   // --- replace with space
        $string = str_replace("=", ' ', $string);   // --- replace with space
        $string = str_replace("-", ' ', $string);   // --- replace with space
        $string = str_replace("+", ' ', $string);   // --- replace with space
        $string = str_replace(",", ' ', $string);   // --- replace with space
        $string = str_replace("!", ' ', $string);   // --- replace with space
        $string = str_replace("\"", ' ', $string);   // --- replace with space
        $string = str_replace("%", ' ', $string);   // --- replace with space
        $string = str_replace("^", ' ', $string);   // --- replace with space
        $string = str_replace("'", ' ', $string);   // --- replace with space
        $string = str_replace("/", ' ', $string);   // --- replace with space
        $string = str_replace("_", ' ', $string);   // --- replace with space
        $string = str_replace("~", ' ', $string);   // --- replace with space
        //$string = str_replace("\", ' ', $string);   // --- replace with space
        //$string = str_replace("\{", ' ', $string);   // --- replace with space
        //$string = str_replace("\}", ' ', $string);   // --- replace with space
        //$string = str_replace("\]", ' ', $string);   // --- replace with space
        //$string = str_replace("\[", ' ', $string);   // --- replace with space
        //$string = str_replace("|", ' ', $string);   // --- replace with space
        $string = str_replace("nbsp", ' ', $string);   // --- replace with space

    // ----- remove multiple spaces -----
    $string = trim(preg_replace('/ {2,}/', ' ', $string));

    return $string;
}	

// Below Functions added By dileswar and Manoj sir..on dated 25-02-2013 & 28-02-2013 for get EBslink and payment status of ebs Automantically	
	public function sendEbslinkAction()
			{
	
			$orderIds = $this->getRequest()->getPost('order_ids');
			foreach($orderIds as $_orderId){
					$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
					$_orderData = $read->query("SELECT * FROM `sales_flat_order` as sfo
											LEFT JOIN `sales_flat_order_address` as sfoa 
											ON sfo.`entity_id` = sfoa.`parent_id`
											LEFT JOIN `ebslink` as es
											ON sfo.`increment_id` = es.`order_no`
											WHERE sfo.`entity_id` = '".$_orderId."' and sfoa.`address_type` = 'billing'")->fetchAll();
					/*echo '<pre>';
					print_r($_orderData);*/
					
					$namecust = $_orderData[0]['customer_firstname'];
					$email = $_orderData[0]['customer_email'];
					$currencyTotal = Mage::app()->getLocale()->currency($_orderData[0]['order_currency_code'])->getSymbol();
					$currency = $_orderData[0]['order_currency_code'];
					$grandtotal = $_orderData[0]['grand_total'];
					//$ebslinkurl =  $_orderData[0]['ebslinkurl'];
					$entityid = $_orderData[0]['entity_id'];
					$incrementid = $_orderData[0]['increment_id'];
					$_customerTelephone = $_orderData[0]['telephone'];
					$address1 = $_orderData[0]['street'];
					$address = $this->rip_tags($address1);
					$city = $_orderData[0]['city'];
					$region = $_orderData[0]['region'];
					$postcode1 = '000000'.$_orderData[0]['postcode'];
					$postcode = substr($postcode1,strlen($postcode1)-6,6);
					$country_id = $_orderData[0]['country_id'];
					$total_qt_ordered = $_orderData[0]['total_qty_ordered'];
					$_grandTotal = $grandtotal/$total_qt_ordered;
					$expiry_days = 30;
		//GET ebslinkurlllll
				$url = 'https://secure.ebs.in/api/invoice';
				$myvar1 = 'create';
				$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
				$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
				$myvar4 = $incrementid;
				$myvar5 = $currency;
				$myvar6 = $namecust;
				$myvar7 = $address;
				$myvar8 = $city;
				$myvar9 = $region;
				$myvar10 = $postcode;
				$myvar11 = 'IND';
				$myvar12 = $email;
				$myvar13 = $_customerTelephone;
				$myvar14 = 'Craftsvilla Products';
				$myvar15 = $total_qt_ordered;
				$myvar16 = $_grandTotal;
				$myvar17 = '0';
				$myvar18 = $expiry_days;
				
				
				$fields = array(
								'action' => urlencode($myvar1),
								'account_id' => urlencode($myvar2),
								'secret_key' => urlencode($myvar3),
								'reference_no' => urlencode($myvar4),
								'currency' => urlencode($myvar5),
								'name' => urlencode($myvar6),
								'address' => urlencode($myvar7),
								'city' => urlencode($myvar8),
								'state' => urlencode($myvar9),
								'postal_code' => urlencode($myvar10),
								'country' => urlencode($myvar11),
								'email' => urlencode($myvar12),
								'phone' => urlencode($myvar13),
								'products[0][name]' => urlencode($myvar14),
								'products[0][qty]' => urlencode($myvar15),
								'products[0][price]' => urlencode($myvar16),
								'payment_mode' => urlencode($myvar17),
								'expiry_in_days' => urlencode($myvar18)
								
								);
				$fields_string = '';
				//url-ify the data for the POST
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
				/*$j = 0;
				while ($j < $totalItems) { 
					$fields_string .= 'products['.$j.'][name]'.'='.$products[$j]['name'].'&';
					$fields_string .= 'products['.$j.'][qty]'.'='.$products[$j]['qty'].'&';
					$fields_string .= 'products['.$j.'][price]'.'='.$products[$j]['price'].'&';
					$j++;
					}*/
				
				rtrim($fields_string, '&');
				
				//$url = 'https://secure.ebs.in/api/1_0';
				//$url = 'http://www.craftsvilla.com';
				
				//$myvars = 'Action=getCurrencyValue' . '&AccountID=' . $myvar2 . '&SecretKey='.$myvar3 . '&Currency='.$myvar4;
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_VERBOSE, 1); 
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt( $ch, CURLOPT_HEADER, 0);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				
				
				$response = curl_exec( $ch );
				if(curl_errno($ch))
				{		
				print curl_error($ch);
				}
				else
				{
				//print_r($response);exit;
				//echo $response;exit;
				
				$xml = @simplexml_load_string($response);
				/*echo '<pre>';
				print_r($xml);exit;*/
				$ebslinkurl = (string)$xml->invoice[0]->payment_url;
				$ebslinkinvoiceId = (string)$xml->invoice[0]->invoice_id;
				//$ebslinkurl = 'http://craftsvilla.com';
				$readEbslink = $read->query("select * from `ebslink` WHERE `order_no` = '".$incrementid."'")->fetch();
				
				if($readEbslink) {
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ebslink Email & SMS Already Sent! '));
					}
				else
					{
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$updateEbslink = $write->query("INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`,`order_id`,`ref_no`) VALUES ('".$incrementid."', '".$ebslinkurl."',NOW(),'".$_orderId."','".$ebslinkinvoiceId."')");
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ebslink Email & SMS Sent Successfully! '));
				}
				}
				curl_close($ch);
				Mage::getModel('ebslink/ebslink')->sendebslink($incrementid);	
				}
		  $this->_redirect('*/*/');	
		}
	
	public function checkEbspaymentAction(){
		$orderIds = $this->getRequest()->getPost('order_ids');
			foreach($orderIds as $_orderId){
				$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
				$orderquery = $read->query("SELECT `increment_id` FROM `sales_flat_order` WHERE `entity_id` = '".$_orderId."'")->fetchAll();
				$url = 'https://api.secure.ebs.in/api/1_0';
				
				$myvar1 = 'statusByRef';
				$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
				$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
				$myvar4 = $orderquery[0]['increment_id'];
				
				
				$fields = array(
							'Action' => urlencode($myvar1),
							'AccountID' => urlencode($myvar2),
							'SecretKey' => urlencode($myvar3),
							'RefNo' => urlencode($myvar4)
						);
				$fields_string = '';
				//url-ify the data for the POST
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
				rtrim($fields_string, '&');
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_VERBOSE, 1); 
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt( $ch, CURLOPT_HEADER, 0);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				$response = curl_exec( $ch );
				if(curl_errno($ch))
					{		
						print curl_error($ch);
					}
				else
					{
				$xml = @simplexml_load_string($response);
				
				
				$myvar8 = $xml['transactionType'];
				$myvar9 = $xml['isFlagged'];
					if ($myvar8 == 'Authorized')
						{
							if($myvar9 == 'NO')
								{
								Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("Payment of this Order :-".$myvar4." is Authorised and Not Flagged"));
								}
							else
								{
								Mage::getSingleton('adminhtml/session')->addSucess(Mage::helper('adminhtml')->__("Payment of this Order :-".$myvar4." is Authorised and is Flagged"));
								}	
							}
					
						elseif($myvar8 == 'Captured')
								{
									Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("Payment of this Order :-".$myvar4." is Authorised and Captured"));
								}
					else
						{
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("This Order Payment is not Authorized :-".$myvar4));
						}	
				}
				curl_close($ch);
				}
		$this->_redirect('*/*/');
		}

	public function captureEbspaymentAction()
		{
			$orderIds = $this->getRequest()->getPost('order_ids');
			$order = Mage::getModel('sales/order')->load($orderIds);
			//if($order->getStatus() == 'processing' || $order->getStatus() == 'closed'|| $order->getStatus() == 'complete')
			//{
				$url = 'https://secure.ebs.in/api/1_0';
				$myvar1 = 'statusByRef';
				$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
				$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
				$myvar4 = $order->getIncrementId();//100075504;
				$myvar5 = 'capture';
			
				$fields = array(
						'Action' => urlencode($myvar1),
						'AccountID' => urlencode($myvar2),
						'SecretKey' => urlencode($myvar3),
						'RefNo' => urlencode($myvar4)
						);
				$fields_string = '';
				//url-ify the data for the POST
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
				rtrim($fields_string, '&');
			
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_VERBOSE, 1); 
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt( $ch, CURLOPT_HEADER, 0);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
				$response = curl_exec( $ch );
				
				if(curl_errno($ch))
					{		
					print curl_error($ch);
					}
				else
					{
						$xml = @simplexml_load_string($response);
						$myvar6 = $xml['amount'];
						$myvar7 = $xml['paymentId'];
						$myvar8 = $xml['transactionType'];
						$myvar9 = $xml['isFlagged'];
				
						if($myvar8 == 'Authorized')
								{
								if($myvar9 == 'NO')
									{		
									$fields1 = array(
												'Action' => urlencode($myvar5),
												'AccountID' => urlencode($myvar2),
												'SecretKey' => urlencode($myvar3),
												'Amount' => urlencode($myvar6),
												'PaymentID' =>urlencode($myvar7)
													);
									$fields_string1 = '';
									//url-ify the data for the POST
									foreach($fields1 as $key=>$value) { $fields_string1 .= $key.'='.$value.'&'; }
									rtrim($fields_string1, '&');
								
									$ch1 = curl_init();
									curl_setopt($ch1, CURLOPT_VERBOSE, 1); 
									curl_setopt($ch1,CURLOPT_URL, $url);
									curl_setopt($ch1,CURLOPT_POSTFIELDS, $fields_string1);
									curl_setopt( $ch1, CURLOPT_POST, 1);
									curl_setopt( $ch1, CURLOPT_FOLLOWLOCATION, 1);
									curl_setopt( $ch1, CURLOPT_HEADER, 0);
									curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
									curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
								
									$response1 = curl_exec( $ch1 );
									if(curl_errno($ch1))
										{		
											print curl_error($ch1);
										}
									else
										{
								 		$xml1 = @simplexml_load_string($response1);
								
										/*echo '<pre>';
										print_r($xml1);
										exit;*/
											// Below code Added by dileswar on dated 30-032013 for throw the error....	
												if($xml1['errorCode'] == '13')
													{
														Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("This Payment For this Order Already Captured:-".$myvar4));
													}
												else
													{
														Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Captured :-".$myvar4));	
													}
										}
									curl_close($ch1);
									
									}
							
								else
									{
									Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Flagged :-".$myvar4));
									}
								}
							else
								{
									Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("This Order Payment is not Authorized :-".$myvar4));
								}
					}
			curl_close($ch);
			/*}
			else
				{
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("Order is on some other status"));
				}*/
			$this->_redirect('*/*/');
		}
	//To convert NUmbers in words Added By Dileswar
	
		
		
	public function invInternationalAction()
		{
//Function For Convert Number To words....Started			
			function convert_number_to_words($number) {
					$hyphen      = '-';
					$conjunction = ' and ';
					$separator   = ', ';
					$negative    = 'negative ';
					$decimal     = ' point ';
					$dictionary  = array(
					0                   => 'zero',
					1                   => 'one',
					2                   => 'two',
					3                   => 'three',
					4                   => 'four',
					5                   => 'five',
					6                   => 'six',
					7                   => 'seven',
					8                   => 'eight',
					9                   => 'nine',
					10                  => 'ten',
					11                  => 'eleven',
					12                  => 'twelve',
					13                  => 'thirteen',
					14                  => 'fourteen',
					15                  => 'fifteen',
					16                  => 'sixteen',
					17                  => 'seventeen',
					18                  => 'eighteen',
					19                  => 'nineteen',
					20                  => 'twenty',
					30                  => 'thirty',
					40                  => 'fourty',
					50                  => 'fifty',
					60                  => 'sixty',
					70                  => 'seventy',
					80                  => 'eighty',
					90                  => 'ninety',
					100                 => 'hundred',
					1000                => 'thousand',
					1000000             => 'million',
					1000000000          => 'billion',
					1000000000000       => 'trillion',
					1000000000000000    => 'quadrillion',
					1000000000000000000 => 'quintillion'
					);
					
					if (!is_numeric($number)) {
					return false;
					}
					
					if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
					// overflow
					trigger_error(
						'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
						E_USER_WARNING
					);
					return false;
					}
					
					if ($number < 0) {
					return $negative . convert_number_to_words(abs($number));
					}
					
					$string = $fraction = null;
					
					if (strpos($number, '.') !== false) {
					list($number, $fraction) = explode('.', $number);
					}
					
					switch (true) {
					case $number < 21:
						$string = $dictionary[$number];
						break;
					case $number < 100:
						$tens   = ((int) ($number / 10)) * 10;
						$units  = $number % 10;
						$string = $dictionary[$tens];
						if ($units) {
							$string .= $hyphen . $dictionary[$units];
						}
						break;
					case $number < 1000:
						$hundreds  = $number / 100;
						$remainder = $number % 100;
						$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
						if ($remainder) {
							$string .= $conjunction . convert_number_to_words($remainder);
						}
						break;
					default:
						$baseUnit = pow(1000, floor(log($number, 1000)));
						$numBaseUnits = (int) ($number / $baseUnit);
						$remainder = $number % $baseUnit;
						$string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
						if ($remainder) {
							$string .= $remainder < 100 ? $conjunction : $separator;
							$string .= convert_number_to_words($remainder);
						}
						break;
					}
					
					if (null !== $fraction && is_numeric($fraction)) {
					$string .= $decimal;
					$words = array();
					foreach (str_split((string) $fraction) as $number) {
						$words[] = $dictionary[$number];
					}
					$string .= implode(' ', $words);
					}
					return $string;
					
					}
					
///////////////////////////////////Function For Convert Number To words....Ended///////////////////
// Code for Invoice..For International Format.....
		$_pageHtml = '';
		$orderIds = $this->getRequest()->getPost('order_ids');
		$grossweight = $this->getRequest()->getParam('grossweight');
		$descriptiongood = $this->getRequest()->getParam('goodsdescription');
		$orderqty = $this->getRequest()->getParam('qty');
		$unitprice = $this->getRequest()->getParam('unitprice');
		$goods = explode(",",$descriptiongood);
		
		$order = Mage::getModel('sales/order')->load($orderIds);
		echo $_pageHtml ="<table border='1'>
<tr><td height='120px' width='500px'><font size='2'>Sender: <br>Craftsvilla Handicrafts Pvt.Ltd.,<br> Craftsvilla.com,<br>19 CENTRIUM MALL, FIRST FLOOR, <br>LOKHANDWALA, KANDIVALI EAST<br><br>MUMBAI<bR>IN<br>India 400101<br><br>Phone: 9892676399</font></td><td colspan='8'  height='120px' align='center' style='background-color:#A4A4A4;font-size:30'>Performa Invoice</td></tr>";
	   $items = $order->getAllItems();
		$_totalData = $order->getData();
        
		// convert_number_to_words('123');
		$_grand = $_totalData['base_grand_total'];
	//	echo $_pageHtml = "<tr><td rowspan='3'>Receiver: </td><td colspan='7'>Date".date('Y-m-d')."</td></tr><tr><td colspan='8'>Invoice Number: </td></tr><tr><td colspan='8'>Shipment Reference: ".$_totalData['increment_id']."</td></tr>";
		//echo $_pageHtml = "<td colspan='2'><table border='1' width='100%'><tr><td>".date('Y-m-d')."</td></tr></table><table border='1' width='100%'><tr><td>".$_totalData['increment_id']."</td></tr></table>";
        
		//echo $_pageHtml = "<tr><td></td><td>Exporter's Ref No.</td><td>".$_totalData['increment_id']."</td><td>&nbsp;</td></tr>";
		$custName = $order->getShippingAddress()->getName();
	    $custStreet =	$order->getShippingAddress()->getStreet();
		$custLand = $custStreet[0].'<br/>'.$custStreet[1];
		$custCity =	$order->getShippingAddress()->getCity();
		$custTele =	$order->getShippingAddress()->getTelephone();
		$custPostCode =$order->getShippingAddress()->getPostcode();
		$country_id = $order->getShippingAddress()->getCountryId();
		$countryModel = Mage::getModel('directory/country')->loadByCode($country_id);
		$countryName = $countryModel->getName();
		$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
    						->setOrderFilter($order)
    						->load();
		$readQp = Mage::getSingleton('core/resource')->getConnection('core_read');
		$qtysum = "SELECT SUM(`total_qty`) FROM `sales_flat_shipment` WHERE `order_id` = '".$orderIds[0]."'";
		$resultqtysum = $readQp->query($qtysum)->fetch();
		
		foreach ($resultqtysum as $_resultqtysum)
		{
		    $totalqty = $_resultqtysum['[SUM(`total_qty`)]'];
		}
		$awbnumber = Mage::getModel('dhlinternational/dhlinternational')->getCollection()->addFieldToFilter('order_id', $_totalData['increment_id']);
		foreach ($awbnumber as $_awbnumber){
		 $trackingawb = $_awbnumber['tracking_awb'];
		}				
		//echo $_pageHtml = "<table border='1' width='100%'><tr><td>";
		foreach ($shipmentCollection as $shipment){
    			//echo $shipment->getIncrementId().'/';
				
				}
		//echo $_pageHtml = "</td></tr></table><table border='1' width='100%'><tr><td>&nbsp;</td></tr></table><table border='1' width='100%'><tr><td>&nbsp;</td></tr></table></td></tr>";		
		echo $_pageHtml = "<tr><td rowspan='3' height='184'><font size='2'>Receiver: <br>".$custName."<br>".$custLand."<br>".$custCity."<br>".$custPostCode."<br>".$countryName."<br><br>Phone: ".$custTele."</td><td colspan='7' height='61'><font size='2'>Date: ".date('Y-m-d')."</font></td></tr><tr><td colspan='8' height='61'><font size='2'>Invoice Number: </font></td></tr><tr><td colspan='8' height='62'><font size='2'>Shipment Reference: ".$_totalData['increment_id']."</font></td></tr>";
		echo $_pageHtml = "<tr><td><font size='2'>Exporter Id : DUNS Number </font></td><td colspan='8'><font size='2'>Exporter Code: </font></td></tr><tr><td rowspan='2' height='120'></td><td colspan='8' height='60'><font size='2'>Other Remarks: </font></td></tr><tr><td colspan='8'  height='60'><font size='2'>Waybill Number: ".$trackingawb."</font></td></tr>";
		echo $_pageHtml = "<tr><td><font size='2'>Full Description of Goods</font></td><td width='50'><font size='2'>Qty</font></td><td><font size='2'>Community Code</font></td><td><font size='2'>Unit Value</font></td><td><font size='2'>Subtotal Value</font></td><td><font size='2'>Gross Weight</font></td><td><font size='2'>Country of Origin</font></td></tr>";
	//	echo $_pageHtml = "<td colspan='2'><table border='1' width='100%'><tr><td>".$countryName."</td></tr></table><table border='1' width='100%'><tr><td>Place Of Receipt</td></tr></table><table border='1' width='100%'><tr><td>Pre Carrier</td></tr></table><table border='1' width='100%'><tr><td>&nbsp;</td></tr></table><table border='1' width='100%'><tr><td>Port Of Destination</td></tr></table></td></tr>";
	//	echo $_pageHtml = "<tr><td></td><td>Mumbai</td><td colspan='2'>".$countryName."</td></tr><tr><td colspan='4'>The Consignment has no commercial value<br>Value declared here are only for customs purpose</td></tr><tr><td>Description Of Goods</td><td>Quantity</td><td>Value</td><td>Amount</td></tr>";
		$itemcount=count($items);
		$unitvalue = $unitprice/$orderqty;
	  echo $_pageHtml ="<tr><td height='50'><font size='2'>".$goods[0]."<br><br>".$goods[1]."<br><br>".$goods[2]."</font></td>";
	 echo $_pageHtml =" <td height='50' rowspan='2'><font size='2'>".round($orderqty,0)." Pieces</font></td>
        				 <td height='50' rowspan='2'>&nbsp;</td>
						 <td height='50' rowspan='2'><font size='2'>".number_format($unitvalue,2)."</font></td>
						 <td height='50' rowspan='2'><font size='2'>".number_format($unitprice,2)."</font></td>
						 
						 <td height='50' rowspan='2'><font size='2'>".$grossweight."</font></td>
						 <td height='50' align='left' rowspan='2'><font size='2'>India</font></td></tr>";
    //    	echo $_pageHtml = "</table><br>";			 
    
			/*echo $sObject2->Item_name__c = $item->getName();
			echo $sObject2->Unit_price__c = $item->getPrice();
			echo $sObject2->Sku__c = $item->getSku();
			echo $sObject2->Qty__c = $item->getQtyOrdered();
			*/
			//echo '<pre>'.$sObject2->Quantity__c = $item->getQtyToInvoice();
   //	}
		
	//	echo $_pageHtml ="<tr><td>&nbsp;</td></tr>";
	//	echo $_pageHtml = "<tr><td>Total</td><td>".round($_totalData['total_qty_ordered'])."<br></td><td>&nbsp;</td><td>".number_format($_grand,2)."</td></tr><tr><td colspan='4'>Amount in Words:".ucwords(convert_number_to_words(round($_grand)))." Rupees only</td></tr>";	
	////	echo $_pageHtml = "<tr><td>Declaration:	<br>We declare that this invoice shows the actual price of the goods	<br>described and that all particulars are true and correct.</td><td colspan='3'>For Kribha Handicrafts<br><br>Authorized Signatory	</td></tr>";
		echo $_pageHtml = "</table>";
		echo $_pageHtml = "<table>
<tr><td width='20%'><font size='2'>Total Declared Value: &nbsp;</font></td><td><font size='2'>".number_format($unitprice,2)."</font></td><td width='20%'><font size='2'>Total Net Weight:  </font></td><td></td></tr>
<tr><td width='20%'><font size='2'>Total Line Items: &nbsp;</font></td><td><font size='2'>".number_format($orderqty,0)."</font></td><td width='20%'><font size='2'>Total Gross Weight: &nbsp;</font></td><td><font size='2'>".$grossweight."</font></td></tr>
<tr><td width='20%'><font size='2'>Payer of GST/VAT: </font></td><td></td><td width='20%'><font size='2'>Currency Code: &nbsp;</font></td><td><font size='2'>INR</font></td></tr>
<tr><td width='20%'><font size='2'>Harm.Comm.Code: </font></td><td></td><td width='20%'><font size='2'>Terms Of Payment: </font></td><td></td></tr>
<tr><td width='20%'><font size='2'>Invoice Type: &nbsp;</font></td><td><font size='2'>PRO </font></td><td width='20%'><font size='2'>Terms of Trade: &nbsp;</font></td><td><font size='2'>CIP </font></td></tr>
<tr><td width='20%'><font size='2'>Reason for Export: &nbsp;</font></td><td><font size='2'>Permanent</font></td><td></td></tr>
<tr><td width='20%'><font size='2'>Other Charges: </font></td><td></td><td></td></tr>
</font></table>";
		echo $_pageHtml = "<br>
<font size='2'>I/We hereby certify that the information on this invoice is true and correct and that the contents of this shipment are as stated above.<br>
SIGNATURE:</font>";
		exit;
		//$this->_redirect('*/*/');total_qty_ordered
		}
		
public function suspectfraudAction()
	{
		$user = Mage::getSingleton('admin/session');
		$userEmail = $user->getUser()->getEmail();
        $userName = $user->getUser()->getName();
	if($userEmail == "surekhak@craftsvilla.com" || $userEmail == "rajm@craftsvilla.com" || $userEmail == "swapnil@craftsvilla.com" || $userEmail == "pritit@craftsvilla.com" || $userEmail == "anilp@craftsvilla.com" || $userEmail == "jitesh@craftsvilla.com" || $userEmail == "santoshc@craftsvilla.com" || $userEmail == "bhimraj@craftsvilla.com" || $userEmail == "Seemag@craftsvilla.com" || $userEmail == "monica@craftsvilla.com" || $userEmail == "manoj@craftsvilla.com" || $userEmail == "Rohit@craftsvilla.com" || $userEmail == "tribhuvan@craftsvilla.com" || $userEmail == "dilipcscare@craftsvilla.com" || $userEmail == "eulalia.fernandes@craftsvilla.com" || $userEmail == "gaurav@craftsvilla.com" || $userEmail == "niraj@craftsvilla.com")
		{
		$orderIds = $this->getRequest()->getPost('order_ids');
		$order = Mage::getModel('sales/order')->load($orderIds); //load order             
		//echo '<pre>';print_r($order);exit;
		$entityIdSus = $order->getEntityId();
        $createdAt=now();
        $amount=$order->getBaseGrandTotal();
		    if($entityIdSus == ''){
		        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("Please select only suspected fraud Order"));
					$this->_redirectUrl('/index.php/kribhasanvi/sales_order/index/');
		    }

		$incrementId = $order->getIncrementId();
		$custemail = $order->getCustomerEmail();
		$state = 'processing';
		$status = $state;
		$comment = "AWRP, status changes to $status Status ";
		$isCustomerNotified = false; //whether customer to be notified
		$order->setState($state, $status, $comment, $isCustomerNotified);    
		$order->save();
		$order->sendNewOrderEmail();
        
        $statsconn=Mage::getSingleton('core/resource')->getConnection('core_write');
        $insertAgentProcessingOrdersCv="INSERT INTO agent_processing_orders_cv (`order_id`,`agent_name`,`created_at`,`amount`,`payment_method`) VALUES('".$incrementId."','".$userName."','".$createdAt."','".$amount."','".$payment_method."');";
        $resAgentProcessingOrdersCv= $statsconn->query($insertAgentProcessingOrdersCv);
        $statsconn->closeConnection();

		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("This Order No:- ".$incrementId." Status has changes to processing.."));
		$this->_redirectUrl('/index.php/kribhasanvi/sales_order/index/');
		}
	else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("You are not authorised to do this action, Please contact technical team ! "));
			$this->_redirectUrl('/index.php/kribhasanvi/sales_order/index/');
		}		
		//$this->_redirect('*/sales_order/view');
		

	}
	
	public function sentpayuInvoiceAction()
	{
	 require_once(Mage::getBaseDir('lib').'/payu/payu.php');

//$plp = Mage::helper('payucheckout');
			$orderIds = $this->getRequest()->getPost('order_ids');
			foreach($orderIds as $_orderId){
					$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$_orderData = $read->query("SELECT * FROM `sales_flat_order` as sfo
											LEFT JOIN `sales_flat_order_address` as sfoa 
											ON sfo.`entity_id` = sfoa.`parent_id`
											LEFT JOIN `ebslink` as es
											ON sfo.`increment_id` = es.`order_no`
											WHERE sfo.`entity_id` = '".$_orderId."' and sfoa.`address_type` = 'billing'")->fetchAll();
					/*echo '<pre>';
					print_r($_orderData);*/
					
					$namecust = $_orderData[0]['customer_firstname'];
					$email = $_orderData[0]['customer_email'];
					$currencyTotal = Mage::app()->getLocale()->currency($_orderData[0]['order_currency_code'])->getSymbol();
					$currency = $_orderData[0]['order_currency_code'];
					$grandtotal = $_orderData[0]['base_grand_total'];
					//$ebslinkurl =  $_orderData[0]['ebslinkurl'];
					$entityid = $_orderData[0]['entity_id'];
					$incrementid = $_orderData[0]['increment_id'];
					$_customerTelephone = str_replace('/','',substr($_orderData[0]['telephone'],0,10));
					$address1 = $_orderData[0]['street'];
					$address = $this->rip_tags($address1);
					$city = $_orderData[0]['city'];
					$region = $_orderData[0]['region'];
					$postcode = $_orderData[0]['postcode'];
					$country_id = $_orderData[0]['country_id'];
					$total_qt_ordered = $_orderData[0]['total_qty_ordered'];
					$_grandTotal = $grandtotal/$total_qt_ordered;
					$expiry_days = 30;
		//GET payUlinkurlllll
				
				$myvar1 = 'create';
				//$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
				//$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
				$myvar4 = $incrementid;
				$myvar5 = $currency;
				$myvar6 = $namecust;
				$myvar7 = $address;
				$myvar8 = $city;
				$myvar9 = $region;
				$myvar10 = $postcode;
				$myvar11 = 'INDIA';
				$myvar12 = $email;
				$myvar13 = $_customerTelephone;
				$myvar14 = 'Craftsvilla Products';
				$myvar15 = $total_qt_ordered;
				$myvar16 = $grandtotal;
				$myvar17 = '0';
				$payuref = $myvar4.'P';
					
				//$myvar18 = $expiry_days;
				$key =	Mage::getStoreConfig('payment/payucheckout_shared/key');
				$salt =	Mage::getStoreConfig('payment/payucheckout_shared/salt');
				$debug_mode =	Mage::getStoreConfig('payment/payucheckout_shared/debug_mode');


			$valueparam = pay_page( array (	'key' => $key, 'txnid' => $payuref, 'amount' => round($myvar16,2),
			'firstname' => $myvar6, 'email' => $myvar12, 'phone' => $myvar13,
			'productinfo' => $myvar14, 'surl' => 'payment_success', 'furl' => 'payment_failure'),  $salt);
			// Merchant key here as provided by Payu

			$payulinkurl = $valueparam['data'];

			$readEbslink = $read->query("select * from `ebslink` WHERE `order_no` = '".$incrementid."'")->fetch();
			if($readEbslink) {
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ebslink Email & SMS Already Sent! '));
			}
			else
			{
				$write = Mage::getSingleton('core/resource')->getConnection('core_write');
				$updatepayulink = $write->query("INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`,`order_id`,`ref_no`) VALUES ('".$incrementid."', '".$payulinkurl."',NOW(),'".$_orderId."','".$payuref."')");
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Payu link Email & SMS Sent Successfully! '));
			}
Mage::getModel('ebslink/ebslink')->sendebslink($incrementid);

}
		  $this->_redirect('*/*/');	
	}
	
	public function lowerShipingPriceAction(){
		$newShippingAmount = $this->getRequest()->getParam('newshipping_amount');	
		
			$orderIds = $this->getRequest()->getPost('order_ids');
			foreach($orderIds as $_orderId){
					$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
					
					$_orderData = $read->query("SELECT * FROM `sales_flat_order` as sfo
											LEFT JOIN `sales_flat_order_address` as sfoa 
											ON sfo.`entity_id` = sfoa.`parent_id`
											LEFT JOIN `ebslink` as es
											ON sfo.`increment_id` = es.`order_no`
											WHERE sfo.`entity_id` = '".$_orderId."' and sfoa.`address_type` = 'billing'")->fetchAll();
					/*echo '<pre>';
					print_r($_orderData);*/
					
					$namecust = $_orderData[0]['customer_firstname'];	
					$email = $_orderData[0]['customer_email'];
					$currencyTotal = Mage::app()->getLocale()->currency($_orderData[0]['order_currency_code'])->getSymbol();
					$currency = $_orderData[0]['order_currency_code'];
					$grandtotal = $_orderData[0]['base_grand_total'];
					$newgrandTotal = $grandtotal - $newShippingAmount;
					//$ebslinkurl =  $_orderData[0]['ebslinkurl'];
					$entityid = $_orderData[0]['entity_id'];
					$incrementid1 = $_orderData[0]['increment_id'];
					$incrementid = $_orderData[0]['increment_id'].'-R4';//for new revised invoice added -R
					$_customerTelephone = str_replace('/','',substr($_orderData[0]['telephone'],0,10));
					$address1 = $_orderData[0]['street'];
					$address = $this->rip_tags($address1);
					$city = $_orderData[0]['city'];
					$region = $_orderData[0]['region'];
					$postcode1 = '000000'.$_orderData[0]['postcode'];
					$postcode = substr($postcode1,strlen($postcode1)-6,6);
					$country_id = $_orderData[0]['country_id'];
					$total_qt_ordered = $_orderData[0]['total_qty_ordered'];
					$_grandTotalOld = $grandtotal/$total_qt_ordered;
					$_grandTotal = $newgrandTotal/$total_qt_ordered;
					$expiry_days = 30;
		//GET ebslinkurlllll
				$url = 'https://secure.ebs.in/api/invoice';
				$myvar1 = 'create';
				$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
				$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
				$myvar4 = $incrementid;
				$myvar5 = $currency;
				$myvar6 = $namecust;
				$myvar7 = $address;
				$myvar8 = $city;
				$myvar9 = $region;
				$myvar10 = $postcode;
				$myvar11 = 'IND';
				$myvar12 = $email;
				$myvar13 = $_customerTelephone;
				$myvar14 = 'Craftsvilla Products';
				$myvar15 = $total_qt_ordered;
				$myvar16 = $_grandTotal;
				$myvar17 = '0';
				$myvar18 = $expiry_days;
				
				
				$fields = array(
								'action' => urlencode($myvar1),
								'account_id' => urlencode($myvar2),
								'secret_key' => urlencode($myvar3),
								'reference_no' => urlencode($myvar4),
								'currency' => urlencode($myvar5),
								'name' => urlencode($myvar6),
								'address' => urlencode($myvar7),
								'city' => urlencode($myvar8),
								'state' => urlencode($myvar9),
								'postal_code' => urlencode($myvar10),
								'country' => urlencode($myvar11),
								'email' => urlencode($myvar12),
								'phone' => urlencode($myvar13),
								'products[0][name]' => urlencode($myvar14),
								'products[0][qty]' => urlencode($myvar15),
								'products[0][price]' => urlencode($myvar16),
								'payment_mode' => urlencode($myvar17),
								'expiry_in_days' => urlencode($myvar18)
								
								);
				$fields_string = '';
				//url-ify the data for the POST
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
				/*$j = 0;
				while ($j < $totalItems) { 
					$fields_string .= 'products['.$j.'][name]'.'='.$products[$j]['name'].'&';
					$fields_string .= 'products['.$j.'][qty]'.'='.$products[$j]['qty'].'&';
					$fields_string .= 'products['.$j.'][price]'.'='.$products[$j]['price'].'&';
					$j++;
					}*/
				
				rtrim($fields_string, '&');
				
				//$url = 'https://secure.ebs.in/api/1_0';
				//$url = 'http://www.craftsvilla.com';
				
				//$myvars = 'Action=getCurrencyValue' . '&AccountID=' . $myvar2 . '&SecretKey='.$myvar3 . '&Currency='.$myvar4;
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_VERBOSE, 1); 
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt( $ch, CURLOPT_HEADER, 0);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				
				
				$response = curl_exec( $ch );
				if(curl_errno($ch))
				{		
				print curl_error($ch);
				}
				else
				{
				//print_r($response);exit;
				//echo $response;exit;
				
				$xml = @simplexml_load_string($response);
				//echo '<pre>';
				//print_r($xml);exit;
				$ebslinkurl = (string)$xml->invoice[0]->payment_url;
				$ebslinkinvoiceId = (string)$xml->invoice[0]->invoice_id;
				//$ebslinkurl = 'http://craftsvilla.com';
				$write = Mage::getSingleton('core/resource')->getConnection('core_write');
				
				$readEbslink = $read->query("select * from `ebslink` WHERE `order_no` = '".$incrementid."'")->fetch();
				$comment = "Revised Shipping Amount: Rs.".$newShippingAmount." And Revised Payment value: Rs. ".$newgrandTotal." And Created New Ebslink Link For New GrandTotal";
					
				if(!$readEbslink) 
					{
						
					$updateEbslink1 = $write->query("UPDATE `ebslink` SET `order_no`='".$incrementid1."',`ebslinkurl`='".$ebslinkurl."',`created_time`=NOW(),`order_id`='".$_orderId."' WHERE `order_no` = '".$incrementid1."'");
					$updateEbslink = $write->query("INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`,`order_id`,`ref_no`) VALUES ('".$incrementid."', '".$ebslinkurl."',NOW(),'".$_orderId."','".$ebslinkinvoiceId."')");
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ebslink Revised Email & SMS Sent Successfully! '));
					Mage::getModel('sales/order_status_history')
						->setParentId($_orderId)
						->setStatus($status)
						->setComment($comment)
						->setCreatedAt(NOW())
						->save();
					}
					
				}
				
				curl_close($ch);
				Mage::getModel('ebslink/ebslink')->sendreviewebslink($incrementid1,$newgrandTotal,$ebslinkurl,$newShippingAmount);	
				}
				
		  $this->_redirect('*/*/');	
		
		}
		
public function massPrintorderAction()
	{
		//$orderIds = $this->getRequest()->getPost('order_ids', array());
	 $orderIds = $this->getRequest()->getPost('order_ids');
        if(!is_array($orderIds)) {
        	
            $this->_getSession()->addError($this->__('Please select order(s)'));
        } else {
            try 
            {
				 foreach ($orderIds as $orderId) {
                   $this->loadLayout();
				  $block = $this->getLayout()->createBlock('adminhtml/sales_order_print1')
				  ->setData('order_ids', $orderId);
				  $this->getResponse()->setBody($block->toHtml());
                }
                    $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully printed', count($orderIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }

	}
	// $this->_redirect('*/*/index');
    }
    
    public function getawbAction()
	{
		$id = $this->getRequest()->getParam('order_ids');
		$this->createawb($id);
			$this->_redirect('*/*/');
	} 
	
protected function createawb($id)
	{
		
		$order = Mage::getModel('sales/order')->load($id);
		$session = $this->_getSession();
        $msg = $this->_getSession()->getMessages(true); 
        $this->getLayout()->getMessagesBlock()->addMessages($msg);
        $this->_initLayoutMessages('core/session'); 
        $declaredvalue = $this->getRequest()->getParam('unitprice');
		//$model = Mage::getModel('dhlinternational/dhlinternational');
		$xmlObj = Mage::helper('dhlinternational')->xmlRequest1($declaredvalue,$order->getIncrementId(), 'shipmentvalidation');
			/*$response = $xmlObj->getResponse();
			
			$xmlResponse = simplexml_load_string($response);
			$model = Mage::getModel('dhlinternational/dhlinternational')->getCollection()
									->addFieldToFilter('order_id', $order->getIncrementId());
		
		
		
			if($model->count() == 0)
				{
					
					$modelload = Mage::getModel('dhlinternational/dhlinternational')
											     									->setOrderId($order->getIncrementId())
												     							    ->setStatusAwb($response)
																				    ->setTrackingAwb($xmlResponse->AirwayBillNumber)
																				    ->save();
	           $session->addSuccess('AWB Number ia successfully created for the shipment number: '.$xmlResponse->Reference->ReferenceID);
                return true;
				}
			else
				{
					$session->addError('AWB Number is already created for the shipment number: '.$xmlResponse->Reference->ReferenceID);
				}  
				
		*/
		

	}
    
	public function editshippingaddressAction(){

		$changeFirstName = mysql_escape_string($this->getRequest()->getParam('firstname'));
		$changelastName = mysql_escape_string($this->getRequest()->getParam('lastname'));
		$changestreet = mysql_escape_string($this->getRequest()->getParam('street'));
		$changemobile = mysql_escape_string($this->getRequest()->getParam('mobile'));
		$changezip = mysql_escape_string($this->getRequest()->getParam('zip'));
		$changecity = mysql_escape_string($this->getRequest()->getParam('city'));
		$changeCountry = $this->getRequest()->getParam('country');
		$changestate1 =mysql_escape_string($this->getRequest()->getParam('state'));
		$regionModel = Mage::getModel('directory/region')->loadByName(ucfirst($changestate1), $changeCountry);
		$changeRegionid = $regionModel->getId();
		$changestate = mysql_escape_string($regionModel->getName());
		$orderIds = $this->getRequest()->getPost('order_ids');

		foreach($orderIds as $orderId)
		{
			$orderData = Mage::getModel("sales/order")->load($orderId); //load order by order id
			$incrementId = $orderData->getIncrementId();
			$shipping_address = $orderData->getShippingAddress();
			$getfirstname = $shipping_address->getFirstname();
			$getlastname = $shipping_address->getLastname();
			$getStreet = $shipping_address['street'];
			$getTelephone = $shipping_address->getTelephone();
			$getPostcode = $shipping_address->getPostcode();
			$getCity = $shipping_address->getCity();
			$getCountryId = $shipping_address->getCountryId();
			$getRegionId = $shipping_address->getRegionId();
			$getRegion = $shipping_address->getRegion();
		}

		if($changeFirstName){ $firstnameup = $changeFirstName; } else { $firstnameup = $getfirstname; }	
		if($changelastName){ $lastnameup = $changelastName; } else { $lastnameup = $getlastname; }	
		if($changestreet){ $changestreetup = $changestreet; } else { $lastnameup = $getStreet; }
		if($changemobile){ $changemobileup = $changemobile; } else { $changemobileup = $getTelephone; }	
		if($changezip){ $changezipup = $changezip; } else { $changezipup = $getPostcode; }
		if($changecity){ $changecityup = $changecity; } else { $changecityup = $getCity; }
		if($changeCountry){ $changeCountryup = $changeCountry; } else { $changeCountryup = $getCountryId; }
		if($changestate1){ $changestateup = $changestate1; } else { $changestateup = $getRegion; }
		if($changeRegionid){ $changeRegionidup = $changeRegionid; } else { $changeRegionidup = $getRegionId; }

$writeUp = Mage::getSingleton('core/resource')->getConnection('core_write');
		
$updatedAddQuery = "UPDATE `sales_flat_order_address` SET `region_id`='".$changeRegionidup."',`region`='".$changestateup."',
`postcode`='".$changezipup."',`lastname`='".$lastnameup."',`street`='".$changestreetup."',`city`='".$changecityup."',`telephone`='".$changemobileup."',`country_id`='".$changeCountryup."',`firstname`='".$firstnameup."' WHERE `parent_id` = '".$orderId."' AND `address_type`='shipping'";
$writeUp->query($updatedAddQuery);

Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Address updated Successfully for order: '.$incrementId));
$this->_redirect('*/*/');	
}


public function changePaymentMethodAction()
	{
		$user = Mage::getSingleton('admin/session');
		$userEmail = $user->getUser()->getEmail();
	if($userEmail == "surekhak@craftsvilla.com" || $userEmail == "rajm@craftsvilla.com" || $userEmail == "swapnil@craftsvilla.com" || $userEmail == "pritit@craftsvilla.com" || $userEmail == "anilp@craftsvilla.com" || $userEmail == "jitesh@craftsvilla.com" || $userEmail == "santoshc@craftsvilla.com" || $userEmail == "bhimraj@craftsvilla.com" || $userEmail == "Seemag@craftsvilla.com" || $userEmail == "monica@craftsvilla.com" || $userEmail == "manoj@craftsvilla.com" || $userEmail == "Rohit@craftsvilla.com" || $userEmail == "tribhuvan@craftsvilla.com" || $userEmail == "dilipcscare@craftsvilla.com" || $userEmail == "eulalia.fernandes@craftsvilla.com" || $userEmail == "gaurav@craftsvilla.com" || $userEmail == "niraj@craftsvilla.com")
		{
		$orderIds = $this->getRequest()->getPost('order_ids');
		$order = Mage::getModel('sales/order')->load($orderIds); //load order  
		$payment = $order->getPayment();
		
  
//echo '<pre>';print_r($order);exit;
		$entityIdSus = $order->getEntityId();
		$incrementId = $order->getIncrementId();
		$custemail = $order->getCustomerEmail();
		$state = 'processing';
		$status = $state;
		$comment = "AWRP, status changes to  to $status Status ";
		$isCustomerNotified = false; //whether customer to be notified
			if($entityIdSus != '')
			{
			$order->setState($state, $status, $comment, $isCustomerNotified);    
			$payment->setMethod('purchaseorder');
			$payment->save();         		
			$order->save();
			$order->sendNewOrderEmail();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("This Order No:- ".$incrementId." Status has changes to processing. and Payment Method EBS-B"));
			
			
		}
			else
			{
			
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("Please select only suspected fraud Order"));
			}
		}
	else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("You are not authorised to do this action, Please contact technical team ! "));
		}		
		//$this->_redirect('*/sales_order/view');
		$this->_redirect('*/*/');
	}
public function checkcouponAction(){
		echo $couponcodetocheck = $this->getRequest()->getParam('coupon_code');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');		
		$getQuery = "SELECT * FROM `sales_flat_order` WHERE `coupon_code` LIKE  '%".$couponcodetocheck."%'";
		$resultodcode =	$read->query($getQuery)->fetch();
		$getStatus = $resultodcode['status'];
		$getIncrement = $resultodcode['increment_id'];
		if($resultodcode)
		{
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("Coupon Code ".$couponcodetocheck." Has Been Used In Order No:- ".$getIncrement." And Order Status Is ".strtoupper($getStatus).""));
		}
		else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("Coupon Code ".$couponcodetocheck." Not Used In Any Order"));
			}		
$this->_redirect('*/*/');
		}


//----------------------------------------Orderviewlite function added by chetan on 20/03/2015-----------------------------
public function viewuliteAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $orderdata = Mage::getModel('sales/order')->load($id);


        $shipments = $orderdata->getShipmentsCollection();
        $shipmentIncrementIdA = array();
        $entityIdA = array();

        foreach ($shipments as $_shipment){
                $shipmentIncrementIdA[] = $_shipment->getIncrementId();
                $entityIdA[] = $_shipment->getId();

        }

        $shipmentLinkHtml = "";
        $i = 0;
        $shp = array();
        $j = 0;
        $shipment123 = array();
        foreach($shipmentIncrementIdA as $_shipmentId)
        {
        $shipment_url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order_shipment/view',array('shipment_id' => $entityIdA[$i++]));
        $shp = Mage::getModel("sales/order_shipment")->load(array('entity_id' => $entityIdA[$j++]));
        $shipmentStatus = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
        $shipmentLinkHtml .= '<a href="'.$shipment_url.'" target="_blank">'.$_shipmentId.'</a><strong> STATUS : </strong><font color=#ff0000>'.$shipmentStatus[$shp['udropship_status']].'</font><br>';

        }


        $shipping_address = $orderdata->getShippingAddress();


echo   '<font size=5><b>OrderUltraLite</b></font>
<div style="padding:5px;">
        <div class="entry-edit" style="border:2px solid black; width:48%;float : left;background-color:#FBFAF6;">
                <div class="entry-edit-head">
                        <h4 class="icon-head head-account"style="color:#EB5E00;background-color: antiquewhite;
                        ">Order # '.$orderdata['increment_id'].' (the order confirmation email was sent)</h4>
                </div>
                <div class="fieldset">
                        <table cellspacing="0" class="form-list">
                                <tr>
                                        <td class="label"><label><strong>Order Date</strong></label></td>
  <td class="value">'.date("M d,Y H:i A",strtotime($orderdata['created_at'])).'</td>
                                </tr>
                                <tr>
                                        <td class="label"><strong><label>Order Status</strong></label></td>
                                        <td class="value"><span id="order_status">'.$orderdata['status'].' </span></td>
                                </tr>


                        </table><br><br>
                </div>
        </div>



        <div class="entry-edit" style="border:2px solid black; width:48%;float : left;background-color:#FBFAF6;">
                <div class="entry-edit-head">
                        <h4 class="icon-head head-account" style="color:#EB5E00;background-color: antiquewhite;
                        ">Account Information</h4>
                </div>
                <div class="fieldset">
                        <table cellspacing="0" class="form-list">
                                <tr>
                                        <td class="label"><label><strong>Customer Name </strong></label></td>
                                        <td class="value">'.$orderdata['customer_firstname'].' '.$orderdata['customer_middlename'].' '.$orderdata['customer_lastname'].'</td>
                                </tr>
                                <tr>
                                        <td class="label"><label><strong>Email</strong></label></td>
                                        <td class="value"><span id="order_status">'.$orderdata['customer_email'].' </span></td>
                                </tr>


                        </table><br><br>
                </div>
        </div>


        <div class="entry-edit" style="border:2px solid black; width:48%;float :left;background-color:#FBFAF6;">
                <div class="entry-edit-head">
                        <h4 class="icon-head head-account" style="color:#EB5E00;background-color: antiquewhite;
                        ">Order Totals</h4>
                </div>
                <div class="fieldset">
                        <table cellspacing="0" class="form-list">
                                <tr>
                                        <td class="label"><label><strong>Subtotal</strong></label></td>
                                        <td class="value">'.$orderdata['base_subtotal'].'</td>
                                </tr>
                                <tr>
</tr>
                                <tr>
                                        <td class="label"><strong><label>Shipping & Handling</strong></label></td>
                                        <td class="value"><span id="order_status">'.$orderdata['base_shipping_amount'].' </span></td>
                                </tr>
                                <tr>
                                        <td class="label"><label><strong> Total Paid </strong></label></td>
                                        <td class="value">'.$orderdata['base_total_paid'].' </td>
                                </tr>
                                <tr>
                                        <td class="label"><label><strong>Total Due</strong></label></td>
                                        <td class="value">'.$orderdata['base_grand_total'].'</td>
                                </tr>
                                <tr>
                                        <td class="label"><label><strong>&nbsp;</strong></label></td>
                                        <td class="value">&nbsp;</td>
                                </tr>

                        </table><br><br>
                </div>
        </div>


        <div class="entry-edit" style="border:2px solid black; width:48%; float:left;background-color:#FBFAF6;">
                <div class="entry-edit-head">
                        <h4 class="icon-head head-account" style="color:#EB5E00;background-color: antiquewhite;
                        ">Shipping Address</h4>
                </div>
                <div class="fieldset">
                        <table cellspacing="0" class="form-list">
                                <tr>
                                        <td class="value">'.$orderdata['customer_firstname'].' '.$orderdata['customer_middlename'].' '.$orderdata['customer_lastname'].' </td>
                                </tr>
                                <tr>
                                        <td class="value"><span id="order_status">'.$shipping_address['street'].' </span></td>
                                </tr>
                                <tr>
                                        <td class="value">'.$shipping_address['city'].",".$shipping_address['region'].",".$shipping_address['postcode'].' <br/>Craftsvilla</td>
                                </tr>
                                <tr>
                                        <td class="value">'.$shipping_address['country_id'].' </td>
                                </tr>
                                <tr>
                                        <td class="value"><strong> T :</strong>'.$shipping_address['telephone'].' </td>
</tr>
                        </table><br>
                </div>
        </div>

        <div class="entry-edit" style="border:2px solid black; width:48%; float:left;background-color:#FBFAF6;">
                <div class="entry-edit-head" >
                        <h4 class="icon-head head-account" style="color:#EB5E00;background-color: antiquewhite;
                        ">Shipments</h4>
                </div>

                <div class="fieldset">
                '.$shipmentLinkHtml.'
                </div>
<br><br>
        </div>


</div>


';


    }
 //----------------------------------------------------end ViewUltralite function-------------------------------------------------------


}

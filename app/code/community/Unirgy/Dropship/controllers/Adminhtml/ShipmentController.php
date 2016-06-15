<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Adminhtml_ShipmentController extends Mage_Adminhtml_Controller_Action
{
    public function shipAction()
    {
        $id = $this->getRequest()->getParam('id');
        $shipment = Mage::getModel('sales/order_shipment')->load($id);
        if ($shipment->getId()) {
            try {

                Mage::helper('udropship')->setShipmentComplete($shipment);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Shipment has been marked as complete'));

                // function calls the smsTrack Added By Dileswar on date 07-11-2012
                $this->smsTrack($shipment->getData('order_id'));

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udropship')->__('There was a problem marking this shipment as complete: '.$e->getMessage()));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udropship')->__('Invalid shipment ID supplied'));
        }

        $orderId = $this->getRequest()->getParam('order_id');
        $this->_redirect("adminhtml/sales_order_shipment/view/shipment_id/$id/order_id/$orderId");
    }

    // Function has added for to send SMS track to customer on click Mark as Shipped @ dated 07-11-2012
    protected function smsTrack($_oid){
        $_order = Mage::getModel('sales/order')->load($_oid);
        $_orderShippingCountry = $_order->getShippingAddress()->getCountryId();
        $_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
        $_customerTelephone = $_order->getBillingAddress()->getTelephone();
        $orderId = $_order->getId();
        $shipment = Mage::getModel('sales/order_shipment');

        $_shipmenttrack = Mage::getModel('sales/order_shipment_track');

        $_helpv = Mage::helper('udropship');

        $shippmentCollection = $shipment->getCollection()
                                    ->addAttributeToFilter('order_id', $orderId);
        $_shipmentIncrmentId = 0;
        foreach($shippmentCollection as $_shipment){
            $_shipmentIncrmentId = $_shipment['increment_id'];
        }


        $_shipmenttrackCollection = $_shipmenttrack->getCollection()
                                    ->addAttributeToFilter('order_id', $orderId);
        /*echo "<pre>";
        print_r($_shipmenttrackCollection->getData());exit;    */
        $_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
        $_smsUserName = Mage::getStoreConfig('sms/general/user_name');
        $_smsPassowrd = Mage::getStoreConfig('sms/general/password');
        $_smsSource = Mage::getStoreConfig('sms/general/source');

        foreach($_shipmenttrackCollection as $_shipmenttrack){
            //$vendorIfo = '';
            //$message = '';
            //$vendorIfo = $_helpv->getVendor($_shipment->getUdropshipVendor());
            //$_vendorTelephone = $vendorIfo->getTelephone();
            /*if($_orderShippingCountry!='IN')
                $message = 'Received new shipment: International, #'.$_shipment->getIncrementId().', Rs. '.number_format($_shipment->getBaseTotalValue(), 2, '.', '').'. Please ship immediately to Craftsvilla Warehouse in Mumbai.';
            else
                $message = 'Received new shipment: Domestic, #'.$_shipment->getIncrementId().', Rs. '.number_format($_shipment->getBaseTotalValue(), 2, '.', '').'. Please ship immediately to Customer directly.';

            $_smsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$_vendorTelephone."&source=".$_smsSource."&message=".urlencode($message);
            $parse_url = file($_smsUrl);
*/
            // Send SMS to customer
                if($_orderBillingCountry == 'IN'){
                    $customerMessage = 'Your order has been shipped. Tracking Details: Shipment#: '.$_shipmentIncrmentId.' , Track Number: '.$_shipmenttrack->getNumber().'Courier Name :'.$_shipmenttrack->getCourierName().' - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)
';
                    $_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$_customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
                    $parse_url = file($_customerSmsUrl);
                }
            ///

        }


    }
    protected function _initShipment()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        if (!$shipment->getId()) {
            $this->_getSession()->addError($this->__('This shipment no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('current_shipment', $shipment);

        return $shipment;
    }




    public function addCommentAction()
    {
        /*Start..
        *Craftsvilla Comment
        *Added to get order id
        *Added by Suresh on 04-06-2012
        */
        
        $shipmentId_value = $this->getRequest()->getParam('shipment_id');
        $shipment_collection = Mage::getModel('sales/order_shipment')->load($shipmentId_value);
        $shipment_id_value = $shipment_collection->getIncrementId();
        $reason = $this->getRequest()->getPost('returnRequestRemark');
       
        /*End..
        *Craftsvilla Comment
        *Added to get order id
        *Added by Suresh on 04-06-2012
        */

        try {
            $data = $this->getRequest()->getPost('comment');
        $dataDispute = $this->getRequest()->getPost('disputeremarks');
        $dataRefund = $this->getRequest()->getPost('refundamount');
            $shipment = $this->_initShipment();
            if (empty($data['comment']) && $data['status']==$shipment->getUdropshipStatus()) {
                Mage::throwException($this->__('Comment text field cannot be empty.'));
            }

            //mstart
            if (empty($dataRefund) && $data['status']==23) {
                    Mage::throwException($this->__('Please Enter Refund Amount in Refund To Do Dropdown'));
                }
            if (!is_numeric($dataRefund) && $data['status']==23){
            Mage::throwException($this->__('Only Numeric Value Allow in Refund To Do Dropdown'));
            }
            
            if (empty($dataDispute) && $data['status']==20) {
                    Mage::throwException($this->__('Please Select Reason For Dispute Raise'));
                }
            //mend
            if (empty($reason) && $data['status']==37) {
                Mage::throwException($this->__('Please Select Return Request Reason'));
            }

            $hlp = Mage::helper('udropship');
            $status = $data['status'];

            $statusShipped   = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
            $statusDelivered = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED;
            $statusCanceled  = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;
            //added By dileswar 13-10-2012
            $statusOutofstock = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_OUTOFSTOCK_CRAFTSVILLA ;


            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();


            $statusSaveRes = true;
            if ($status!=$shipment->getUdropshipStatus()) {
                $oldStatus = $shipment->getUdropshipStatus();
                if (($oldStatus==$statusShipped || $oldStatus==$statusDelivered )
                    && $status!=$statusShipped && $status!=$statusDelivered && $hlp->isUdpoActive()
                ) {
                    Mage::helper('udpo')->revertCompleteShipment($shipment, true);
                } elseif ($oldStatus==$statusCanceled && $hlp->isUdpoActive()) {
                    Mage::throwException(Mage::helper('udpo')->__('Canceled shipment cannot be reverted'));
                }
                $changedComment = $this->__("%s\n\n[%s has changed the shipment status to %s]", $data['comment'], 'Administrator', $statuses[$status]);
                $triedToChangeComment = $this->__("%s\n\n[%s tried to change the shipment status to %s]", $data['comment'], 'Administrator', $statuses[$status]);
                if ($status==$statusShipped || $status==$statusDelivered) {
                    $hlp->completeShipment($shipment, true, $status==$statusDelivered);
                    $hlp->completeOrderIfShipped($shipment, true);
                    $hlp->completeUdpoIfShipped($shipment, true);
                    $commentText = $changedComment;
                } elseif ($status == $statusCanceled && $hlp->isUdpoActive()) {
                    if (Mage::helper('udpo')->cancelShipment($shipment, true)) {
                        $commentText = $changedComment;
                        Mage::helper('udpo')->processPoStatusSave(Mage::helper('udpo')->getShipmentPo($shipment), Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL, true, null);
                    } else {
                        $commentText = $triedToChangeComment;
                    }
                } else {
                    $shipment->setUdropshipStatus($status)->save();
                    $commentText = $changedComment;
                }

            //******Below cond. added By Dileswar  for call of below function on line 357 *****//

                    if($status == 12)
                    {
                       $this->adjustmentRefundAmount($shipment_id_value,$shipmentId_value);
                    }
                    if($status == 23)
                    {
                         $this->refundTodo($shipment_id_value,$shipmentId_value);
                    }
                    if($status == 18)
                    {
                        $this->penaltyOutofstock($shipment_id_value);
                    }
                    if($status == 20)
                    {
                        if(isset($_POST['disputeMail']))
                        {
                            $this->sendDisputeRaisedEmailtoCustomer($shipment_id_value);
                        }
                        //mstart
                        if(isset($_POST['disputeremarks']))
                        {
                            $this->disputeCustomerRemarks($shipment_id_value);
                        }
                        //msend
                    }
                     if($status == 7)
                    {
                       $this->deliver($shipment_id_value,$shipmentId_value);
                    }
                     if($status == 37)
                    {
                        
                       //$sendResponse = $this->returnrequested($shipment_id_value,$shipmentId_value,$reason);
                       //if($sendResponse == '1'){
                       // Mage::throwException($this->__('Please try again later'));
                       // return false ;
                       //}
                        $shipmentModel = Mage::getModel('sales/order_shipment');
                        $shipment = $shipmentModel->load($shipmentId_value);
                      
                        $vendorId = $shipment['udropship_vendor'];
                        //print_r($shipment['udropship_vendor']);//echo $vendorId;
                        $requestParams  =   array();
                        $readdb         =   Mage::getSingleton('core/resource')->getConnection('custom_db');
                        $trackSql       =   "SELECT `number` FROM `sales_flat_shipment_track` WHERE `parent_id` = '$shipmentId_value'";
                        $trackInformation   =   $readdb->query($trackSql)->fetch();
                        $readdb->closeConnection();
                        //echo '<pre>';print_r($trackInformation); exit;
                        if(!empty($trackInformation))
                        {
                            $trackNumber                =   $trackInformation['number'];
                            $requestParams['tracking_number']   =   $trackNumber;
                            $jsonInput              =   str_replace('\\/', '/', json_encode($requestParams));
                             
                            #call sendd api if tracking number exists
                            $response               =   $this->senddReverseOrder($jsonInput,$shipmentId_value,$vendorId);
                            //var_dump(count((array)$response)); exit;
                             
                            if($response == NULL)
                            {
                               Mage::throwException($this->__('Please try again later'));
                               return false ;
                            }
                            
                            $awb=   $response->partner_tracking_detail->tracking_number; 
                            $shipping_docs      =   $response->partner_tracking_detail->shipping_docs;
                            $c_company      =   $response->partner_tracking_detail->company;
                            $created_at         =   $response->partner_tracking_detail->tracking_status_updates[0]->created_at;
                            $updated_at         =   $response->partner_tracking_detail->tracking_status_updates[0]->updated_at;
                            $message_status     =   $response->partner_tracking_detail->tracking_status_updates[0]->message;
                            $generalcheck_hlp = Mage::helper('generalcheck');
                            try{
                                $writedb = Mage::getSingleton('core/resource')->getConnection('core_write');
                                $updatereAwb = "INSERT INTO `sales_flat_shipment_reverse_track`(`shipment_id`, `reverse_awb_no`, `reverse_shipping_docs`,`courier_code`,`reverse_reason`,`created_at`,`updated_at`,`message`,`created_by`,`updated_by`) VALUES ('".$shipment_id_value."','".$awb."','".$shipping_docs."','".$c_company."','".$reason."','".$created_at."','".$updated_at."','".$message_status."','1','1') ";
                                //Mage::log("ShipmentReverseTrack: " .$updatereAwb, null, 'system.log', true).
                                $_updatereAwb = $writedb->query($updatereAwb);
                                $writedb->closeConnection();
                                if(strlen($awb)>0) 
                                {
                                    
                                    $dest       =   "/tmp/".$shipment_id_value.".pdf";
                                    copy($shipping_docs, $dest);
                    
                                    $bucketName             ='assets1.craftsvilla.com';
                                    $imageSlipNameTwo       = 'sendd/barcode/'.$vendorId.'/'.$shipment_id_value.'_invoice_reverse_shipment_label.pdf';
                                    $moveToBucketSlipTwo    = $generalcheck_hlp->uploadToS3($dest,$bucketName, $imageSlipNameTwo);
                                    unlink($dest);
                                } 
                               }
                            catch (Exception $e){
                                //$session = $this->_getSession();
                                //$session->addError($this->__($e->getMessage()));
                                Mage::throwException($this->__($e->getMessage()));
                             }
                            $shipment->setUdropshipStatus(37);
                            $courierName = $generalcheck_hlp->getCouriernameFromCourierCode($c_company);
                            Mage::helper('udropship')->addShipmentComment($shipment,('Status has been changed to Return Requested from customer care agent having AWB No:'.$awb. ' And Courier Name:'.$courierName));
                            $shipment->save();
                        }
                    }
                     

                $comment = Mage::getModel('sales/order_shipment_comment')
                    ->setComment($commentText)
                    ->setIsCustomerNotified(isset($data['is_customer_notified']))
                    ->setIsVendorNotified(isset($data['is_vendor_notified']))
                    ->setIsVisibleToVendor(isset($data['is_visible_to_vendor']))
                    ->setUsername(Mage::getSingleton('admin/session')->getUser()->getUsername())
                    ->setUdropshipStatus(@$statuses[$status]);
                $shipment->addComment($comment);


                if (isset($data['is_vendor_notified'])) {
                    Mage::helper('udropship')->sendShipmentCommentNotificationEmail($shipment, $data['comment']);
                    Mage::helper('udropship')->processQueue();
                }

                /*Start..
                *Craftsvilla Comment
                *Added sendmail to customercare@craftsvilla.com when shipment status Changed to 'Shipped To Craftsvilla'
                *Added by Suresh on 2-06-2012
                */
               
                if($status == 15 || $status == 16 || $status == 17 || $status == 18 )
                {
                    //$shipment->sendUpdateEmail('suresh.konda@craftsvilla.com', $data['comment']);
                    Mage::log("Sending email to $sendTo");
                    $mail = Mage::getModel('core/email');

                    if($status == 15)
                    {
                        $mail->setBody('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Shipped To Craftsvilla"');
                        $mail->setSubject('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Shipped To Craftsvilla"');
                    }

                    if($status == 16)
                    {

                        $mail->setBody('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "QC Rejected by Craftsvilla"');
                        $mail->setSubject('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "QC Rejected by Craftsvilla"');
                    }
                   
                    if($status == 17)
                    {
                        $mail->setBody('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Received in Craftsvilla"');
                        $mail->setSubject('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Received in Craftsvilla"');
                    }

                    if($status == 18)
                    {
                        $mail->setBody('Shipment Id:#'.$shipment_id_value.'Shipment status changed to "Product Out Of Stock"');
                        $mail->setSubject('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Product Out Of Stock"');
                    }
                    if($status == 21)
                    {
                        $mail->setBody('Shipment Id:#'.$shipment_id_value.'Shipment status changed to "Shipment Delayed"');
                        $mail->setSubject('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Shipment Delayed"');
                    }
                    


                $mail->setToName('Customercare');
                    $mail->setToEmail('customercare@craftsvilla.com');
                    $mail->setFromEmail("customercare@craftsvilla.com");
                    $mail->setFromName("Customer Care Craftsvilla");
                    $mail->setType('text');
                    $mail->send();

                }

                /*End..
                *Craftsvilla Comment
                *Added sendmail to customercare@craftsvilla.com when shipment status Changed to 'Shipped To Craftsvilla'
                *Added by Suresh on 2-06-2012
                */

                $shipment->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
                $shipment->getCommentsCollection()->save();
            } else {

                $comment = Mage::getModel('sales/order_shipment_comment')
                    ->setComment($data['comment'])
                    ->setIsCustomerNotified(isset($data['is_customer_notified']))
                    ->setIsVendorNotified(isset($data['is_vendor_notified']))
                    ->setIsVisibleToVendor(isset($data['is_visible_to_vendor']))
                    ->setUsername(Mage::getSingleton('admin/session')->getUser()->getUsername())
                    ->setUdropshipStatus(@$statuses[$status]);
                $shipment->addComment($comment);
                if($status == 12)
                    {
                       $this->adjustmentRefundAmount($shipment_id_value,$shipmentId_value);
                    }
                    if($status == 23)
                    {
                       $this->refundTodo($shipment_id_value,$shipmentId_value);
                    }
                   if($status == 25)
                    {
                       $this->codrto($shipment_id_value,$shipmentId_value);
                    }
                    if($status == 7)
                    {

                       $this->deliver($shipment_id_value,$shipmentId_value);
                    }
                    if($status == 18)
                    {
                        $this->penaltyOutofstock($shipment_id_value);
                    }
                    if($status == 20)

                    {

                        if(isset($_POST['disputeMail']))

                        {

                            $this->sendDisputeRaisedEmailtoCustomer($shipment_id_value);

                        }
                        //mstart
                        if(isset($_POST['disputeremarks']))

                        {

                            $this->disputeCustomerRemarks($shipment_id_value);

                        }
                        //mend
                    }

                if (isset($data['is_vendor_notified'])) {
                    Mage::helper('udropship')->sendShipmentCommentNotificationEmail($shipment, $data['comment']);
                    Mage::helper('udropship')->processQueue();
                }

                /*Start..
                *Craftsvilla Comment
                *Added sendmail to customercare@craftsvilla.com when shipment status Changed to 'Shipped To Craftsvilla'
                *Added by Suresh on 2-06-2012
                */

                if($status == 15 || $status == 16 || $status == 17)
                {
                    //$shipment->sendUpdateEmail('suresh.konda@craftsvilla.com', $data['comment']);
                    Mage::log("Sending email to $sendTo");
                    $mail = Mage::getModel('core/email');
                    if($status == 15)
                    {
                        $mail->setBody('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Shipped To Craftsvilla"');
                        $mail->setSubject('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Shipped To Craftsvilla"');
                    }

                    if($status == 16)
                    {
                        $mail->setBody('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "QC Rejected by Craftsvilla"');
                        $mail->setSubject('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "QC Rejected by Craftsvilla"');
                    }

                    if($status == 17)
                    {
                        $mail->setBody('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Received in Craftsvilla"');
                        $mail->setSubject('Shipment Id:#'.$shipment_id_value.' Shipment status changed to "Received in Craftsvilla"');
                    }


                    $mail->setToName('Craftsvilla');
                    $mail->setToEmail('customercare@craftsvilla.com');
                    $mail->setFromEmail("customercare@craftsvilla.com");
                    $mail->setFromName("Customer Care Craftsvilla");
                    $mail->setType('text');
                    $mail->send();
                }

                /*End..
                *Craftsvilla Comment
                *Added sendmail to customercare@craftsvilla.com when shipment status Changed to 'Shipped To Craftsvilla'
                *Added by Suresh on 2-06-2012
                */

                $shipment->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
                $shipment->getCommentsCollection()->save();
            }

            $this->loadLayout();
            $response = $this->getLayout()->getBlock('order_comments')->toHtml();
        ///  Craftsvilla Comment - By Amit Pitre On 12 Jun-2012 To set feedback reminder entry and shippment payout when shippment status changed to 'Shipped To Customer' //////////////////////////
        Mage::dispatchEvent(
                'craftsvilla_shipment_status_save_after',
                array('shipment'=>$shipment)
            );
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage()
            );
            $response = Zend_Json::encode($response);
        }
        catch (Exception $e) {
            //echo '<pre>';print_r($e->getMessage());
            //Mage::logException($e);
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add new comment.')
            );
            $response = Zend_Json::encode($response);
        }
        $this->getResponse()->setBody($response);
    }

// New Function Created By Dileswar On Dated 19-02-2013 For get closing balance(in submit of refund inited the amount will reflect on shipment payout & dropship panel)******    *******************////

    public function adjustmentRefundAmount($shipment_id_value,$shipentId)
        {

        $user = Mage::getSingleton('admin/session');
        $userFirstname = $user->getUser()->getFirstname();
        $storeId = Mage::app()->getStore()->getId();
        $templateId = 'refund_initiated';
        $shipment1 = Mage::getModel('sales/order_shipment');
        $shipment = $shipment1->load($shipentId);
        $product=Mage::getModel('catalog/product')->load($product_id);
        $_orderId = $shipment->getOrderId();
        $orders  = Mage::getModel('sales/order')->load($_orderId);
        $orderId = $orders->getIncrementId();
        $_order = $shipment->getOrder();
        $write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
        $sender = Array('name'  => 'Craftsvilla',
                        'email' => 'places@craftsvilla.com');
        $emailrefunded = Mage::getModel('core/email_template');
        $orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
        $_address = $_order->getShippingAddress();
        $getName = $_order->getCustomerFirstname();
        $customerTelephone = $_order->getBillingAddress()->getTelephone();
        $_orderBillingCountry = $_order->getBillingAddress();
        $_orderBillingEmail = $_order->getBillingAddress()->getEmail();
        $orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
        $_address = $_order->getShippingAddress();
        $getName = $_order->getCustomerFirstname();
        $customerTelephone = $_order->getBillingAddress()->getTelephone();
        $customerstreet = $_order->getBillingAddress()->getStreet();
        $customercity = $_order->getBillingAddress()->getCity();
        $customercountry_id = $_order->getBillingAddress()->getCountryId();
        $customerState= $_order->getBillingAddress()->getRegion();
        $customerPincode = $_order->getBillingAddress()->getPostcode();
        $payment_method = $_order->getPayment()->getMethodInstance()->getTitle();
        $items = $shipment->getAllItems();
        $sku=array();
        $productName=array();
        foreach($items as $_items)
        {
             $_product=Mage::getModel('catalog/product')->load($_items->getProductId());
             $base_url= "http://www.craftsvilla.com/catalog/product/view/id/".$_product['entity_id'];
             $productName[]=mysql_escape_string($_items->getName());

        }
        $productImage="http://img1.craftsvilla.com/thumb/166x166".$_product->getImage();
        $vars = array(
                'orderId'=>$orderId,
                'productName'=>$productName[0],
                'custtomerName'=> $getName,
             );
         // echo '<pre>';print_r($vars);exit;
        $emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                 ->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);
        $shipment->setUdropshipStatus(12);
        Mage::helper('udropship')->addShipmentComment($shipment, ('Status has been changed to Refund Initiated'));
            $shipment->save();
         $collection = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
            $collection->getSelect()
                      ->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id',array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
                    ->where('main_table.shipment_id = '.$shipment_id_value);
            /*echo "Query:".$collection->getSelect()->__toString();
            exit();*/
            /*echo '<pre>';
            print_r($collection->getData());exit;*/

            $payouData = $collection->getData();
            foreach ($payouData as $_payoutData)
                {
                    $payoutStatus = $_payoutData['shipmentpayout_status'];
                    $payoutAdjust = $_payoutData['adjustment'];
                    $total_amount1 = $_payoutData['subtotal'];
                    $total_amount = $_payoutData['subtotal'];
                    $vendorId = $_payoutData['udropship_vendor'];
                    $collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
                    $closingbalance = $collectionVendor->getClosingBalance();
                    $vendors = Mage::helper('udropship')->getVendor($_payoutData['udropship_vendor']);
                    /*print_r($vendors->getData());
                        exit();*/
            if($payoutStatus == 1)
                {
                        //$_liveDate = "2012-08-21 00:00:00";
                        $order = Mage::getModel('sales/order')->loadByIncrementId($_payoutData['order_id']);
                        $_orderCurrencyCode = $order->getOrderCurrencyCode();
                        //if(($_orderCurrencyCode != 'INR') && (strtotime($_payoutData['order_created_at']) >= strtotime($_liveDate)))
                        if($_orderCurrencyCode != 'INR')
                        $total_amount = $_payoutData['subtotal']/1.5.'<br>';
                        $vendors = Mage::helper('udropship')->getVendor($shipment_id_value);
                        $vendorId = $shipment_id_value['udropship_vendor'];
                        $hlp = Mage::helper('udropship');
                        $commission_amount = $hlp->getVendorCommission($vendorId, $shipment_id_value);
                        $service_tax = $hlp->getServicetaxCv($shipment_id_value);

                        $commission_amount = $_payoutData['commission_percent'].'<br>';
                        $itemised_total_shippingcost = $_payoutData['itemised_total_shippingcost'].'<br>';
                        $base_shipping_amount = $_payoutData['base_shipping_amount'];

                            if($vendors->getManageShipping() == "imanage")
                            {

                                $vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
                                //$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'];
                                //change to accomodate 3% Payment gateway charges on dated 20-12-12
                                $kribha_amount = ($total_amount1+$itemised_total_shippingcost+$_payoutData['cod_fee'])*0.97 - $vendor_amount;
                            }
                            else {
                                $vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+$service_tax)));
                                //$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
                                //change to accomodate 3% Payment gateway charges on dated 20-12-12

                            // Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////
                                //$kribha_amount = ((($total_amount1+$itemised_total_shippingcost)*0.97) - $vendor_amount);
                                $kribha_amount = ((($total_amount1+$base_shipping_amount)*0.97) - $vendor_amount);
                            }
                        //}
                    $payoutAdjust = $payoutAdjust-$vendor_amount;

                    $write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
                    $queryUpdate = "update shipmentpayout set adjustment='".$payoutAdjust."' , `comment` = 'Adjustment Against Refund Paid'  WHERE shipment_id = '".$shipment_id_value."'";
                    $write->query($queryUpdate);

                    $closingbalance = $closingbalance - $vendor_amount;
                    $queyVendor = "update `udropship_vendor` set closing_balance = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
                    $write->query($queyVendor);
                }
                else
                    {
                    $write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
                    $queryUpdate = "update shipmentpayout set shipmentpayout_status= '2' WHERE shipment_id = '".$shipment_id_value."'";
                    $write->query($queryUpdate);

                    }

        // Email To seller in refund intiated state
            $storeId = Mage::app()->getStore()->getId();
            $templateId = 'udropship_refund_seller_email_template';
            $sender = Array('name'  => 'Craftsvilla Customer care',
            'email' => 'customercare@craftsvilla.com');
            $_vendorName = $vendors->getVendorName();
            $_vendorEmail = $vendors->getEmail();
            $_vendorShopName = $vendors->getShopName();
            $varsVendorEmail = Array('shipmentid' => $shipment_id_value,
                        'vendorName' => $_vendorName,
                        'vendorShopName' => $_vendorShopName,
                        );
            $_email = Mage::getModel('core/email_template');
            $_email->sendTransactional($templateId, $sender, $_vendorEmail, $_vendorName, $varsVendorEmail, $storeId);

        }


    }
    public function refundTodo($shipment_id_value,$shipentId)
        {
        $user = Mage::getSingleton('admin/session');
        $userFirstname = $user->getUser()->getFirstname();
        $refundedamount = $this->getRequest()->getParam('refundamount');
        $storeId = Mage::app()->getStore()->getId();
        $templateId = 'refundtodo_email_to_customer';
        $templateId2 = 'refundtodo_email_to_vendor';
        $shipment1 = Mage::getModel('sales/order_shipment');
        $shipment = $shipment1->load($shipentId);
        $dropship = Mage::getModel('udropship/vendor')->load($shipment->getUdropshipVendor());
        $vendorName = $dropship->getVendorName();
        $venEmail = $dropship->getEmail();
        //    echo '<pre>';print_r($shipment);exit;
        $_order = $shipment->getOrder();
        //echo '<pre>';print_r($_order);exit;
        $entityid = $shipment->getEntityId();
        $baseTotalValue = floor($shipment->getBaseTotalValue());
        $itemisedtotalshippingcost = floor($shipment->getItemisedTotalShippingcost());
        $totalcost = floor($itemisedcost);
        $write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
        $sender = Array('name'  => 'Craftsvilla',
                        'email' => 'places@craftsvilla.com');
        $emailrefunded = Mage::getModel('core/email_template');
        $orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
        $orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
                                  ->where('main_table.parent_id='.$entityid)
                                  ->columns('SUM(a.base_discount_amount) AS amount');

        //echo $orderitem->getSelect()->__toString();exit;
            $orderitemdata = $orderitem->getData();
            //print_r($orderitemdata);exit;
            foreach($orderitemdata as $_orderitemdata)
            {
                 $discountamount = floor(($_orderitemdata['amount']));
            }
        $totalAmounttoRefund = Mage::helper('udropship')->getrefundCv($shipentId);
//to get summary of all price

        $summaryprice = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;text-align: right;background:#F2F2F2;color:#CE3D49;'>Total Value : Rs.".$baseTotalValue."<br/>Shipping & Handling:Rs. ".$itemisedtotalshippingcost."<br/> Discount:Rs.".$discountamount."</td></tr><table>";
//echo $summaryprice;exit;
        $_address = $_order->getShippingAddress();
        //echo '<pre>';print_r($_order->getShippingAddress());exit;
//to get Shipping address
        $getName = $_order->getCustomerFirstname();
        $customerTelephone = $_order->getBillingAddress()->getTelephone();
        $_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
        $_orderBillingEmail = $_order->getBillingAddress()->getEmail();
        $street = $_order->getShippingAddress()->getStreet();
        $street1 = $street[0].$street[1];
        $city = $_order->getShippingAddress()->getCity();
        $fname = $_order->getShippingAddress()->getFirstname();
        $lname = $_order->getShippingAddress()->getLastname();
        $fullname = $fname.$lname;
        $postcode = $_order->getShippingAddress()->getPostcode();
        $countrycode = $_order->getShippingAddress()->getCountryId();


$customerShipaddressHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$fullname."<br/>".$street1."<br/>".$city."<br/>".$postcode."<br/>".$countrycode."</td></tr><table>";


        $currencysym = Mage::app()->getLocale()->currency($_order->getBaseCurrencyCode())->getSymbol();
        $_items = $shipment1->getAllItems();
                $customerShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
                foreach ($_items as $_item)
                {
                //echo $_item['product_id'];exit;
                $product = Mage::helper('catalog/product')->loadnew($_item['product_id']);
            try{
            $image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />                    ";}catch(Exception $e){$image = '';}
                 $customerShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shipment_id_value."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
                }
        $customerShipmentItemHtml .= "</table>";

        if($refundedamount)
            {
            $editRefundamount = $refundedamount;
            $vars = array('shipmentid'=>$_shipmentId,
                            'summaryPrice'=>$summaryprice,
                            'custAddress' => $customerShipaddressHtml,
                            'shipmentitem' => $customerShipmentItemHtml,
                            'custtomerName'=> $getName,
                            'shipmentID' =>    $shipment_id_value,
                            'refundedamt' => $editRefundamount,
                            'sellerName' => $vendorName,
                            );
            //print_r($vars);exit;
            $emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                 ->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);
            $emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                 ->sendTransactional($templateId2, $sender,$venEmail, '', $vars, $storeId);
            $queryUpdate = "update shipmentpayout set `refundtodo`='".$editRefundamount."' WHERE shipment_id = '".$shipment_id_value."'";
            $write->query($queryUpdate);
            $shipment->setUdropshipStatus(23);
            Mage::helper('udropship')->addShipmentComment($shipment, ('Status has been changed to Refunded to do, Value  is Rs '.$editRefundamount.' and done by agent '.$userFirstname));
            $shipment->save();
            }
        else{
            $systRefundamount = $totalAmounttoRefund;
            $vars = array('shipmentid'=>$_shipmentId,
                            'summaryPrice'=>$summaryprice,
                            'custAddress' => $customerShipaddressHtml,
                            'shipmentitem' => $customerShipmentItemHtml,
                            'custtomerName'=> $getName,
                            'shipmentID' =>    $shipment_id_value,
                            'refundedamt' => $systRefundamount,
                            );
            //print_r($vars);exit;
            $emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                 ->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);
            $emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                 ->sendTransactional($templateId2, $sender,$venEmail, '', $vars, $storeId);
            $queryUpdate = "update shipmentpayout set `refundtodo`='".$systRefundamount."' WHERE shipment_id = '".$shipment_id_value."'";
            $write->query($queryUpdate);
            $shipment->setUdropshipStatus(23);
            Mage::helper('udropship')->addShipmentComment($shipment, ('Status has been changed to Refunded to do, Value  is Rs '.$systRefundamount.' and done by agent '.$userFirstname));
            $shipment->save();
            }
     }

     public function qcrejectAction()
        {
            $data = $this->getRequest()->getPost('comment');
             $comment = $data['comment'];
            $shipmentId_value = $this->getRequest()->getParam('shipment_id');
            $_target = Mage::getBaseDir('media') . DS . 'noticeboardimages' . DS;
            $targetimg = Mage::getBaseUrl('media').'noticeboardimages/';
            $newfilename = mt_rand(10000000, 99999999).'_rejectimage.jpg';
            $path = $_target.$newfilename;
            $path1 = $targetimg.$newfilename;
            file_put_contents($path, file_get_contents($_FILES['import_image']['tmp_name']));
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId_value);
            $pathhtml = '<a href="'.$path1.'" target="_blank"><img src="'.$path1.'" width="300px"/></a>';
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId_value);
            $shipmentvalue = $shipment['increment_id'];
            $orderid = $shipment['order_id'];
            $vendorid = $shipment['udropship_vendor'];
            $vendor = Mage::getModel('udropship/vendor')->load($vendorid);
            $vendorname = $vendor->getVendorName();
            $vendoremail = $vendor['email'];
            if ($shipment->getUdropshipStatus()!==Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_QC_REJECTED_CRAFTSVILLA) {

                    $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_QC_REJECTED_CRAFTSVILLA);
                    Mage::helper('udropship')->addShipmentComment($shipment,
                    $this->__($comment.' QC Rejected by Craftsvilla')
                );

                            }
                      $shipment->save();
                    $storeId = Mage::app()->getStore()->getId();
                        $templateId = 'qcreject_email_template1';
                        $sender = Array('name'  => 'Craftsvilla',
                        'email' => 'places@craftsvilla.com');
                        $translate  = Mage::getSingleton('core/translate');
                        $translate->setTranslateInline(false);
                        $_email = Mage::getModel('core/email_template');
                        $mailSubject = 'Your Product Has Been QC Rejected By Craftsvilla!';
                        $vendorShipmentItemHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";

                $_items1 = Mage::getModel('sales/order_shipment')->load($shipmentId_value);

                $all = $_items1->getAllItems();

                foreach ($all as $_item)
                {
                $product = Mage::getModel('catalog/product')->load($_item->getProductId());
                $image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
                 $vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$shipmentvalue."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
                }
        $vendorShipmentItemHtml .= "</table>";


                    $vars = Array('shipmentitem' =>$vendorShipmentItemHtml,
                                   'qcrejectimage' => $pathhtml,
                                   'vendorname' => $vendorname,
                                   'comment' => $comment,
                                   'shipmentid' => $shipmentvalue );
                    //print_r($vars);exit;
                    /*$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                            ->setTemplateSubject($mailSubject)
                            ->sendTransactional($templateId, $sender, 'gsonar8@gmail.com', $recname, $vars, $storeId);*/
                    $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                            ->setTemplateSubject($mailSubject)
                            ->sendTransactional($templateId, $sender, $vendoremail, $recname, $vars, $storeId);
                    /*$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                            ->setTemplateSubject($mailSubject)
                            ->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $recname, $vars, $storeId);*/
                    //print_r($_email);exit;
                    $translate->setTranslateInline(true);
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Shipment has been marked as QC Rejected and email has been sent to vendor'));
                    $this->_redirect("adminhtml/sales_shipment/view/shipment_id/$shipmentId_value");

     }

     public function penaltyOutofstock($shipment_id_value)
         {
            $_shipmentId = $shipment_id_value;
            $detailShipment = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();

            $detailShipment->getSelect()
                      ->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id',array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
                    ->where('main_table.shipment_id = '.$_shipmentId);
            //echo "Query:".$detailShipment->getSelect()->__toString();
            //exit();
            //echo '<pre>';
            //print_r($detailShipment->getData());exit;
            foreach($detailShipment->getData() as $_detailShipment)
            {
            $baseTotalValue = $_detailShipment['subtotal'];
            $amntAdjust = $_detailShipment['adjustment'];
            $vendorAttn = $_detailShipment['udropship_vendor'];
            $templateId = 'refund_in_outofstock_email_template';
            $sender = Array('name'  => 'Craftsvilla',
                'email' => 'places@craftsvilla.com');
            $_email = Mage::getModel('core/email_template');

            $penaltyAmount = ($baseTotalValue*0.02);

            $amntAdjust = $amntAdjust-$penaltyAmount;

            $read = Mage::getSingleton('core/resource')->getConnection('udropship_read');
            $getClosingblncQuery = "SELECT `email`,`vendor_name`,`closing_balance` FROM `udropship_vendor` where `vendor_id` = '".$vendorAttn."'";
            $getClosingblncResult = $read->query($getClosingblncQuery)->fetch();
            $read->closeConnection();
            $closingBalance = $getClosingblncResult['closing_balance'];
            $vendorName = $getClosingblncResult['vendor_name'];
            $vendorEmail = $getClosingblncResult['email'];
            $closingBalance = $closingBalance-$penaltyAmount;

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $queryUpdateForAdjustment = "update shipmentpayout set adjustment='".$amntAdjust."' , `comment` = 'Adjustment Against Out Of Stock'  WHERE shipment_id = '".$_shipmentId."'";
            $write->query($queryUpdateForAdjustment);

            $queryUpdateForClosingbalance = "update `udropship_vendor` set `closing_balance`='".$closingBalance."'  WHERE `vendor_id`= '".$vendorAttn."'";
            $write->query($queryUpdateForClosingbalance);
            //Added by Ankit for Panalty Invoice Implementation
            $user = Mage::getSingleton('admin/session');
            $userId = $user->getUser()->getUserId();
            $today = date("Y-m-d H:i:s");
            $queryUpdatePenalty = "INSERT INTO `cv_udropship_vendor_penalty`(`penalty_id`, `increment_id`, `penalty_amount`, `penalty_waiveoff`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES ('DEFAULT','".$_shipmentId."','".$penaltyAmount."','N','".$userId."','".$today."','".$userId."','".$today."' )";

            $write->query($queryUpdatePenalty);
            $write->closeConnection();
            //End Ankit Addition
            $vars = array('penaltyprice'=>$penaltyAmount,
                          'shipmentid'=>$_shipmentId,
                          'vendorShopName'=>$vendorName
                        );
            $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                    ->sendTransactional($templateId, $sender,$vendorEmail, '', $vars, $storeId);
            }
        }

public function sendDisputeRaisedEmailtoCustomer($shipment_id_value)

    {
+
        $user = Mage::getSingleton('admin/session');
        $userFirstname = $user->getUser()->getFirstname();
        $shipmentId_value = $this->getRequest()->getParam('shipment_id');
        $shipment = Mage::getModel('sales/order_shipment');
        $_shipmentid = $shipment->load($shipmentId_value);
        $shipmentId = $_shipmentid->getIncrementId();
        $vendorDetails1 = '';
        $_order = $shipment->getOrder();
        $_orderBillingEmail = $_order->getBillingAddress()->getEmail();
        $_items = $shipment->getAllItems();
                $vendorDetails1 .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";

                foreach ($_items as $_item)

                {

                //echo $_item['product_id'];exit;

                $product = Mage::helper('catalog/product')->loadnew($_item['product_id']);

            try{

            $image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";

               }

            catch(Exception $e){}

            $vendorDetails1 .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shipment_id_value."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";

                }

        $vendorDetails1 .= "</table>";



//print_r($vendorDetails1); exit;



$vendorid = $shipment->getUdropshipVendor();

$vendorDataaa = Mage::getModel('udropship/vendor')->load($vendorid);

//echo '<pre>';print_r($vendorDataaa);exit;

$vendorName = $vendorDataaa['vendor_name'];

$vendorAddress = $vendorDataaa['vendor_name'].', '.$vendorDataaa['vendor_attn'].', '.$vendorDataaa['street'].$vendorDataaa['city'].', '.$vendorDataaa['zip'].', Telephone : '.$vendorDataaa['telephone'];

$vendoremail = $vendorDataaa->getEmail();





        $templateId ='sendDisputeRaisedMail';

        $sender = Array('name'=> 'Craftsvilla',

                'email' => 'customercare@craftsvilla.com');

        $translate  = Mage::getSingleton('core/translate');

        //$translate->setTranslateInline(false);

        $_email = Mage::getModel('core/email_template');

        $mailSubject = 'send_Dispute_Raised_Mail';

        $vars = Array('shipmentId' => $shipmentId,

                    'vendorAddress' => $vendorAddress,

                    'vendorDetails' => $vendorDetails1

                   );

        $_email->setTemplateSubject($mailSubject)

        ->setReplyTo('customercare@craftsvilla.com')

        ->sendTransactional($templateId, $sender,$_orderBillingEmail,'', $vars);

        //echo "email sent successfully to your email";





        $templateIdvendor ='disputeraise_seller_template';

        $senderVendor = Array('name'=> 'Craftsvilla',

                'email' => 'customercare@craftsvilla.com');

        $translate  = Mage::getSingleton('core/translate');

        //$translate->setTranslateInline(false);

        $_emailVendor = Mage::getModel('core/email_template');

        //$mailSubject = 'send_Dispute_Raised_Mail';

        $varsVendor = Array(     'shipmentId' => $shipmentId,

                        'vendorName' => $vendorName,

                        'vendorDetails' => $vendorDetails1

                   );



        $_emailVendor->setTemplateSubject($mailSubject)

        ->setReplyTo('customercare@craftsvilla.com')

        ->sendTransactional($templateIdvendor, $senderVendor,$vendoremail,'', $varsVendor);

        //echo "email sent successfully to your email";



       $shipment->setUdropshipStatus(20);

       Mage::helper('udropship')->addShipmentComment($shipment, ('Return Address sent by system and agent '.$userFirstname));

       $shipment->save();



    }

public function disputeCustomerRemarks($shipment_id_value)
    {
//mstart
        $connread = Mage::getSingleton('core/resource')->getConnection('core_read');
        $connwrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $shipmentId = $shipment_id_value;
        $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipment_id_value);
        $vendorId = $shipment->getUdropshipVendor();
        $vendorDetails = Mage::getModel('udropship/vendor')->load($vendorId);

        $vendorName = mysql_escape_string($vendorDetails->getVendorName());
        $disputeRemark = mysql_escape_string($this->getRequest()->getParam('disputeremarks'));

        $duplicateDispute = "SELECT * FROM `disputecusremarks` WHERE `shipment_id` = '".$shipmentId."'";
        $duplicateDisputeRes = $connread->query($duplicateDispute)->fetchAll();
        $connread->closeConnection();
        if(!($duplicateDisputeRes))
        {

            $insertDisputeRemark = "INSERT INTO `disputecusremarks`(`shipment_id`, `vendor_id`, `vendor_name`, `remarks`) VALUES ($shipmentId,$vendorId,'".$vendorName."','".$disputeRemark."')";
               $connwrite->query($insertDisputeRemark);
               $connwrite->closeConnection();

        }
        else
        {

            $updateDisputeRemark = "UPDATE `disputecusremarks` SET `remarks` = '".$disputeRemark."' WHERE `shipment_id` = '".$shipmentId."'";
               $connwrite->query($updateDisputeRemark);
               $connwrite->closeConnection();

           }

  //mend
    }

    public function codrto($shipment_id_value,$shipentId)
    {
       $user = Mage::getSingleton('admin/session');
        $userFirstname = $user->getUser()->getFirstname();

        $storeId = Mage::app()->getStore()->getId();
        $templateId = 'codrto_email_to_customer';

        $shipment1 = Mage::getModel('sales/order_shipment');
        $shipment = $shipment1->load($shipentId);

        //  echo '<pre>';print_r($shipment);exit;
        $_order = $shipment->getOrder();
        $write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
        $sender = Array('name'  => 'Craftsvilla',
                        'email' => 'places@craftsvilla.com');
        $emailrefunded = Mage::getModel('core/email_template');
        $orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
        $_address = $_order->getShippingAddress();
        $getName = $_order->getCustomerFirstname();
        $customerTelephone = $_order->getBillingAddress()->getTelephone();
        $_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
        $_orderBillingEmail = $_order->getBillingAddress()->getEmail();
        $_orderId = $shipment->getOrderId();
        $orders  = Mage::getModel('sales/order')->load($_orderId);
        $orderId = $orders->getIncrementId();
        $vars = array('shipmentid'=>$_shipmentId,
                            'summaryPrice'=>$summaryprice,
                            'custAddress' => $customerShipaddressHtml,
                            'shipmentitem' => $customerShipmentItemHtml,
                            'custtomerName'=> $getName,
                            'shipmentID' => $shipment_id_value,
                            'refundedamt' => $systRefundamount,
                            'orderId'=>$orderId,
                            );

        $emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                 ->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);

        $shipment->setUdropshipStatus(25);
        Mage::helper('udropship')->addShipmentComment($shipment, ('Status has been changed to Cod rto'));
            $shipment->save();

    }

     public function deliver($shipment_id_value,$shipentId)
    {
        $user = Mage::getSingleton('admin/session');
        $userFirstname = $user->getUser()->getFirstname();

        $storeId = Mage::app()->getStore()->getId();
        $templateId = 'delivery_acknowledgment';

        $shipment1 = Mage::getModel('sales/order_shipment');
        $shipment = $shipment1->load($shipentId);
        $product=Mage::getModel('catalog/product')->load($product_id);
       // echo '<pre>';print_r($product);exit;
        //  echo '<pre>';print_r($shipment);exit;
        $_orderId = $shipment->getOrderId();
        $orders  = Mage::getModel('sales/order')->load($_orderId);
        $orderId = $orders->getIncrementId();
        $_order = $shipment->getOrder();
        $write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
        $sender = Array('name'  => 'Craftsvilla',
                        'email' => 'places@craftsvilla.com');
        $emailrefunded = Mage::getModel('core/email_template');
        $orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
        $_address = $_order->getShippingAddress();
        $getName = $_order->getCustomerFirstname();
        $customerTelephone = $_order->getBillingAddress()->getTelephone();
        $_orderBillingCountry = $_order->getBillingAddress();
        $_orderBillingEmail = $_order->getBillingAddress()->getEmail();

         $orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
        $_address = $_order->getShippingAddress();
        $getName = $_order->getCustomerFirstname();
        $customerTelephone = $_order->getBillingAddress()->getTelephone();
        $customerstreet = $_order->getBillingAddress()->getStreet();
        $customercity = $_order->getBillingAddress()->getCity();
        $customercountry_id = $_order->getBillingAddress()->getCountryId();
        $customerState= $_order->getBillingAddress()->getRegion();
        $customerPincode = $_order->getBillingAddress()->getPostcode();
        $payment_method = $_order->getPayment()->getMethodInstance()->getTitle();
       // $shipmentDetail = Mage::getModel('sales/order_shipment')->load($shipment);
        $items = $shipment->getAllItems();

        $sku=array();
        $productName=array();
        foreach($items as $_items)
        {
             $_product=Mage::getModel('catalog/product')->load($_items->getProductId());
             $base_url= "http://www.craftsvilla.com/catalog/product/view/id/".$_product['entity_id'];
             $sku[]=mysql_escape_string($_items->getSku());
             $productName[]=mysql_escape_string($_items->getName());
             $price[] =mysql_escape_string($_items->getPrice());
              $qty[] =mysql_escape_string($_items->getQtyOrdered());
        }
        foreach($shipment->getAllTracks() as $tracknum)
        {
             $tracknums[]=$tracknum->getNumber();
             $getCourierName[]=$tracknum->getCourierName();
        }

        $productImage="http://img1.craftsvilla.com/thumb/166x166".$_product->getImage();
        $vars = array(
                'sku'=>$sku[0],
                'productName'=>$productName[0],
                'price'=>$price[0],
                'qty'=>$qty[0],
            'custtomerName'=> $getName,
                            'shipmentID' => $shipment_id_value,
                            'customercity'=>$customercity,
                            'customerstreet' => $customerstreet[0],
                            'customerState' => $customerState,
                            'customerPincode' => $customerPincode,
                            'customerTelephone' => $customerTelephone,
                            'customercountry_id'=>$customercountry_id,
                            'payment_method' => $payment_method,
                            'image_url' => $productImage,
                            'tracknums'=> $tracknums[0],
                            'getCourierName' => $getCourierName[0],
                            'orderId'=>$orderId,
                            'base_url'=>$base_url,
                            );
         // echo '<pre>';print_r($vars);exit;

        $emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                 ->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);

        $shipment->setUdropshipStatus(7);
        Mage::helper('udropship')->addShipmentComment($shipment, ('Status has been changed to Delivered'));
            $shipment->save();
    }

    //public function returnrequested($shipment_id_value,$shipentId,$reason='')
    //{
    //    $shipment1 = Mage::getModel('sales/order_shipment');
    //    $shipment = $shipment1->load($shipentId);
    //  
    //    $vendorId = $shipment['udropship_vendor']; //print_r($shipment['udropship_vendor']);//echo $vendorId;
    //    $requestParams  =   array();
    //    $readdb         =   Mage::getSingleton('core/resource')->getConnection('custom_db');
    //    $trackSql       =   "SELECT `number` FROM `sales_flat_shipment_track` WHERE `parent_id` = '$shipentId'";
    //    $trackInformation   =   $readdb->query($trackSql)->fetch();
    //    $readdb->closeConnection();
    //    
    //    
    //    if(!empty($trackInformation))
    //    {
    //        $trackNumber                =   $trackInformation['number'];
    //        $requestParams['tracking_number']   =   $trackNumber;
    //        $jsonInput              =   str_replace('\\/', '/', json_encode($requestParams));
    //         
    //        #call sendd api if tracking number exists
    //        $response               =   $this->senddReverseOrder($jsonInput,$shipentId,$vendorId);
    //         
    //        //if(count((array)$response)  == 0)
    //        //{
    //        //    $flag = 1 ; 
    //        //    return $flag ; //return 'Please try again later';
    //        //}
    //        
    //        $awb=   $response->partner_tracking_detail->tracking_number; 
    //        $shipping_docs      =   $response->partner_tracking_detail->shipping_docs;
    //        $c_company      =   $response->partner_tracking_detail->company;
    //        $created_at         =   $response->partner_tracking_detail->tracking_status_updates[0]->created_at;
    //        $updated_at         =   $response->partner_tracking_detail->tracking_status_updates[0]->updated_at;
    //        $message_status     =   $response->partner_tracking_detail->tracking_status_updates[0]->message;
    //        
    //        if(strlen($awb)>0) 
    //        {
    //            $dest       =   "/tmp/".$shipment_id_value.".pdf";
    //            copy($shipping_docs, $dest);
    //
    //            $bucketName             ='assets1.craftsvilla.com';
    //            $imageSlipNameTwo       = 'sendd/barcode/'.$vendorId.'/'.$shipment_id_value.'_invoice_reverse_shipment_label.pdf';
    //            $moveToBucketSlipTwo    = $this->uploadToS3($dest,$bucketName, $imageSlipNameTwo);
    //            unlink($dest);
    //        } 
    //
    //        $writedb = Mage::getSingleton('core/resource')->getConnection('core_write');
    //        $updatereAwb = "INSERT INTO `sales_flat_shipment_reverse_track`(`shipment_id`, `reverse_awb_no`, `reverse_shipping_docs`,`courier_code`,`reverse_reason`,`created_at`,`updated_at`,`message`,`created_by`,`updated_by`) VALUES ('".$shipment_id_value."','".$awb."','".$shipping_docs."','".$c_company."','".$reason."','".$created_at."','".$updated_at."','".$message_status."','1','1') ";
    //        //Mage::log("ShipmentReverseTrack: " .$updatereAwb, null, 'system.log', true).
    //        $_updatereAwb = $writedb->query($updatereAwb);
    //        $writedb->closeConnection();
    //    }
    //    
    //    $shipment->setUdropshipStatus(37);
    //    Mage::helper('udropship')->addShipmentComment($shipment,('Status has been changed to Return Requested from customer care agent'));
    //    $shipment->save();
    //
    //}
    
    public function senddReverseOrder($jsonInput, $incrementId, $vendorId)
    {
        $generalcheck_hlp = Mage::helper('generalcheck');
        $token      =   $generalcheck_hlp->getSenddToken() ;

        if(!$token)
        {
            return NULL;
        }
    
        $inputHeader = 'Token '.$token; //echo $inputHeader; exit;
        $model= Mage::getStoreConfig('craftsvilla_config/sendd');
        $url = $model['base_url'].'core/api/v1/shipment/reverse/';

        $count = 3;
        while($count > 0)
        { 
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => -1,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $jsonInput,
                CURLOPT_HTTPHEADER => array("cache-control: no-cache",
                                            "content-type: application/json",
                                            "Authorization : ".$inputHeader
                ),
            ));
            $result = curl_exec($curl);
            $response = json_decode($result); 
           // echo "<pre>";print_r($response);exit; //print_r($response);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            //echo "<pre>";print_r($response);exit;
            if($httpCode == 200 || $httpCode == 201)
            {       
                return $response;
            }
            if($httpCode == 401)
            {
               // $token = $this->getSenddToken();
                $token = $generalcheck_hlp->getSenddToken() ;
                if(!$token)
                {
                    return NULL;
                }
                $inputHeader = 'Token '.$token;
                $count--;
                continue;
            }
            $count--;
        }   //echo "The count is: ".$count; exit;
        $errorArr = array();        
        //$errorArr[] = "The error http status code : " . $httpCode;
        if($response)
        {
            $response = json_decode($result, true); 
            $errorArr[] = $response ;
        }
        if($error)
        {
           $errorArr[] = $error; 
        }            
        $issue = "The Awb Number is not generated for Reverse";
        return $generalcheck_hlp->sendErrorEmail($errorArr, $issue, $incrementId, $vendorId); 
    }
}


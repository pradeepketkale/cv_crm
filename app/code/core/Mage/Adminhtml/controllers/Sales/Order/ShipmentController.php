<?php
//echo "gfsdfsdf";exit;

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
 * Adminhtml sales order shipment controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Controller_Sales_Shipment
{
    /**
     * Initialize shipment items QTY
     */
    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    /**
     * Initialize shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _initShipment()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));

        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        } elseif ($orderId) {
            $order      = Mage::getModel('sales/order')->load($orderId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_getSession()->addError($this->__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedDoShipmentWithInvoice()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

            $tracks = $this->getRequest()->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        Mage::throwException($this->__('Tracking number cannot be empty.'));
                    }
                    $track = Mage::getModel('sales/order_shipment_track')
                        ->addData($data);
                    $shipment->addTrack($track);
                }
            }
        }

        Mage::register('current_shipment', $shipment);
        return $shipment;
    }
    
    public function indexAction() {
        if ($order = $this->_initShipment()) {
    
            echo $this->getLayout()->createBlock('adminhtml/sales_order_shipment')->toHtml();
        }
    }
    
    public function multipleprintAction() {
            echo $this->getLayout()->createBlock('adminhtml/sales_order_shipmentmultiple')->toHtml();
    }
    
    /**
     * Save shipment and order in one transaction
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }

    /**
     * shipment information page
     */
    public function viewAction()
    {
        if ($shipment = $this->_initShipment()) {
            $this->_title($this->__('View Shipment'));

            $this->loadLayout();
            $this->getLayout()->getBlock('sales_shipment_view')
                ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
            $this->_setActiveMenu('sales/order')
                ->renderLayout();
        }
        else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Start create shipment action
     */
    public function startAction()
    {
        /**
         * Clear old values for shipment qty's
         */
        $this->_redirect('*/*/new', array('order_id'=>$this->getRequest()->getParam('order_id')));
    }

    /**
     * Shipment create page
     */
    public function newAction()
    {
        if ($shipment = $this->_initShipment()) {
            $this->_title($this->__('New Shipment'));

            if ($comment = Mage::getSingleton('adminhtml/session')->getCommentText(true)) {
                $shipment->setCommentText($comment);
            }

            $this->loadLayout()
                ->_setActiveMenu('sales/order')
                ->renderLayout();
        } else {
            $this->_redirect('*/sales_order/view', array('order_id'=>$this->getRequest()->getParam('order_id')));
        }
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            if ($shipment = $this->_initShipment()) {
                $shipment->register();

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $shipment->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (!empty($data['send_email'])) {
                    $shipment->setEmailSent(true);
                }

                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveShipment($shipment);
                $shipment->sendEmail(!empty($data['send_email']), $comment);
                $this->_getSession()->addSuccess($this->__('The shipment has been created.'));
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
                return;
            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot save shipment.'));
        }
        $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
    }

    /**
     * Send email with shipment data to customer
     */
    public function emailAction()
    {
        try {
            if ($shipment = $this->_initShipment()) {
                $shipment->sendEmail(true)
                    ->setEmailSent(true)
                    ->save();
                $this->_getSession()->addSuccess($this->__('The shipment has been sent.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot send shipment information.'));
        }
        $this->_redirect('*/*/view', array(
            'shipment_id' => $this->getRequest()->getParam('shipment_id')
        ));
    }

    /**
     * Add new tracking number action
     */
public function addTrackAction()
    {
        try {
       $shipmentId = $this->getRequest()->getParam('shipment_id');
           $readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
       $getquery = "SELECT * FROM `sales_flat_shipment_track` WHERE `parent_id` = '".$shipmentId."'";
       $resultCheck = $readConn->query($getquery)->fetch(); 
            $carrier = $this->getRequest()->getPost('carrier');
            $number  = $this->getRequest()->getPost('number');
            $title  = $this->getRequest()->getPost('title');
            $courier_name = $this->getRequest()->getPost('courier_name');
        if($resultCheck['number'])
        {
                Mage::throwException($this->__('You cannot add duplicate tracking number'));
            }   
            if (empty($carrier)) {
                Mage::throwException($this->__('The carrier needs to be specified.'));
            }
            if (empty($number)) {
                Mage::throwException($this->__('Tracking number cannot be empty.'));
            }
            if (empty($courier_name)) {
                Mage::throwException($this->__('Courier name cannot be empty.'));
            }
            if ($shipment = $this->_initShipment()) {
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($number)
                    ->setCarrierCode($carrier)
                    ->setCourierName($courier_name)
                    ->setTitle($title);
                $shipment->addTrack($track)
                    ->save();

                $this->loadLayout();
                $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot initialize shipment for adding tracking number.'),
                );
            }
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add tracking number.'),
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }


    /*--------
        SendDNXT authentication
        Author: pbketkale@gmail.com
    ----------*/

    public function getSenddToken(){
        $lifetime = 7776000; // 90 days
        $cacheSenddLoginKey = 'Craftsvilla-Sendd-Login-Token';
        if($cacheContent = Mage::app()->loadCache($cacheSenddLoginKey)){
            $token = $cacheContent; //echo $token; exit;
        } else {
            $token = $this->senddLogin();
            if($token){
                Mage::app()->saveCache($token, $cacheSenddLoginKey, $tags, $lifetime);
            }
        }
        return $token;

    }

    public function senddLogin(){
        
        $errorArr = array();
        $model= Mage::getStoreConfig('craftsvilla_config/sendd');
        $url = $model['base_url'].'rest-auth/login/';
        $email = $model['email'];
        $password  = $model['password'];

        $input = array('email' => $email, 'password' => $password);
        $input = json_encode($input);

        $count = 3;
        while($count >0){ // try api calls 3 times
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => -1,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $input,
                CURLOPT_HTTPHEADER => array("cache-control: no-cache","content-type: application/json"),
            ));
            $result = curl_exec($curl);
            $result = json_decode($result);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if($httpCode == 200 && $result->key ){
                return $result->key;
            }
            $count--;
        } 

        $errorArr[] = "The error http status code : " . $httpCode;
        if($result){
            foreach ($result as $key=>$value){
               $errorArr[] =  $key." : ".$value[0];
            }
        }
        if($error){
            $errorArr[] = $error;
        }

        $issue = 'Unable to get the access token of Sendd APIs!';
        return $this->sendErrorEmail($errorArr, $issue);

    }

    public function sendErrorEmail($errors, $issue, $shipmentId ='', $vendorId =''){
        //echo "sent the mail";
        date_default_timezone_set('Asia/Kolkata');
        $errorTiming = date("Y-m-d h:i:sa");
        $courierName = 'India Post';

        $serverInfo  = json_encode($_SERVER);

        $errorMessage = json_encode($errors);
        $errorTable = "";
        $errorTable .="<div>";
        $errorTable .="<p>".$issue.".The Status is shown below table.</p>";
        $errorTable .="<table border='1' cellpadding='2px'>";
        $errorTable .="<th>Courier Name</th><th>Shipment Id</th><th>Reason</th>";
        $errorTable .="<tr><td>".$courierName."</td><td>".$shipmentId."</td><td>".$errorMessage."</td></tr>";
        $errorTable .="<tr><td colspan=3>".$serverInfo."</td></tr>";
        $errorTable .="</table></div>";

        $mail = Mage::getModel('core/email');
        $mail->setToName('Craftsvilla');
        $mail->setToEmail('awb.errors@craftsvilla.com');
        //$mail->setToEmail('pradeep.k@iksula.com');
        $mail->setBody($errorTable);
        $mail->setSubject($courierName.' Awb Number Creation Issue at '.$errorTiming);
        $mail->setFromEmail('dileswar@craftsvilla.com');
        $mail->setFromName("Craftsvilla");
        $mail->setType('html');
        $mail->send();
        $awberrorCourierName = $courierName;
        $writedb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $updateAwbError = "INSERT INTO `courier_awb_error`(`shipment_id`, `vendor_id`, `courier`, `error`) VALUES ('".$shipmentId."','".$vendorId."','".$awberrorCourierName."', '".$errorMessage."') ";
        $writedb->query($updateAwbError);
        $writedb->closeConnection();

        return NULL;
    }

    public function removeSenddLoginKey(){
        $cacheSenddLoginKey = 'Craftsvilla-Sendd-Login-Token';
        Mage::app()->removeCache($cacheSenddLoginKey);
        return;
    }

    /*--------
        API Call to SendDNXT when shipment tracking is deleted
        This API is called when order is COD and courier is India Post - on shipment track remove action
        Author: pbketkale@gmail.com (outsourced agent)
    ----------*/

    public function cancelShipmentIndiaPost($trackNumber){
        //$track = Mage::getModel('sales/order_shipment_track')->load($trackId);
       // echo $track->getNumber().'<-->'.$trackId ; exit;
        if($trackNumber){

                    // get authenitication token
                    $token =  $this->getSenddToken();

                    if(!$token){
                        return NULL;
                    }

                    $requestBody = array('tracking_number'=>$trackNumber);
                    $model= Mage::getStoreConfig('craftsvilla_config/sendd');
                    $url = $model['base_url'].'core/api/v1/shipment/cancel/';

                    $inputHeader = 'Token '.$token;
                   
                    // try api calls 3 times
                    $calls = 3;
                    do{
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HEADER, FALSE);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                          "Content-Type: application/json",
                          "Authorization: Token ".$token
                        ));

                        $result = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $response = json_decode($result);

                        curl_close($ch);

                        if($httpCode == 200){
                            if($response->status == NULL){
                                return $response;
                            } else {
                                return $response->status;
                            }
                        }
                        if($httpCode == 400){
                            return $response;
                            break;
                        }

                        if($httpCode == 401){
                            $this->removeSenddLoginKey();
                            $token = $this->getSenddToken();
                            if(!$token){
                                return NULL;
                            }
                            $inputHeader = 'Token '.$token;                                         
                           
                            continue;
                        }
                        $calls--;    
                    }while($calls > 0); 
 		
              
        } else {
                 return "Not Applicable";
        }
    }


    /**
     * Remove tracking number from shipment
     */
    public function removeTrackAction()
    {

          
        $trackId    = $this->getRequest()->getParam('track_id');
        $setStatusShipment = $shipmentId = $this->getRequest()->getParam('shipment_id'); 

        $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
       // echo $track->getNumber(); echo 'dsfs';print_r($track);  exit;
        if ($track->getNumber()) {

            try {
                if ($shipmentId = $this->_initShipment()) {
                    /* 
                    Added by Pradeep (assigned by Dileswar) - 29 March 2016
                    Set shipment status to Processing so vendor can accept and assign new shipment
                    */
                    
                    $statusProcessing = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PROCESSING;
                    $commentMsg = 'Agent deleted the tracking id, to create new awb for shipment';
                    $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
                    
                    $shipment = Mage::getModel('sales/order_shipment')->load($setStatusShipment);
                    
                    $commentNew = Mage::getModel('sales/order_shipment_comment')->setComment($commentMsg)->setUdropshipStatus($statuses[$statusProcessing]);
                    
                    $shipment->setUdropshipStatus($statusProcessing);
                    $shipment->addComment($commentNew);
                    $shipment->save();
                    
                    //$shipmentStatus = Mage::getModel('sales/order_shipment')->load($setStatusShipment)->setUdropshipStatus($statusProcessing)->save();
                    
                    $track->delete();
                    $this->loadLayout();


                    // call to senddnxt API for cancelling cod order
                    try{

                        $status = $this->cancelShipmentIndiaPost($track->getNumber());
                        //print_r($status);
                        if($status != "CA" && $status != 'Not Applicable' && $status != ''){
                            $response = array(
                                'error'     => true,
                                'message'   => $this->__('Response from sendDNXT- '.$status->tracking_number[0]),
                            );
                            $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
                        } 
                        else{
                            $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
                            //$response = array(
                            //    'error'     => false,
                            //    'message'   => $this->__('Response from sendDNXT- '.$status),
                            //);
                        }                        

                    }catch(Exception $e){
                        $response = array(
                            'error'     => true,
                            'message'   => $this->__('Error in SendDNXT API call.'),
                        );
                    }

                } else {
                    $response = array(
                        'error'     => true,
                        'message'   => $this->__('Cannot initialize shipment for delete tracking number.'),
                    );
                }

            } catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot delete tracking number.'),
                );
            }
        } else {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot load track with retrieving identifier.'),
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * View shipment tracking information
     */
    public function viewTrackAction()
    {
        $trackId    = $this->getRequest()->getParam('track_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
        if ($track->getId()) {
            try {
                $response = $track->getNumberDetail();
            } catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot retrieve tracking number detail.'),
                );
            }
        } else {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot load track with retrieving identifier.'),
            );
        }

        if ( is_object($response)){
            $className = Mage::getConfig()->getBlockClassName('adminhtml/template');
            $block = new $className();
            $block->setType('adminhtml/template')
                ->setIsAnonymous(true)
                ->setTemplate('sales/order/shipment/tracking/info.phtml');

            $block->setTrackingInfo($response);

            $this->getResponse()->setBody($block->toHtml());
        } else {
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
            }

            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Add comment to shipment history
     */
    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam(
                'shipment_id',
                $this->getRequest()->getParam('id')
            );
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                Mage::throwException($this->__('Comment text field cannot be empty.'));
            }
            $shipment = $this->_initShipment();
            $shipment->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $shipment->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
            $shipment->save();

            $this->loadLayout(false);
            $response = $this->getLayout()->getBlock('shipment_comments')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage()
            );
            $response = Mage::helper('core')->jsonEncode($response);
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add new comment.')
            );
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }



    /**
     * Decides if we need to create dummy shipment item or not
     * for eaxample we don't need create dummy parent if all
     * children are not in process
     *
     * @deprecated after 1.4, Mage_Sales_Model_Service_Order used
     * @param Mage_Sales_Model_Order_Item $item
     * @param array $qtys
     * @return bool
     */
    protected function _needToAddDummy($item, $qtys) {
        if ($item->getHasChildren()) {
            foreach ($item->getChildrenItems() as $child) {
                if ($child->getIsVirtual()) {
                    continue;
                }
                if ((isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0)
                        || (!isset($qtys[$child->getId()]) && $child->getQtyToShip())) {
                    return true;
                }
            }
            return false;
        } else if($item->getParentItem()) {
            if ($item->getIsVirtual()) {
                return false;
            }
            if ((isset($qtys[$item->getParentItem()->getId()]) && $qtys[$item->getParentItem()->getId()] > 0)
                || (!isset($qtys[$item->getParentItem()->getId()]) && $item->getParentItem()->getQtyToShip())) {
                return true;
            }
            return false;
        }
    }
    
    public function codshipmentsAction()
    {
        $shipmentData = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentData->getSelect()->join(array('b'=>'sales_flat_order_address'), 'b.parent_id=main_table.order_id')
                                  ->join(array('d'=>'sales_flat_shipment_track'), 'd.parent_id=main_table.entity_id','d.number')
                                  ->join(array('c'=>'sales_flat_order'),'c.entity_id=main_table.order_id','c.base_discount_amount')
                                  ->where('main_table.udropship_status = 24 AND b.address_type = "shipping"');
                    //echo $shipmentData->getSelect()->__toString();              exit;
        $shipmentreport = $shipmentData->getData();
        $filename = "CODShipmentReport"."_".date("Y-m-d");
        $outputreport = "";
        $list = array("Waybill","Order No","Consignee Name","City","State","Country","Address","Pincode","Phone","Mobile","Weight","Payment Mode","Package Amount","Cod Amount","Product To Be Shipped","Shipping Client","Shipping Client Address","Shipping Client Phone");
        $numfields = sizeof($list);
        for($k =0; $k < $numfields;  $k++) { 
            $outputreport .= $list[$k];
            if ($k < ($numfields-1)) $outputreport .= ", ";
        }
        $outputreport .= "\n";
        foreach($shipmentreport as $_shipmentreport)
        {
            $shipmentmodel = Mage::getModel('sales/order_shipment')->getCollection()->addAttributeToFilter('order_id', $_shipmentreport['order_id']);
            $shipmentcount = $shipmentmodel->count();
            $incrementid = $_shipmentreport['increment_id'];
            $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
            $orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
            
            $orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
                                  ->where('main_table.parent_id='.$shipment['entity_id'])
                                  ->columns('SUM(a.base_discount_amount) AS amount');
                    //echo $orderitem->getSelect()->__toString();             exit;
            $orderitemdata = $orderitem->getData();
            foreach($orderitemdata as $_orderitemdata)
            {
              $discountamount = $_orderitemdata['amount'];
            
            }
                $countryModel = Mage::getModel('directory/country')->loadByCode($_shipmentreport['country_id']);
                    $countryName = $countryModel->getName();
                    $paymentmode = 'COD';
                    $vendorid=$_shipmentreport['udropship_vendor'];
                    $vendor = Mage::getModel('udropship/vendor')->load($vendorid);
                    $shippingclient = $vendor->getVendorName();
                    $street = $vendor['street'];
                    $city = $vendor->getCity();
                    $zipcode = $vendor->getZip();
                    
                    $vtelephone = $vendor->getTelephone();
                    $custom = Zend_Json::decode($vendor->getCustomVarsCombined());
                    $codfee = $custom['cod_fee'];
                    $amountCOD = $_shipmentreport['base_total_value'] + $_shipmentreport['itemised_total_shippingcost'] - $discountamount + $codfee;    
                
                    for($m =0; $m < sizeof($list); $m++) 
                    {
                                $fieldvalue = $list[$m];
                                if($fieldvalue == "Waybill")
                                {
                                    $outputreport .= $_shipmentreport['number'];
                                }
                                    
                                if($fieldvalue == "Order No")
                                {
                                    $outputreport .= $_shipmentreport['increment_id'];
                                }
                                
                                if($fieldvalue == "Consignee Name")
                                {
                                    $outputreport .=  $_shipmentreport['firstname'].' '.$_shipmentreport['lastname'];
                                }
                                    
                                if($fieldvalue == "City")
                                {
                                    $outputreport .= $_shipmentreport['city'];
                                }
                                
                                if($fieldvalue == "State")
                                {
                                    $outputreport .= $_shipmentreport['region'];
                                }
                                
                                if($fieldvalue == "Country")
                                {
                                    $outputreport .= $countryName;
                                }
                                if($fieldvalue == "Address")
                                {
                                    $outputreport .= '"'.$_shipmentreport['street'].", ".$_shipmentreport['city'].", ".$_shipmentreport['region'].", ".$countryName.", ".$_shipmentreport['postcode'].'"';
                                }
                                if($fieldvalue == "Pincode")
                                {
                                    $outputreport .= $_shipmentreport['postcode'];
                                }
                                if($fieldvalue == "Phone")
                                {
                                    $outputreport .= $_shipmentreport['telephone'];
                                }
                                if($fieldvalue == "Mobile")
                                {
                                    $outputreport .= $_shipmentreport['telephone'];
                                }
                                if($fieldvalue == "Weight")
                                {
                                    $outputreport .= '';
                                }
                                if($fieldvalue == "Payment Mode")
                                {
                                    $outputreport .= $paymentmode;
                                }
                                if($fieldvalue == "Package Amount")
                                {
                                    $outputreport .= $amountCOD;
                                }
                                if($fieldvalue == "Cod Amount")
                                {
                                    $outputreport .= $amountCOD;
                                }
                                if($fieldvalue == "Product To Be Shipped")
                                {
                                    $outputreport .= 'Handicraft Item';
                                }
                                if($fieldvalue == "Shipping Client")
                                {
                                    $outputreport .= $shippingclient;
                                }
                                if($fieldvalue == "Shipping Client Address")
                                {
                                    $outputreport .= '"'.$street.", ".$city.", ".$zipcode.'"';
                                }
                                if($fieldvalue == "Shipping Client Phone")
                                {
                                    $outputreport .= $vtelephone;
                                }                       
                                if ($m < ($numfields-1))
                                {
                                    $outputreport .= ",";
                                }
            }
                          
                
                $outputreport .= "\n";
        }
        
                header("Content-type: text/x-csv");
                header("Content-Disposition: attachment; filename=$filename.csv");
                header("Pragma: no-cache");
                header("Expires: 0");
                echo $outputreport;
                exit;
              try{
                     $this->_redirect('*/sales_shipment/index');
                 } 
             catch(Exception $e)
             {
              echo $e->getMessage();
             }       
     
    }
    public function codshipmentManifestAction(){
        
        $shipmentData = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentData->getSelect()->join(array('b'=>'sales_flat_order_address'), 'b.parent_id=main_table.order_id')
                                  ->join(array('d'=>'sales_flat_shipment_track'), 'd.parent_id=main_table.entity_id','d.number')
                                  ->join(array('c'=>'sales_flat_order'),'c.entity_id=main_table.order_id','c.base_discount_amount')
                                  ->where('main_table.udropship_status = 27 AND main_table.updated_at < DATE_SUB(NOW(),INTERVAL 3 DAY) AND b.address_type = "shipping"');
                    //echo $shipmentData->getSelect()->__toString();              exit;
        $shipmentreport = $shipmentData->getData();
        $filename = "CODShipmentdelayedpickupReport-craftsvilla"."_".date("Y-m-d");
        $outputreport = "";
        $list = array("Waybill","Order No","Consignee Name","City","State","Country","Address","Pincode","Phone","Mobile","Weight","Payment Mode","Package Amount","Cod Amount","Product To Be Shipped","Shipping Client","Shipping Client Address","Shipping Client Phone");
        $numfields = sizeof($list);
        for($k =0; $k < $numfields;  $k++) { 
            $outputreport .= $list[$k];
            if ($k < ($numfields-1)) $outputreport .= ", ";
        }
        $outputreport .= "\n";
        foreach($shipmentreport as $_shipmentreport)
        {
            $shipmentmodel = Mage::getModel('sales/order_shipment')->getCollection()->addAttributeToFilter('order_id', $_shipmentreport['order_id']);
            $shipmentcount = $shipmentmodel->count();
            $incrementid = $_shipmentreport['increment_id'];
            $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
            $orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
            
            $orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
                                  ->where('main_table.parent_id='.$shipment['entity_id'])
                                  ->columns('SUM(a.base_discount_amount) AS amount');
                    //echo $orderitem->getSelect()->__toString();             exit;
            $orderitemdata = $orderitem->getData();
            foreach($orderitemdata as $_orderitemdata)
            {
              $discountamount = $_orderitemdata['amount'];
            
            }
                $countryModel = Mage::getModel('directory/country')->loadByCode($_shipmentreport['country_id']);
                    $countryName = $countryModel->getName();
                    $paymentmode = 'COD';
                    $vendorid=$_shipmentreport['udropship_vendor'];
                    $vendor = Mage::getModel('udropship/vendor')->load($vendorid);
                    $shippingclient = $vendor->getVendorName();
                    $street = $vendor['street'];
                    $city = $vendor->getCity();
                    $zipcode = $vendor->getZip();
                    
                    $vtelephone = $vendor->getTelephone();
                    $custom = Zend_Json::decode($vendor->getCustomVarsCombined());
                    $codfee = $custom['cod_fee'];
                    $amountCOD = $_shipmentreport['base_total_value'] + $_shipmentreport['itemised_total_shippingcost'] - $discountamount + $codfee;    
                
                    for($m =0; $m < sizeof($list); $m++) 
                    {
                                $fieldvalue = $list[$m];
                                if($fieldvalue == "Waybill")
                                {
                                    $outputreport .= $_shipmentreport['number'];
                                }
                                    
                                if($fieldvalue == "Order No")
                                {
                                    $outputreport .= $_shipmentreport['increment_id'];
                                }
                                
                                if($fieldvalue == "Consignee Name")
                                {
                                    $outputreport .=  $_shipmentreport['firstname'].' '.$_shipmentreport['lastname'];
                                }
                                    
                                if($fieldvalue == "City")
                                {
                                    $outputreport .= $_shipmentreport['city'];
                                }
                                
                                if($fieldvalue == "State")
                                {
                                    $outputreport .= $_shipmentreport['region'];
                                }
                                
                                if($fieldvalue == "Country")
                                {
                                    $outputreport .= $countryName;
                                }
                                if($fieldvalue == "Address")
                                {
                                    $outputreport .= '"'.$_shipmentreport['street'].", ".$_shipmentreport['city'].", ".$_shipmentreport['region'].", ".$countryName.", ".$_shipmentreport['postcode'].'"';
                                }
                                if($fieldvalue == "Pincode")
                                {
                                    $outputreport .= $_shipmentreport['postcode'];
                                }
                                if($fieldvalue == "Phone")
                                {
                                    $outputreport .= $_shipmentreport['telephone'];
                                }
                                if($fieldvalue == "Mobile")
                                {
                                    $outputreport .= $_shipmentreport['telephone'];
                                }
                                if($fieldvalue == "Weight")
                                {
                                    $outputreport .= '';
                                }
                                if($fieldvalue == "Payment Mode")
                                {
                                    $outputreport .= $paymentmode;
                                }
                                if($fieldvalue == "Package Amount")
                                {
                                    $outputreport .= $amountCOD;
                                }
                                if($fieldvalue == "Cod Amount")
                                {
                                    $outputreport .= $amountCOD;
                                }
                                if($fieldvalue == "Product To Be Shipped")
                                {
                                    $outputreport .= 'Handicraft Item';
                                }
                                if($fieldvalue == "Shipping Client")
                                {
                                    $outputreport .= $shippingclient;
                                }
                                if($fieldvalue == "Shipping Client Address")
                                {
                                    $outputreport .= '"'.$street.", ".$city.", ".$zipcode.'"';
                                }
                                if($fieldvalue == "Shipping Client Phone")
                                {
                                    $outputreport .= $vtelephone;
                                }                       
                                if ($m < ($numfields-1))
                                {
                                    $outputreport .= ",";
                                }
            }
                          
                
                $outputreport .= "\n";
        }
        
                header("Content-type: text/x-csv");
                header("Content-Disposition: attachment; filename=$filename.csv");
                header("Pragma: no-cache");
                header("Expires: 0");
                echo $outputreport;
                exit;
              try{
                     $this->_redirect('*/sales_shipment/index');
                 } 
             catch(Exception $e)
             {
              echo $e->getMessage();
             }       
     
    
        }
public function requestpikuparamexAction()
    {
     $shipmentid = $this->getRequest()->getPost('shipment_ids',array());
     $account=Mage::getStoreConfig('courier/general/account_number');
     foreach ($shipmentid as $shipment_id)
     {
        $shipment = Mage::getModel('sales/order_shipment')->load($shipment_id);
        $shipid = $shipment['increment_id'];
        $orderid = $shipment['order_id'];
        $udropshipvendor = $shipment['udropship_vendor'];
        $vendor = Mage::getModel('udropship/vendor')->load($udropshipvendor);
        $vendoraddress = $vendor['street'];
        $vendorname = $vendor['vendor_name'];
        $vendorcity = $vendor['city'];
        $vendorcountry_id = $vendor['country_id'];
        $vendorphone = $vendor['telephone'];
        $vendorregion = $vendor['region'];
        $vendorcountry = $vendor['country_id'];
        $vendorzip = $vendor['zip'];
        $countryModel = Mage::getModel('directory/country')->loadByCode($vendorcountry_id);
        $vendorcountryName = $countryModel->getName();
        $orderaddress = Mage::getModel('sales/order_address')->getCollection()
                                                            ->addFieldToFilter('address_type','shipping')
                                                             ->addAttributeToFilter('parent_id',$orderid);
         //echo '<pre>';print_r($orderaddress->getData());exit;
        foreach ($orderaddress as $_orderaddress)
        {
          $custfirstname = $_orderaddress['firstname'];
          $custlastname = $_orderaddress['lastname'];
          $custname = $custfirstname.' '.$custlastname;
          $custstreet = $_orderaddress['street'];
          $custcity = $_orderaddress['city'];
          $custregion = $_orderaddress['region'];
          $custcountryid = $_orderaddress['country_id'];
          $custphone = $_orderaddress['telephone'];
          $custpostcode = $_orderaddress['postcode'];
          $countryModel = Mage::getModel('directory/country')->loadByCode($custcountryid);
          $custcountryName = $countryModel->getName();
          
          
        }  
         $read = Mage::getSingleton('core/resource')->getConnection('core_read');
          $pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$custpostcode."' AND `carrier` like '%Aramex%'";
          $rquery = $read->query($pincodeQuery)->fetch();
          $vendpincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$vendorzip."' AND `carrier` like '%Aramex%'";
          $vendquery = $read->query($vendpincodeQuery)->fetch();
          $vendcod = $vendquery['is_cod'];
          $cod = $rquery['is_cod'];
          if(is_null($cod) || ($cod == '') || ($cod != '0'))
          {
            Mage::getSingleton('core/session')->addError("The customer's pickup pincode is not serviceable by Logistic Service Provider");
          }
          elseif (is_null($vendcod) || ($vendcod == '') || ($vendcod != '0'))
        {
            Mage::getSingleton('core/session')->addError("The vendor's pickup pincode is not serviceable by Logistic Service Provider");
          }
          else {
         $storeId = Mage::app()->getStore()->getId();    
            $templateId='request_pickup_Aramex_template';
                $sender = Array('name'  => 'Craftsvilla',
                            'email' => 'customercare@craftsvilla.com');
                            $translate  = Mage::getSingleton('core/translate');
                            $translate->setTranslateInline(false);
                            $_email = Mage::getModel('core/email_template');
                            $mailSubject = 'Request Pickup from Craftsvilla.com';
                                                
                
                        $vars = Array('custname'=>$custname, 'custstreet'=>$custstreet, 'custcity'=>$custcity,
                                       'region' => $custregion,'custphone'=>$custphone,'custpostcode'=>$custpostcode,'custcountry'=>$custcountryName,
                                        'vendorstreet'=>$vendoraddress,'vendname'=>$vendorname,'vendorcity'=>$vendorcity,'vendorregion'=>$vendorregion,
                                        'vendpostcode'=>$vendorzip,'vendcountry'=>$vendorcountryName,'vendphone'=>$vendorphone,'shipmentId'=>$shipid,
                                         'accountnumber'=>$account);        
                        
                    $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                ->setTemplateSubject($mailSubject)
                                ->sendTransactional($templateId, $sender, 'dhaval.Rao@aramex.com', $recname, $vars, $storeId);
                                $translate->setTranslateInline(true);
                        $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                ->setTemplateSubject($mailSubject)
                                ->sendTransactional($templateId, $sender, 'Sandeep.Bhitade@aramex.com', $recname, $vars, $storeId);
                                $translate->setTranslateInline(true);
                        $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                ->setTemplateSubject($mailSubject)
                                ->sendTransactional($templateId, $sender, 'customercare@craftsvilla.com', $recname, $vars, $storeId);
                                $translate->setTranslateInline(true);
                if ($shipment->getUdropshipStatus() !== Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PENDPICKUP) {
                    $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PENDPICKUP);
                    Mage::helper('udropship')->addShipmentComment($shipment,
                              ('Request Pickup for this shipment has been sent to the Logistic Service Provider. '));
                }
                $shipment->save();
             Mage::getSingleton('core/session')->addSuccess("Your request has been successfully submitted");
       }
     }
       try{
                     $this->_redirect('*/sales_shipment/index');
                 } 
             catch(Exception $e)
             {
              echo $e->getMessage();
             }       
     }
}

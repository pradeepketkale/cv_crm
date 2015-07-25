<?php
class Craftsvilla_Uagent_Model_Source extends Craftsvilla_Uagent_Model_Source_Abstract
{
    const AGENT_STATUS_ACTIVE    = 'A';
    const AGENT_STATUS_INACTIVE  = 'I';
    const AGENT_STATUS_DISABLED  = 'D';

      
	const ORDER_STATUS_PENDING  = 0;
    const ORDER_STATUS_CANCELED = 1;
	const ORDER_STATUS_PAYMENT_COLLECTED = 2;
	const ORDER_STATUS_PAYMENT_DEPOSITED = 3;
	const ORDER_STATUS_PAYMENT_RECIEVED = 4;
	
	const AUTO_ORDER_COMPLETE_NO  = 0;
    const AUTO_ORDER_COMPLETE_ALL = 1;
    const AUTO_ORDER_COMPLETE_ANY = 2;

    const NOTIFYON_TRACK = 1;
    const NOTIFYON_ORDER = 2;

    const HANDLING_SYSTEM = 0;
    const HANDLING_SIMPLE = 1;
    const HANDLING_ADVANCED = 2;

    const CALCULATE_RATES_DEFAULT = 0;
    const CALCULATE_RATES_ROW = 1;
    const CALCULATE_RATES_ITEM = 2;

    protected $_carriers = array();
    protected $_methods = array();
    protected $_agents = array();
    protected $_visiblePreferences = array();

    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('uagent');

        $options = array();

        switch ($this->getPath()) {

        case 'uagent/customer/notify_on_tracking':
        case 'uagent/customer/notify_on_shipment':
        case 'yesno':
            $options = array(
                1 => $hlp->__('Yes'),
                0 => $hlp->__('No'),
            );
            break;
            
        case 'yesno_useconfig':
            $options = array(
                -1 => $hlp->__('Use config'),
                1 => $hlp->__('Yes'),
                0 => $hlp->__('No'),
            );
            break;

        case 'uagent/customer/notify_on':
            $options = array(
                0 => $hlp->__('Disable'),
                1 => $hlp->__('When Tracking ID is added'),
                2 => $hlp->__('When Vendor Shipment is complete'),
                #3 => $hlp->__('When Order is completely shipped'),
            );
            break;

        case 'uagent/customer/poll_tracking':
            // not used
            break;

        case 'uagent/customer/estimate_error_action':
            $options = array(
                'fail' => $hlp->__('Fail estimate and show the error'),
                'skip' => $hlp->__('Skip failed carrier call and show prices without'),
            );
            break;

        case 'uagent/stock/availability':
        case 'uagent/stock/reassign_availability':
            $options = array();
            $methods = Mage::getConfig()->getNode('global/uagent/availability_methods')->children();
            foreach ($methods as $code=>$method) {
                if (!$method->is('active') || !$method->label) {
                    continue;
                }
                $options[$code] = (string)$method->label;
            }
            break;

        case 'carriers/uagent/free_method':
            $selector = false;
            $options = $this->getMethods(true);
            break;

        case 'carriers':
            $options = $this->getCarriers();
            break;

        case 'agents':
        case 'uagent/agent/local_agent':
            $options = $this->getAgents();
            break;

        case 'uagent/agent/make_available_to_uagent':
            $selector = false;
            $options = Mage::getSingleton('sales/order_config')->getStatuses();
            break;

        case 'uagent/agent/visible_preferences':
            $selector = false;
            $options = $this->getAgentVisiblePreferences();
            break;

		

        case 'uagent/batch/export_on_po_status':
        case 'uagent/agent/default_order_status':
        case 'uagent/agent/restrict_order_status':
        case 'order_statuses':
        case 'initial_order_status':
        case 'agent_po_grid_status_filter':
            $options = array(
                self::ORDER_STATUS_PENDING   => $hlp->__('Pending'),
                self::ORDER_STATUS_CANCELED  => $hlp->__('Canceled'),
	        	self::ORDER_STATUS_PAYMENT_COLLECTED  => $hlp->__('Payment Collected'),
				self::ORDER_STATUS_PAYMENT_DEPOSITED  => $hlp->__('Payment Deposited'),
				self::ORDER_STATUS_PAYMENT_RECIEVED  => $hlp->__('Payment Received'),
            );
            if ($this->getPath() == 'initial_order_status') {
                $options = array('999' => $hlp->__('* Default (global setting)')) + $options;
            }
            break;

        case 'uagent/agent/agent_notification_field':
            $options = $this->getAgentVisiblePreferences();
            array_unshift($options, array('value'=>'', 'label'=>$hlp->__('* Use Agent Email')));
            break;

       
            
        case 'uagent/vendor/pdf_use_font':
            $options = array(
                '' => $hlp->__('* Magento Bundled Fonts'),
                'TIMES' => $hlp->__('Times New Roman'),
                'HELVETICA' => $hlp->__('Helvetica'),
                'COURIER' => $hlp->__('Courier'),
            );
            break;

        case 'uagent/customer/estimate_total_method':
            $options = array(
                '' => $hlp->__('Sum of order vendors estimates'),
                'max' => $hlp->__('Maximum of order vendors estimates'),
            );
            break;

        case 'uagent/misc/mail_transport':
            $options = array(
                '' => $hlp->__('* Automatic'),
                'sendmail' => $hlp->__('Sendmail'),
            );
            break;

        case 'vendor_statuses':
            $options = array(
                self::VENDOR_STATUS_ACTIVE   => $hlp->__('Active'),
                self::VENDOR_STATUS_INACTIVE => $hlp->__('Inactive'),
                #self::VENDOR_STATUS_DISABLED  => $hlp->__('Disabled'),
            );
            break;

        case 'new_order_notifications':
            $options = array(
                '' => $hlp->__('* No notification'),
                '1' => $hlp->__('* Email notification'),
            );
            $config = Mage::getConfig()->getNode('global/uagent/notification_methods');
            foreach ($config->children() as $code=>$node) {
                if (!$node->label) {
                    continue;
                }
                $options[$code] = $hlp->__((string)$node->label);
            }
            asort($options);
            break;

        case 'statement_withhold_totals':
            $options = array(
                'tax' => 'Tax',
                'shipping' => 'Shipping',
                'handling' => 'Handling',
            );
            break;

        case 'statement_shipping_in_payout':
        case 'statement_tax_in_payout':
        	$options = array(
                'include' => 'Include',
                'exclude_show' => 'Exclude but Show',
                'exclude_hide' => 'Exclude and Hide',
            );
            break;

        case 'stockcheck_method':
            $options = array(
                '' => $hlp->__('* Local database'),
                //'1' => $hlp->__('Always in stock'),
            );
            $config = Mage::getConfig()->getNode('global/uagent/stockcheck_methods');
            foreach ($config->children() as $code=>$node) {
                if (!$node->label) {
                    continue;
                }
                $options[$code] = $hlp->__((string)$node->label);
            }
            asort($options);
            break;

        case 'tax_regions':
            $options = $this->getTaxRegions();
            break;


        case 'handling_integration':
            $options = array(
                'bypass' => $hlp->__('Use system configured handling fee only'),
                'replace' => $hlp->__('Use vendor configured handling fee only'),
                'add' => $hlp->__('Add vendor handling fee to the system handling fee'),
            );
            break;

        case 'poll_tracking':
            $options = array(
                '-' => $hlp->__('* Disable tracking API polling'),
                '' => $hlp->__('* Use label carrier API if available'),
            );
            $trackConfig = Mage::getConfig()->getNode("global/uagent/track_api");
            foreach ($trackConfig->children() as $code=>$node) {
                if ($node->is('disabled') || !$node->label) {
                    continue;
                }
                $options[$code] = (string)$node->label;
            }
            break;

        case 'label_type':
            $options = array(
                ''=>$hlp->__('No label printing'),
                'PDF'=>$hlp->__('PDF'),
                'EPL'=>$hlp->__('EPL'),
//                'ZPL'=>$hlp->__('ZPL'),
            );
            break;
        case 'uagent/label/label_size':
            $options = array(
                '4X6'=>$hlp->__('4X6'),
            );
            break;

        case 'pdf_label_rotate':
            $options = array(
                '0'=>'None',
                '90'=>'90 degrees',
                '180'=>'180 degrees',
                '270'=>'270 degrees',
            );
            break;

        case 'endicia_label_type':
            $options = array(
                'Default'=>'Default',
                'CertifiedMail'=>'CertifiedMail',
                'DestinationConfirm'=>'DestinationConfirm',
                //'International'=>'International',
            );
            break;

        case 'endicia_label_size':
            $options = array(
                '4X6'=>'4X6',
                '4X5'=>'4X5',
                '4X4.5'=>'4X4.5',
                'DocTab'=>'DocTab',
                '6x4'=>'6x4',
            );
            break;
        case 'endicia_mail_class':
            $options = array(
                'Express'=>'Express Mail',
                'First'=>'First-Class Mail',
                'LibraryMail'=>'Library Mail',
                'MediaMail'=>'Media Mail',
                'ParcelPost'=>'Parcel Post',
                'ParcelSelect'=>'Parcel Select',
                'Priority'=>'Priority Mail',
            );
            break;
        case 'endicia_mailpiece_shape':
            $options = array(
                'Card'=>'Card',
                'Letter'=>'Letter',
                'Flat'=>'Flat',
                'Parcel'=>'Parcel',
                'FlatRateBox'=>'FlatRateBox',
                'FlatRateEnvelope'=>'FlatRateEnvelope',
                'IrregularParcel'=>'IrregularParcel',
                'LargeFlatRateBox'=>'LargeFlatRateBox',
                'LargeParcel'=>'LargeParcel',
                'OversizedParcel'=>'OversizedParcel',
                'SmallFlatRateBox'=>'SmallFlatRateBox',
            );
            break;

        case 'endicia_insured_mail':
            $options = array(
                'OFF' => 'No Insurance',
                'ON'  => 'USPS Insurance',
                'UspsOnline' => 'USPS Online Insurance',
                'Endicia' => 'Endicia Insurance',
            );
            break;

        case 'endicia_customs_form_type':
            $options = array(
                'Form2976' => 'Form 2976 (same as CN22)',
                'Form2976A' => 'Form 2976A (same as CP72)',
            );
            break;

        case 'weight_units':
            $options = array(
                'LB'=>'Pounds (lb)',
                'KG'=>'Kilograms (kg)',
            );
            break;

        case 'dimension_units':
            $options = array(
                'IN'=>'Inch',
                'CM'=>'Centimeter',
            );
            break;

        case 'pdf_page_size':
            $options = array(
                Zend_Pdf_Page::SIZE_LETTER => 'Letter',
            );
            break;

        case 'ups_pickup':
            $options = array(
                '' => '* Default',
                '01' => 'Daily Pickup',
                '03' => 'Customer Counter',
                '06' => 'One Time Pickup',
                '07' => 'On Call Air',
                '11' => 'Suggested Retail',
                '19' => 'Letter Center',
                '20' => 'Air Service Center',
            );
            break;

        case 'ups_container':
            $options = array(
                '' => '* Default',
                '00' => 'Customer Packaging',
                '01' => 'UPS Letter Envelope',
                '03' => 'UPS Tube',
                '21' => 'UPS Express Box',
                '24' => 'UPS Worldwide 25 kilo',
                '25' => 'UPS Worldwide 10 kilo',
            );
            break;

        case 'ups_dest_type':
            $options = array(
                '' => '* Default',
                '01' => 'Residential',
                '02' => 'Commercial',
            );
            break;

        case 'ups_delivery_confirmation':
            $options = array(
                '' => 'No Delivery Confirmation',
                '1' => 'Delivery Confirmation',
                '2' => 'Delivery Confirmation Signature Required',
                '3' => 'Delivery Confirmation Adult Signature Required',
            );
            break;

        case 'ups_shipping_method_combined':
            $usa = Mage::helper('usa');
            $options = array(
                'UPS CGI' => array(
                    '1DM'    => $usa->__('Next Day Air Early AM'),
                    '1DML'   => $usa->__('Next Day Air Early AM Letter'),
                    '1DA'    => $usa->__('Next Day Air'),
                    '1DAL'   => $usa->__('Next Day Air Letter'),
                    '1DAPI'  => $usa->__('Next Day Air Intra (Puerto Rico)'),
                    '1DP'    => $usa->__('Next Day Air Saver'),
                    '1DPL'   => $usa->__('Next Day Air Saver Letter'),
                    '2DM'    => $usa->__('2nd Day Air AM'),
                    '2DML'   => $usa->__('2nd Day Air AM Letter'),
                    '2DA'    => $usa->__('2nd Day Air'),
                    '2DAL'   => $usa->__('2nd Day Air Letter'),
                    '3DS'    => $usa->__('3 Day Select'),
                    'GND'    => $usa->__('Ground'),
                    'GNDCOM' => $usa->__('Ground Commercial'),
                    'GNDRES' => $usa->__('Ground Residential'),
                    'STD'    => $usa->__('Canada Standard'),
                    'XPR'    => $usa->__('Worldwide Express'),
                    'WXS'    => $usa->__('Worldwide Express Saver'),
                    'XPRL'   => $usa->__('Worldwide Express Letter'),
                    'XDM'    => $usa->__('Worldwide Express Plus'),
                    'XDML'   => $usa->__('Worldwide Express Plus Letter'),
                    'XPD'    => $usa->__('Worldwide Expedited'),
                ),
                'UPS XML' => array(
                    '01' => $usa->__('UPS Next Day Air'),
                    '02' => $usa->__('UPS Second Day Air'),
                    '03' => $usa->__('UPS Ground'),
                    '07' => $usa->__('UPS Worldwide Express'),
                    '08' => $usa->__('UPS Worldwide Expedited'),
                    '11' => $usa->__('UPS Standard'),
                    '12' => $usa->__('UPS Three-Day Select'),
                    '13' => $usa->__('UPS Next Day Air Saver'),
                    '14' => $usa->__('UPS Next Day Air Early A.M.'),
                    '54' => $usa->__('UPS Worldwide Express Plus'),
                    '59' => $usa->__('UPS Second Day Air A.M.'),
                    '65' => $usa->__('UPS Saver'),

                    '82' => $usa->__('UPS Today Standard'),
                    '83' => $usa->__('UPS Today Dedicated Courrier'),
                    '84' => $usa->__('UPS Today Intercity'),
                    '85' => $usa->__('UPS Today Express'),
                    '86' => $usa->__('UPS Today Express Saver'),
                ),
            );
            break;

        case 'fedex_dropoff_type':
            $options = array(
                'REGULAR_PICKUP' => $hlp->__('Regular Pickup'),
                'REQUEST_COURIER' => $hlp->__('Request Courier'),
                'DROP_BOX' => $hlp->__('Drop Box'),
                'BUSINESS_SERVICE_CENTER' => $hlp->__('Business Service Center'),
                'STATION' => $hlp->__('Station'),
            );
            break;

        case 'fedex_service_type':
            break;

        case 'fedex_packaging_type':
            break;

        case 'fedex_label_stock_type':
            $options = array(
                'PAPER_4X6' => $hlp->__('PDF: Paper 4x6'),
                'PAPER_4X8' => $hlp->__('PDF: Paper 4x8'),
                'PAPER_4X9' => $hlp->__('PDF: Paper 4x9'),
                'PAPER_7X4.75' => $hlp->__('PDF: Paper 7x4.75'),
                'PAPER_8.5X11_BOTTOM_HALF_LABEL' => $hlp->__('PDF: Paper 8.5x11 Bottom Half Label'),
                'PAPER_8.5X11_TOP_HALF_LABEL' => $hlp->__('PDF: Paper 8.5x11 Top Half Label'),

                'STOCK_4X6' => $hlp->__('EPL: Stock 4x6'),
                'STOCK_4X6.75_LEADING_DOC_TAB' => $hlp->__('EPL: Stock 4x6.75 Leading Doc Tab'),
                'STOCK_4X6.75_TRAILING_DOC_TAB' => $hlp->__('EPL: Stock 4x6.75 Trailing Doc Tab'),
                'STOCK_4X8' => $hlp->__('EPL: Stock 4x8'),
                'STOCK_4X9_LEADING_DOC_TAB' => $hlp->__('EPL: Stock 4x9 Leading Doc Tab'),
                'STOCK_4X9_TRAILING_DOC_TAB' => $hlp->__('EPL: Stock 4x9 Trailing Doc Tab'),
            );
            break;

        case 'fedex_signature_option':
            $options = array(
                'NO_SIGNATURE_REQUIRED' => 'No Signature Required',
                'SERVICE_DEFAULT' => 'Default Appropriate Signature Option',
                'DIRECT' => 'Direct',
                'INDIRECT' => 'Indirect',
                'ADULT' => 'Adult',
            );
            break;
        
        case 'manage_shipping':
            $options = array(
                'vmanage' => 'vmanage',
                'imanage' => 'imanage',
            );
            break;
            
        case 'uagent/vendor/reassign_available_shipping':
            $options = array(
                'all' => $hlp->__('All'),
                'order' => $hlp->__('Limit by order shipping method'),
            );
            break;
            
        case 'statement_po_type':
            $options = array(
                'shipment' => $hlp->__('Shipment'),
            );
            if ($hlp->isUdpoActive()) {
                $options['po'] = $hlp->__('Purchase Order');
            }
            break;
        
        case 'statement_subtotal_base':
            $options = array(
                'price' => $hlp->__('Price'),
            	'cost'  => $hlp->__('Cost'),
            );
            break;

        case 'vendor_po_grid_sortby':
            $options = array(
                'increment_id' => $hlp->__('Shippment ID'),
                'shipment_date' => $hlp->__('Shippment Date'),
                'uagent_status' => $hlp->__('Shipping Status'),
            );
            break;

        case 'vendor_po_grid_sortdir':
            $options = array(
                'desc' => $hlp->__('Descending'),
				'asc' => $hlp->__('Ascending'),
            );
            break;

        case 'shipping_extra_charge_type':
            $options = array(
                'fixed' => $hlp->__('Fixed'),
                'shipping_percent' => $hlp->__('Percent of shipping amount'),
                'subtotal_percent' => $hlp->__('Percent of vendor subtotal'),
            );
            break;

        case 'uagent/customer/vendor_enable_disable_action':
            $options = array(
                'noaction' => $hlp->__('No action'),
                'enable_disable' => $hlp->__('Enable / Disable vendor products'),
            );
            break;

        case 'use_handling_fee':
            $options = array(
                self::HANDLING_SYSTEM => $hlp->__('* Default System Rules'),
                self::HANDLING_SIMPLE => $hlp->__('Simple Custom Rules'),
                self::HANDLING_ADVANCED => $hlp->__('Advanced Custom Rules'),
            );
            break;

        case 'handling_rule':
            $options = array(
                'price'  => $hlp->__('Total Price'),
                'cost'   => $hlp->__('Total Cost'),
                'qty'    => $hlp->__('Qty'),
                'line'   => $hlp->__('Line Number'),
                'weight' => $hlp->__('Weight'),
            );
            break;

        case 'product_calculate_rates':
            $options = array(
                self::CALCULATE_RATES_DEFAULT => $hlp->__('Vendor Package'),
                self::CALCULATE_RATES_ROW      => $hlp->__('Row Separate Rate'),
                self::CALCULATE_RATES_ITEM     => $hlp->__('Item Separate Rate'),
            );
            break;

        case 'uagent/customer/vendor_delete_action':
            $options = array(
                'noaction' => $hlp->__('No action'),
                'assign_local_enabled' => $hlp->__('Assign to local vendor and leave enabled'),
                'assign_local_disable' => $hlp->__('Assign to local vendor and disable vendor products'),
                'delete' => $hlp->__('Delete vendor products'),
            );
            break;
        default:
            Mage::throwException($hlp->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$hlp->__('* Please select')) + $options;
        }

        return $options;
    }

    public function toOptionArray($selector=false)
    {
        switch ($this->getPath()) {
        case 'uagent/uagent/vendor_notification_field':
        case 'uagent/uagent/visible_preferences':
            return $this->toOptionHash($selector);
        }
        return parent::toOptionArray($selector);
    }

    public function getCarriers()
    {
        if (empty($this->_carriers)) {
            $carriersRaw = Mage::getSingleton('shipping/config')->getAllCarriers();
            $carriers = array();
            foreach ($carriersRaw as $carrierCode=>$carrierModel) {
                $label = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
                if (!$label || in_array($carrierCode, array('uagent', 'udsplit'))) {
                    continue;
                }
                $carriers[$carrierCode] = $label;
            }
            $this->_carriers = $carriers;
        }
        return $this->_carriers;
    }

    public function getMethods($codeAsKey=false)
    {
        if (empty($this->_methods)) {
            $methodsCollection = Mage::helper('uagent')->getShippingMethods()
                ->setOrder('days_in_transit', 'desc');
            foreach ($methodsCollection as $m) {
                $this->_methods[$codeAsKey ? $m->getShippingCode() : $m->getShippingId()] = $m->getShippingTitle();
            }
        }
        return $this->_methods;
    }

    public function getAgents($includeInactive=false)
    {
        if (empty($this->_agents[$includeInactive])) {
            $this->_agents[$includeInactive] = array();
            $vendors = Mage::getModel('uagent/uagent')->getCollection()
                ->setOrder('agent_name', 'asc');
            foreach ($vendors as $v) {
                $this->_agents[$includeInactive][$v->getId()] = $v->getAgentName();
            }
        }
        return $this->_agents[$includeInactive];
    }

  

    public function getVendorVisiblePreferences()
    {
        if (empty($this->_visiblePreferences)) {
            $hlp = Mage::helper('uagent');

            $fieldsets = array();
            foreach (Mage::getConfig()->getNode('global/uagent/vendor/fieldsets')->children() as $code=>$node) {
                $fieldsets[$code] = array(
                    'position' => (int)$node->position,
                    'label' => (string)$node->legend,
                    'value' => array(),
                );
            }
            foreach (Mage::getConfig()->getNode('global/uagent/vendor/fields')->children() as $code=>$node) {
                if (empty($fieldsets[(string)$node->fieldset])) {
                    continue;
                }
                $field = array(
                    'position' => (int)$node->position,
                    'label' => (string)$node->label,
                    'value' => $code,
                );
                $fieldsets[(string)$node->fieldset]['value'][] = $field;
            }
            uasort($fieldsets, array($hlp, 'usortByPosition'));
            foreach ($fieldsets as $k=>$v) {
                if (empty($v['value'])) {
                    continue;
                }
                uasort($v['value'], array($hlp, 'usortByPosition'));
            }
            $this->_visiblePreferences = $fieldsets;
        }
        return $this->_visiblePreferences;
    }
}

<?php
class Marketplace_Thoughtyard_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');

        $options = array();

        switch ($this->getPath()) {

        case 'vendors':
        case 'item_location':
            $options = $this->getVendors();
            break;

        case 'ship_handling_time':
            $selector = false;
                        $options = array(
                '24 hours'   => $hlp->__('24 hours'),
                '48 hours'  => $hlp->__('48 hours'),
                '72 hours'  => $hlp->__('72 hours'),
                '5 days'  => $hlp->__('5 Days'),
                '10 days'       => $hlp->__('10 Days'),
            );
            //$options = Mage::getSingleton('sales/order_config')->getStatuses();
            break;

        case 'item_return_in_days':
            $selector = false;
            $options = array(
				'No Return'       => $hlp->__('No Return'),
                '24 hours'   => $hlp->__('24 hours'),
                '48 hours'  => $hlp->__('48 hours'),
                '72 hours'  => $hlp->__('72 hours'),
                '5 days'  => $hlp->__('5 Days'),
                '10 days'       => $hlp->__('10 Days'),
            );
            //$options = $this->getVendorVisiblePreferences();
            break;

        case 'refund_made_as':
            $options = array(
                'voucher'   => $hlp->__('Voucher'),
                'Moneyback'  => $hlp->__('Moneyback'),
                //'other'       => $hlp->__('other'),
            );
            /*if ($this->getPath() == 'initial_shipment_status') {
                $options = array('999' => $hlp->__('* Default (global setting)')) + $options;
            }*/
            break;

        case 'refund_cost_bearer':
        $options = array(
                'buyer'   => $hlp->__('Buyer'),
                'seller'  => $hlp->__('Seller'),
            );
            //$options = $this->getVendorVisiblePreferences();
            //array_unshift($options, array('value'=>'', 'label'=>$hlp->__('* Use Vendor Email')));
            break;


        case 'company_type':
            $options = array(
                'individual' => $hlp->__('individual'),
                'proprietorship' => $hlp->__('Proprietorship'),
                'partnership' => $hlp->__('Partnership'),
                'privateltd' => $hlp->__('Pvt. Ltd, Firm'),
                'publicltd' => $hlp->__('Public Ltd. Firm'),
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
        case 'udropship/vendor/vendor_notification_field':
        case 'udropship/vendor/visible_preferences':
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
                if (!$label || in_array($carrierCode, array('udropship', 'udsplit'))) {
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
            $methodsCollection = Mage::helper('udropship')->getShippingMethods()
                ->setOrder('days_in_transit', 'desc');
            foreach ($methodsCollection as $m) {
                $this->_methods[$codeAsKey ? $m->getShippingCode() : $m->getShippingId()] = $m->getShippingTitle();
            }
        }
        return $this->_methods;
    }

    public function getVendors($includeInactive=false)
    {
        if (empty($this->_vendors[$includeInactive])) {
            $this->_vendors[$includeInactive] = array();
            $vendors = Mage::getModel('udropship/vendor')->getCollection()
                ->addStatusFilter($includeInactive ? array('I', 'A') : 'A')
                ->setOrder('vendor_name', 'asc');
            foreach ($vendors as $v) {
                $this->_vendors[$includeInactive][$v->getId()] = $v->getVendorName();
            }
        }
        return $this->_vendors[$includeInactive];
    }

    public function getTaxRegions()
    {
        if (!$this->_taxRegions) {
            $collection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter('US')
                ->load();
            $this->_taxRegions = array();
            foreach ($collection as $region) {
                $this->_taxRegions[$region->getRegionId()] = $region->getDefaultName().' ('.$region->getCode().')';
            }
        }
        return $this->_taxRegions;
    }

    public function getVendorVisiblePreferences()
    {
        if (empty($this->_visiblePreferences)) {
            $hlp = Mage::helper('udropship');

            $fieldsets = array();
            foreach (Mage::getConfig()->getNode('global/udropship/vendor/fieldsets')->children() as $code=>$node) {
                $fieldsets[$code] = array(
                    'position' => (int)$node->position,
                    'label' => (string)$node->legend,
                    'value' => array(),
                );
            }
            foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
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

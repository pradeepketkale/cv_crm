<?php

class Craftsvilla_Couponrequest_Block_Adminhtml_Couponrequest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('couponrequestGrid');
      $this->setDefaultSort('couponrequest_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
     
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('couponrequest/couponrequest')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('couponrequest_id', array(
          'header'    => Mage::helper('couponrequest')->__('CouponrequestID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'couponrequest_id',
      ));

      $this->addColumn('shipment_id', array(
          'header'    => Mage::helper('couponrequest')->__('ShipmentID'),
          'align'     =>'left',
          'index'     => 'shipment_id',
      ));
       $this->addColumn('price', array(
          'header'    => Mage::helper('couponrequest')->__('CouponValue'),
          'align'     =>'left',
          'index'     => 'price',
      ));
      $this->addColumn('coupon_code', array(
          'header'    => Mage::helper('couponrequest')->__('CouponCode'),
          'align'     =>'left',
          'index'     => 'coupon_code',
      ));

	  $this->addColumn('expire_date_ofcoupon', array(
          'header'    => Mage::helper('couponrequest')->__('ExpireDateOfCoupon'),
          'align'     =>'left',
          'index'     => 'expire_date_ofcoupon',
      ));
      
      $this->addColumn('created_time', array(
          'header'    => Mage::helper('couponrequest')->__('CreatedTime'),
          'align'     =>'left',
          'index'     => 'created_time',
      ));
            
      $this->addColumn('status_coupon', array(
          'header'    => Mage::helper('couponrequest')->__('CouponStatus'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status_coupon',
          'type'      => 'options',
          'options'   => array(
				1 => 'Requested',
				2 => 'Approved',
				3 => 'Rejected',
          ),
      ));
	 
	  
       
       
        /*$this->addColumn('action',
            array(
                'header'    =>  Mage::helper('couponrequest')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('couponrequest')->__('Edit'),
                        'url'       => array('base'=> '*//*edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));*/
		  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('couponrequest_id');
        $this->getMassactionBlock()->setFormFieldName('couponrequest');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('couponrequest')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('couponrequest')->__('Are you sure?')
        ));
        $statuses = Mage::getSingleton('couponrequest/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('couponrequest')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('couponrequest')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        
 		
        $this->getMassactionBlock()->addItem('update', array(
        'label'    => Mage::helper('couponrequest')->__('Modify Data'),
            'url'      => $this->getUrl('*/*/Update'),
            'additional' => array(
            'visibility' => array(
            'name' => 'shipmentId',
            'class' => 'required-entry ""',
            'required' => true,
            'type' => 'text',
            'label' => Mage::helper('couponrequest')->__('ShipmentId'),//'<font style="color:#ff0000">'.'*'."</font>"),
            
            
           ),array(
            'name' => 'price',
            'type' => 'text',
            'label' => Mage::helper('couponrequest')->__('Price'),
           ),array(
             'name' => 'expireDateOfCoupon',
             'type' => 'date',
             'format' 	  => 'yyyy-MM-dd',
			 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
			 'input_format' =>  Varien_Date::DATE_INTERNAL_FORMAT, 
             'label' => Mage::helper('couponrequest')->__('ExpireDtaeOfCoupon')
             )
           
           )
		 ));
        return $this;
    }

  public function getRowUrl($row)
  {
      //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  

}

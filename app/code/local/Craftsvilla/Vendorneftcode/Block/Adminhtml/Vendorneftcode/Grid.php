<?php

class Craftsvilla_Vendorneftcode_Block_Adminhtml_Vendorneftcode_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('vendorneftcodeGrid');
      $this->setDefaultSort('vendor_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('vendorneftcode/vendorneftcode')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
 	
 	$this->addColumn('vendorinfo_id', array(
          'header'    => Mage::helper('vendorneftcode')->__('Vendorneft ID'),
          'align'     =>'left',
          'index'     => 'vendorinfo_id',
          'width'   => '100px',
      ));    


      $this->addColumn('vendor_id', array(
          'header'    => Mage::helper('vendorneftcode')->__('Vendor ID'),
          'align'     =>'left',
          'index'     => 'vendor_id',
          'width'   => '100px',
      ));

	  
      $this->addColumn('vendor_name', array(
			'header'    => Mage::helper('vendorneftcode')->__('Vendor Name'),
			'width'     => '150px',
			'index'     => 'vendor_name',
      ));
	  $this->addColumn('merchant_id_city', array(
			'header'    => Mage::helper('vendorneftcode')->__('NEFT CODE'),
			'width'     => '150px',
			'index'     => 'merchant_id_city',
      ));

      $this->addColumn('catalog_privileges', array(
			'header'    => Mage::helper('vendorneftcode')->__('Catalog Privileges'),
			'width'     => '150px',
			'index'     => 'catalog_privileges',
      ));

     $this->addColumn('logistics_privileges', array(
			'header'    => Mage::helper('vendorneftcode')->__('Logistics Privileges'),
			'width'     => '150px',
			'index'     => 'logistics_privileges',
      ));

     $this->addColumn('payment_privileges', array(
			'header'    => Mage::helper('vendorneftcode')->__('Payment Privileges'),
			'width'     => '150px',
			'index'     => 'payment_privileges',
      ));
      $this->addColumn('bulk_privileges', array(
      'header'    => Mage::helper('vendorneftcode')->__('Bulk Privileges'),
      'width'     => '150px',
      'index'     => 'bulk_privileges',
      ));
      $this->addColumn('commission_percentage', array(
      'header'    => Mage::helper('vendorneftcode')->__('Commission Percent'),
      'width'     => '150px',
      'index'     => 'commission_percentage',
      ));

     

      /*$this->addColumn('status', array(
          'header'    => Mage::helper('vendorneftcode')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));*/
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('vendorneftcode')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('vendorneftcode')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'vendorinfo_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('vendorneftcode')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('vendorneftcode')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('vendorinfo_id');
        $this->getMassactionBlock()->setFormFieldName('vendorneftcode');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('vendorneftcode')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('vendorneftcode')->__('Are you sure?')
        ));

        
        $this->getMassactionBlock()->addItem('catalogprivileges', array(
             'label'=> Mage::helper('vendorneftcode')->__('Catalog Privileges'),
             'url'  => $this->getUrl('*/*/catalogprivileges'),
			 'additional' => array(
             'visibility' =>array(
								'name' => 'catalog_privileges',
								'class' => 'required-entry',
								'type'  => 'text',
								'label' => Mage::helper('customerreturn')->__('Remark :Use only when catalog privileges is disable'),

						 )
             
             )
        ));

        $this->getMassactionBlock()->addItem('logisticsprivileges', array(
             'label'=> Mage::helper('vendorneftcode')->__('Logistics Privileges'),
             'url'  => $this->getUrl('*/*/logisticsprivileges'),
			 'additional' => array(
             'visibility' =>array(
								'name' => 'logistics_privileges',
								'class' => 'required-entry',
								'type'  => 'text',
								'label' => Mage::helper('customerreturn')->__('Remark :Use only when logistics privileges is disable'),
						 )
             )
        ));


        $this->getMassactionBlock()->addItem('paymentprivileges', array(
             'label'=> Mage::helper('vendorneftcode')->__('Payment Privileges'),
             'url'  => $this->getUrl('*/*/paymentprivileges'),
			 'additional' => array(
             'visibility' =>array(
								'name' => 'payment_privileges',
								'class' => 'required-entry',
								'type'  => 'text',
								'label' => Mage::helper('customerreturn')->__('Remark :Use only when payment privileges is disable'),
						 )
             )
        ));

        //$statuses = Mage::getSingleton('vendorneftcode/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             //'label'=> Mage::helper('vendorneftcode')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('vendorneftcode')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('vendorinfo_id' => $row->getId()));
  }
  public function getGridUrl()
  {
      return $this->getUrl('*/*/grid', array('_current' => true));
  }
  

}

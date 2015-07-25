<?php

class Craftsvilla_Mktvendors_Block_Adminhtml_Mktvendors_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('mktvendorsGrid');
      $this->setDefaultSort('mktvendors_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('mktvendors/mktvendors')->getCollection();
	  $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('mktvendors_id', array(
          'header'    => Mage::helper('mktvendors')->__('Mkt ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'mktvendors_id',
      ));

      $this->addColumn('package_name', array(
          'header'    => Mage::helper('mktvendors')->__('Package Name'),
          'align'     =>'left',
          'index'     => 'package_name',
		  'type'      => 'options',
          'options'   => array(
		  	  4 => 'Other',
			  3 => 'Rs 5000',	
              2 => 'Rs 2000',
              1 => 'Rs 1000',)
      ));
	  $this->addColumn('vendor', array(
          'header'    => Mage::helper('mktvendors')->__('Vendor Id'),
          'align'     =>'left',
          'index'     => 'vendor',
		  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
	  $this->addColumn('paidamount', array(
          'header'    => Mage::helper('mktvendors')->__('Paid Amount'),
          'align'     =>'left',
          'index'     => 'paidamount',
		  'type'  	  => 'price',
		  'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
	  $this->addColumn('balance', array(
          'header'    => Mage::helper('mktvendors')->__('Balance Amount'),
          'align'     =>'left',
          'index'     => 'balance',
		  'type'  	  => 'price',
		  'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
	  $this->addColumn('date_bought', array(
          'header'    => Mage::helper('mktvendors')->__('Date Bought'),
          'align'     => 'left',
          'index'     => 'date_bought',
		  'format' 	  => 'yyyy-MM-dd',
		  'type'      => 'datetime',
      ));
	  $this->addColumn('valid_till', array(
          'header'    => Mage::helper('mktvendors')->__('Valid Till'),
          'align'     => 'left',
          'index'     => 'valid_till',
		  'format' 	  => 'yyyy-MM-dd',
		  'type'      => 'datetime',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('web')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */
		
     /* $this->addColumn('status', array(
          'header'    => Mage::helper('mktvendors')->__('Status'),
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
                'header'    =>  Mage::helper('mktvendors')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('mktvendors')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('mktvendors')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('mktvendors')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('mktvendors_id');
        $this->getMassactionBlock()->setFormFieldName('mktvendors');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('mktvendors')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('mktvendors')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('mktvendors/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('mktvendors')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('mktvendors')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}

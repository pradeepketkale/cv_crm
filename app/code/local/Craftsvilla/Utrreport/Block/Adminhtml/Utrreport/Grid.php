<?php

class Craftsvilla_Utrreport_Block_Adminhtml_Utrreport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('utrreportGrid');
      $this->setDefaultSort('utrreport_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('utrreport/utrreport')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('utrreport_id', array(
          'header'    => Mage::helper('utrreport')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'utrreport_id',
      ));

      $this->addColumn('utrno', array(
          'header'    => Mage::helper('utrreport')->__('UTR Number'),
          'align'     =>'left',
          'index'     => 'utrno',
      ));
	  $this->addColumn('payin_date', array(
          'header'    => Mage::helper('utrreport')->__('Pay In Date'),
          'align'     =>'left',
          'index'     => 'payin_date',
		  'format' 	  => 'yyyy-MM-dd',
		  'type'      => 'date',
      ));
	$this->addColumn('amount', array(
          'header'    => Mage::helper('utrreport')->__('Amount'),
          'align'     =>'left',
          'index'     => 'amount',
		  'type'  => 'price',
          'currency' => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
	  $this->addColumn('balance', array(
          'header'    => Mage::helper('utrreport')->__('Balance Amount'),
          'align'     =>'left',
          'index'     => 'balance',
		  'type'  => 'price',
          'currency' => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));		
	  
	  $this->addColumn('utrpaid', array(
          'header'    => Mage::helper('utrreport')->__('UTR Paid'),
          'align'     =>'left',
          'index'     => 'utrpaid',
		  'type'  	  => 'price',
          'currency' => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
      /*$this->addColumn('status', array(
          'header'    => Mage::helper('utrreport')->__('Status'),
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
                'header'    =>  Mage::helper('utrreport')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('utrreport')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
	//	$this->addExportType('*/*/exportCsv', Mage::helper('utrreport')->__('CSV'));
	//	$this->addExportType('*/*/exportXml', Mage::helper('utrreport')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('utrreport_id');
        $this->getMassactionBlock()->setFormFieldName('utrreport');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('utrreport')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('utrreport')->__('Are you sure?')
        ));
		
		 $this->getMassactionBlock()->addItem('assign', array(
             'label'=> Mage::helper('utrreport')->__('Assign Utr'),
             'url'  => $this->getUrl('*/*/assign', array('_current'=>true)),
        ));
		
		 $this->getMassactionBlock()->addItem('calculatePaid', array(
             'label'=> Mage::helper('utrreport')->__('Calculate Paid'),
             'url'  => $this->getUrl('*/*/calculatePaid', array('_current'=>true)),
        ));
		
		$this->getMassactionBlock()->addItem('complianceReport', array(
             'label'=> Mage::helper('utrreport')->__('Compliance Report'),
             'url'  => $this->getUrl('*/*/complianceReport', array('_current'=>true)),
        ));
		
        /*$statuses = Mage::getSingleton('utrreport/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('utrreport')->__('Change status'),
             'url'  => $this->getUrl('*//*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('utrreport')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));*/
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
public function getGridUrl()
 	{
          return $this->getUrl('*/*/grid', array('_current' => true));
  	}

}

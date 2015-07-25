<?php

class Craftsvilla_Wholesale_Block_Adminhtml_Wholesale_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('wholesaleGrid');
      $this->setDefaultSort('wholesale_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
		$collection = Mage::getModel('wholesale/wholesale')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      /*$this->addColumn('wholesale_id', array(
          'header'    => Mage::helper('wholesale')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'wholesale_id',
      ));*/

      /*$this->addColumn('productid', array(
          'header'    => Mage::helper('wholesale')->__('Product Id'),
          'align'     =>'left',
          'index'     => 'productid',
      ));
	  */
	  $this->addColumn('sku', array(
          'header'    => Mage::helper('wholesale')->__('SKU'),
          'align'     =>'left',
          'index'     => 'sku',
      ));
	  
	  $this->addColumn('productname', array(
          'header'    => Mage::helper('wholesale')->__('Product Name'),
          'align'     =>'left',
          'index'     => 'productname',
      ));

	  $this->addColumn('vendorid', array(
          'header'    => Mage::helper('wholesale')->__('Vendor Name'),
          'align'     =>'left',
          'index'     => 'vendorname',
      ));
	  
	  $this->addColumn('name', array(
          'header'    => Mage::helper('wholesale')->__('Name Of Customer'),
          'align'     =>'left',
          'index'     => 'name',
      ));
	  
	  $this->addColumn('email', array(
          'header'    => Mage::helper('wholesale')->__('Email'),
          'align'     =>'left',
          'index'     => 'email',
      ));
	  
	  $this->addColumn('phone', array(
          'header'    => Mage::helper('wholesale')->__('Phone'),
          'align'     =>'left',
          'index'     => 'phone',
      ));
	  
	  $this->addColumn('quantity', array(
          'header'    => Mage::helper('wholesale')->__('Qty'),
          'align'     =>'left',
          'index'     => 'quantity',
      ));
		
	  $this->addColumn('offer_price', array(
          'header'    => Mage::helper('wholesale')->__('Your Price'),
          'align'     =>'left',
          'index'     => 'offer_price',
      ));
	  
	  $this->addColumn('custom', array(
          'header'    => Mage::helper('wholesale')->__('Customisation'),
          'align'     =>'left',
          'index'     => 'custom',
      ));
	  
	  $this->addColumn('created_date', array(
          'header'    => Mage::helper('wholesale')->__('Created Date'),
          'align'     =>'left',
          'index'     =>'created_date',
          'type'    =>'datetime',
          //'renderer' = new Namespace_Module_Block_Adminhtml_Renderer_Date(),
      ));
	  
	  $this->addColumn('expected_date', array(
          'header'    => Mage::helper('wholesale')->__('Expected Date'),
          'align'     =>'left',
          'index'     =>'expected_date',
          'type'    =>'datetime',
          //'renderer' = new Namespace_Module_Block_Adminhtml_Renderer_Date(),
      )); 	
	  
      $this->addColumn('comments', array(
			'header'    => Mage::helper('wholesale')->__('Comment'),
			'width'     => '150px',
			'index'     => 'comments',
      ));
	  
		 $this->addColumn('vendorquote', array(
				'header'    => Mage::helper('wholesale')->__('Vendor Quote'),
				'width'     => '150px',
				'index'     => 'vendorquote',
		  ));
		
	 $this->addColumn('deliverydate', array(
			  'header'    => Mage::helper('wholesale')->__('Delivery Date'),
			  'align'     =>'left',
			  'index'     =>'deliverydate',
			  'type'    =>'datetime',
		 ));
	  

      $this->addColumn('status', array(
          'header'    => Mage::helper('wholesale')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Open',
              2 => 'Qualified',
			  3 => 'Processing',
			  4 => 'Payment Received',
			  5 => 'Delivered',
			  6 => 'Closed'
          ),
      ));
	  
        $this->addColumn('action', array(
                'header'    =>  Mage::helper('wholesale')->__('Action'),
                'width'     => '100',
            	  'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                  		   array(
                    	'caption'   => Mage::helper('wholesale')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    		)
                		),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
       ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('wholesale')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('wholesale')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('wholesale_id');
        $this->getMassactionBlock()->setFormFieldName('wholesale');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('wholesale')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('wholesale')->__('Are you sure?')
        ));
		//For email customer of wholesale detail
		$this->getMassactionBlock()->addItem('email', array(
             'label'    => Mage::helper('wholesale')->__('Email To Customer'),
             'url'      => $this->getUrl('*/*/email'),
        	    
        ));
		
		

        $statuses = Mage::getSingleton('wholesale/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('wholesale')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('wholesale')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }
// comment for  for edit option on admin page by Dileswar
  public function getRowUrl($row)
  {
 		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
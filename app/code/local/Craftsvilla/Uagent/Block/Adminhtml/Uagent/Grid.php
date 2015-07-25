<?php

class Craftsvilla_Uagent_Block_Adminhtml_Uagent_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('uagentGrid');
      $this->setDefaultSort('agent_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);	
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('uagent/uagent')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('agent_id', array(
          'header'    => Mage::helper('uagent')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'agent_id',
      ));
	  
	  $this->addColumn('created_time', array(
          'header'    => Mage::helper('uagent')->__('Created Date'),
          'align'     => 'left',
          'index'     => 'created_time',
		  'format' 	  => 'yyyy-MM-dd',
		  'type'      => 'datetime',
      ));
	  
	  $this->addColumn('update_time', array(
          'header'    => Mage::helper('uagent')->__('Updated Date'),
          'align'     => 'left',
          'index'     => 'update_time',
		  'format' 	  => 'yyyy-MM-dd',
		  'type'      => 'datetime',
      ));

      $this->addColumn('agent_name', array(
          'header'    => Mage::helper('uagent')->__('Agent Name'),
          'align'     =>'left',
          'index'     => 'agent_name',
      ));
	 
	 $this->addColumn('agent_attn', array(
          'header'    => Mage::helper('uagent')->__('Agent Attn'),
          'align'     =>'left',
          'index'     => 'agent_attn',
      ));
	  $this->addColumn('email', array(
          'header'    => Mage::helper('uagent')->__('Agent Email'),
          'align'     =>'left',
          'index'     => 'email',
      ));
	  $this->addColumn('telephone', array(
          'header'    => Mage::helper('uagent')->__('Cont No.'),
          'align'     =>'left',
          'index'     => 'telephone',
      ));
	  $this->addColumn('street', array(
          'header'    => Mage::helper('uagent')->__('Street'),
          'align'     =>'left',
          'index'     => 'street',
      ));
	  $this->addColumn('city', array(
          'header'    => Mage::helper('uagent')->__('City'),
          'align'     =>'left',
          'index'     => 'city',
      ));
	  $this->addColumn('zip', array(
          'header'    => Mage::helper('uagent')->__('Zip Code'),
          'align'     =>'left',
          'index'     => 'zip',
      ));
	  $this->addColumn('country_id', array(
          'header'    => Mage::helper('uagent')->__('Zip Code'),
          'align'     =>'left',
          'index'     => 'country_id',
      ));
	  $this->addColumn('agent_commission', array(
            'header'    => Mage::helper('uagent')->__('Commission'),
            'index'     => 'agent_commission',
           
        ));
	    
	  $this->addColumn('bank_account_number', array(
          'header'    => Mage::helper('uagent')->__('Bank a/c no.'),
          'align'     =>'left',
          'index'     => 'bank_account_number',
      ));
	  $this->addColumn('bank_name', array(
          'header'    => Mage::helper('uagent')->__('Name Of Bank'),
          'align'     =>'left',
          'index'     => 'bank_name',
      ));
	  $this->addColumn('check_pay_to', array(
          'header'    => Mage::helper('uagent')->__('Check To Pay '),
          'align'     =>'left',
          'index'     => 'check_pay_to',
      ));
	  
	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('uagent')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */
	$this->addColumn('closing_balance', array(
            'header'    => Mage::helper('uagent')->__('Closing Balance'),
			'index'     => 'closing_balance',
            'currency'  => 'base_currency_code',
			'currency_code' => Mage::getStoreConfig('currency/options/base'),
			));
      $this->addColumn('status', array(
          'header'    => Mage::helper('uagent')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        //$this->addColumn('action',
           // array(
              //  'header'    =>  Mage::helper('uagent')->__('Action'),
              //  'width'     => '100',
              //  'type'      => 'action',
              //  'getter'    => 'getId',
              //  'actions'   => array(
              //      array(
              //          'caption'   => Mage::helper('uagent')->__('Edit'),
              //          'url'       => array('base'=> '*/*/edit'),
              //          'field'     => 'id'
              //      )
             //   ),
              //  'filter'    => false,
             //   'sortable'  => false,
              //  'index'     => 'stores',
              //  'is_system' => true,
        //));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('uagent')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('uagent')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('agent_id');
        $this->getMassactionBlock()->setFormFieldName('uagent');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('uagent')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('uagent')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('uagent/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('uagent')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('uagent')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
		$this->getMassactionBlock()->addItem('agentcommission', array(
             'label'=> Mage::helper('uagent')->__('Change Commission'),
             'url'  => $this->getUrl('*/*/agentcommission', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'agcommission',
                         'type' => 'text',
                         'label' => Mage::helper('uagent')->__('Commission Percentage.')
                     )
             )
        ));
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

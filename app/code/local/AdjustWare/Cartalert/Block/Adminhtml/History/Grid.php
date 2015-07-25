<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.4.1.x-1.5.0.1 - 06/07/11
 * Package:     AdjustWare_Cartalert_3.0.5_0.2.3_183688
 * Purchase ID: Y6M1PHMt9YjaYLDNXoI3HVQQ5WLuo3S19F0xW5tLYM
 * Generated:   2012-02-06 21:31:16
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/History/Grid.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ kmOWayrDVyBQprpA('0367a566126634d22e467cbd1b551e2a'); ?><?php
/**
 * @author Adjustware
 */ 
class AdjustWare_Cartalert_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('historyGrid');
      $this->setDefaultSort('id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('adjcartalert/history')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $hlp =  Mage::helper('adjcartalert'); 
    $this->addColumn('id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'id',
    ));

    $this->addColumn('sent_at', array(
        'header'    => $hlp->__('Sent On'),
        'index'     => 'sent_at',
        'type'      => 'datetime', 
        'width'     => '150px',
        'gmtoffset' => true,
        'default'	=> ' ---- ',
    ));

    $this->addColumn('recovered_at', array(
        'header'    => $hlp->__('Recovered On'),
        'index'     => 'recovered_at',
        'type'      => 'datetime', 
        'width'     => '150px',
        'gmtoffset' => true,
        'default'	=> ' ---- ',
    ));

    $this->addColumn('recovered_from', array(
        'header'    => $hlp->__('Remote IP'),
        'index'     => 'recovered_from',
        'width'     => '150px',
    ));
    
    $this->addColumn('follow_up', array(
        'header'    => $hlp->__('Follow Up'),
        'index'     => 'follow_up',
        'type'      => 'options',
        'options'   => array(
    		'first' 	=> $hlp->__('First'),
    		'second' 	=> $hlp->__('Second'),
    		'third' 	=> $hlp->__('Third'),
         ),
        'width'     => '100px',
    ));
    
    $this->addColumn('customer_email', array(
        'header'    => $hlp->__('Customer E-mail'),
        'index'     => 'customer_email',
    ));

    $this->addColumn('customer_name', array(
        'header'    => $hlp->__('Customer Name'),
        'index'     => 'customer_name',
    ));
    
    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  protected function _prepareMassaction(){
    $this->setMassactionIdField('id');
    $this->getMassactionBlock()->setFormFieldName('cartalert');
    
    $this->getMassactionBlock()->addItem('delete', array(
         'label'    => Mage::helper('adjcartalert')->__('Delete'),
         'url'      => $this->getUrl('*/*/massDelete'),
         'confirm'  => Mage::helper('adjcartalert')->__('Are you sure?')
    ));
    
    return $this; 
  }

} } 
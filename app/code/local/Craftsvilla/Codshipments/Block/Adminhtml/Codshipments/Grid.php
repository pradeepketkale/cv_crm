<?php

class Craftsvilla_Codshipments_Block_Adminhtml_Codshipments_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('codshipmentsGrid');
      $this->setDefaultSort('codshipments_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }
  
  protected function _getCollectionClass()
    {
        return 'sales/order_collection';
    }
	
	public function t($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('sales/order_shipment')->getCollection();
      $this->setCollection($collection);
	
	   $collection->getSelect()
                ->join(array('o'=>'sales_flat_order'), 'o.entity_id=main_table.order_id', array('status'=>'o.status'))
                ->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=o.entity_id and a.address_type="shipping"', array('firstname', 'lastname', 'a.telephone as custphone', 'country_id', 'city', 'region','a.street as custcity','postcode'))
                ->join(array('b'=>'sales_flat_order_payment'), 'b.parent_id=o.entity_id and b.method="cashondelivery"', array('b.method'))
				->join(array('d'=>'sales_flat_shipment_track'), 'd.parent_id=main_table.entity_id', array('d.number'))
				->join(array('e'=>'sales_flat_order_item'), 'e.item_id=main_table.order_id', array('e.base_discount_amount'))
				->join(array('f'=>'udropship_vendor'), 'f.vendor_id=main_table.udropship_vendor', array('vendor_name', 'f.street as vendstreet', 'f.telephone as vendphone'))
				->columns(new Zend_Db_Expr("CONCAT(`firstname`, ' ',`lastname`) AS customer_name"))
				->columns(array('main_table.cod_tax_amount AS cod_amount'))
				->where('main_table.udropship_status = 24')
				->group(array('main_table.entity_id'));
				//echo $collection->getSelect()->__ToString(); exit;
		foreach($collection as $collect)
		{
			$collect['base_total_value'];
			$collect['itemised_total_shippingcost'];
			
			$orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
		$orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
			                      ->where('main_table.parent_id='.$collect['entity_id'])
								  ->columns('SUM(a.base_discount_amount) AS amount');
								
          foreach($orderitemdata as $_orderitemdata)
			{
			   $discountamount = $_orderitemdata['amount'];
			}
		   $vendorid=$collect['udropship_vendor'];
			 $vendor = Mage::getModel('udropship/vendor')->load($vendorid);
			 $custom = Zend_Json::decode($vendor->getCustomVarsCombined());
		   $codfee = $custom['cod_fee'];
			$amountCOD = $collect['base_total_value'] + $collect['itemised_total_shippingcost'] - $discountamount + $codfee;
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$productquery = "update `sales_flat_shipment` set `cod_tax_amount` = '".$amountCOD."' WHERE `increment_id`= ".$collect['increment_id'];
					$writequery = $write->query($productquery);	
		}

	//echo $collection->getSelect()->__ToString(); exit;
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('number', array(
          'header'    => Mage::helper('codshipments')->__('Waybill'),
          'align'     =>'left',
          'index'     => 'number',
      ));
	  $this->addColumn('increment_id', array(
          'header'    => Mage::helper('codshipments')->__('Order No'),
          'align'     =>'left',
          'index'     => 'increment_id',
		   'type'      => 'text',
		   'filter_index'=>'main_table.increment_id',
      ));
	  $this->addColumn('customer_name', array(
          'header'    => Mage::helper('codshipments')->__('Consignee Name'),
          'align'     =>'left',
            'index'     => 'customer_name',
		 'filter_index' => 'CONCAT(`firstname`, " ",`lastname`)',
      ));
	  $this->addColumn('city', array(
          'header'    => Mage::helper('codshipments')->__('City'),
          'align'     =>'left',
          'index'     => 'city',
      ));
	  $this->addColumn('region', array(
          'header'    => Mage::helper('codshipments')->__('State'),
          'align'     =>'left',
          'index'     => 'region',
      ));
	  $this->addColumn('country_id', array(
          'header'    => Mage::helper('codshipments')->__('Country'),
          'align'     =>'left',
          'index'     => 'country_id',
      ));
	  $this->addColumn('custcity', array(
          'header'    => Mage::helper('codshipments')->__('Address'),
          'align'     =>'left',
          'index'     => 'custcity',
		  'filter_index' => 'a.street',
      ));
	  $this->addColumn('postcode', array(
          'header'    => Mage::helper('codshipments')->__('Pincode'),
          'align'     =>'left',
          'index'     => 'postcode',
      ));
	  $this->addColumn('phone', array(
          'header'    => Mage::helper('codshipments')->__('Phone'),
          'align'     =>'left',
          'index'     => 'custphone',
		   'filter_index' => 'a.telephone',
      ));
	  $this->addColumn('custphone', array(
          'header'    => Mage::helper('codshipments')->__('Mobile'),
          'align'     =>'left',
          'index'     => 'custphone',
		   'filter_index' => 'a.telephone',
      ));
	  $this->addColumn('method', array(
          'header'    => Mage::helper('codshipments')->__('Payment Mode'),
          'align'     =>'left',
          'index'     => 'method',
      ));
	  $this->addColumn('package_amount', array(
          'header'    => Mage::helper('codshipments')->__('Package Amount'),
             'align'     =>'left',
          'index'     => 'cod_amount',
		  'precision' => 2,
		   'filter_index' => 'main_table.cod_tax_amount',
	  	   ));
	  $this->addColumn('cod_amount', array(
          'header'    => Mage::helper('codshipments')->__('Cod Amount'),
          'align'     =>'left',
		   'index' => 'cod_amount',
		    'filter_index' => 'main_table.cod_tax_amount',
       
      ));
	  $this->addColumn('productshipped', array(
          'header'    => Mage::helper('codshipments')->__('Product To Be Shipped'),
          'align'     =>'left',
          'index'     => 'productshipped',
		  'default'      => 'Handicraft Item',
        
      ));
	  $this->addColumn('vendor_name', array(
          'header'    => Mage::helper('codshipments')->__('Shipping Client'),
          'align'     =>'left',
          'index'     => 'vendor_name',
      ));
	  $this->addColumn('vendstreet', array(
          'header'    => Mage::helper('codshipments')->__('Shipping Client Address'),
          'align'     =>'left',
          'index'     => 'vendstreet',
		  'filter_index' => 'f.street',
      ));
	  $this->addColumn('vendphone', array(
          'header'    => Mage::helper('codshipments')->__('Shipping Client Phone'),
          'align'     =>'left',
          'index'     => 'vendphone',
		   'filter_index' => 'f.telephone',
      ));

	 	//$this->addExportType('*/*/exportCsv', Mage::helper('codshipments')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('codshipments')->__('XML'));
	  
      return parent::_prepareColumns();
  }
  


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('codshipments_id');
        $this->getMassactionBlock()->setFormFieldName('codshipments');

     
        return $this;
    }

 /* public function getRowUrl($row)
  {*/
     // return $this->getUrl('*/*/edit', array('id' => $row->getId()));
 // }

}
<?php

class Craftsvilla_Homepage_Block_Adminhtml_Homepage2_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('homepageGrid');
      $this->setDefaultSort('homepage_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
	 
  }
  

  protected function _prepareCollection()
  {
               		$collection = Mage::getModel('catalog/product')->getCollection();
	  $this->setCollection($collection);
	  $collection->getSelect()
	            ->join(array('o'=>'catalog_product_entity_varchar'), 'o.entity_id=e.entity_id AND o.attribute_id="56" AND o.store_id=0', array('name'=>'value'))
				//->join(array('p'=>'catalog_product_entity_text'), 'p.entity_id=e.entity_id AND p.attribute_id="58"', array('short_description'=>'value'))
				//->join(array('q'=>'catalog_product_entity_decimal'), 'q.entity_id=e.entity_id AND q.attribute_id="60"', array('price'=>'value'))
				->join(array('r'=>'catalog_product_entity_varchar'), 'r.entity_id=e.entity_id AND r.attribute_id="70" AND r.store_id=0', array('thumbnail'=>'value'))
				->where('e.category_id1=991 OR e.category_id2=991 OR e.category_id3=991 OR e.category_id4=991 AND e.store_id=1');
				//->join(array('s'=>'wishlist_item'), 's.product_id=main_table.product_id', array('wishlist_id'=>'wishlist_id'))
				//->join(array('t'=>'wishlist'), 't.wishlist_id=s.wishlist_id', array('customer_id'=>'customer_id'))
				//->joinLeft(array('u' =>'customer_entity'),'u.entity_id = t.customer_id', array('email'=>'email'));
		//	echo   $collection->getSelect()->__toString(); exit;	

foreach($collection as $collect)
		{			
			   $prid = $collect['entity_id'];
			  $product = Mage::getModel('catalog/product')->load($prid);
			  //echo '<pre>';print_r($product);exit;exit;
			   $purl = 'catalog/product/view/id/'.$prid;
			  $image = '<a href="'.Mage::getBaseUrl().$purl.'" target="_blank"><img src="'.Mage::helper('catalog/image')->init($product, 'image')->resize(75, 75).'" alt=""  border="0"/></a>';
		}
      return parent::_prepareCollection();
	 
  }

  protected function _prepareColumns()
  {
     /* $this->addColumn('homepage_id', array(
          'header'    => Mage::helper('homepage')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'homepage_id',
      ));*/
	  
     
      $this->addColumn('sku', array(
			'header'    => Mage::helper('homepage')->__('SKU'),
			'width'     => '200px',
			'index'     => 'sku',
      ));
	  
	   $this->addColumn('name', array(
			'header'    => Mage::helper('homepage')->__('Name'),
			'width'     => '200px',
			'index'     => 'name',
      ));
	  
	   $this->addColumn('price', array(
			'header'    => Mage::helper('homepage')->__('Price'),
			'width'     => '200px',
			'index'     => 'price',
      ));
	  
	 /*  $this->addColumn('email', array(
			'header'    => Mage::helper('homepage')->__('Customer Email'),
			'width'     => '200px',
			'index'     => 'email',
			'filter_index' => $email
      ));*/
	  
  $this->addColumn('image', array(
          'header'    => Mage::helper('homepage')->__('Image'),
          'align'     =>'left',
          'index'     => 'thumbnail',
		  'type' => 'image',
		  //'renderer'  => new Craftsvilla_Homepage_Block_Adminhtml_Renderer_Image,
		  'width'     => '50px',
		  'align' => 'center',
		  'filter_index' => $image
      ));
	  
	 /*  $this->addColumn('status', array(
          'header'    => Mage::helper('homepage')->__('Status'),
          'align'     =>'left',
		  'width'     => '200px',
          'index'     => 'status',
		  'type'      => 'text',
		  'value' => 'Assigned',
          'options'   => array(1 => 'Assigned',2 => 'Not Assigned')
      ));*/
	  
                   
		$this->addExportType('*/*/exportCsv', Mage::helper('homepage')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('homepage')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('sku');
        $this->getMassactionBlock()->setFormFieldName('homepage');
		
		$this->getMassactionBlock()->addItem('showitem', array(
             'label'    => Mage::helper('homepage')->__('Show Items'),
             'url'      => $this->getUrl('homepage/adminhtml_homepage/showitem'),
			 'additional' => array(
                         'name' => 'emailid',
                         'type' => 'text',
                         'label' => Mage::helper('homepage')->__('Customer Email Id'),
                    
             )
               ));
			   
		 array_unshift($reject, array('label'=>'', 'value'=>''));
         $this->getMassactionBlock()->addItem('showitem', array(
             'label'    => Mage::helper('homepage')->__('Show Items'),
             'url'      => $this->getUrl('homepage/adminhtml_homepage/showitem', array('_current'=>true)),
			 'additional' => array(
                    'visibility' => array(
                         'name' => 'emailid',
                         'type' => 'text',
                         'class' => 'required-entry',
                         'label' => Mage::helper('homepage')->__('Customer Email Id'),
                       //  'values' => $this->getUrl('*/*/reject', array('_current'=>true)),
                     )
             )
               ));	

         $this->getMassactionBlock()->addItem('addtohomepage', array(
             'label'    => Mage::helper('homepage')->__('Add To Home Page'),
             'url'      => $this->getUrl('homepage/adminhtml_homepage/addtohomepage'),
               ));
			   
		 $this->getMassactionBlock()->addItem('removefromhomepage', array(
             'label'    => Mage::helper('homepage')->__('Remove From Home Page'),
             'url'      => $this->getUrl('homepage/adminhtml_homepage2/removefromhomepage'),
               ));
		
		
			   
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('homepage')->__('Delete'),
             'url'      => $this->getUrl('homepage/adminhtml_homepage2/massDelete'),
             'confirm'  => Mage::helper('homepage')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('homepage/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('homepage')->__('Change status'),
             'url'  => $this->getUrl('homepage/adminhtml_homepage/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('homepage')->__('Status'),
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
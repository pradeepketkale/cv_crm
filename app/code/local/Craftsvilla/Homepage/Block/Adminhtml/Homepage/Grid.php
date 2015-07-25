<?php

class Craftsvilla_Homepage_Block_Adminhtml_Homepage_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('homepageGrid');
      $this->setDefaultSort('homepage_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
	  $this->setUseAjax(true);
	 
  }

  protected function _prepareCollection()
  {
	 
	  $collection = Mage::getModel('homepage/homepage')->getCollection();
	  $this->setCollection($collection);
	  $collection->getSelect()
	            ->joinLeft(array('o'=>'catalog_product_entity_varchar'), 'o.entity_id=main_table.product_id AND o.attribute_id="56"', array('name'=>'value'))
				->joinLeft(array('p'=>'catalog_product_entity_text'), 'p.entity_id=main_table.product_id AND p.attribute_id="58"', array('short_description'=>'value'))
				->joinLeft(array('q'=>'catalog_product_entity_decimal'), 'q.entity_id=main_table.product_id AND q.attribute_id="60"', array('price'=>'value'))
				->joinLeft(array('r'=>'catalog_product_entity_varchar'), 'r.entity_id=main_table.product_id AND r.attribute_id="70"', array('thumbnail'=>'value'));
				//->join(array('s'=>'wishlist_item'), 's.product_id=main_table.product_id', array('wishlist_id'=>'wishlist_id'))
				//->join(array('t'=>'wishlist'), 't.wishlist_id=s.wishlist_id', array('customer_id'=>'customer_id'))
				//->joinLeft(array('u' =>'customer_entity'),'u.entity_id = t.customer_id', array('email'=>'email'));
			//	echo $collection->getSelect()->__ToString(); exit;	
			foreach($collection as $collect)
		{
			  $prid = $collect['product_id'];
			  $product = Mage::getModel('catalog/product')->load($prid);
		      $purl = 'catalog/product/view/id/'.$prid;
			 $image = '<a href="'.Mage::getBaseUrl().$purl.'" target="_blank"><img src="'.Mage::helper('catalog/image')->init($product, 'image')->resize(75, 75).'" alt=""  border="0"/></a>';
		}
      return parent::_prepareCollection();
	 
  }

  protected function _prepareColumns()
  {
      $this->addColumn('homepage_id', array(
          'header'    => Mage::helper('homepage')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'homepage_id',
      ));
	  
     
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
	   $this->addColumn('description', array(
			'header'    => Mage::helper('homepage')->__('Description'),
			'width'     => '200px',
			'index'     => 'short_description',
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
		//  'renderer'  => new Craftsvilla_Homepage_Block_Adminhtml_Renderer_Image,
		  'width'     => '50px',
		  'align' => 'center',
		  'filter_index' => $image
      ));
	  
	   $this->addColumn('status', array(
          'header'    => Mage::helper('homepage')->__('Status'),
          'align'     => 'left',
          'width'     => '200px',
          'index'     => 'status',
		  'type'      => 'options',
		  'values' => 2,
          'options'   => array(
              1 => 'Assigned',
              2 => 'Not Assigned',
		
			 
          ),
      ));
	  
                   
		$this->addExportType('*/*/exportCsv', Mage::helper('homepage')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('homepage')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('homepage_id');
        $this->getMassactionBlock()->setFormFieldName('homepage');
		
		$this->getMassactionBlock()->addItem('showitem', array(
             'label'    => Mage::helper('homepage')->__('Show Items'),
             'url'      => $this->getUrl('*/*/showitem'),
			 'additional' => array(
                         'name' => 'emailid',
                         'type' => 'text',
                         'label' => Mage::helper('homepage')->__('Customer Email Id'),
                    
             )
               ));
			   
		 array_unshift($reject, array('label'=>'', 'value'=>''));
         $this->getMassactionBlock()->addItem('showitem', array(
             'label'    => Mage::helper('homepage')->__('Show Items'),
             'url'      => $this->getUrl('*/*/showitem', array('_current'=>true)),
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
             'url'      => $this->getUrl('*/*/addtohomepage'),
               ));
			   
		 $this->getMassactionBlock()->addItem('removefromhomepage', array(
             'label'    => Mage::helper('homepage')->__('Remove From Home Page'),
             'url'      => $this->getUrl('*/*/removefromhomepage'),
               ));
	
			  $this->getMassactionBlock()->addItem('showhomepageproduct', array(
             'label'    => Mage::helper('homepage')->__('Show Home Page Products'),
            'url'      => $this->getUrl('homepage/adminhtml_homepage2/showhomepageproduct'),
               ));  
   $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('homepage')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('homepage')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('homepage/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('homepage')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
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
	public function getGridUrl()
 	{
          return $this->getUrl('*/*/grid', array('_current' => true));
  	}

}

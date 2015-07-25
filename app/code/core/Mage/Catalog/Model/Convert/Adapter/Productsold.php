<?php
class Mage_Catalog_Model_Convert_Adapter_Products extends Mage_Eav_Model_Convert_Adapter_Entity {

	const MULTI_DELIMITER = ',';
    const ENTITY          = 'catalog_product_import';

	protected $_productModel;
    protected $_productTypes;
    protected $_productAttributeSets;
    protected $_stores;
    protected $_attributes = array();
    protected $_configs = array();
    protected $_requiredFields = array();
    protected $_ignoreFields = array();
    protected $_imageFields = array();
    protected $_inventorySimpleFields = array();
    protected $_inventoryOtherFields = array();
    protected $_toNumber = array();

    public function load()
    {
        $attrFilterArray = array();
        $attrFilterArray ['name']           = 'like';
        $attrFilterArray ['sku']            = 'like';
        $attrFilterArray ['type']           = 'eq';
        $attrFilterArray ['attribute_set']  = 'eq';
        $attrFilterArray ['visibility']     = 'eq';
        $attrFilterArray ['status']         = 'eq';
        $attrFilterArray ['price']          = 'fromTo';
        $attrFilterArray ['qty']            = 'fromTo';
        $attrFilterArray ['store_id']       = 'eq';

        $attrToDb = array(
            'type'          => 'type_id',
            'attribute_set' => 'attribute_set_id'
        );

        $filters = $this->_parseVars();

        if ($qty = $this->getFieldValue($filters, 'qty')) {
            $qtyFrom = isset($qty['from']) ? $qty['from'] : 0;
            $qtyTo   = isset($qty['to']) ? $qty['to'] : 0;

            $qtyAttr = array();
            $qtyAttr['alias']       = 'qty';
            $qtyAttr['attribute']   = 'cataloginventory/stock_item';
            $qtyAttr['field']       = 'qty';
            $qtyAttr['bind']        = 'product_id=entity_id';
            $qtyAttr['cond']        = "{{table}}.qty between '{$qtyFrom}' AND '{$qtyTo}'";
            $qtyAttr['joinType']    = 'inner';
            $this->setJoinField($qtyAttr);
        }

        parent::setFilter($attrFilterArray, $attrToDb);

        if ($price = $this->getFieldValue($filters, 'price')) {
            $this->_filter[] = array(
                'attribute' => 'price',
                'from'      => $price['from'],
                'to'        => $price['to']
            );
            $this->setJoinAttr(array(
                'alias'     => 'price',
                'attribute' => 'catalog_product/price',
                'bind'      => 'entity_id',
                'joinType'  => 'LEFT'
            ));
        }
        return parent::load();
    }

    public function getProductModel()
    {
        if (is_null($this->_productModel)) {
            $productModel = Mage::getModel('catalog/product');
            $this->_productModel = Mage::objects()->save($productModel);
        }
        return Mage::objects()->load($this->_productModel);
    }

    public function getAttribute($code)
    {
        if (!isset($this->_attributes[$code])) {
            $this->_attributes[$code] = $this->getProductModel()->getResource()->getAttribute($code);
        }
        if ($this->_attributes[$code] instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
            $applyTo = $this->_attributes[$code]->getApplyTo();
            if ($applyTo && !in_array($this->getProductModel()->getTypeId(), $applyTo)) {
                return false;
            }
        }
        return $this->_attributes[$code];
    }

    public function getProductTypes()
    {
        if (is_null($this->_productTypes)) {
            $this->_productTypes = array();
            $options = Mage::getModel('catalog/product_type')->getOptionArray();
            foreach ($options as $k => $v) {
                $this->_productTypes[$k] = $k;
            }
        }
        return $this->_productTypes;
    }

    public function getProductAttributeSets()
    {
        if (is_null($this->_productAttributeSets)) {
            $this->_productAttributeSets = array();

            $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
            $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter($entityTypeId);
            foreach ($collection as $set) {
                $this->_productAttributeSets[$set->getAttributeSetName()] = $set->getId();
            }
        }
        return $this->_productAttributeSets;
    }

    protected function _initStores ()
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores(true, true);
            foreach ($this->_stores as $code => $store) {
                $this->_storesIdCode[$store->getId()] = $code;
            }
        }
    }

    public function getStoreByCode($store)
    {
        $this->_initStores();
        if (isset($this->_stores[$store])) {
            return $this->_stores[$store];
        }
        return false;
    }

    public function getStoreById($id)
    {
        $this->_initStores();
		if (isset($this->_storesIdCode[$id])) {
            return $this->getStoreByCode($this->_storesIdCode[$id]);
        }
        return false;
    }

    public function parse()
    {
        $batchModel = Mage::getSingleton('dataflow/batch');
        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds = $batchImportModel->getIdCollection();

        foreach ($importIds as $importId) {
            //print '<pre>'.memory_get_usage().'</pre>';
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();
			$this->saveRow($importData);
        }
    }

    protected $_productId = '';

    public function __construct()
    {
        foreach (Mage::getConfig()->getFieldset('catalog_product_dataflow', 'admin') as $code=>$node){
            if ($node->is('inventory')) {
                $this->_inventorySimpleFields[] = $code;
                if ($node->is('use_config')) {
                    $this->_inventorySimpleFields[] = 'use_config_'.$code;
                    $this->_configs[] = $code;
					
                }
                if ($node->is('inventory_other')){
                    $this->_inventoryOtherFields[] = $code;
                }
            }
            if ($node->is('required')){
                $this->_requiredFields[] = $code;
            }
            if ($node->is('ignore')){
                $this->_ignoreFields[] = $code;
            }
            if ($node->is('img')){
                $this->_imageFields[] = $code;
            }
            if ($node->is('to_number')){
                $this->_toNumber[] = $code;
            }
        }

        $this->setVar('entity_type', 'catalog/product');
        if (!Mage::registry('Object_Cache_Product')) {
            $this->setProduct(Mage::getModel('catalog/product'));
        }

        if (!Mage::registry('Object_Cache_StockItem')){
            $this->setStockItem(Mage::getModel('cataloginventory/stock_item'));
        }
    }

	protected function _getCollectionForLoad($entityType)
    {
        $collection = parent::_getCollectionForLoad($entityType)->setStoreId($this->getStoreId())->addStoreFilter($this->getStoreId());
        return $collection;
	}

    public function setProduct(Mage_Catalog_Model_Product $object)
    {
        $id = Mage::objects()->save($object);
        Mage::register('Object_Cache_Product', $id);
    }

    public function getProduct()
    {
        return Mage::objects()->load(Mage::registry('Object_Cache_Product'));
    }

    public function setStockItem(Mage_CatalogInventory_Model_Stock_Item $object)
    {
        $id = Mage::objects()->save($object);
        Mage::register('Object_Cache_StockItem', $id);
    }
	
	public function setProductTypeInstance(Mage_Catalog_Model_Product $product)
	{
		$type = $product->getTypeId();
		if (!isset($this->_productTypeInstances[$type])) {
			$this->_productTypeInstances[$type] = Mage::getSingleton('catalog/product_type')
				->factory($product, true);
		}
		$product->setTypeInstance($this->_productTypeInstances[$type], true);
		return $this;
	}

    public function getStockItem()
    {
        return Mage::objects()->load(Mage::registry('Object_Cache_StockItem'));
    }
    public function save()
    {
		$stores = array();
        foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
            $stores[(int)$storeNode->system->store->id] = $storeNode->getName();
        }

        $collections = $this->getData();
		
        if ($collections instanceof Mage_Catalog_Model_Entity_Product_Collection) {
            $collections = array($collections->getEntity()->getStoreId()=>$collections);
        } elseif (!is_array($collections)) {
            $this->addException(Mage::helper('catalog')->__('No product collections found'), Mage_Dataflow_Model_Convert_Exception::FATAL);
        }
        //$stockItems = $this->getInventoryItems();
        $stockItems = Mage::registry('current_imported_inventory');
        if ($collections) foreach ($collections as $storeId=>$collection) {
            $this->addException(Mage::helper('catalog')->__('Records for "'.$stores[$storeId].'" store found'));

            if (!$collection instanceof Mage_Catalog_Model_Entity_Product_Collection) {
                $this->addException(Mage::helper('catalog')->__('Product collection expected'), Mage_Dataflow_Model_Convert_Exception::FATAL);
            }
            try {
                $i = 0;
                foreach ($collection->getIterator() as $model) {
                    $new = false;
                    // if product is new, create default values first
                    if (!$model->getId()){
                        $new = true;
                        $model->save();

                        if (0 !== $storeId){
                            $data = $model->getData();
                            $default = Mage::getModel('catalog/product');
                            $default->setData($data);
                            $default->setStoreId(0);
                            $default->save();
                            unset($default);
                        } // end
                    }
                    if (!$new || 0!==$storeId){
                        if (0!==$storeId){
                            Mage::getResourceSingleton('catalog_entity/convert')->addProductToStore($model->getId(), $storeId);
                        }
                        $model->save();
                    }

                    if (isset($stockItems[$model->getSku()]) && $stock = $stockItems[$model->getSku()]){
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($model->getId());
                        $stockItemId = $stockItem->getId();

                        if (!$stockItemId){
                            $stockItem->setData('product_id', $model->getId());
                            $stockItem->setData('stock_id', 1);
                            $data = array();
                        } else {
                            $data = $stockItem->getData();
                        }

                        foreach($stock as $field => $value){
                            if (!$stockItemId) {
                                if (in_array($field, $this->_configs)){
                                    $stockItem->setData('use_config_'.$field, 0);
                                }
                                $stockItem->setData($field, $value?$value:0);
                            } else{

                                if (in_array($field, $this->_configs)){
                                    if ($data['use_config_'.$field] == 0){
                                        $stockItem->setData($field, $value?$value:0);
                                    }
                                }else{
                                    $stockItem->setData($field, $value?$value:0);
                                }
                            }
                        }
                        $stockItem->save();
                        unset($data);
                        unset($stockItem);
                        unset($stockItemId);
                    }
                    unset($model);
                    $i++;
                }
                $this->addException(Mage::helper('catalog')->__("Saved ".$i." record(s)"));
            } catch (Exception $e){
                if (!$e instanceof Mage_Dataflow_Model_Convert_Exception){
                    $this->addException(Mage::helper('catalog')->__('Problem saving the collection, aborting. Error: %s', $e->getMessage()),
                        Mage_Dataflow_Model_Convert_Exception::FATAL);
                }
            }
        }
        //unset(Zend::unregister('imported_stock_item'));
        unset($collections);
        return $this;
    }
	
    public function saveRow(array $importData)
    {
		//print_r($importData);
		$product = new Mage_Catalog_Model_Product();
		$product = $this->getProductModel();
        $product->setData(array());
		
        if ($stockItem = $product->getStockItem()){
            $stockItem->setData(array());
        }
		
		if (empty($importData['store'])){
            if (!is_null($this->getBatchParams('store'))){
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else{
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'store');
                Mage::throwException($message);
            }
        } else{
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false){
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists', $importData['store']);
            Mage::throwException($message);
        }
        if (empty($importData['sku'])){
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
		//$product->setStoreId(0);
        $productId = $product->getIdBySku($importData['sku']);

        if ($productId){
            $product->load($productId);
        }
        else{
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();

            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])){
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'type');
                Mage::throwException($message);
            }
            $product->setTypeId($productTypes[strtolower($importData['type'])]);
          
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])){
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'attribute_set');
                Mage::throwException($message);
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

            foreach ($this->_requiredFields as $field){
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()){
                    $message = Mage::helper('catalog')->__('Skip import row, required field "%s" for new products not defined', $field);
                    Mage::throwException($message);
                }
            }
        }
		
		//================================================
		// this part handles configurable products and links
		//================================================
		
		if ($importData ['type'] == 'configurable'){
			//print_r($importData);
			//$product->setIsInStock(1);
			$product->setCanSaveConfigurableAttributes(true);
			$configAttributeCodes = $this->userCSVDataAsArray($importData['config_attributes']);
			$usingAttributeIds = array();
			//$importData ['qty'] == 1;

			//Check the product's super attributes (see catalog_product_super_attribute table), and make a determination that way.
			
			$cspa  = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
			$attr_codes = array();
			if(isset($cspa) && !empty($cspa)){ //found attributes
				foreach($cspa as $cs_attr){
				//$attr_codes[$cs_attr['attribute_id']] = $cs_attr['attribute_code'];
					$attr_codes[] = $cs_attr['attribute_id'];
				}
			}
			//print_r($attr_codes);

			foreach($configAttributeCodes as $attributeCode){
				$attribute = $product->getResource()->getAttribute($attributeCode);
				if ($product->getTypeInstance()->canUseAttribute($attribute)){
				//echo $attribute->getAttributeId();
				//print_r($attr_codes);
					if (!in_array($attribute->getAttributeId(),$attr_codes)) { // fix for duplicating attributes error
						$usingAttributeIds[] = $attribute->getAttributeId();
					}
				}
			}
			if (!empty($usingAttributeIds)){
				$product->getTypeInstance()->setUsedProductAttributeIds($usingAttributeIds);
				
				//$product->setConfigurableAttributesData($product->getTypeInstance()->getConfigurableAttributesAsArray());
				//FS2010-02-15 The label MUST be set or we'll get a MySQL error
				$configurableAttributesArray = $product->getTypeInstance()->getConfigurableAttributesAsArray();
				foreach($configurableAttributesArray as &$configurableAttributeArray){
							
					//Set a number of default values
					$configurableAttributeArray['use_default']     = 1;
					$configurableAttributeArray['position']        = 0;

					//Use the frontend_label as label, if available        
					if(isset($configurableAttributeArray['frontend_label'])){
						$configurableAttributeArray['label'] = $configurableAttributeArray['frontend_label'];
						continue;
					}
					$configurableAttributeArray['label'] = $configurableAttributeArray['attribute_code']; //Use the attribute_code as a label
				}
				$product->setConfigurableAttributesData($configurableAttributesArray);
				$product->setCanSaveConfigurableAttributes(true);
				$product->setCanSaveCustomOptions(true);
			}
			if (isset($importData['associated'])){
				$product->setConfigurableProductsData($this->skusToIds($importData['associated'], $product));
			}
		}
		//Init product links data (related, upsell, crosssell, grouped)
		if (isset ( $importData ['related'] )){
			$linkIds = $this->skusToIds ( $importData ['related'], $product );
			if (! empty ( $linkIds )){
				$product->setRelatedLinkData ( $linkIds );
			}
		}
		if (isset ( $importData ['upsell'] )){
			$linkIds = $this->skusToIds ( $importData ['upsell'], $product );
			if (! empty ( $linkIds )){
				$product->setUpSellLinkData ( $linkIds );
			}
		}
		if (isset ( $importData ['crosssell'] )){
			$linkIds = $this->skusToIds ( $importData ['crosssell'], $product );
			if (! empty ( $linkIds )){
				$product->setCrossSellLinkData ( $linkIds );
			}
		}
		if (isset ( $importData ['grouped'] )) {
			$linkIds = $this->skusToIds ( $importData ['grouped'], $product );
			if (! empty ( $linkIds )) {
				$product->setGroupedLinkData($linkIds);
			}
		}
		
		//================================================
		// End part of configurable products and links 
		//================================================

        if (isset($importData['category_ids'])){
            $product->setCategoryIds($importData['category_ids']);
        }
        if (isset($importData['categories'])){
            $categoryIds = $this->_addCategories($importData, $store);
            if ($categoryIds) {
                $product->setCategoryIds($categoryIds);
            }
        }

        foreach ($this->_ignoreFields as $field){
            if (isset($importData[$field])){
                unset($importData[$field]);
            }
        }

        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)){
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)){
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }

        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            $websiteCodes = split(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode){
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)){
                        $websiteIds[] = $website->getId();
                    }
                }
                catch (Exception $e) {}
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }

		foreach ($importData as $field => $value) {
			if (in_array($field, $this->_inventorySimpleFields)) {
				continue;
			}
			if (in_array($field, $this->_imageFields)){
				continue;
			}
			$attribute = $this->getAttribute($field);
			if (!$attribute) {				
				continue;
			} 
			
			$isArray = false;
			$setValue = $value;

			if ($attribute->getFrontendInput() == 'multiselect') {
                $value = split(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }
			
			if ($value && $attribute->getBackendType() == 'decimal') {
				$setValue = $this->getNumber($value);
			}
			//CODE MODIFICATION STARTS HERE
			$optionLabelArray=array();
			if ($attribute->usesSource()) {
			 $options = $attribute->getSource()->getAllOptions(false);
			//Update the Source of the attribute when source has no options.
			 if(count($options)<1){
			 	if($isArray){
			 		foreach($value as $key=>$subvalue){
			 			if(!in_array($subvalue,$newOptionLabelArray)){
			 				$setValue[]=$this->updateSourceAndReturnId($field,trim($subvalue));
			 				array_push($newOptionLabelArray,trim($subvalue));
			 			}
			 		}
			 	}
			 	else{
			 		if(!in_array($value,$newOptionLabelArray)){
						$setValue=$this->updateSourceAndReturnId($field,trim($value));
						array_push($newOptionLabelArray,$value);
			 		}
			 	}
			 }
			//Work on the source when it has options
			 else{
			//This is the case of Multi-Select
			 	if ($isArray){
			 		foreach ($options as $item) {
						// Setting the option's ID if Label matches with the current value of XML column.
			 			if (in_array($item['label'], $value)) {
			 				$setValue[] = trim($item['value']);
			 				array_push($optionLabelArray,$item['label']); //Adding Reference to worked attribute option
			 			}
			 		}
			 		//Checking in the current XML column value if all values were used in the above loop or not
			 		//If not used then they are new options value, then new option is created and then assigned.
			 		foreach($value as $key=>$subvalue){
						if(!in_array($subvalue,$optionLabelArray)){
			 				$setValue[]=$this->updateSourceAndReturnId($field,trim($subvalue));
			 			}
			 		}
			 	}
			 	//This is the case of single select
			 	else {
			 		$setValue = null;
			 		$newOptionLabelArray=array();
			 		foreach ($options as $item) {
			 			if ($item['label'] == $value) {
			 				$setValue = $item['value'];
			 				array_push($optionLabelArray,$item['label']); //Adding Reference to worked attribute option
			 			}
			 		}
			 		if(!in_array($value,$optionLabelArray)){
			 			$setValue=$this->updateSourceAndReturnId($field,trim($value));
			 		}
			 	}
			 }
			}
			//CODE MODIFICATION ENDS HERE
			$product->setData($field, $setValue);
			//$product->setQuantity(1);
		}

        if (!$product->getVisibility()){
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }
		//if($product->getTypeId() == 'simple')
		//{
		$stockData = array();
        $inventoryFields = $this->_inventorySimpleFields;
		foreach ($inventoryFields as $field){
            if (isset($importData[$field])){
                if (in_array($field, $this->_toNumber)){
                    $stockData[$field] = $this->getNumber($importData[$field]);
                } else{
                    $stockData[$field] = $importData[$field];
                }
            }
			$product->setStockData($stockData);
		}
		/*}
		elseif($product->getTypeId() == 'configurable') {
			$stockDataw['qty'] = 1;
			$stockDataw['is_in_stock'] = 1;
			$stockDataw['manage_stock'] = 0;
		   $product->setStockData($stockDataw);
		}
		*/

		$imageData = array();
        foreach ($this->_imageFields as $field){
            if (!empty($importData[$field]) && $importData[$field] != 'no_selection'){
                if (!isset($imageData[$importData[$field]])){
                    $imageData[$importData[$field]] = array();
                }
                $imageData[$importData[$field]][] = $field;
            }
        }
 
        foreach ($imageData as $file => $fields){
            try{
                $product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $file, $fields);
            }
            catch (Exception $e){}
        }
		//for multiple images
		try{
			$galleryData = explode(';',$importData["gallery"]);
			foreach($galleryData as $gallery_img){
				$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $gallery_img, null, false, false);
			}
		}
	    catch (Exception $e) {}
		//end for multiple images
        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(false);
		//$product->setQuantity(1);
        $product->save();
        return true;
    }
	
	protected function userCSVDataAsArray($data) {
		return explode( ',', str_replace (" ","", $data ));
	}
	
	public function _saveRow( array $importData )
	{
		//print_r($importData);die;
		$product = $this -> getProductModel();
		$product -> setData( array() );
		
		if ( $stockItem = $product -> getStockItem() ) {
			$stockItem -> setData( array() );
		} 
		
		if ( empty( $importData['store'] ) ) {
			if ( !is_null( $this -> getBatchParams( 'store' ) ) ) {
				$store = $this -> getStoreById( $this -> getBatchParams( 'store' ) );
			} else {
				$message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, required field "%s" not defined', 'store' );
				Mage :: throwException( $message );
			} 
		} else {
			$store = $this -> getStoreByCode( $importData['store'] );
		} 
		
		if ( $store === false ) {
			$message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, store "%s" field not exists', $importData['store'] );
			Mage :: throwException( $message );
		} 
		
		if ( empty( $importData['sku'] ) ) {
			$message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, required field "%s" not defined', 'sku' );
			Mage :: throwException( $message );
		} 
		
		$product -> setStoreId( $store -> getId() );
		$productId = $product -> getIdBySku( $importData['sku'] );
		//$new = true; // fix for duplicating attributes error
		if ( $productId ) {
			$product -> load( $productId );
			//$new = false; // fix for duplicating attributes error
		} 
		$productTypes = $this -> getProductTypes();
		$productAttributeSets = $this -> getProductAttributeSets();
		
		// delete disabled products
		if ( $importData['status'] == 'Disabled' ) {
			$product = Mage :: getSingleton( 'catalog/product' ) -> load( $productId );
			$this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'image' ) ) );
			$this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'small_image' ) ) );
			$this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'thumbnail' ) ) );
			$media_gallery = $product -> getData( 'media_gallery' );
			foreach ( $media_gallery['images'] as $image ) {
				$this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $image['file'] ) );
			} 
			$product -> delete();
			return true;
		} 
		
		if ( empty( $importData['type'] ) || !isset( $productTypes[strtolower( $importData['type'] )] ) ) {
			$value = isset( $importData['type'] ) ? $importData['type'] : '';
			$message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, is not valid value "%s" for field "%s"', $value, 'type' );
			Mage :: throwException( $message );
		} 
		$product -> setTypeId( $productTypes[strtolower( $importData['type'] )] );
		
		if ( empty( $importData['attribute_set'] ) || !isset( $productAttributeSets[$importData['attribute_set']] ) ) {
			$value = isset( $importData['attribute_set'] ) ? $importData['attribute_set'] : '';
			$message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, is not valid value "%s" for field "%s"', $value, 'attribute_set' );
			Mage :: throwException( $message );
		} 
		$product -> setAttributeSetId( $productAttributeSets[$importData['attribute_set']] );
		
		foreach ( $this -> _requiredFields as $field ) {
			$attribute = $this -> getAttribute( $field );
			if ( !isset( $importData[$field] ) && $attribute && $attribute -> getIsRequired() ) {
				$message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, required field "%s" for new products not defined', $field );
				Mage :: throwException( $message );
			} 
		} 
		
		if ($importData['type'] == 'configurable') {
			$product->setCanSaveConfigurableAttributes(true);
			$configAttributeCodes = $this->userCSVDataAsArray($importData['config_attributes']);
			$usingAttributeIds = array();

			/***
			* Check the product's super attributes (see catalog_product_super_attribute table), and make a determination that way.
			**/
			$cspa  = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
			$attr_codes = array();
			if(isset($cspa) && !empty($cspa)){ //found attributes
				foreach($cspa as $cs_attr){
				//$attr_codes[$cs_attr['attribute_id']] = $cs_attr['attribute_code'];
					$attr_codes[] = $cs_attr['attribute_id'];
				}
			}

			foreach($configAttributeCodes as $attributeCode) {
				$attribute = $product->getResource()->getAttribute($attributeCode);
				if ($product->getTypeInstance()->canUseAttribute($attribute)) {
					if (!in_array($attributeCode,$attr_codes)) { // fix for duplicating attributes error
						$usingAttributeIds[] = $attribute->getAttributeId();
					}
				}
			}
			if (!empty($usingAttributeIds)) {
				$product->getTypeInstance()->setUsedProductAttributeIds($usingAttributeIds);
				//$product->setConfigurableAttributesData($product->getTypeInstance()->getConfigurableAttributesAsArray());
				//FS2010-02-15 The label MUST be set or we'll get a MySQL error
				$configurableAttributesArray = $product->getTypeInstance()->getConfigurableAttributesAsArray();
				foreach($configurableAttributesArray as &$configurableAttributeArray){
							
					//Set a number of default values
					$configurableAttributeArray['use_default']     = 1;
					$configurableAttributeArray['position']        = 0;

					//Use the frontend_label as label, if available        
					if(isset($configurableAttributeArray['frontend_label'])){
						$configurableAttributeArray['label'] = $configurableAttributeArray['frontend_label'];
						continue;
					}
						//Use the attribute_code as a label
					$configurableAttributeArray['label'] = $configurableAttributeArray['attribute_code'];
				}
				$product->setConfigurableAttributesData($configurableAttributesArray);
				//
				
				$product->setCanSaveConfigurableAttributes(true);
				$product->setCanSaveCustomOptions(true);
			}
			if (isset($importData['associated'])) {
				$product->setConfigurableProductsData($this->skusToIds($importData['associated'], $product));
			}
		}
		
		if ( isset( $importData['related'] ) ) {
			$linkIds = $this -> skusToIds( $importData['related'], $product );
			if ( !empty( $linkIds ) ) {
				$product -> setRelatedLinkData( $linkIds );
			} 
		} 

		if ( isset( $importData['upsell'] ) ) {
			$linkIds = $this -> skusToIds( $importData['upsell'], $product );
			if ( !empty( $linkIds ) ) {
				$product -> setUpSellLinkData( $linkIds );
			} 
		} 

		if ( isset( $importData['crosssell'] ) ) {
			$linkIds = $this -> skusToIds( $importData['crosssell'], $product );
			if ( !empty( $linkIds ) ) {
				$product -> setCrossSellLinkData( $linkIds );
			} 
		} 

		if ( isset( $importData['grouped'] ) ) {
			$linkIds = $this -> skusToIds( $importData['grouped'], $product );
			if ( !empty( $linkIds ) ) {
				$product -> setGroupedLinkData( $linkIds );
			} 
		} 

		if ( isset( $importData['category_ids'] ) ) {
			$product -> setCategoryIds( $importData['category_ids'] );
		} 

		//Tier Prices
		if( isset($importData['tier_prices']) && !empty($importData['tier_prices']) ) {
			$this->_editTierPrices($product, $importData['tier_prices']);
		}
		
        if (isset($importData['categories'])) {
            $categoryIds = $this->_addCategories($importData, $store);
            if ($categoryIds) {
                $product->setCategoryIds($categoryIds);
            }
        } 
		
		foreach ( $this -> _ignoreFields as $field ) {
			if ( isset( $importData[$field] ) ) {
				unset( $importData[$field] );
			} 
		} 
		
		if ( $store -> getId() != 0 ) {
			$websiteIds = $product -> getWebsiteIds();
			if ( !is_array( $websiteIds ) ) {
				$websiteIds = array();
			} 
			if ( !in_array( $store -> getWebsiteId(), $websiteIds ) ) {
				$websiteIds[] = $store -> getWebsiteId();
			} 
			$product -> setWebsiteIds( $websiteIds );
		} 
		
		if ( isset( $importData['websites'] ) ) {
			$websiteIds = $product -> getWebsiteIds();
			if ( !is_array( $websiteIds ) ) {
				$websiteIds = array();
			} 
			$websiteCodes = split( ',', $importData['websites'] );
			foreach ( $websiteCodes as $websiteCode ) {
				try {
					$website = Mage :: app() -> getWebsite( trim( $websiteCode ) );
					if ( !in_array( $website -> getId(), $websiteIds ) ) {
						$websiteIds[] = $website -> getId();
					} 
				} 
				catch ( Exception $e ) {
				} 
			} 
			$product->setWebsiteIds( $websiteIds );
			unset($websiteIds);
		} 
		
		foreach ($importData as $field=>$value){
			if (in_array($field, $this->_inventorySimpleFields)){ 
				continue;
			} 
			if (in_array($field, $this->_imageFields)){
				continue;
			} 
			
			$attribute = $this->getAttribute($field);
			if ( !$attribute ) {
				continue;
			} 
			
			$isArray = false;
			$setValue = $value;
			
			if ( $attribute -> getFrontendInput() == 'multiselect' ) {
				$value = split( self :: MULTI_DELIMITER, $value );
				$isArray = true;
				$setValue = array();
			} 
			
			if ( $value && $attribute -> getBackendType() == 'decimal' ) {
				$setValue = $this -> getNumber( $value );
			} 
			
			if ( $attribute -> usesSource() ) {
				$options = $attribute -> getSource() -> getAllOptions( false );
				
				if ( $isArray ) {
					foreach ( $options as $item ) {
						if ( in_array( $item['label'], $value ) ) {
							$setValue[] = $item['value'];
						} 
					} 
				} 
				else {
					$setValue = null;
					foreach ( $options as $item ) {
						if ( $item['label'] == $value ) {
							$setValue = $item['value'];
						} 
					} 
				} 
			} 
			$product -> setData( $field, $setValue );
		} 
		
		if ( !$product -> getVisibility() ) {
			$product -> setVisibility( Mage_Catalog_Model_Product_Visibility :: VISIBILITY_NOT_VISIBLE );
		} 
		
		$stockData = array();
		$inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])? $this->_inventoryFieldsProductTypes[$product->getTypeId()] : array(); 
		foreach ( $inventoryFields as $field ) {
			if ( isset( $importData[$field] ) ) {
				if ( in_array( $field, $this -> _toNumber ) ) {
					$stockData[$field] = $this -> getNumber( $importData[$field] );
				} 
				else {
					$stockData[$field] = $importData[$field];
					
				} 
			} 
		} 
		$product->setStockData($stockData);
		
		$imageData = array();
		foreach ( $this -> _imageFields as $field ) {
			if ( !empty( $importData[$field] ) && $importData[$field] != 'no_selection' ) {
				if ( !isset( $imageData[$importData[$field]] ) ) {
					$imageData[$importData[$field]] = array();
				} 
				$imageData[$importData[$field]][] = $field;
			} 
		} 
		
		foreach ( $imageData as $file => $fields ) {
			try {
				$product->addImageToMediaGallery( Mage :: getBaseDir( 'media' ) . DS . 'import/' . $file, $fields, false ,false);
			} 
			catch ( Exception $e ) {
			} 
		} 
		
		if ( !empty( $importData['gallery'] ) ) {
			$galleryData = explode( ',', $importData["gallery"] );
			foreach( $galleryData as $gallery_img ) {
				try {
					$product->addImageToMediaGallery( Mage :: getBaseDir( 'media' ) . DS . 'import/' . $gallery_img, null, false, false );
				} 
				catch ( Exception $e ) {
				} 
			} 
		} 
		
		$product -> setIsMassupdate( true );
		$product -> setExcludeUrlRewrite( true );
		$product -> save();
		return true;
	} 
	
	protected function skusToIds($userData, $product) 
	{
		$productIds = array ();
		foreach ( $this->userCSVDataAsArray ( $userData ) as $oneSku ) {
			if (($a_sku = ( int ) $product->getIdBySku ( $oneSku )) > 0) {
				parse_str ( "position=", $productIds[$a_sku] );
			}
		}
		return $productIds;
	}
	
	public function updateSourceAndReturnId($attribute_code,$newOption)
	{
		$attribute_model        = Mage::getModel('eav/entity_attribute');
		$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
		$attribute_code         = $attribute_model->getIdByCode('catalog_product', $attribute_code);
		$attribute              = $attribute_model->load($attribute_code);
		$attribute_table        = $attribute_options_model->setAttribute($attribute);
		try{
			$value['option'] = array(trim($newOption),trim($newOption));
			$result = array('value' => $value,
							'attribute_set_id' => '4',
							'attribute_group_id' => '4',
							'sort_order' => '35'
							);
			$attribute->setData('option',$result);
			$attribute->save();
			
		}
		catch(Exception $e){}
		$options = $attribute_options_model->getAllOptions(false);
		foreach($options as $option){
			if ($option['label'] == $newOption){
				return $option['value'];
			}
		}
		return "";
	}
	// Create attribute
	public function createAttribute($code, $label, $attribute_type, $product_type,$value)
	{	
		$_attribute_data = array(
		'attribute_code' => $code,
		'is_global' => '1',
		'frontend_input' => 'select',//$attribute_type, //'boolean',
		'default_value_text' => '',
		'default_value_yesno' => '0',
		'default_value_date' => '',
		'default_value_textarea' => '',
		'is_unique' => '0',
		'backend_type'=> 'int',
		'is_required' => '0',
		'apply_to' => '', //array('grouped')
		'is_configurable' => '0',
		'is_searchable' => '0',
		'is_visible_in_advanced_search' => '0',
		'is_comparable' => '0',
		'is_used_for_price_rules' => '0',
		'is_wysiwyg_enabled' => '0',
		'is_visible' =>'1',
		'is_html_allowed_on_front' => '1',
		'is_visible_on_front' => '1',
		'used_in_product_listing' => '1',
		'used_for_sort_by' => '0',
		'frontend_label' => array($label)
		//'frontend_label' => array('Old Site Attribute '.(($product_type) ? $product_type : 'joint').' '.$label)
		);
 
		$model = Mage::getModel('catalog/resource_eav_attribute');
 
		if (!isset($_attribute_data['is_configurable'])) {
			$_attribute_data['is_configurable'] = 0;
		}
		if (!isset($_attribute_data['is_filterable'])) {
			$_attribute_data['is_filterable'] = 0;
		}
		if (!isset($_attribute_data['is_filterable_in_search'])) {
			$_attribute_data['is_filterable_in_search'] = 0;
		}
	 
		if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
			$_attribute_data['backend_type'] = $model->getBackendTypeByInput($_attribute_data['frontend_input']);
		}
	 
		$defaultValueField = $model->getDefaultValueByInput($_attribute_data['frontend_input']);
		if ($defaultValueField) {
			$_attribute_data['default_value'] =$value;
		}
		
		$model->addData($_attribute_data);
		$model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
		$model->setAttributeSetId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
		$model->setAttributeGroupId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
		$model->setIsUserDefined(1);
		
		try {
			$model->save();
		} 
		catch (Exception $e) { echo '<p>Sorry, error occured while trying to save the attribute. Error: '.$e->getMessage().'</p>'; }
	}
	
    public function saveRowSilently(array $importData)
    {
        try{
            $result = $this->saveRow($importData);
            return $result;
        }
        catch (Exception $e){
            return false;
        }
    }

    public function finish(array $importData)
    {
        Mage::dispatchEvent('catalog_product_import_after', array());
    }
    protected $_categoryCache = array();
   
	protected function _addCategories($importData, $store)
    {
		$categories = $importData['categories'];
		$importDataTemp = $importData;
        $rootId = $store->getRootCategoryId();
        if (!$rootId){
            return array();
        }
		if($categories=="")
		   return array();
        $rootPath = '1/'.$rootId;
        if (empty($this->_categoryCache[$store->getId()])){
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStore($store)
                ->addAttributeToSelect('name');
            $collection->getSelect()->where("path like '".$rootPath."/%'");
			
            foreach ($collection as $cat){
                $pathArr = explode('/', $cat->getPath());
                $namePath = '';
                for ($i=2, $l=sizeof($pathArr); $i<$l; $i++){
                    $name = $collection->getItemById($pathArr[$i])->getName();
                    $namePath .= (empty($namePath) ? '' : '/').trim($name);
                }
                $cat->setNamePath($namePath);
			}
            
            $cache = array();
            foreach ($collection as $cat){
                $cache[strtolower($cat->getNamePath())] = $cat;
                $cat->unsNamePath();
            }
            $this->_categoryCache[$store->getId()] = $cache;
        }
        $cache =& $this->_categoryCache[$store->getId()];
		
        $catIds = array();
        foreach (explode(',', $categories) as $categoryPathStr){
            $categoryPathStr = preg_replace('#\s*/\s*#', '/', trim($categoryPathStr));
			
            if (!empty($cache[$categoryPathStr])){
                $catIds[] = $cache[$categoryPathStr]->getId();
                continue;
            }
            $path = $rootPath;
            $namePath = '';
            foreach (explode('/', $categoryPathStr) as $catName){
                $namePath .= (empty($namePath) ? '' : '/').strtolower($catName);
                if (empty($cache[$namePath])){
					
					//for display mode
					if($importDataTemp['display_mode']=='Products only') $sbo='PRODUCTS';
					if($importDataTemp['display_mode']=='Static block only') $sbo='PAGE';
					if($importDataTemp['display_mode']=='Static block and products') $sbo='PRODUCTS_AND_PAGE';
					
					//for include in navigation
					if($importDataTemp['include_in_menu']=='Yes') $include= 1 ;
					if($importDataTemp['include_in_menu']=='No') $include= 0;
					
					//for category type
				/*
					if($importDataTemp['cat_type']=='Women') $menutype= 1;
					if($importDataTemp['cat_type']=='Men') $menutype= 2;
					if($importDataTemp['cat_type']=='Home') $menutype= 3;
					if($importDataTemp['cat_type']=='Experience') $menutype= 4;
					*/
					
					$collection = Mage::getModel('menucategory/menucategory')->getCollection();
					$collection	=	$collection->addFieldToFilter('status', 1);
					$collection	=	$collection->addFieldToFilter('menu_category', "$importDataTemp[cat_type]");
					//$collection->printLogQuery(1);;
					if(count($collection)>0)
					{
						foreach($collection as $obj)
						{
							$menu_cat					=	trim($obj->getMenuCategory());
							$menucategory_id			=	trim($obj->getMenucategoryId());
							$menutype					=	$menucategory_id;
						}
					}		
					// for cms Block
					//for page layout
					/*					
					if($importDataTemp['page_layout']=='Custom static page 1') $layoutpage='custom_static_page_one';
					if($importDataTemp['page_layout']=='Homepage') $layoutpage='homepage';
					if($importDataTemp['page_layout']=='Empty') $layoutpage='empty';
					if($importDataTemp['page_layout']=='1 column') $layoutpage='one_column';
					if($importDataTemp['page_layout']=='2 columns with left bar') $layoutpage='two_columns_left';
					if($importDataTemp['page_layout']=='2 columns with right bar') $layoutpage='two_columns_right';
					if($importDataTemp['page_layout']=='3 columns') $layoutpage='three_columns';
					*/
					if($importDataTemp['page_layout']=='Custom Product Catalog page') $layoutpage='custom_product_catalog_page';
					
					
					foreach ($imageData as $file => $fields){
						try{
							$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $file, $fields);
						}
						catch (Exception $e) {}
					}
					if(isset($importDataTemp['category_image']))
					{
						$cat_imageName	= $importDataTemp['category_image'];
						$imageUrl		= Mage::getBaseDir()."/media/import/".$cat_imageName;
						$rename_cat_image	=	"banner_".$this->cleanURL($catName).".".end(explode(".",$cat_imageName));					
						$imageResized	= Mage::getBaseDir('media').DS."catalog".DS."category".DS.$rename_cat_image;
						$dirImg = Mage::getBaseDir().str_replace("/",DS,strstr($imageUrl,'/media'));
									
						if (!file_exists($imageResized)&&file_exists($dirImg)) {
							$imageObj = new Varien_Image($dirImg);
							$imageObj->constrainOnly(TRUE);
							$imageObj->keepAspectRatio(FALSE);
							$imageObj->keepFrame(FALSE);
							$imageObj->resize(742, 140);
							$imageObj->save($imageResized);
						}
					}
					
					if(isset($importDataTemp['category_brand_logo_image']))
					{
						$category_brand_logo_image	= $importDataTemp['category_brand_logo_image'];
						$brandLogoimageUrl		= Mage::getBaseDir()."/media/import/".$category_brand_logo_image;
						$rename_brand_logo_image	=	"brand_logo_".$this->cleanURL($catName).".".end(explode(".",$category_brand_logo_image));					
						$brandLogoImageResized	= Mage::getBaseDir('media').DS."catalog".DS."category".DS.$rename_brand_logo_image;
						$dirImg = Mage::getBaseDir().str_replace("/",DS,strstr($brandLogoimageUrl,'/media'));
									
						if (!file_exists($brandLogoImageResized)&&file_exists($dirImg)) {
							$imageObj = new Varien_Image($dirImg);
							$imageObj->constrainOnly(TRUE);
							$imageObj->keepAspectRatio(FALSE);
							$imageObj->keepFrame(FALSE);
							$imageObj->resize(NULL, 52);
							$imageObj->save($brandLogoImageResized);
						}
					}
					
                    $cat = Mage::getModel('catalog/category')
					
                        ->setStoreId(0)
						->setPath($path)
                        ->setName($catName)
                        ->setIsActive(1)
						->setDescription($importDataTemp['description'])
						->setStartDate($importDataTemp['start_date'])
						->setEndDate($importDataTemp['end_date'])
						->setCatType($menutype)
						->setMetaTitle($importDataTemp['meta_title'])
						->setMetaDescription($importDataTemp['meta_description'])
						->setMetaKeywords($importDataTemp['meta_keywords'])
						->setLandingPage(0)
						->setCustomDesignFrom($importDataTemp['custom_design_from'])
						->setCustomDesignTo($importDataTemp['custom_design_to'])
						->setPageLayout('custom_product_catalog_page')
						->setCustomLayoutUpdate($importDataTemp['custom_layout_update'])
						->setDisplayMode('PRODUCTS')
						->setImage($rename_cat_image)
						->setBrandImageLogo($rename_brand_logo_image)
						->setIncludeInMenu(1)
						->setEstimateDeliveryTime($importDataTemp['estimate_delivery_time'])
						->setSizeChartFile($importDataTemp['size_chart_file'])
						->setSizeFit($importDataTemp['size_fit'])
						->setIsAnchor(0)
						->save();
						$cache[$namePath] = $cat;
                }
                $catId = $cache[$namePath]->getId();
                $path .= '/'.$catId;
            }
			
			//for products in all categories mehul
			$currentCatId	=	'';
			$catIds			=	array();
			$currentCatId 		=	$this->getParentTopCategory($cache[$namePath]);
			
            if ($catId)
			{
				if($catId==$currentCatId)
				{
					//$catIds[]	=	array();
				}
				else
				{
					$catIds[] 	=	$currentCatId ;
				}
				$catIds[] = $catId;
            }
			//ended
        }
        return join(',', $catIds);
    }
	
	//function added mehul
	protected function getParentTopCategory($category)
	{
		if($category->getLevel() == 3){
			return $category->getId();
		} else {
			$parentCategory = Mage::getModel('catalog/category')->load($category->getParentId());
			return $this->getParentTopCategory($parentCategory);
		}
	}
	
	function cleanURL($string)
	{
		$url = str_replace("'", '', $string);
		$url = str_replace('%20', ' ', $url);
		$url = preg_replace('~[^\\pL0-9_]+~u', '-', $url); // substitutes anything but letters, numbers and '_' with separator
		$url = trim($url, "-");
		$url = iconv("utf-8", "us-ascii//TRANSLIT", $url);  // you may opt for your own custom character map for encoding.
		$url = strtolower($url);
		$url = preg_replace('~[^-a-z0-9_]+~', '', $url); // keep only letters, numbers, '_' and separator
		return $url;
	}
	//ended
}
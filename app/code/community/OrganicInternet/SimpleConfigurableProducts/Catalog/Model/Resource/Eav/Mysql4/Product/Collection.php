<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    #Adds an additional price column called 'indexed_price' as the price_index.price value is
    #overidden elsewhere in the codebase by the normal(i.e. direct on conf product) product price.
    protected function _productLimitationJoinPrice()
    {
        $filters = $this->_productLimitationFilters;
        if (empty($filters['use_price_index'])) {
            return $this;
        }

        $connection = $this->getConnection();

        $joinCond = join(' AND ', array(
            //'price_index.entity_id = e.entity_id',
            $connection->quoteInto('cat_index.website_id = ?', $filters['website_id']),
            $connection->quoteInto('cat_index.customer_group_id = ?', $filters['customer_group_id'])
        ));

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart['cat_index'])) {
            $minimalExpr = new Zend_Db_Expr(
               // 'IF(`price_index`.`tier_price`, LEAST(`price_index`.`min_price`, `price_index`.`tier_price`), `price_index`.`min_price`)'
              //commented on dated 08-07-2013 Added By Dileswar 
               '`cat_index`.`min_price`'
            );
            $indexedExpr = new Zend_Db_Expr('cat_index.price');
            $this->getSelect()->join(
              
			  // added new table in below to instead of two table it converted to one table ... on dated 08-01-2014
			    //array('price_index' => $this->getTable('catalog/product_index_price')),
				array('cat_index' => 'catalog_product_craftsvilla'),
                $joinCond,
                array('indexed_price'=>$indexedExpr,'price', 'final_price', 'minimal_price'=>$minimalExpr , 'min_price', 'max_price', 'tier_price'));
		//->where('e.updated_at > DATE_SUB(NOW(),INTERVAL 730 DAY)');
		
				//echo $this->getSelect()->__toString();

        } else {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }

        return $this;
    }
}

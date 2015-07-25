<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.4.1.x-1.5.0.1 - 06/07/11
 * Package:     AdjustWare_Cartalert_3.0.5_0.2.3_183688
 * Purchase ID: Y6M1PHMt9YjaYLDNXoI3HVQQ5WLuo3S19F0xW5tLYM
 * Generated:   2012-02-06 21:31:16
 * File path:   app/code/local/AdjustWare/Cartalert/Model/Mysql4/Cartalert.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ geDjmBMkZBZqiMic('a2c23a8cb79a88a449107e2ee8213041'); ?><?php
/**
 * Cartalert module observer
 *
 * @author Adjustware
 */
class AdjustWare_Cartalert_Model_Mysql4_Cartalert extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_select;

    public function _construct()
    {    
        $this->_init('adjcartalert/cartalert', 'cartalert_id');
    }
    
    
    private function _getAbandonedCartsIds($fromDate, $toDate){
        $db = $this->_getReadAdapter();
        $sql = $db->select()
            ->from(array('q' => $this->getTable('sales/quote')), array('q.entity_id'))
            ->where('q.updated_at > ?', $fromDate)
            ->where('q.updated_at < ?', $toDate)
            ->where('q.is_active=1');
        $result = $db->fetchAll($sql);  
        $ids = array();      
        foreach ($result as $row)
            $ids[] = $row['entity_id'];
            
        return $ids;
    }
    
    private function _getAbandonedCartsContent($ids){
        $db = $this->_getReadAdapter();
        // select all products and check that quotes have a valid email
        $fields = array(
            'store_id'         => 'q.store_id', 
            'quote_id'         => 'q.entity_id', 
            'customer_id'      => 'q.customer_id', 
            'customer_email'   => new Zend_Db_Expr('IFNULL(q.customer_email, ba.email)'),
            'customer_fname'   => new Zend_Db_Expr('IFNULL(q.customer_firstname, ba.firstname)'),
            'customer_lname'   => new Zend_Db_Expr('IFNULL(q.customer_lastname, ba.lastname)'),
            'products'         => new Zend_Db_Expr('GROUP_CONCAT(CONCAT(i.product_id,"##,",i.name,"##"))'), 
            'abandoned_at'     => 'q.updated_at', 
        );
        
        $this->_select = $db->select()
            ->from(array('q' => $this->getTable('sales/quote')), $fields)
            ->joinInner(array('i' => $this->getTable('sales/quote_item')), 'q.entity_id=i.quote_id', array())
            ->joinLeft(array('ba' => $this->getTable('sales/quote_address')), 'q.entity_id=ba.quote_id AND ba.address_type="billing"', array())
            ->where('q.entity_id IN(?)', $ids)
            ->where('IFNULL(q.customer_email, ba.email) IS NOT NULL')
            ->where('i.parent_item_id IS NULL')
            ->where('q.allow_alerts = 1')
            ->group('q.entity_id')
            ->limit(50); // we expect that there will be 10-20 carts maximum, because cron runs each hour
        $this->_addFilter('visibility', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());
        $this->_addFilter('status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds());
        
        return $db->fetchAll($this->_select); 
    }
    
    private function _updateDates($now){
        $timeout = intVal(Mage::getStoreConfig('catalog/adjcartalert/timeout'));
        
        $toDate   = date('Y-m-d H:i:s', strtotime($now) - 60*$timeout);
        $fromDate = $this->_loadFromDate();
        
        $this->_saveFromDate($toDate);  
        
         return array($fromDate, $toDate);      
    }
    
    public function generate($now){
        
        list($fromDate, $toDate) = $this->_updateDates($now);
        $ids = $this->_getAbandonedCartsIds($fromDate, $toDate);
        
        if (!$ids)
            return array($fromDate, $toDate);
        
        $carts = $this->_getAbandonedCartsContent($ids);
        if (!$carts)
            return array($fromDate, $toDate);
            
        // START creating insert SQL to schedule follow-ups    
        $db = $this->_getReadAdapter();     
        $insertSql = 'INSERT INTO ' . $this->getMainTable() . '(' . join(',', array_keys($carts[0])) . ', follow_up, sheduled_at) VALUES ';

        foreach ($carts as $row){
            $vals = '';
            foreach ($row as $field){
                $vals .= $db->quote($field) . ',';
            }
            
            $abandoned_at = strtotime($row['abandoned_at']);
            
            // first follow up, minutes
            $delay  = Mage::getStoreConfig('catalog/adjcartalert/delay', $row['store_id']); 
            if ($delay){ 
                $sheduled_at = date('Y-m-d H:i:s', $abandoned_at + $delay*60);
                $insertSql .= "($vals 'first', '$sheduled_at'),";
            }
            // second follow up, hours
            $delay2 = Mage::getStoreConfig('catalog/adjcartalert/delay2', $row['store_id']); 
            if ($delay2){ 
                $sheduled_at = date('Y-m-d H:i:s', $abandoned_at + $delay2*3600);
                $insertSql .= "($vals 'second', '$sheduled_at'),";
            }
            // third follow up, also hours
            $delay3 = Mage::getStoreConfig('catalog/adjcartalert/delay3', $row['store_id']); 
            if ($delay3){ 
                $sheduled_at = date('Y-m-d H:i:s', $abandoned_at + $delay3*3600);
                $insertSql .= "($vals 'third', '$sheduled_at'),";
            }
			
			$dbw = $this->_getWriteAdapter();
	        $sql = 'UPDATE ' . $this->getTable('sales/quote') . ' SET `allow_alerts` = "0"'
	             . ' WHERE entity_id="' . $row['quote_id'] . '"'     
	             . ' LIMIT 1';
	        $dbw->query($sql); 

        }
        // END creating SQL
       // echo $insertSql;
       // exit;
        //finally insert records in bulk
        //Craftsvilla Comment - Changed Database adaptor from read to write as it was taking slave DB connection to wirte. (Amit Pitre On 30-04-2012)
	$dbw->raw_query(substr($insertSql, 0, -1));
        //$db->raw_query(substr($insertSql, 0, -1));
            
        return array($fromDate, $toDate);
    }

    
    private function _loadFromDate(){
        $db = $this->_getReadAdapter();
        $sql = 'SELECT value FROM ' . $this->getTable('core/config_data') 
             . ' WHERE scope="default" AND path="catalog/adjcartalert/from_date"'
             . ' LIMIT 1';
        return $db->fetchOne($sql);   
    }
    
    private function _saveFromDate($toDate){
        $db = $this->_getWriteAdapter();
        $sql = 'UPDATE ' . $this->getTable('core/config_data') . ' SET `value` = "'. $toDate .'"'
             . ' WHERE scope="default" AND path="catalog/adjcartalert/from_date"'     
             . ' LIMIT 1';
        $db->query($sql);    
    }
    
    protected function _addFilter($attributeCode, $value)
    {
        $attribute = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attributeCode);
        $t  = 't1_'.$attributeCode;
        $t2 = 't2_'.$attributeCode;

        $this->_select->join(
            array($t => $attribute->getBackend()->getTable()),
            'i.product_id='.$t.'.entity_id AND '.$t.'.store_id=0',
            array()
        )
        ->joinLeft(
            array($t2 => $attribute->getBackend()->getTable()),
            $t.'.entity_id = '.$t2.'.entity_id AND '.$t.'.attribute_id = '.$t2.'.attribute_id AND '.$t2.'.store_id=q.store_id',
            array()
        )
        ->where($t.'.attribute_id=?', $attribute->getId())
        ->where('IFNULL('.$t2.'.value, '.$t.'.value) IN(?)', $value);

        return true;
    }

    public function cancelAlertsFor($email){
        $db = $this->_getWriteAdapter();
        $db->delete($this->getMainTable(), 'customer_email = ' . $db->quote($email));
    }
} } 

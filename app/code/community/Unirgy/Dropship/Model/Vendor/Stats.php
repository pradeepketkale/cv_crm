<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Unirgy_Dropship_Model_Vendor_Stats extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_stats');
        parent::_construct();
    }
    
    public function setStats($vendorId = null, $statType = null){
        $date       = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        $checkDate  = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
        $checkDate  = explode("-",$checkDate);
        $model      = Mage::getModel('udropship/vendor_stats');
        $count      = $model->getCollection()->addFieldToFilter('vendor_id', $vendorId)->addFieldToFilter('stat_type', $statType);
        if(count($count) == 0){
            $model->setVendorId($vendorId);
            $model->setStatType($statType);
            $model->setPageviews(1);
            $model->setDate($date);
            $model->save();
        }
        else if($checkDate[0] < 2){
            $count->getSelect()->order('id desc')->limit(1);
            $data = $count->getData();
            if(date("Y-m-d",strtotime($data[0]['date'])) != date("Y-m-d", Mage::getModel('core/date')->timestamp(time()))){
                $model->setVendorId($vendorId);
                $model->setStatType($statType);
                $model->setPageviews(1);
                $model->setDate($date);
                $model->save();
            }else{
                $model->setVendorId($vendorId);
                $model->setStatType($statType);
                $model->setPageviews($data[0]['pageviews']+1);
                $model->setId($data[0]['id']);
                $model->save();
            }
        }else{
            $count->getSelect()->order('id desc')->limit(1);
            $data = $count->getData();
            $model->setVendorId($vendorId);
            $model->setStatType($statType);
            $model->setPageviews($data[0]['pageviews']+1);
            $model->setId($data[0]['id']);
            $model->save();
        }
    }
    
    public function getTotalpageviewsByVendor($vendorId = null,$date = null)
    {
        $model = Mage::getModel('udropship/vendor_stats')
                    ->getCollection()->addFieldToFilter('vendor_id', $vendorId);
        if($date !=''):
            $model->addFieldToFilter('date', array('gteq' => date($date.'-01')));
        endif;
        $model->getSelect()->columns('SUM(pageviews) AS total');
        $data = $model->getData();
        if($data[0]['total'] !=0)
            return $data[0]['total'];            
        else
            return 0;
    }
    
    public function getTotalpageviewsByStatType($vendorId = null,$statType = null,$date = null)
    {
        $model = Mage::getModel('udropship/vendor_stats')
                    ->getCollection()->addFieldToFilter('vendor_id', $vendorId)->addFieldToFilter('stat_type', $statType);
        if($date !=''):
            $model->addFieldToFilter('date', array('gteq' => date($date.'-01')));
        endif;
        $model->getSelect()->columns('SUM(pageviews) AS total');
        $data = $model->getData();
        if($data[0]['total'] !=0)
            return $data[0]['total'];            
        else
            return 0;
    }
    
    public function getTotalShipmentOrdersByVendor($vendorId = null,$date = null)
    {
        $totalShipments = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('udropship_vendor', $vendorId)->addFieldToFilter('udropship_status',1); 

        if($date !=''):
            $totalShipments->addFieldToFilter('created_at', array('gteq' => date($date.'-01')));
        endif;
        return count($totalShipments);
    }  
    
    public function getRevenueByVendor($vendorId = null,$date = null)
    {
        $totalShipments = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('udropship_vendor', $vendorId)->addFieldToFilter('udropship_status',1);
        if($date !=''):
            $totalShipments->addFieldToFilter('created_at', array('gteq' => date($date.'-01')));
        endif;
        $totalShipments->addExpressionFieldToSelect('total', 'SUM({{base_total_value}})', 'base_total_value');
        $count = $totalShipments->getData();
        return $count[0]['total'];
    } 

//-----------------------------------------------add by chetan -------------------------------------------------------


	public function getPageviewsStats($vendorId)           //Page Views
	{

$hlp1 = Mage::helper('generalcheck');
$statsconn = $hlp1->getStatsdbconnection();
$productStatsCraftsvilla = $hlp1->getproductStatsCraftsvillaTable();
$last_date24 =  date("Y-m-d H:i:s",strtotime('-24 hour'));
$last_date30 =  date("Y-m-d",strtotime('-30 day'));
$now = date("Y-m-d H:i:s");
$now1 = date("Y-m-d");


$pageview24 = mysql_query("SELECT COUNT(*) AS `count` FROM `".$productStatsCraftsvilla."` WHERE `vendorid` = '".$vendorId."' AND (`date` BETWEEN '".$last_date24."' AND '".$now."') ", $statsconn);

	$rowsPageviews24 = mysql_fetch_array($pageview24);
	$totalPageViews24 = $rowsPageviews24['count'];


$pageview30 = mysql_query("SELECT COUNT(*) AS `count` FROM `".$productStatsCraftsvilla."` WHERE `vendorid` = '".$vendorId."' AND (`date` BETWEEN '".$last_date30."' AND '".$now."') ", $statsconn);

	$rowsPageviews30 = mysql_fetch_array($pageview30);
	$totalPageViews30 = $rowsPageviews30['count'];


$pageviewtotal = mysql_query("SELECT COUNT(*) AS `count` FROM `".$productStatsCraftsvilla."` WHERE `vendorid` = '".$vendorId."'", $statsconn);

	$rowsPageviews = mysql_fetch_array($pageviewtotal);
	$totalPageViews = $rowsPageviews['count'];

$topProduct = mysql_query("SELECT COUNT(*) AS `count`, `product_id` FROM `".$productStatsCraftsvilla."` WHERE `vendorid` = '".$vendorId."' AND (`date` BETWEEN '".$last_date30."' AND '".$now1."') GROUP BY `product_id` ORDER BY `count` DESC LIMIT 5", $statsconn);


		$table1 =   
		'
		<table border=1>
		<tr height="20px">
		<th width=150><b> Image </b></th>
		<th width=150><b> PageViews Last 24 Hour </b></th>
		<th width=150><b> PageViews Last 30 Days </b></th>
		<th width=150><b> Total PageViews </b></th>
		</tr>
		';
$table2 = '';      
	while($rowstopProduct = mysql_fetch_array($topProduct))
	{
		$productId = $rowstopProduct['product_id'];
		$_product = Mage::getModel('catalog/product')->load($productId);
		$prodImage=Mage::helper('catalog/image')->init($_product, 'image')->resize(150, 150);
		$prdLink = $_product->getProductUrl();

	$productCount24 = mysql_query("SELECT COUNT(*) AS `count` FROM `".$productStatsCraftsvilla."` WHERE `product_id` = '".$productId."' AND (`date` BETWEEN '".$last_date24."' AND '".$now."')", $statsconn);

		$rowsproductCount24 = mysql_fetch_array($productCount24);
		$totalproductCount24 = $rowsproductCount24['count'];

	$productCount30 = mysql_query("SELECT COUNT(*) AS `count` FROM `".$productStatsCraftsvilla."` WHERE `product_id` = '".$productId."' AND (`date` BETWEEN '".$last_date30."' AND '".$now."')", $statsconn);

		$rowsproductCount30 = mysql_fetch_array($productCount30);
		$totalproductCount30 = $rowsproductCount30['count'];

	$productCount = mysql_query("SELECT COUNT(*) AS `count` FROM `".$productStatsCraftsvilla."` WHERE `product_id` = '".$productId."'", $statsconn);

		$rowsproductCount = mysql_fetch_array($productCount);
		$totalproductCount = $rowsproductCount['count'];

		$table2 .= '
		<tr>
		<td width=150 ><a href="'.$prdLink.'" target=_blank> <img src ="'.$prodImage.'" ></a></td>
		<td width=200 ><span class="data-tbl"> '.$totalproductCount24.' </span></td>
		<td width=200 ><span class="data-tbl"> '.$totalproductCount30.' </span></td>
		<td width=200 ><span class="data-tbl"> '.$totalproductCount.' </span></td>
		';
	}

$table = $table1.$table2."</table>";

		 mysql_close($statsconn);
		return array($totalPageViews24, $totalPageViews30, $totalPageViews, $table);

	} 

public function getCVscore($dispatchDays, $dispatchDaysCOD, $refundRatio, $disputeRatio)  // CV Score
	
		{  

	if(($dispatchDays > 5) || ($dispatchDays < 0.1))
		{
			$dispatchDays = 0;
		}
	else    {
		$dispatchDays=	min((1.25/$dispatchDays), 1.25);	//prepaid	
		}

	if(($dispatchDaysCOD > 10) ||($dispatchDaysCOD < 0.1))
		{
			$dispatchDaysCOD = 0;
		}

	else    {
			$dispatchDaysCOD = min((4/$dispatchDaysCOD), 1.25);	
		}

	if($refundRatio > 10)
		{
			$refundRatio = 0;
		}
	else    {
			if($refundRatio < 3)
			{ $refundRatio = 2; }
			else{
				$refundRatio = ((3/$refundRatio)*2);
			}
			
		}

	if($disputeRatio > 10)
		{
			$disputeRatio = 0;
		}
	else    {
			if($disputeRatio < 1)
			{ $disputeRatio = 0.5; }
			else{
				$disputeRatio = ((1/$disputeRatio)*0.5);
			}
			
		}

$dispatchDaysRate = ($dispatchDays/1.25) * 5 ;
$dispatchDaysCODRate = ($dispatchDaysCOD/1.25) * 5 ;
$refundRatioRate = ($refundRatio/2) * 5 ;
$disputeRatioRate = ($disputeRatio/0.5) * 5 ;

$totalScore = $dispatchDays + $dispatchDaysCOD + $refundRatio + $disputeRatio;

return array($totalScore, $dispatchDaysRate, $dispatchDaysCODRate, $refundRatioRate, $disputeRatioRate);
 	}
}
?>

<?php
class Craftsvilla_Generalcheck_StatsviewController extends Mage_Core_Controller_Front_Action{
        public function setStatsAction(){

//$hey = $_SERVER['REMOTE_ADDR'];
                $pid = $this->getRequest()->getParam('prdvalue');
                $vid = $this->getRequest()->getParam('vendorId');
                $pid1 = (int) $pid;
                $vid1 = (int) $vid;
//$file = "/var/www/html/var/import/pass1.txt";
//$fp = fopen($file,'wr');
//fwrite($fp,$pid.$vid.':passwordd');
            $new_db_resource = Mage::getSingleton('core/resource');
                $connection = $new_db_resource->getConnection('stats_collectdb');
                //$results    = $connection->query('SELECT * FROM `product_stats_craftsvilla`');

                $queryInsert = "INSERT INTO `product_stats_craftsvilla`( `product_id`, `vendorid`, `date`) VALUES ('".$pid1."','".$vid1."',NOW())";
                $resultValue = $connection->query($queryInsert);

header("access-control-allow-origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

   }


<?php
/**
 * @author      Vikram Acharya
 * @category    Import Anchor Tags in bulk using csv
 * @created On 07-07-2016
 */
ini_set('display_errors', 'On');    
$valid_passwords = array ("seo" => "s3o");
$valid_users = array_keys($valid_passwords);
$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];
$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);
if (!$validated) {
  header('WWW-Authenticate: Basic realm="craftsvilla"');
  header('HTTP/1.0 401 Unauthorized');
  die ("Not authorized");
}
set_time_limit(0);
ini_set('memory_limit','1024M');
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
Mage::app();
 
/***************** UTILITY FUNCTIONS ********************/
function _getConnection($type = 'core_read'){
    return Mage::getSingleton('core/resource')->getConnection($type);
}

function _getTableName($tableName){
    return Mage::getSingleton('core/resource')->getTableName($tableName);
}
 
function _checkIfAnchorExists($anchor_type, $page_detail, $anchor_tag){
    $connection = _getConnection('core_read');
    $sql        = "SELECT COUNT(*) AS count_no FROM " . _getTableName('seo_anchor_tags') . " where `anchor_type` = ? and `page_detail` = ? and `anchor_tag` = ?";
    $count      = $connection->fetchOne($sql, array($anchor_type, $page_detail, $anchor_tag));
    if($count > 0){
        return true;
    }else{
        return false;
    }
}
			
function _insertAnchors($data){
    $connection     = _getConnection('core_write');
    $anchor_type    = $data[0];
    $page_detail    = $data[1];
	$anchor_tag     = $data[2];
	$anchor_link    = $data[3];
    $sequence       = $data[4];
    $sql = "INSERT INTO " . _getTableName('seo_anchor_tags') . " VALUES (?,?, ?, ?, ?, ?)";
    $connection->query($sql, array('',$anchor_type, $page_detail, $anchor_tag, $anchor_link, $sequence));
}

/***************** UTILITY FUNCTIONS ********************/

$message = '';
$count   = 1;

$fh = fopen($_FILES['file']['tmp_name'], 'r+');
$lines = array();
while(($_data = fgetcsv($fh, 10000)) !== FALSE ) {
    if(_checkIfAnchorExists($_data[0], $_data[1], $_data[2])){
        $message .= $count . '> Error:: Anchor Already Exist(' . $_data[2] . ') . <br />';
    }else{
    	try {
        	_insertAnchors($_data);
            $message .= $count . '> Success:: While Inserting Anchor (' . $_data[2] . ') . <br />';
        } catch(Exception $e){
            $message .=  $count .'> Error:: While Inserting Anchor (' . $_data[2] . ') '. $e->getMessage().'<br />';
        }
    }
    $count++;
}
echo $message;
?>
<?php
class Craftsvilla_Pendingorder_Model_Cron
{
	public function sendreminder()
	{
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'Pendingorder_email_template';
		$mailSubject = 'Alarm: Received High Value Order in Pending Status!';
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'places@craftsvilla.com');
		$query = "SELECT sfo.increment_id,sfo.base_grand_total,concat(sfoa.firstname,' ',sfoa.lastname) as name,sfoa.telephone,sfoa.country_id FROM sales_flat_order as sfo left join sales_flat_order_address as sfoa On sfo.entity_id = sfoa.parent_id WHERE sfo.created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 15 MINUTE), INTERVAL 30 MINUTE) AND sfo.created_at <= DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND sfo.state = 'new' AND sfo.status = 'pending' AND sfo.base_grand_total > 1000 AND sfoa.address_type = 'billing'";
		$data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
		$_email = Mage::getModel('core/email_template');
		if(!empty($data)){
			$_pendingOrderData = '';
			foreach($data as $_orderData){
				$_pendingOrderData .= '<tr>
       								<td style="background:#eee;">'.$_orderData['increment_id'].'</td><td style="background:#eee;">'.$_orderData['base_grand_total'].'</td><td style="background:#eee;">'.$_orderData['name'].'</td><td style="background:#eee;">'.$_orderData['telephone'].'</td><td style="background:#eee;">'.$_orderData['country_id'].'</td></tr>';
			}
			$vars = array('orderDetail' => $_pendingOrderData);
			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->sendTransactional($templateId, $sender, array("customercare@craftsvilla.com","manoj@craftsvilla.com"), "CustomerCare", $vars, $storeId);
		}
		
	}
}
?>

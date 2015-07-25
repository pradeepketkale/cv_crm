<?php
class Craftsvilla_Craftsvillatop_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }

	public function postdataAction(){
exit;
		$writecon = Mage::getSingleton('core/resource')->getConnection('core_write');
		$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
		$name = $_POST['name'];
		$emailgosf = $_POST['emailgosf'];
		$phone = $_POST['contactno'];		
		//$password1 = 'helllooooooo';
		//$file = "/home/amit/doejofinal/var/import/chkoutput.txt";
		//$fp = fopen($file,'wr');
		//fwrite($fp,$name.':passwordd'.$emailgosf.':passwordd'.$phone.':passwordd');
		//echo $emailgosf;
		
		$queryDatagosfcheck = "SELECT * FROM `craftsvilla_gosf` WHERE `gosf_email` = '".$emailgosf."'";
		$checkQuery = $readcon->query($queryDatagosfcheck)->fetch();
		//if($checkQuery['gosf_email'] != $emailgosf)
		if($checkQuery['gosf_email'] != $emailgosf)
		{
		$queryDatagosf = "INSERT INTO `craftsvilla_gosf`(`gosf_name`, `gosf_email`, `gosf_number`) VALUES ('".$name."','".$emailgosf."','".$phone."')";
		$insertQuery = $writecon->query($queryDatagosf);
		echo "inserted succesfully";
		}
	}
}

<?php
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
umask(0);
Mage::app();
$vendormodel = Mage::getModel('udropship/vendor')->getCollection();
$i = 0;
	foreach($vendormodel as $_vendormodel)
	{
if($i >= 5000 && $i < 5300){		
		$vendormodelData = Mage::getModel('udropship/vendor')->load($_vendormodel->getVendorId());
		$datavendor = $vendormodelData->getData();
		$data = Zend_Json::decode($datavendor['custom_vars_combined']);
		$data['return_policy_detail'] = '';
		$data1 = Zend_Json::encode($data);
		$datavendor['custom_vars_combined'] = $data1;
		$datavendor['return_policy_detail'] = '';
		$vendormodelData->updateData($datavendor)->save();
		unset($vendormodelData);
		echo $_vendormodel->getVendorId()."-".$_vendormodel->getVendorName()."\n";
		}
		$i++;
	}

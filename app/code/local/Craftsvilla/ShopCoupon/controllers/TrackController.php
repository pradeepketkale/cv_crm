<?php


class Craftsvilla_ShopCoupon_TrackController extends Mage_Core_Controller_Front_Action{
    
	public function getTrackdetailAction()
	{
	
	$trackid = $this->getRequest()->getParam('tracknumber');
	//$vendorcouponcache['trackvalue'] = $trackid;	
	//$password1 = $trackid;
	//$file = "/home/amit/doejofinal/var/import/pass1.txt";
	//$fp = fopen($file,'wr');
	//fwrite($fp,$password1.':passwordd');	
	
	$baseUrl = Mage::helper('courier')->getWsdlPath();
	//SOAP object
	$clientAramex = new SoapClient($baseUrl . 'Tracking.wsdl');
	$aramex_errors = false;
	$clientInfo = Mage::helper('courier')->getClientInfo();	

	$aramexParams = $this->_getAuthDetails();
	$aramexParams['Transaction'] 	= array('Reference1' => '001' );
	$aramexParams['Shipments'] 		= array($trackid);

	//print_r($aramexParams);exit;

	$_resAramex = $clientAramex->TrackShipments($aramexParams);

	$_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;

	$vendorcouponcache['trackvalue'] = $this->getTrackingInfoTable($_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult);
	
	$encode_json_str = json_encode($vendorcouponcache);
	echo $encode_json_str;
		exit();

	}
	public function _getAuthDetails()
    {
		return array(
						'ClientInfo'  			=> array(
												'AccountCountryCode'	=> Mage::getStoreConfig('courier/general/account_country_code'),
												'AccountEntity'		 	=> Mage::getStoreConfig('courier/general/account_entity'),
												'AccountNumber'		 	=> Mage::getStoreConfig('courier/general/account_number'),
												'AccountPin'		 	=> Mage::getStoreConfig('courier/general/account_pin'),
												'UserName'			 	=> Mage::getStoreConfig('courier/general/user_name'),
												'Password'			 	=> Mage::getStoreConfig('courier/general/password'),
												'Version'			 	=> 'v1.0'
												)
					);
    }
	
	public function getTrackingInfoTable($HAWBHistory) {
	$_resultTable = "Please contact Aramex customer care number for more details. Phone number is - 022-3300-3300";
        $_resultTable .= '<table summary="Item Tracking"  class="data-table">';
        $_resultTable .= '<col width="1">
                          <col width="1">
                          <col width="1">
                          <col width="1">
                          <thead>
                          <tr class="first last">
                          <th>Location</th>
                          <th>Action Date/Time</th>
                          <th class="a-right">Tracking Description</th>
                          <th class="a-center">Comments</th>
                          </tr>
                          </thead><tbody>';

        foreach ($HAWBHistory as $HAWBUpdate) {

            $_resultTable .= '<tr>
                <td>' . $HAWBUpdate->UpdateLocation . '</td>
                <td>' . $HAWBUpdate->UpdateDateTime . '</td>
                <td>' . $HAWBUpdate->UpdateDescription . '</td>
                <td>' . $HAWBUpdate->Comments . '</td>
                </tr>';
        }
        $_resultTable .= '</tbody></table>';

        return $_resultTable;
    }
}

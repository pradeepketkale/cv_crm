<?php

class Craftsvilla_Dhlinternational_Helper_Data extends Mage_Core_Helper_Abstract
{
const DISP_HOME_PAGE = '0';
	const DISP_CATEGORY = '1';
	protected $globalproductcode = "P";
	protected $localproductcode = "P";
	public $remoteAreaService = false;
	public $arrayResponse = array();
	public $paymentCountryCode = 'SG';
	protected $siteId;
	protected $password;
	protected $timeNow;
	protected $mref;
	protected $time;
	protected $paymentAccountNumber;
	protected $conFullname;
	protected $conAddressLine1;
	protected $conAddressLine2;
	protected $conCity;
	protected $conRegion;
	protected $conPostcode;
	protected $conCountryId;
	protected $conCountry;
	protected $conPersonName;
	protected $conTelp;
	protected $conEmailFrom;
	protected $conEmailTo;
	protected $pieceWeight;
	protected $totalWeight;
	protected $oriStoreName;
	protected $oriStreetLine1;
	protected $oriStreetLine2;
	protected $oriCity;
	protected $oriRegionId;
	protected $oriPostcode;
	protected $oriCountryId;
	protected $oriCountry;
	protected $oriOwnerName;
	protected $oriPhone;
	protected $oriEmailFrom;
	protected $oriEmailTo;
	protected $xml;
	protected $shippingPaymentType = 'S';
	protected $dutyPaymentType = 'R';
	//property sambung menyambung
	protected $regionType;
	protected $xmlRequestType;
	protected $xmlRequest;
	protected $xmlResponse;
	protected $payer;
	protected $dutyAccountNumber;
	protected $orderId;
	protected $dutiableTerm;
	protected $dutiableDeclaredValue;
	protected $dutiableDeclareCurrency;
	protected $billingDutyAccountNumber;
	protected $contentText;
	protected $insuredAmmount;
	
	public function getDisplayOption(){
		return array(
			array('value'=>self::DISP_HOME_PAGE, 'label'=>$this->__('Home page')),
			array('value'=>self::DISP_CATEGORY, 'label'=>$this->__('Category')),
		);
	}
 
	public function xmlRequest1($x,$orderId, $xmlRequestType, $type = 'tracking')
 	 {
 	  
		return $this->asisXml($x,$orderId,$xmlRequestType,$type)->regionType()->xmlType()->xmlGenarate();
 	 }
 	 
 public function getId($order_id)
	{
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$table = $resource->getTableName('dhlinternational/dhlinternational');
		$query = 'SELECT id FROM ' . $table . ' WHERE order_id = '
				. $order_id . ' LIMIT 1';
		$id = $readConnection->fetchOne($query);
		return $id;
	}

	public function getCountry($id)
	{
		$countries = Mage::getResourceModel('directory/country_collection')->loadByStore()->toOptionArray();

		foreach ($countries as $country)
		{
			if (in_array($id, $country))
			{
				return $country['label'];
			}
		}
	}

	public function getRegion($name, $country = 'US')
	{
		if ($country == 'US')
		{
			$regions = $this->regionUs();
			return $regions["$name"];
		}
		else
			return $name;
	}

	public function xmlRequest($xml)
	{
		
		$client = new Zend_Http_Client();
		$client->setUri('https://xmlpi-ea.dhl.com/XMLShippingServlet');
		
		$response = $client->setRawData($xml, 'text/xml')->request('POST');
		if ($response->isSuccessful())
		{
		var_dump($response->getBody());
		exit;
			return $response->getBody();
		}
	}

	

	public function regionUs()
	{
		return array(
			"Alabama" => "AL",
			"Alaska" => "AK",
			"Arizona" => "AZ",
			"Arkansas" => "AR",
			"California" => "CA",
			"Colorado" => "CO",
			"Connecticut" => "CT",
			"Delaware" => "DE",
			"District of Columbia" => "DC",
			"Florida" => "FL",
			"Georgia" => "GA",
			"Hawaii" => "HI",
			"Idaho" => "ID",
			"Illinois" => "IL",
			"Indiana" => "IN",
			"Iowa" => "IA",
			"Kansas" => "KS",
			"Kentucky" => "KY",
			"Louisiana" => "LA",
			"Maine" => "ME",
			"Montana" => "MT",
			"Nebraska" => "NE",
			"Nevada" => "NV",
			"New Hampshire" => "NH",
			"New Jersey" => "NJ",
			"New Mexico" => "NM",
			"New York" => "NY",
			"North Carolina" => "NC",
			"North Dakota" => "ND",
			"Ohio" => "OH",
			"Oklahoma" => "OK",
			"Oregon" => "OR",
			"Maryland" => "MD",
			"Massachusetts" => "MA",
			"Michigan" => "MI",
			"Minnesota" => "MN",
			"Mississippi" => "MS",
			"Missouri" => "MO",
			"Pennsylvania" => "PA",
			"Rhode Island" => "RI",
			"South Carolina" => "SC",
			"South Dakota" => "SD",
			"Tennessee" => "TN",
			"Texas" => "TX",
			"Utah" => "UT",
			"Vermont" => "VT",
			"Virginia" => "VA",
			"Washington" => "WA",
			"West Virginia" => "WV",
			"Wisconsin" => "WI",
			"Wyoming" => "WY"
		);
	}

	public function asisXml($declaredvalue,$orderId, $xmlRequestType, $type = 'tracking')
	{
		
		$this->orderId = $orderId;
		
		$originSetting = Mage::getStoreConfig('shipping');
		
		$storeName = 'Craftsvilla';
		$storePhone = '+91-98926-76399';
		$storeEmail = "customercare@craftsvilla.com";
		$storeOwnerName = Mage::getStoreConfig('trans_email/ident_general/name');
         $originSetting["origin"]["street_line1"] = '19,Centrium Mall, 1st Floor,';
         $originSetting["origin"]["street_line2"]='Lokhandwala, Kandivli(E)';
//penerima
		$order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);
		
		$items = $order->getAllVisibleItems();
		
		foreach ($items as $item)
		{
			//$this->contentMessage = $item->name;
			//$this->contentMessage = array();
			$dhlitemname = mysql_escape_string($item->name);
      		$this->contentMessage = substr(str_replace('&','',$dhlitemname),0,90);
			break;
		}
		
		
		$shipping_method = $order->shipping_method;
		
		if(strpos($shipping_method, 'EXPRESS WORLDWIDE') != 0)
		{
			$this->globalproductcode = 'P';
			$this->localproductcode = 'P';
		}
		elseif(strpos($shipping_method, 'EXPRESS 9:00') != 0)
		{
			$this->globalproductcode = 'E';
			$this->localproductcode = 'E';
		}
		elseif(strpos($shipping_method, 'EXPRESS 10:30') != 0)
		{
			$this->globalproductcode = 'M';
			$this->localproductcode = 'M';
		}
		elseif(strpos($shipping_method, 'EXPRESS 12:00') != 0)
		{
			$this->globalproductcode = 'Y';
			$this->localproductcode = 'Y';
		}
		$shippingAddressId = $order->shipping_address_id;
		$address = Mage::getModel('sales/order_address')->load($shippingAddressId);
       
		$latestdecimal=substr(round($order->weight,1),-1);
		if($latestdecimal<=5){
			$weight=floor($weight)+0.5;
		}		
		if($latestdecimal>=6){
			$weight=ceil($weight);
		}


		$timestamp = time();
		$data = array();
		for ($i = 0; $i < 31; $i++)
		{
			$data[$i] = rand(1, 9);
		}
		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
		$addressLine = array();
        $addressLine[0] = str_replace($search, $replace, substr($address->street,0,35));
         $addressLine[1]= str_replace($search, $replace, substr($address->street,35));
		//$addressLine = wordwrap($address->street, 35, "<br>", true);
		//$addressLine = explode(",", $addressLine);
      // print_r($addressLine);exit;
       
//config
	   $this->xmlRequestType = $xmlRequestType;
		$this->siteId = 'KRIBHAHCR';
		$this->password = 'sBRCMQuUI8';
		$this->timeNow = date('Y-m-d');
		$this->mref = implode('', $data);
		$this->time = date('c', $timestamp);
		$this->paymentAccountNumber = '530956424';
//		$this->billingDutyAccountNumber = ($getXml->getConfigXml('inco_term')) ? $getXml->getConfigXml('dtp_account') : $getXml->getConfigXml('payment_account_number');
		$this->conFullname = $address->firstname . ' ' . $address->lastname;
		$this->conAddressLine1 =$addressLine[0];
		$this->conAddressLine2 = $addressLine[1];
		$this->conCity = str_replace($search, $replace, $address->city);
		$this->conRegion = str_replace($search, $replace, $this->getRegion($address->region));
		$this->conPostcode = $address->postcode;
		$this->conCountryId = $address->country_id;
		$this->conCountry = str_replace($search, $replace, $this->getCountry($address->country_id));
		$this->conPersonName = $address->firstname . ' ' . $address->lastname;
		//echo $this->conTelp = $address->telephone;exit;
		$this->conTelp = preg_replace('/[^A-Za-z0-9]/', "", $address->telephone);
		$this->conEmailFrom = $address->email;
		$this->conEmailTo = $address->email;

		$this->pieceWeight = $weight;
		$this->totalWeight = $weight;
		$this->globalproductcode;
		$this->localproductcode;
		$this->timeNow;
        
		$this->oriStoreName = $storeName;
		$this->oriStreetLine1 = $originSetting["origin"]["street_line1"];
		$this->oriStreetLine2 = $originSetting["origin"]["street_line2"];
		$this->oriCity = $originSetting["origin"]["city"];
		$this->oriRegionId = $originSetting["origin"]["region_id"];
		$this->oriPostcode = $originSetting["origin"]["postcode"];
		$this->oriCountryId = $originSetting["origin"]["country_id"];
		$this->oriCountry = $this->getCountry($originSetting["origin"]["country_id"]);
		$this->oriOwnerName = $storeOwnerName;
		$this->oriPhone = $storePhone;
		$this->oriEmailFrom = $storeEmail;
		$this->oriEmailTo = $storeEmail;
		if ($type == 'return')
		{
			
			$this->shippingPaymentType = 'R';
			$this->dutyPaymentType = 'R';

			$this->oriStoreName = $address->firstname . ' ' . $address->lastname;
			$this->oriStreetLine1 = $addressLine[0];
			$this->oriStreetLine2 = $addressLine[1];
			
			$this->oriCity = $address->city;
			$this->oriRegionId = $this->getRegion($address->region);
			$this->oriPostcode = $address->postcode;
			$this->oriCountryId = $address->country_id;
			$this->oriCountry = $this->getCountry($address->country_id);
			$this->oriOwnerName = $address->firstname . ' ' . $address->lastname;
			$this->oriPhone = $address->telephone;
			$this->oriEmailFrom = $address->email;
			$this->oriEmailTo = $address->email;

			$this->conFullname = $storeName;
			$this->conAddressLine1 = $originSetting["origin"]["street_line1"];
			$this->conAddressLine2 = $originSetting["origin"]["street_line2"];
			$this->conCity = $originSetting["origin"]["city"];
			$this->conRegion = $originSetting["origin"]["region_id"];
			$this->conPostcode = $originSetting["origin"]["postcode"];
			$this->conCountryId = $originSetting["origin"]["country_id"];
			$this->conCountry = $this->getCountry($originSetting["origin"]["country_id"]);
			$this->conPersonName = $storeOwnerName;
			$this->conTelp = $storePhone;
			$this->conEmailFrom = $storeEmail;
			$this->conEmailTo = $storeEmail;
		}
		elseif ($type == 'tracking')
		{
			
			$this->shippingPaymentType = 'S';
			$this->dutyPaymentType = 'R';
		}
		//		setting pickup
		$this->accountType = 'D';
		$this->phoneExtention = '5053';
		$this->locationType = 'B';
		$this->readyByTime = '10:20:00 AM';
		$this->closeTime = '02:20:00 PM';
		$this->afterHoursClosingTime = '04:20:00 PM';
		$this->afterHoursLocation = 'String';
		$this->pickupContactName = 'Subhayu';
		$this->pickupContactPhone = '4801313131';
		$this->pickupContactPhoneExtention = '5768';
		$this->doorTo = 'DD';
		$this->dimesionUnit = 'CM';
//		$this->globalProductCode = $getXml->getConfigXml('global_product_code');
		$this->weightUnit = 'KG';
		$this->billingAccountNumber = $this->paymentAccountNumber;
		$this->insuredAmmount  = "0.00";
//		$this->contentMessage = $getXml->getConfigXml('infotext');
		
		if($this->xmlRequestType == 'shipmentvalidation')
		{
			$this->dutiableTerm = 'DAP';
			$this->dutiableDeclareCurrency = 'INR';
			$this->dutiableDeclaredValue = number_format($order->base_subtotal, 2, '.', '');
			$this->declaredvalue = $declaredvalue;
		}

	
	
		return $this;
	}

	public function regionType()
	{
		$ap = array(
			'AE' => 'UNITED ARAB EMIRATES',
			'AF' => 'AFGHANISTAN',
			'AL' => 'ALBANIA',
			'AM' => 'ARMENIA',
			'AU' => 'AUSTRALIA',
			'BA' => 'BOSNIA AND HERZEGOVINA',
			'BD' => 'BANGLADESH',
			'BH' => 'BAHRAIN',
			'BN' => 'BRUNEI',
			'BY' => 'BELARUS',
			'CI' => 'COTE DIVOIRE',
			'CN' => 'CHINA ',
			'CY' => 'CYPRUS',
			'DZ' => 'ALGERIA',
			'EG' => 'EGYPT',
			'FJ' => 'FIJI',
			'GH' => 'GHANA',
			'HK' => 'HONG KONG',
			'HR' => 'CROATIA',
			'ID' => 'INDONESIA',
			'IL' => 'ISRAEL',
			'IN' => 'INDIA',
			'IQ' => 'IRAQ',
			'IR' => 'IRAN (ISLAMIC REPUBLIC OF)',
			'JO' => 'JORDAN',
			'JP' => 'JAPAN',
			'KE' => 'KENYA',
			'KG' => 'KYRGYZSTAN',
			'KR' => 'KOREA',
			'KW' => 'KUWAIT',
			'KZ' => 'KAZAKHSTAN',
			'LA' => 'LAO PEOPLES DEMOCRATIC REPUBLIC',
			'LB' => 'LEBANON',
			'LK' => 'SRI LANKA',
			'MA' => 'MOROCCO',
			'MD' => 'MOLDOVA',
			'MK' => 'MACEDONIA',
			'MM' => 'MYANMAR',
			'MO' => 'MACAU',
			'MT' => 'MALTA',
			'MU' => 'MAURITIUS',
			'MY' => 'MALAYSIA',
			'NA' => 'NAMIBIA',
			'NG' => 'NIGERIA',
			'NP' => 'NEPAL',
			'NZ' => 'NEW ZEALAND',
			'OM' => 'OMAN',
			'PH' => 'PHILIPPINES',
			'PK' => 'PAKISTAN',
			'QA' => 'QATAR',
			'RE' => 'REUNION',
			'RS' => 'SERBIA',
			'RU' => 'RUSSIAN FEDERATION',
			'SA' => 'SAUDI ARABIA',
			'SD' => 'SUDAN',
			'SG' => 'SINGAPORE',
			'SN' => 'SENEGAL                            ',
			'SY' => 'SYRIA                             ',
			'TH' => 'THAILAND                          ',
			'TJ' => 'TAJIKISTAN                        ',
			'TR' => 'TURKEY                            ',
			'TW' => 'TAIWAN                            ',
			'UA' => 'UKRAINE                           ',
			'UZ' => 'UZBEKISTAN                        ',
			'VN' => 'VIETNAM                           ',
			'YE' => 'YEMEN ',
			'ZA' => 'SOUTH AFRICA                      ',
		);
		$ea = array(
			'AT' => 'AUSTRIA                           ',
			'BE' => 'BELGIUM                            ',
			'BG' => 'BULGARIA                          ',
			'CH' => 'SWITZERLAND                       ',
			'CZ' => 'CZECH  ',
			'DE' => 'GERMANY                           ',
			'DK' => 'DENMARK                           ',
			'EE' => 'ESTONIA                            ',
			'ES' => 'SPAIN                             ',
			'FI' => 'FINLAND                           ',
			'FR' => 'FRANCE                            ',
			'GB' => 'UNITED KINGDOM                    ',
			'GR' => 'GREECE                            ',
			'HU' => 'HUNGARY                           ',
			'IE' => 'IRELAND ',
			'IS' => 'ICELAND                           ',
			'IT' => 'ITALY                             ',
			'LT' => 'LITHUANIA                         ',
			'LU' => 'LUXEMBOURG                        ',
			'LV' => 'LATVIA                            ',
			'NL' => 'NETHERLANDS ',
			'NO' => 'NORWAY                            ',
			'PL' => 'POLAND                            ',
			'PT' => 'PORTUGAL                          ',
			'RO' => 'ROMANIA                           ',
			'SE' => 'SWEDEN                            ',
			'SI' => 'SLOVENIA                          ',
			'SK' => 'SLOVAKIA                          ',
		);
		$am = array(
			'AG' => 'ANTIGUA                            ',
			'AI' => 'ANGUILLA                           ',
			'AR' => 'ARGENTINA                          ',
			'AW' => 'ARUBA                              ',
			'BB' => 'BARBADOS',
			'BM' => 'BERMUDA                            ',
			'BO' => 'BOLIVIA                            ',
			'BR' => 'BRAZIL                             ',
			'BS' => 'BAHAMAS                            ',
			'CA' => 'CANADA                             ',
			'CL' => 'CHILE                              ',
			'CO' => 'COLOMBIA                           ',
			'CR' => 'COSTA RICA                         ',
			'DM' => 'DOMINICA                           ',
			'DO' => 'DOMINICAN REPUBLIC                 ',
			'EC' => 'ECUADOR                            ',
			'GD' => 'GRENADA                            ',
			'GF' => 'FRENCH GUYANA                      ',
			'GP' => 'GUADELOUPE                         ',
			'GT' => 'GUATEMALA                          ',
			'GU' => 'GUAM                               ',
			'GY' => 'GUYANA (BRITISH)                   ',
			'HN' => 'HONDURAS                           ',
			'HT' => 'HAITI                              ',
			'JM' => 'JAMAICA                            ',
			'KN' => 'ST. KITTS                          ',
			'KY' => 'CAYMAN ISLANDS                     ',
			'LC' => 'ST. LUCIA                          ',
			'MQ' => 'MARTINIQUE                         ',
			'MX' => 'MEXICO                             ',
			'NI' => 'NICARAGUA                          ',
			'PA' => 'PANAMA                             ',
			'PE' => 'PERU                               ',
			'PR' => 'PUERTO RICO                        ',
			'PY' => 'PARAGUAY                           ',
			'SR' => 'SURINAME                           ',
			'SV' => 'EL SALVADOR                        ',
			'TC' => 'TURKS AND CAICOS ISLANDS           ',
			'TT' => 'TRINIDAD AND TOBAGO                ',
			'US' => 'UNITED STATES OF AMERICA   ',
			'UY' => 'URUGUAY                            ',
			'VC' => 'ST. VINCENT                        ',
			'VE' => 'VENEZUELA                          ',
			'VG' => 'VIRGIN ISLANDS (BRITISH)           ',
			'XC' => 'CURACAO                            ',
			'XM' => 'ST. MAARTEN                        ',
			'XN' => 'NEVIS                              ',
			'XY' => 'ST. BARTHELEMY                     ',
		);

		if (array_key_exists($this->oriCountryId, $ap))
			$this->regionType = 'AP';
		elseif (array_key_exists($this->oriCountryId, $ea))
			$this->regionType = 'EA';
		elseif (array_key_exists($this->oriCountryId, $am))
			$this->regionType = 'US';
		else
			$this->regionType = 'KOSONG';
        
		return $this;
	}
	

	public function xmlType()
	{
		$this->contentShipmentValidation = "
		<Request>
			<ServiceHeader>
				<MessageTime>$this->time</MessageTime>
				<MessageReference>$this->mref</MessageReference>
				<SiteID>$this->siteId</SiteID>
				<Password>$this->password</Password>
			</ServiceHeader>
		</Request>
		<LanguageCode>en</LanguageCode>
		<PiecesEnabled>Y</PiecesEnabled>
		<Billing>
			<ShipperAccountNumber>$this->paymentAccountNumber</ShipperAccountNumber>
			<ShippingPaymentType>$this->shippingPaymentType</ShippingPaymentType>	
			<BillingAccountNumber>$this->paymentAccountNumber</BillingAccountNumber>
			<DutyPaymentType>$this->dutyPaymentType</DutyPaymentType>
		</Billing>
		<Consignee>
			<CompanyName>$this->conFullname</CompanyName>
			<AddressLine>$this->conAddressLine1</AddressLine>
			<AddressLine>$this->conAddressLine2</AddressLine>
			<City>$this->conCity</City>
			<PostalCode>$this->conPostcode</PostalCode>
			<CountryCode>$this->conCountryId</CountryCode>
			<CountryName>$this->conCountry</CountryName>
			<Contact>
				<PersonName>$this->conPersonName</PersonName>
				<PhoneNumber>$this->conTelp</PhoneNumber>
				<PhoneExtension>44444</PhoneExtension>
				<FaxNumber>444444444</FaxNumber>
				<Telex>44444444444</Telex>
				<Email>
					<From>$this->conEmailFrom</From>
					<To>$this->conEmailTo</To>
					<cc>testcc1</cc>
					<cc>testcc2</cc>
					<Subject>test email</Subject>
					<ReplyTo>test@dhl.com</ReplyTo>
					<Body>this is test shipment</Body>
				</Email>
			</Contact>
		</Consignee>
		<Commodity>
			<CommodityCode>1</CommodityCode>
			<CommodityName>String</CommodityName>
		</Commodity>
		<Dutiable>
			<DeclaredValue>$this->declaredvalue</DeclaredValue>
			<DeclaredCurrency>$this->dutiableDeclareCurrency</DeclaredCurrency>
			<TermsOfTrade>$this->dutiableTerm</TermsOfTrade>
		</Dutiable>
		<Reference>
			<ReferenceID>$this->orderId</ReferenceID>
			<ReferenceType>St</ReferenceType>
		</Reference>
		<ShipmentDetails>
			<NumberOfPieces>1</NumberOfPieces>
			<CurrencyCode>USD</CurrencyCode>
			<Pieces>
							<Piece>
								<PieceID>1</PieceID>
								<PackageType>EE</PackageType>
								<Weight>$this->pieceWeight</Weight>
							</Piece>
			</Pieces>
			<PackageType>CP</PackageType>
			<Weight>$this->totalWeight</Weight>
			<DimensionUnit>C</DimensionUnit>
			<WeightUnit>K</WeightUnit>
			<GlobalProductCode>$this->globalproductcode</GlobalProductCode>
			<LocalProductCode>$this->localproductcode</LocalProductCode>
			<DoorTo>DD</DoorTo>
			<Date>$this->timeNow</Date>
			<Contents>$this->contentMessage</Contents>
			<IsDutiable>Y</IsDutiable>
			<InsuredAmount>$this->insuredAmmount</InsuredAmount>
		</ShipmentDetails>
		<Shipper>
			<ShipperID>967080215</ShipperID>
			<CompanyName>$this->oriStoreName</CompanyName>
			<AddressLine>$this->oriStreetLine1</AddressLine>
			<AddressLine>$this->oriStreetLine2</AddressLine>
			<City>$this->oriCity</City>
			<PostalCode>$this->oriPostcode</PostalCode>
			<CountryCode>$this->oriCountryId</CountryCode>
			<CountryName>$this->oriCountry</CountryName>
			<Contact>
				<PersonName>$this->oriOwnerName</PersonName>
				<PhoneNumber>$this->oriPhone</PhoneNumber>
				<PhoneExtension>2222</PhoneExtension>
				<FaxNumber>2222222222</FaxNumber>
				<Telex>2222222222</Telex>
				<Email>
					<From>$this->oriEmailFrom</From>
					<To>$this->oriEmailTo</To>
					<cc>CC</cc>
					<cc>CC</cc>
					<Subject>Subject</Subject>
					<ReplyTo>ReplayTo</ReplyTo>
					<Body>Body</Body>
				</Email>
			</Contact>
		</Shipper>";

		$apShipmentValidation = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>
	<req:ShipmentValidateRequestAP xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com
	ship-val-req_AP.xsd">
		$this->contentShipmentValidation
		<!--SpecialService>
			<SpecialServiceType>S</SpecialServiceType>
			<ChargeValue>3.1</ChargeValue>
			<CurrencyCode>USD</CurrencyCode>
		</SpecialService-->
	</req:ShipmentValidateRequestAP>
SCRIPT;

		$eaShipmentValidation = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentValidateRequestEA xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-req_EA.xsd">
  $this->contentShipmentValidation
<NewShipper>Y</NewShipper>
</req:ShipmentValidateRequestEA>
SCRIPT;


		$usShipmentValidation = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>		
<req:ShipmentValidateRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-req.xsd">
  $this->contentShipmentValidation
  		 <RequestedPickupTime>Y</RequestedPickupTime>
		<NewShipper>Y</NewShipper>
		<DutyAccountNumber>962730810</DutyAccountNumber>
		<ExportDeclaration>
			<InterConsignee>String</InterConsignee>
			<IsPartiesRelation>N</IsPartiesRelation>
			<ECCN>EAR99</ECCN>
			<SignatureName>String</SignatureName>
			<SignatureTitle>String</SignatureTitle>
			<ExportReason>S</ExportReason>
			<ExportReasonCode>P</ExportReasonCode>
			<SedNumber>FTSR</SedNumber>
			<SedNumberType>F</SedNumberType>
			<MxStateCode>St</MxStateCode>
			<ExportLineItem>
				<LineNumber>200</LineNumber>
				<Quantity>32</Quantity>
				<QuantityUnit>String</QuantityUnit>
				<Description>String</Description>
				<Value>200</Value>
				<IsDomestic>Y</IsDomestic>
				<ScheduleB>3002905110</ScheduleB>
				<ECCN>EAR99</ECCN>
				<Weight>
					<Weight>0.5</Weight>
					<WeightUnit>L</WeightUnit>
				</Weight>
				<License>
					<LicenseNumber>D123456</LicenseNumber>
					<ExpiryDate>2120-08-10</ExpiryDate>
				</License>
				<LicenseSymbol>String</LicenseSymbol>
			</ExportLineItem>
		</ExportDeclaration>
		<PieceID>NA</PieceID>
						<PackageType>EE</PackageType>
						<DoorTo>DD</DoorTo>
			<DimensionUnit>C</DimensionUnit>
			<InsuredAmount>$this->insuredAmmount</InsuredAmount>
			<PackageType>EE</PackageType>
</req:ShipmentValidateRequest>
SCRIPT;

		
//============ BOOKING PICKUP XML =====================
		$this->contentBookingPickup =
				"
<Request>
        <ServiceHeader>
				<MessageTime>$this->time</MessageTime>
				<MessageReference>$this->mref</MessageReference>
            <SiteID>$this->siteId</SiteID>
				<Password>$this->password</Password>
        </ServiceHeader>
    </Request>
    <Requestor>
        <AccountType>$this->accountType</AccountType>
        <AccountNumber>$this->paymentAccountNumber</AccountNumber>
        <RequestorContact>
            <PersonName>$this->oriStoreName</PersonName>
            <Phone>$this->phoneExtention</Phone>
            <PhoneExtension>$this->phoneExtention</PhoneExtension>
        </RequestorContact>
    </Requestor>
    <Place>
			<LocationType>B</LocationType>
			<CompanyName>$this->oriStoreName</CompanyName>
			<Address1>$this->oriStreetLine1</Address1>
			<Address2>$this->oriStreetLine2</Address2>
			<PackageLocation>$this->oriCity</PackageLocation>
			<City>$this->oriCity</City>
			<DivisionName>$this->oriCity</DivisionName>
			<CountryCode>$this->oriCountryId</CountryCode>
			<PostalCode>$this->oriPostcode</PostalCode>
    </Place>
    <Pickup>
        <PickupDate>$this->timePickup</PickupDate>
        <ReadyByTime>$this->readyByTime</ReadyByTime>
        <CloseTime>$this->closeTime</CloseTime>
        <Pieces>1</Pieces>
        <weight>
            <Weight>$this->pieceWeight</Weight>
            <WeightUnit>K</WeightUnit>
        </weight>
    </Pickup>
    <PickupContact>
        <PersonName>$this->pickupContactName</PersonName>
        <Phone>$this->pickupContactPhone</Phone>
        <PhoneExtension>$this->pickupContactPhoneExtention</PhoneExtension>
    </PickupContact>
     <ShipmentDetails>
        <AccountType>$this->accountType</AccountType>
        <AccountNumber>$this->paymentAccountNumber</AccountNumber>
        <BillToAccountNumber>$this->paymentAccountNumber</BillToAccountNumber>
        <AWBNumber>$awbcode</AWBNumber>
        <NumberOfPieces>1</NumberOfPieces>
        <Weight>$this->totalWeight</Weight>
        <WeightUnit>K</WeightUnit>
        <GlobalProductCode>$this->globalproductcode</GlobalProductCode>
        <DoorTo>$this->doorTo</DoorTo>
        <DimensionUnit>C</DimensionUnit>
        <InsuredAmount>$this->insuredAmmount</InsuredAmount>
        <InsuredCurrencyCode>USD</InsuredCurrencyCode>
        <Pieces>
            <Weight>$this->pieceWeight</Weight>
        </Pieces>
        <SpecialService>S</SpecialService>
        <SpecialService>T</SpecialService>
    </ShipmentDetails>
";

		$apBookingPickup = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>		
<req:BookPickupRequestAP xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com book-pickup-req.xsd">
  $this->contentBookingPickup
</req:BookPickupRequestAP>
SCRIPT;

		$eaBookingPickup = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>		
<req:BookPickupRequestEA xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com book-pickup-req_EA.xsd">
  $this->contentBookingPickup
</req:BookPickupRequestEA>
SCRIPT;

		$usBookingPickup = <<<SCRIPT
<?xml version="1.0" encoding="UTF-8"?>		
<req:BookPickupRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com book-pickup-req.xsd">
  $this->contentBookingPickup
</req:BookPickupRequest>
SCRIPT;

		if ($this->xmlRequestType == 'shipmentvalidation')
		{
			switch ($this->regionType)
			{
				case "US":
					//$this->xmlRequest = $this->xml_encode($usShipmentValidation);
					$this->xmlRequest = $usShipmentValidation;
					break;
				case "EA":
					//$this->xmlRequest = $this->xml_encode($eaShipmentValidation);
				     $this->xmlRequest = $eaShipmentValidation;
					break;
				case "AP":
				//	$this->xmlRequest = $this->xml_encode($apShipmentValidation);
					$this->xmlRequest = $apShipmentValidation;
					break;
			}
		}
		elseif ($this->xmlRequestType == 'bookingpickup')
		{
			switch ($this->regionType)
			{
				case "US":
					$this->xmlRequest = $usBookingPickup;
					break;
				case "EA":
					$this->xmlRequest = $eaBookingPickup;
					break;
				case "AP":
					$this->xmlRequest = $apBookingPickup;
					break;
			}
		}
		//echo '<pre>';print_r($this->xmlRequest);
		
		return $this;
	}

   /*public function xml_encode($string)
	{
		$string1 = $this->utf8_for_xml($string);
		$string1=preg_replace("/&/", "&amp;", $string1);
		$string1=preg_replace("/</", "&lt;", $string1);
		$string1=preg_replace("/>/", "&gt;", $string1);
		$string1=preg_replace("/\"/", "&quot;", $string1);
		$string1=preg_replace("/%/", "&#37;", $string1);
		$string1=preg_replace("/]/", "", $string1);
		$string1=preg_replace("/!/", ".", $string1);

    	return utf8_encode($string1);
    	
	}
	
	public function utf8_for_xml($string)
	{
    	return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u',' ', $string);
    	
	}
*/
	
	public function xmlGenarate()
	{
		
       	$client = new Zend_Http_Client();
		$client->setUri('https://xmlpi-ea.dhl.com/XMLShippingServlet');
		//echo '<pre>';print_r($this->xmlRequest);exit;
		$response = $client->setRawData($this->xmlRequest, 'text/xml')->request('POST');
		
	//	echo '<pre>';print_r($response);exit;
		if ($response->isSuccessful())
		{
			
			$this->xmlResponse = $response->getBody();
			//echo '<pre>';print_r($this->xmlResponse);exit;
		}

		$xmlResponse = simplexml_load_string($this->xmlResponse);
		//echo '<pre>';print_r($xmlResponse);exit;
    	$model = Mage::getModel('dhlinternational/dhlinternational')->getCollection()
									->addFieldToFilter('order_id', $xmlResponse->Reference->ReferenceID);
		
		
		
			if($model->count() == 0)
				{
					
					$model = Mage::getModel('dhlinternational/dhlinternational')
											     									->setOrderId($xmlResponse->Reference->ReferenceID)
												     							    ->setStatusAwb($this->xmlResponse)
																				    ->setTrackingAwb($xmlResponse->AirwayBillNumber)
																				    ->save();
	          
                return true;
				}
			
				
    	 
		if ($xmlResponse->Note->ActionNote == 'Success')
		{
			return $this->pdf($xmlResponse->Reference->ReferenceID);
			return $this;
		}
		else
		{
			
			echo $xmlResponse->Response->Status->Condition->ConditionData;
			exit;
		}
	}

	public function getResponse()
	{
		return $this->xmlResponse;
	}
	
	public function pdf($id)
	{
		$typeXml = 'tracking';
		$this->getPdf($id, $typeXml);
	}

	public function getPdf($id, $typeXml = 'tracking')
	{
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$dhlquery = "select * from `dhlinternational` WHERE `order_id`= '".$id."'";
		$resultdhl = $read->query($dhlquery)->fetch();
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($id);
		$items = $order->getAllVisibleItems();
		$inco_term = '1';
		$dtpaccount = '';//530956424/530886811
		$firsitem = '';
		foreach ($items as $item)
		{
			$firsitem = $item->name;
			break;
		}
		
		if ($typeXml == 'return')
			$xml = $resultdhl['status_return'];
		else
			$xml = $resultdhl['status_awb'];
        
		//var_dump($xml);exit;
		$xmlResponse = simplexml_load_string($xml);
		//echo Mage::getBaseDir('skin');exit;
		//echo $xmlResponse->Dutiable->TermsOfTrade;exit;
	//	var_dump($xmlResponse);exit;
//		$this->_helper->layout->disableLayout();
//		$this->_helper->viewRenderer->setNoRender();
       // echo $this->getSkinUrl('img/ambikuk.pdf');;exit;
       
	    $fileName = Mage::getBaseDir('skin').'/frontend/default/craftsvilla2012/img/dhl_ambikuk.pdf';
		$pdf = new Zend_Pdf();

		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
		$width = $page->getWidth(); // A4 : 595
		$height = $page->getHeight(); // A4 : 842
		$imagePath = Mage::getBaseDir('skin').'/frontend/default/craftsvilla2012/img/dhl_invoice.png';
		$image = Zend_Pdf_Image::imageWithPath($imagePath);
		$page->drawImage($image, 527, -200, 850, 600);
       //		$logoPath = dirname(__FILE__) . '/logo.jpg';
//		$logo = Zend_Pdf_Image::imageWithPath($logoPath);
//		$page->drawImage($logo, 463, 795, 540, 822);

		$page = $this->contentPdf($page, $model, $xmlResponse);

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 9);
		$page->drawText($xmlResponse->ProductShortName, 550, 570, 'UTF-8');
		$page->setFont($font, 7);
		$page->drawText('XML PI v4.0', 560, 560, 'UTF-8');

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 7);
		$page->drawText('Order #' . $id, 540, 325, 'UTF-8');
		$page->drawText('Piece Weight:', 650, 325, 'UTF-8');
      
		$barcode_binary = base64_decode($xmlResponse->Barcodes->AWBBarCode);
        
		$tmp_dir = Mage::getBaseDir('media'). DS . 'tmpdhl'. DS;
		$png_file = $tmp_dir. DS. $xmlResponse->AirwayBillNumber."-DHLawb.png";
		
	    file_put_contents($png_file, $barcode_binary);
//		$png_file = $tmp_file . '.png';
//		rename($tmp_file, $png_file);
		$image = Zend_Pdf_Image::imageWithPath($png_file);
		//$page->drawImage($image, 560, 210, 720, 285);
		$page->drawImage($image, 540, 210, 835, 280);
         //  $page->drawImage($image, 560, 210);
		$barcode_binary = base64_decode($xmlResponse->Barcodes->DHLRoutingBarCode);
		// Temporary dir
		$tmp_dir = Mage::getBaseDir('media'). DS . 'tmpdhl'. DS;
		$png_file = $tmp_dir. DS. $xmlResponse->AirwayBillNumber."-DHLroute.png";
//		$tmp_file = array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()] = tmpfile())));
		file_put_contents($png_file, $barcode_binary);
//		$png_file = $tmp_file . '.png';
//		rename($tmp_file, $png_file);
		$image = Zend_Pdf_Image::imageWithPath($png_file);
		//$page->drawImage($image, 570, 125, 780, 200);
          //$page->drawImage($image, 520, 125, 919, 200);
          $page->drawImage($image, 550, 125, 810, 200);
		$barcode_binary = base64_decode($xmlResponse->Pieces->Piece->LicensePlateBarCode);
		$tmp_dir = Mage::getBaseDir('media'). DS . 'tmpdhl'. DS;
		$png_file = $tmp_dir. DS. $xmlResponse->AirwayBillNumber."-DHLlicence.png";
//		$tmp_file = array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()] = tmpfile())));
		file_put_contents($png_file, $barcode_binary);
//		$png_file = $tmp_file . '.png';
//		rename($tmp_file, $png_file);
		$image = Zend_Pdf_Image::imageWithPath($png_file);
		$page->drawImage($image, 550, 40, 820, 115);

		$page->setFont($font, 8);
		$page->drawText('WAYBILL ' . $xmlResponse->AirwayBillNumber, 580, 202, 'UTF-8');
		$page->drawText('(2L) ' . $xmlResponse->DHLRoutingCode, 590, 117, 'UTF-8');
		$page->drawText('(J) ' . $xmlResponse->Pieces->Piece->LicensePlate, 600, 32, 'UTF-8');

		//colomn 7
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 8);
		$page->drawText('Content: ' . $xmlResponse->Contents, 540, 290, 'UTF-8');

		$imagePath = Mage::getBaseDir('skin').'/frontend/default/craftsvilla2012/img/dhl_label1.png';
		$image = Zend_Pdf_Image::imageWithPath($imagePath);
		$page->drawImage($image, 0, 500, 23, 750);
		$pdf->pages[] = $page;

		// ARCIVE
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
		$width = $page->getWidth(); // A4 : 595
		$height = $page->getHeight(); // A4 : 842
		$imagePath = Mage::getBaseDir('skin').'/frontend/default/craftsvilla2012/img/dhl_invoice2.png';
		$image = Zend_Pdf_Image::imageWithPath($imagePath);
		$page->drawImage($image, 527, -200, 850, 600);

//		$logoPath = dirname(__FILE__) . '/logo.jpg';
//		$logo = Zend_Pdf_Image::imageWithPath($logoPath);
//		$page->drawImage($logo, 463, 795, 540, 822);

		$page = $this->contentPdf($page, $model, $xmlResponse);
		$page = $this->contentPdf($page, $model, $xmlResponse);
		$imagePath = Mage::getBaseDir('skin').'/frontend/default/craftsvilla2012/img/dhl_label2.png';
		$image = Zend_Pdf_Image::imageWithPath($imagePath);
		$page->drawImage($image, 0, 500, 23, 750);

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 8);
		$page->drawText('*ARCHIVE DOC*', 560, 575, 'UTF-8');
		$page->setFont($font, 6);
		$page->drawText('Do not attach to package!', 560, 568, 'UTF-8');

		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 6);
//		$page->drawText('Account No: ' . $xmlResponse->Billing->BillingAccountNumber, 314, 597, 'UTF-8');
		$page->drawText('Order #' . $id, 540, 325, 'UTF-8');
		$page->drawText('Shipment Weight:', 650, 325, 'UTF-8');

		$barcode_binary = base64_decode($xmlResponse->Barcodes->AWBBarCode);
		$tmp_dir = Mage::getBaseDir('media'). DS . 'tmpdhl'. DS;
		$png_file = $tmp_dir. DS. $xmlResponse->AirwayBillNumber."-DHLawb2.png";
//		$tmp_file = array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()] = tmpfile())));
		file_put_contents($png_file, $barcode_binary);
//		$png_file = $tmp_file . '.png';
//		rename($tmp_file, $png_file);
		$image = Zend_Pdf_Image::imageWithPath($png_file);
		$page->drawImage($image, 540, 210, 820, 270);

		$page->setFont($font, 8);
		$page->drawText('WAYBILL ' . $xmlResponse->AirwayBillNumber, 580, 190, 'UTF-8');

		//colomn 7
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 7);
		$page->drawText('DHL standard Terms and Conditions apply. Warsaw convention may also apply.', 540, 290, 'UTF-8');
		$page->drawText('Shipment may be carried via intermediated stopping places DHL deems appropriate.', 540, 283, 'UTF-8');
		$page->drawText('Content: ' . $xmlResponse->Contents, 540, 273, 'UTF-8');

		$page->setFont($font, 7);
		$page->drawText('Product                   : ' . $xmlResponse->GlobalProductCode . ' ' . $xmlResponse->ProductShortName, 540, 166, 'UTF-8');
		$page->drawText('Service                   : ', 540, 159, 'UTF-8');
		$page->drawText('Billing Account No  : ' . $xmlResponse->Billing->BillingAccountNumber, 540, 151, 'UTF-8');
		$dtp_account = ($inco_term) ? $dtpaccount : '-';
		$page->drawText('DTP Account No     : ' .$dtp_account, 540, 143, 'UTF-8');
		$page->drawText('Insurance value      : ' . $xmlResponse->InsuredAmount, 540, 134, 'UTF-8');
		$page->drawText('Declared Value       : ' . $xmlResponse->Dutiable->DeclaredValue . ' ' . $xmlResponse->Dutiable->DeclaredCurrency, 540, 125, 'UTF-8');
		$page->drawText('Terms of Trade       : ' . $xmlResponse->Dutiable->TermsOfTrade, 540, 116, 'UTF-8');

		$page->drawText('Licence plates of Pieces in Shipment : ', 540, 90, 'UTF-8');
		$page->drawText('-(' . $xmlResponse->Pieces->Piece->DataIdentifier . ')' . $this->spacepablic($xmlResponse->Pieces->Piece->LicensePlate), 540, 80, 'UTF-8');
        
		$pdf->pages[] = $page;
		
		$fileName = $xmlResponse->AirwayBillNumber . '.pdf';
        Mage::helper('udropship')->sendDownload($fileName, $pdf->render(), 'application/x-pdf');
		//$this->getResponse()->setHeader('Content-type', 'application/x-pdf', true);
		//$this->getResponse()->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"', true);
		//$this->getResponse()->setBody($pdf->render());
      // print_r($pdf->render());exit;
		return $pdf->render();
	}

	public function spacepablic($str)
	{
		$read = str_split($str, 4);
		foreach ($read as $r)
		{
			$n .= $r . ' ';
		}
		return $n;
	}

	public function contentPdf($page, $model, $xmlResponse)
	{
		//header
         $inco_term = '1';
		$dtpaccount = '530886811';
		
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFillColor(Zend_Pdf_Color_Html::color('#FFFFFF'));
		$page->setFont($font, 19);
		$page->drawText($xmlResponse->ProductContentCode, 690, 560, 'UTF-8');

		//From
		$page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		$page->setFont($font, 6);
		$page->drawText('From:', 540, 545, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 7);
		$page->drawText($xmlResponse->Shipper->CompanyName, 560, 545, 'UTF-8');
		$page->drawText($xmlResponse->Shipper->Contact->PersonName, 560, 537, 'UTF-8');
		$page->drawText($xmlResponse->Shipper->AddressLine[0], 560, 530, 'UTF-8');
		$page->drawText($xmlResponse->Shipper->AddressLine[1], 560, 523, 'UTF-8');
		$page->drawText('Ph:' . $xmlResponse->Shipper->Contact->PhoneNumber, 700, 530, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 9);
		$page->drawText($xmlResponse->Shipper->City . ' ' . $xmlResponse->Shipper->DivisionCode . ' ' . $xmlResponse->Shipper->PostalCode, 560, 509, 'UTF-8');
		$page->drawText($xmlResponse->Shipper->CountryName, 560, 498, 'UTF-8');
		$page->drawText($xmlResponse->OriginServiceArea->ServiceAreaCode, 810, 535, 'UTF-8');
		$page->drawText('Origin :', 800, 545, 'UTF-8');

		//To
		$page->setFont($font,7);
		$page->drawText('To:', 540, 483, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 9);
		$page->drawText($xmlResponse->Consignee->CompanyName, 555, 483, 'UTF-8');
		$page->drawText($xmlResponse->Consignee->Contact->PersonName, 555, 474, 'UTF-8');
		$page->drawText($xmlResponse->Consignee->AddressLine[0], 555, 464, 'UTF-8');
		$page->drawText($xmlResponse->Consignee->AddressLine[1], 555, 456, 'UTF-8');
		$page->setFont($font, 8);
		$page->drawText('Ph:' . $xmlResponse->Consignee->Contact->PhoneNumber, 700, 482, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 10);
		$page->drawText($xmlResponse->Consignee->City . ' ' . $xmlResponse->Consignee->DivisionCode . ' ' . $xmlResponse->Consignee->PostalCode, 560, 410, 'UTF-8');
		$page->drawText($xmlResponse->Consignee->CountryName, 560, 400, 'UTF-8');

		//colomn 4
		$page->drawText($xmlResponse->OriginServiceArea->OutboundSortCode, 560, 370, 'UTF-8');
		$page->setFont($font, 25);
		$page->drawText($xmlResponse->Consignee->CountryCode . '-' . $xmlResponse->DestinationServiceArea->ServiceAreaCode . '-' . $xmlResponse->DestinationServiceArea->FacilityCode, 620, 370, 'UTF-8');
		$page->setFont($font, 14);
		$page->drawText($xmlResponse->DestinationServiceArea->InboundSortCode, 570, 340, 'UTF-8');

		//colomn 5
		$page->setFont($font, 18);
		$page->setFillColor(Zend_Pdf_Color_Html::color('#FFFFFF'));
		if($inco_term)
			$page->drawText($xmlResponse->InternalServiceCode, 560, 338, 'UTF-8');
		else
			$page->drawText($xmlResponse->InternalServiceCode, 560, 338, 'UTF-8');
		$page->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		$page->setFont($font, 8);
		$page->drawText($xmlResponse->DeliveryDateCode, 750, 340, 'UTF-8');
		$page->drawText($xmlResponse->DeliveryTimeCode, 790, 340, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 7);
		$page->drawText('Day', 750, 350, 'UTF-8');
		$page->drawText('Time', 790, 350, 'UTF-8');

		//colomn 6
		$page->setFont($font, 7);
		$page->drawText('Date', 650, 310, 'UTF-8');
		$page->drawText('Piece:', 800, 325, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 6);
		$page->drawText($xmlResponse->Pieces->Piece->Weight . ' Kg', 698, 325, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$page->setFont($font, 6);
		$page->drawText($xmlResponse->ShipmentDate, 670, 310, 'UTF-8');
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$page->setFont($font, 12);
		$page->drawText('1/1', 800, 308, 'UTF-8');

        
		return $page;
	}
 	 
}

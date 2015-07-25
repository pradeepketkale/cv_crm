<?php
	//Report all errors
	error_reporting(E_ALL & ~E_NOTICE);
	require('init.php');
	//session_start();
	
	// create a client instance
	$client = new Solarium_Client($config);
	
	// get a select query instance
	$query = $client->createSelect();
	
	$helper = $query->getHelper();
	
	//set number of records to get
	$query->setRows(0);
	
	if($_POST['search_val'] == 'Search All Shops')
	{
		$query->setQuery('*:*');		
	}
	else if(strpos($_POST['search_val'], ' '))
	{
		$query->setQuery('(vendorExact:"'.$_POST['search_val'].'"~1) OR (vendor:"'.$_POST['search_val'].'"~5)');
	}
	else {
		$query->setQuery('(vendorExact:'.$_POST['search_val'].'^20) OR (vendor:'.$_POST['search_val'].'^3)');
	}
	
	/*********** Facet *******************************/
	$facetSet = $query->getFacetSet();
	
	// create a facet field instance and set options
	$facetSet->createFacetField('vendor')->setField('vendor')->setMinCount(1)->setLimit(10000);


	// this executes the query and returns the result
	$resultset = $client->select($query);
	
	/*echo "<pre>";
	print_r($resultset);
	exit();*/
	
	/*********** Facet Get **********************/
	$facet_vendor = $resultset->getFacetSet()->getFacet('vendor');

	$total_array = $resultset->getResponse();
	$total_values = $total_array->getBody();
	
	$decode_json_str = json_decode($total_values);
	
	$facet_vakues = $decode_json_str->facet_counts->facet_fields->vendor;
	
	$facet_values_array = array();
	
	$k=0;
	for($m=0;$m<count($facet_vakues);$m++)
	{
		$k++;
		if($k == 1)
		{
			if($facet_vakues[$m])
			{
				$facet_values_array[$facet_vakues[$m]] = $facet_vakues[($m+1)];				
			}
		}
		else {
			$k = 0;
		}
	}
	
	if($_POST['sort_field'] == 'items_count')
	{
		if($_POST['sort_by'] == 'asc')
		{
			arsort($facet_values_array);
		}
		else
		{
			asort($facet_values_array);
		}	
	}
	
	if($_POST['sort_field'] == 'name')
	{
		if($_POST['sort_by'] == 'asc')
		{
			krsort($facet_values_array);
		}
		else
		{
			ksort($facet_values_array);
		}	
	}
	
	//$total_facet_values = count($facet_values_array);
	
	$tt = 0;
	$facet_values_new_array = array();
	foreach($facet_values_array as $key=>$value)
	{
		$facet_values_new_array[$tt][$key] = $value;
		$tt++;
	}	
	
	/*echo "<pre>";
	print_r($facet_values_new_array);
	exit();*/
	
	$total_facet_values = count($facet_values_new_array);
	
	$start_for_val = 0;
	$end_for_val = 0;
	
	if(!$_POST['page'])
	{
		$start_for_val = 0;
		if($total_facet_values>20)
		{
			$end_for_val = 20;		
		}
		else {
			$end_for_val = $total_facet_values;
		}
	}
	else {
		$start_for_val = (($_POST['page']-1)*20);
		if($total_facet_values>($start_for_val+20))
		{
			$end_for_val = ($start_for_val+20); 
		}
		else {
			$end_for_val = $total_facet_values;	
		}
	}
	
	/*echo "\n Start Value:".$start_for_val;
	echo "\n End Value:".$end_for_val;*/
	
	$j = 0;
	$m = 0;
	$n = 0;
	
	//echo "Total Facet Values:".$total_facet_values;
	
	require_once '../app/Mage.php';
	Mage::app();
	$read = Mage::getSingleton('core/resource')->getConnection('core_read');

	$new_resultset = array();
	
	unset($decode_json_str->facet_counts->facet_fields->vendor);
	
	for($mk=$start_for_val;$mk<$end_for_val;$mk++)
	{
		foreach($facet_values_new_array[$mk] as  $key=>$value)
		{
			if($key)
			{
				$decode_json_str->facet_counts->facet_fields->vendor[$j] = $key;
				$j++;
				$decode_json_str->facet_counts->facet_fields->vendor[$j] = $value;
				$j++;
				
				// create a client instance
				$client1 = new Solarium_Client($config);
		
				// get a select query instance
				$query1 = $client->createSelect();
		
				//set number of records to get
				$query1->setRows(4);
				
				$query1->setFields(array('entity_id', 'name', 'url_path', 'vendor_owner', 'vendor_logo', 'vendor_url', 'image'));
				
				if(strpos($key, ' '))
				{
					$query1->setQuery('(vendorExact:"'.$key.'"~1)');
				}
				else {
					$query1->setQuery('(vendorExact:'.$key.'^20)');
				}
				
				$resultset1 = $client->select($query1);
				
				$resultsetval1 = $resultset1->getResponse();
				$resultsetvalues1 = $resultsetval1->getBody();
				
				$abc_tot = json_decode($resultsetvalues1);
				$total_num_records_value1 = count($abc_tot->response->docs);
				
				
				for($k=0;$k<$total_num_records_value1;$k++)
				{
					/*unset($sql);
					unset($readresult);
					unset($count);
					$sql="select * from op_imagecdn_cache_1 where `http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/small` like '%".trim($abc_tot->response->docs[$k]->image)."'";
					$readresult=$read->fetchAll($sql);
					$count = count($readresult);
							
					unset($_newProduct);
					unset($abc_tot->response->docs[$k]->image);
					unset($new_image);
					if($count == 0)
					{
						$_newProduct = Mage::getModel('catalog/product')->load(trim($abc_tot->response->docs[$k]->entity_id));
						$new_image = Mage::helper('catalog/image')->init($_newProduct, 'thumbnail')->resize(75,75);
						$abc_tot->response->docs[$k]->image = trim($new_image);
					}
					else {
						$abc_tot->response->docs[$k]->image = "http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/small_image/82x82/9df78eab33525d08d6e5fb8d27136e95".trim($abc_tot->response->docs[$k]->image);
					}
		
					if($abc_tot->response->docs[$k]->image == '')
					{
						$abc_tot->response->docs[$k]->image = "http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/small_image/82x82/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg";
					}*/
					$_helpm = Mage::helper('umicrosite');
					
					/* changed url http://cdnkribha1.s3.amazonaws.com*/
					
					$abc_tot->response->docs[$k]->image = "http://d1g63s1o9fthro.cloudfront.net/catalog/product/cache/1/small_image/500x500/9df78eab33525d08d6e5fb8d27136e95".trim($abc_tot->response->docs[$k]->image);
					//http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/thumbnail/75x75/9df78eab33525d08d6e5fb8d27136e95/M/M/MMUSHIVA1000018_1.jpg
					if($abc_tot->response->docs[$k]->vendor_logo != '')
					{
						
						if(file_exists(Mage::getBaseDir('media').'/'.$abc_tot->response->docs[$k]->vendor_logo))
						{
							
							$abc_tot->response->docs[$k]->vendor_logo = $_helpm->getResizedUrl($abc_tot->response->docs[$k]->vendor_logo,30);
						}
						else
						{
							$abc_tot->response->docs[$k]->vendor_logo = $_helpm->getResizedUrl('vendor/noimage/noimage.jpg',30);					
						}
					}
					else
					{
						$abc_tot->response->docs[$k]->vendor_logo = $_helpm->getResizedUrl('vendor/noimage/noimage.jpg',30);
					}
					
				}
			}
		
			$new_resultset[$key] = $abc_tot->response->docs;			
		}
	}
	
	/*foreach($facet_values_array as $key=>$value)
	{
		unset($client1);
		unset($query1);
		unset($resultset1);
		unset($resultsetval1);
		unset($resultsetvalues1);
		
		if(!$_POST['page'])
		{
			$_POST['page'] = 1;
		}

		if(isset($_POST['page']))
		{
			if($m==$_POST['page'] && $n<($_POST['page']*20))
			{
				if($key)
				{
					$decode_json_str->facet_counts->facet_fields->vendor[$j] = $key;
					$j++;
					$decode_json_str->facet_counts->facet_fields->vendor[$j] = $value;
					$j++;
			
					// create a client instance
					$client1 = new Solarium_Client($config);
	
					// get a select query instance
					$query1 = $client->createSelect();
	
					//set number of records to get
					$query1->setRows(4);
			
					$query1->setFields(array('entity_id', 'name', 'url_path', 'vendor_owner', 'vendor_logo', 'vendor_url', 'image'));
			
					if(strpos($key, ' '))
					{
						$query1->setQuery('(vendorExact:"'.$key.'"~1)');
					}
					else {
						$query1->setQuery('(vendorExact:'.$key.'^20)');
					}
			
					$resultset1 = $client->select($query1);
			
					$resultsetval1 = $resultset1->getResponse();
					$resultsetvalues1 = $resultsetval1->getBody();
			
					$abc_tot = json_decode($resultsetvalues1);
					$total_num_records_value1 = count($abc_tot->response->docs);
			
			
					for($k=0;$k<$total_num_records_value1;$k++)
					{
						unset($sql);
						unset($readresult);
						unset($count);
						$sql="select * from op_imagecdn_cache_1 where `http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/small` like '%".trim($abc_tot->response->docs[$k]->image)."'";
						$readresult=$read->fetchAll($sql);
						$count = count($readresult);
						
						unset($_newProduct);
						unset($abc_tot->response->docs[$k]->image);
						unset($new_image);
						if($count == 0)
						{
							$_newProduct = Mage::getModel('catalog/product')->load(trim($abc_tot->response->docs[$k]->entity_id));
							$new_image = Mage::helper('catalog/image')->init($_newProduct, 'thumbnail')->resize(75,75);
							$abc_tot->response->docs[$k]->image = trim($new_image);
						}
						else {
							$abc_tot->response->docs[$k]->image = "http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/small_image/82x82/9df78eab33525d08d6e5fb8d27136e95".trim($abc_tot->response->docs[$k]->image);
						}
	
						if($abc_tot->response->docs[$k]->image == '')
						{
							$abc_tot->response->docs[$k]->image = "http://cdnkribha1.s3.amazonaws.com/catalog/product/cache/1/small_image/82x82/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg";
						}
				
						if($abc_tot->response->docs[$k]->vendor_logo != '')
						{
							if(file_exists(Mage::getBaseDir('media').'/'.$abc_tot->response->docs[$k]->vendor_logo))
							{
								$abc_tot->response->docs[$k]->vendor_logo = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$abc_tot->response->docs[$k]->vendor_logo;
							}
							else
							{
								$abc_tot->response->docs[$k]->vendor_logo = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."frontend/default/craftsvilla2012/images/noimage.jpg";					
							}
						}
						else
						{
							$abc_tot->response->docs[$k]->vendor_logo = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."frontend/default/craftsvilla2012/images/noimage.jpg";
						}
					}
				}
				
				$new_resultset[$key] = $abc_tot->response->docs;
			}
			else
			{
				$m++;
			}
			$n++;		
		}
	}*/
	
	$decode_json_str->products = $new_resultset;
	$decode_json_str->total_facets = $total_facet_values;
	$encode_json_str = json_encode($decode_json_str);
	
	echo $encode_json_str;
	exit();

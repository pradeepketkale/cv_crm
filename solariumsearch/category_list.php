<?php
require('init.php');
session_start();
/*echo "<pre>";
print_r($_POST);
exit();*/

if($_POST['cat'])
{
	// create a client instance
	$client = new Solarium_Client($config);

	// get a select query instance
	$query = $client->createSelect();

	$helper = $query->getHelper();

	//set number of records to get
	$query->setRows(60);
	
	unset($start);
	
	if(isset($_POST['p'])) {
		$start = (trim($_POST['p'])-1)*$query->getRows();
		$query->setStart($start);
	}
	
	$query_str = '';
	
	//'category_id:5,* OR category_id:,5* OR category_id:,5* OR category_id:5 OR category_id:*,5,*'
	//echo "Query:".'category_id:'.trim($_POST['cat']).',* OR category_id:*,'.trim($_POST['cat']).' OR category_id:*,'.trim($_POST['cat']).',* OR category_id:'.trim($_POST['cat']);
	//exit();
	$query->setQuery('category_id:'.trim($_POST['cat']).',* OR category_id:*,'.trim($_POST['cat']).' OR category_id:*,'.trim($_POST['cat']).',* OR category_id:'.trim($_POST['cat']));
	
	/************************ For Location ***********************************************/
	$location_arr_ajax = array();
	$location_query = '';
	if(isset($_POST['location']))
	{
		if(strpos($_POST['location'], ','))
		{
			$location_arr_ajax = explode(",", $_POST['location']);
		}
		else {
			$location_arr_ajax[] = $_POST['location'];
		}
		
		$d = 0;
		foreach($location_arr_ajax as $arr_value)
		{
			$location_query .= 'vendor_city:';
			$d++;
			if(strpos($arr_value, ' '))
			{
				$location_query .= '"'.$arr_value.'"';
			}
			else {
				$location_query .= $arr_value;
			}

			if($d != count($location_arr_ajax))
			{
				$location_query .= " OR ";
			}
		}
		
		/*echo "Location Query:".$location_query;
		exit();*/
		$query->createFilterQuery('location')->setQuery($location_query);
	}
	/************************ For Location ***********************************************/
	
	
	/************************ For Color ***********************************************/
	$color_arr_ajax = array();
	$color_query = '';
	if(isset($_POST['color']))
	{
		if(strpos($_POST['color'], ','))
		{
			$color_arr_ajax = explode(",", $_POST['color']);
		}
		else {
			$color_arr_ajax[] = $_POST['color'];
		}
		
		
		$d = 0;
		foreach($color_arr_ajax as $color_arr_value)
		{
			$color_query .= 'color:';
			$d++;
			if(strpos($color_arr_value, ' '))
			{
				$color_query .= '"'.$color_arr_value.'"';
			}
			else {
				$color_query .= $color_arr_value;
			}

			if($d != count($color_arr_ajax))
			{
				$color_query .= " OR ";
			}
		}
		
		//echo "Color Query:".$color_query;exit();
		$query->createFilterQuery('color')->setQuery($color_query);
	}
	/************************ For Color ***********************************************/
	
	/************************ For Price ***********************************************/
	$price_arr_ajax = array();
	$price_query = '';
	if(isset($_POST['price']))
	{
		if(strpos($_POST['price'], ','))
		{
			$price_arr_ajax = explode(",", $_POST['price']);
		}
		else {
			$price_arr_ajax[] = $_POST['price'];
		}
		
		$d = 0;
		foreach($price_arr_ajax as $arr_value)
		{
			$price_query .= 'price:';
			$d++;
			if(strpos($arr_value, '-'))
			{
				$arr_value = str_replace("-", " TO ", $arr_value);
				$price_query .= "[".$arr_value."]";
			}
			else if(strpos($arr_value, '<'))
			{
				$arr_value = str_replace("<", "", $arr_value);
				$price_query .= "[".$arr_value." TO *]";
			}

			if($d != count($price_arr_ajax))
			{
				$price_query .= " OR ";
			}
		}
		
		$query->createFilterQuery('price')->setQuery($price_query);
	}
	else if(isset($_POST['min']) && isset($_POST['max']))
	{
		$query->createFilterQuery('price')->setQuery('price:['.$_POST['min'].' TO '.$_POST['max'].']');
	}
	/************************ For Price ***********************************************/
	
	if(isset($_POST['dir']) && isset($_POST['order']))
	{
		// sort the results
		if($_POST['order'] == 'price' && $_POST['dir'] == 'asc')
		{
			$query->addSort('price', Solarium_Query_Select::SORT_ASC);
		}
		else if($_POST['order'] == 'price' && $_POST['dir'] == 'desc')
		{
			$query->addSort('price', Solarium_Query_Select::SORT_DESC);
		}
		else if($_POST['order'] == 'name' && $_POST['dir'] == 'asc')
		{
			$query->addSort('name', Solarium_Query_Select::SORT_ASC);
		}
		else if($_POST['order'] == 'name' && $_POST['dir'] == 'desc')
		{
			$query->addSort('name', Solarium_Query_Select::SORT_DESC);
		}
		else if($_POST['order'] == 'entity_id' && $_POST['dir'] == 'desc')
		{
			$query->addSort('entity_id', Solarium_Query_Select::SORT_DESC);
		}
		else if($_POST['order'] == 'entity_id' && $_POST['dir'] == 'asc')
		{
			$query->addSort('entity_id', Solarium_Query_Select::SORT_ASC);
		}
		/*
		 * for newest
		 * else if($_POST['order'] == 'name' && $_POST['dir'] == 'desc')
		{
			$query->addSort('name', Solarium_Query_Select::SORT_DESC);
		}*/	
	}
	
	/*********** Facet *******************************/

	$facetSet = $query->getFacetSet();

	// create a facet field instance and set options for color
	$facetSet->createFacetField('color')->setField('color')->setMinCount(1)->setLimit(1000);
	
	// create a facet field instance and set options for price
	$facetSet->createFacetField('price')->setField('price')->setMinCount(1)->setLimit(1000);
	
	// create a facet field instance and set options for location
	$facetSet->createFacetField('location')->setField('vendor_city')->setMinCount(1)->setLimit(1000);

	/*********** Facet *******************************/
	
	// this executes the query and returns the result
	$resultset = $client->select($query);

	$total_array = $resultset->getResponse();
	$total_values = $total_array->getBody();
	
	/*********** Facet Get **********************/

	$facet_color = $resultset->getFacetSet()->getFacet('color');

	$facet_price = $resultset->getFacetSet()->getFacet('price');

	$facet_location = $resultset->getFacetSet()->getFacet('location');
	
	/*********** Facet Get **********************/
	
	echo $total_values;
}
?>
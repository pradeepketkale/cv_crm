<?php
require('init.php');

// create a client instance
$client = new Solarium\Client($config);
$client->getEndpoint()->setAuthentication(SOLR_USERNAME, SOLR_PASSWORD);

// get a select query instance
$query = $client->createSelect();

$helper = $query->getHelper();

//set number of records to get
$query->setRows(60);

// sort the results

if($_POST['orderby'])
{
	if($_POST['orderby'] == 'new')
	{
	
		$sort_by_value_give = 'score';		
	}
	else if($_POST['orderby'] == 'name')
	{
		$sort_by_value_give = 'name';		
	}
	else if($_POST['orderby'] == 'price')
	{
		$sort_by_value_give = 'price';		
	}
	else {
	
		$sort_by_value_give = 'score';
	}

	if($_POST['order'] == 'desc')
	{
		$query->addSort($sort_by_value_give, $query::SORT_DESC);
	}
	else if($_POST['order'] == 'asc'){
		$query->addSort($sort_by_value_give, $query::SORT_ASC);
	}
	else {
		$query->addSort($sort_by_value_give, $query::SORT_ASC);
	}
}
else {

	$query->addSort('score', $query::SORT_DESC);
} 

if(strpos($_POST['search_val'], ' '))
{
	$query->setQuery('(nameExact:"'.$_POST['search_val'].'"~5) OR (vendorExact:"'.$_POST['search_val'].'"~1) OR (catExact:"'.$_POST['search_val'].'"~1) OR (short_description:"'.$_POST['search_val'].'"~5) OR (vendor:"'.$_POST['search_val'].'"~5) OR (category_name:"'.$_POST['search_val'].'"~1) OR (text:"'.$_POST['search_val'].'"~10)');
}
else {
	$query->setQuery('(nameExact:"'.$_POST['search_val'].'"~5) OR (vendorExact:'.$_POST['search_val'].'^20) OR (catExact:'.$_POST['search_val'].'^30) OR (short_description:'.$_POST['search_val'].'^2) OR (vendor:'.$_POST['search_val'].'^3) OR (category_name:'.$_POST['search_val'].'^4) OR (text:"'.$_POST['search_val'].'"~10)');
}

if($_POST['search_category'] && $_POST['search_category'] != '""')
{
	$category_Qr = '';
	if(strpos($_POST['search_category'], ' '))
	{
	
		$category_Qr = 'category_name:"'.$_POST['search_category'].'"';
	}
	else {
		
		$category_Qr = 'category_name:"'.$_POST['search_category'].'"';
	}
	
	$query->createFilterQuery('category_name')->setQuery($category_Qr);
	
}
else {
	$catagory_query_val = "";
}

if(trim($_POST['search_color_fecet']) && trim($_POST['search_color_fecet']) != "''")
{
	if(strpos($_POST['search_color_fecet'], ','))
	{
		$color_explode_arr = explode(",", $_POST['search_color_fecet']);
		if(is_array($color_explode_arr))
		{
			
			for($t=0;$t<sizeof($color_explode_arr);$t++)
			{
				
				if($t == (sizeof($color_explode_arr)-1))
				{
					$color_value_Qr .= 'color:';
					if(strpos($_POST['search_color_fecet'], ' '))
					{
						$color_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', $color_explode_arr[$t]).'"';
					}
					else {
						$color_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', $color_explode_arr[$t]);
					}	
				}
				else {
					$color_value_Qr .= 'color:';
					if(strpos($_POST['search_color_fecet'], ' '))
					{
						$color_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', $color_explode_arr[$t]).'"';
					}
					else {
						$color_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', $color_explode_arr[$t]);
					}
					$color_value_Qr .= ' OR ';
				}
			}
		}
	}
	else {
		$color_value_Qr = 'color:';
		if(strpos($_POST['search_color_fecet'], ' '))
		{
			$color_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($_POST['search_color_fecet'])).'"';
		}
		else {
			$color_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($_POST['search_color_fecet']));
		}
	}
	$query->createFilterQuery('color')->setQuery($color_value_Qr);
}

if(trim($_POST['search_location_fecet']) && trim($_POST['search_location_fecet']) != "''")
{
	$location_value_Qr = '';
	if(strpos($_POST['search_location_fecet'], ','))
	{
		$location_explode_arr = explode(",", $_POST['search_location_fecet']);

		if(is_array($location_explode_arr))
		{
			
			for($t=0;$t<sizeof($location_explode_arr);$t++)
			{
				
				if($t == (count($location_explode_arr)-1))
				{
					$location_value_Qr .= 'vendor_city:';
					if(strpos(trim($_POST['search_location_fecet']), ' '))
					{
						$location_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($location_explode_arr[$t])).'"';
					}
					else {
						$location_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($location_explode_arr[$t]));
					}	
				}
				else {
					
					$location_value_Qr .= 'vendor_city:';
					if(strpos(trim($_POST['search_location_fecet']), ' '))
					{
						$location_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($location_explode_arr[$t])).'"';
					}
					else {
						$location_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($location_explode_arr[$t]));
					}
					$location_value_Qr .= ' OR ';
				}
			}
		}
	}
	else {
		$location_value_Qr = 'vendor_city:';
		if(strpos(trim($_POST['search_location_fecet']), ' '))
		{
			$location_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($_POST['search_location_fecet'])).'"';
		}
		else {
			$location_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($_POST['search_location_fecet']));
		}
		
	}

	$query->createFilterQuery('location')->setQuery($location_value_Qr);
}

if(trim($_POST['search_country_fecet']) && trim($_POST['search_country_fecet']) != "''")
{
	$country_value_Qr = '';
	if(strpos($_POST['search_country_fecet'], ','))
	{
		$country_explode_arr = explode(",", $_POST['search_country_fecet']);

		if(is_array($country_explode_arr))
		{
			
			for($t=0;$t<sizeof($country_explode_arr);$t++)
			{
				
				if($t == (count($country_explode_arr)-1))
				{
					$country_value_Qr .= 'vendor_country:';
					if(strpos(trim($_POST['search_country_fecet']), ' '))
					{
						$country_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($country_explode_arr[$t])).'"';
					}
					else {
						$country_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($country_explode_arr[$t]));
					}	
				}
				else {
					
					$country_value_Qr .= 'vendor_country:';
					if(strpos(trim($_POST['search_country_fecet']), ' '))
					{
						$country_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($country_explode_arr[$t])).'"';
					}
					else {
						$country_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($country_explode_arr[$t]));
					}
					$country_value_Qr .= ' OR ';
				}
			}
		}
	}
	else {
		$country_value_Qr = 'vendor_country:';
		if(strpos(trim($_POST['search_country_fecet']), ' '))
		{
			$country_value_Qr .= '"'.preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($_POST['search_country_fecet'])).'"';
		}
		else {
			$country_value_Qr .= preg_replace('/[^a-zA-Z0-9_& -]/s', '', trim($_POST['search_country_fecet']));
		}
		
	}

	$query->createFilterQuery('country')->setQuery($country_value_Qr);
}

//For price filter
if(trim($_POST['search_price_fecet']) && trim($_POST['search_price_fecet']) != "''")
{
	$_POST['search_price_fecet'] = str_replace("'", "", $_POST['search_price_fecet']);
	if(strpos($_POST['search_price_fecet'], ','))
	{
		$price_explode_arr = explode(",", $_POST['search_price_fecet']);
		
		if(is_array($price_explode_arr))
		{
		
			$price_value_Qr = '';
			for($t=0;$t<sizeof($price_explode_arr);$t++)
			{
				if(strpos($price_explode_arr[$t], '-'))
				{
					$price_explode_arr[$t] = str_replace("-", " TO ", $price_explode_arr[$t]);
				}
				else if($price_explode_arr[$t][0] == '>')
				{
					$price_explode_arr[$t] = "5000 TO *";
				}
				
				if($price_value_Qr != '')
				{
					$price_value_Qr .= " OR ";
				}
				
				$price_value_Qr .= 'price:['.$price_explode_arr[$t].']';
			}
		}
	}
	else {
		$price_value_Qr = '';
		if(strpos($_POST['search_price_fecet'], '-'))
		{
			$_POST['search_price_fecet'] = str_replace("-", " TO ", $_POST['search_price_fecet']);
		}
		$price_value_Qr .= "price:[".$_POST['search_price_fecet']."]";
	}
	$query->createFilterQuery('price')->setQuery($price_value_Qr);
}
else if(trim($_POST['search_min_price']) && trim($_POST['search_max_price']) && trim($_POST['search_min_price']) != "''" && trim($_POST['search_max_price']) != "''") {
	$_POST['search_min_price'] = str_replace("'", "", $_POST['search_min_price']);
	$_POST['search_max_price'] = str_replace("'", "", $_POST['search_max_price']);
	$price_value_Qr = '';
	if($_POST['search_min_price'])
	{
		$_POST['search_min_price'] = $_POST['search_min_price'];
	}
	else {
		$_POST['search_min_price'] = "*";
	}
	
	if($_POST['search_max_price'])
	{
		$_POST['search_max_price'] = $_POST['search_max_price'];
	}
	else {
		$_POST['search_max_price'] = "*";
	}
	$price_value_Qr .= "price:[".$_POST['search_min_price']." TO ".$_POST['search_max_price']."]";
	$query->createFilterQuery('price')->setQuery($price_value_Qr);
}

/*********** Facet *******************************/

$facetSet = $query->getFacetSet();

// create a facet field instance and set options
$facetSet->createFacetField('color')->setField('color')->setMinCount(1)->setLimit(1000);

// create a facet field instance and set options
$facetSet->createFacetField('category_name')->setField('category_name')->setMinCount(1)->setLimit(1000);

// create a facet field instance and set options
$facetSet->createFacetField('price')->setField('price')->setMinCount(1)->setLimit(1000);

// create a facet field instance and set options
$facetSet->createFacetField('location')->setField('vendor_city')->setMinCount(1)->setLimit(1000);


// create a facet field instance and set options
$facetSet->createFacetField('country')->setField('vendor_country')->setMinCount(1)->setLimit(1000);

/*********** Facet *******************************/

// handle paginating
$start = 0;
if(isset($_POST['s']) && !empty($_POST['s'])) {
	$start = $_POST['s'];
	$query->setStart($start);
}

$query->createFilterQuery('is_in_stock')->setQuery('is_in_stock:1');
// this executes the query and returns the result

$resultset = $client->select($query);

$total_array = $resultset->getResponse();
$total_values = $total_array->getBody();

$home_page_cat_arr = array('Jewelry', 'Sarees', 'Bags', 'Rakhi Gifts', 'Home Decor', 'Clothing', 'Accessories', 'Home Furnishing', 'Bath & Beauty', 'Food & Health', 'Gifts', 'Kids', 'Books', 'Footwear', 'New Arrivals', 'Wedding', 'Spiritual', 'Supplies');

/*********** Facet Get **********************/

$facet_color = $resultset->getFacetSet()->getFacet('color');

$facet_category = $resultset->getFacetSet()->getFacet('category_name');

$facet_country = $resultset->getFacetSet()->getFacet('country');

$facet_category_new = array();
if($facet_category){
	foreach($facet_category as $key=>$val)
	{
		if(in_array($key, $home_page_cat_arr))
		{
			$facet_category_new[$key] = $val;
		}
	}
}
$facet_price = $resultset->getFacetSet()->getFacet('price');

$facet_location = $resultset->getFacetSet()->getFacet('location');

//$abc->facet_counts->facet_fields->category_name = $facet_category_new;

/*********** Facet Get **********************/

$abc = json_decode($total_values);

$abc->facet_counts->facet_fields->category_name = $facet_category_new;

$total_num_records_value = count($abc->response->docs);

for($k=0;$k<$total_num_records_value;$k++)
{
	$abc->response->docs[$k]->image = "http://assets1.craftsvilla.com/catalog/product/cache/1/small_image/166x166/9df78eab33525d08d6e5fb8d27136e95".trim($abc->response->docs[$k]->image);
}
$abc->status = 1;
//print_r($abc);exit;
echo json_encode($abc);
exit();

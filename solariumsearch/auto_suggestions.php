<?php
require('init.php');
//session_start();

// create a client instance
$client = new Solarium_Client($config);

// get a suggester query instance
$query = $client->createSuggester();
$query->setQuery($_GET['query']); //multiple terms
$query->setDictionary('suggest');
$query->setOnlyMorePopular(true);
$query->setCount(10);
$query->setCollate(true);

// this executes the query and returns the result
//$resultset = $client->suggester($_GET['query']);
$resultset = $client->suggester($query);

/*echo '<b>Query:</b> '.$query->getQuery().'<hr/>';
exit();*/

$suggestions_arr = array();
 
// display results for each term
foreach ($resultset as $term => $termResult) {
    /*echo '<h3>' . $term . '</h3>';
    echo 'NumFound: '.$termResult->getNumFound().'<br/>';
    echo 'StartOffset: '.$termResult->getStartOffset().'<br/>';
    echo 'EndOffset: '.$termResult->getEndOffset().'<br/>';
    echo 'Suggestions:<br/>';*/
    foreach($termResult as $result){
        //echo '- '.$result.'<br/>';
        $suggestions_arr[] = $result;
    }
 
    //echo '<hr/>';
}
 
/*echo "<pre>";
 print_r($suggestions_arr);
 exit();*/
// display collation
//echo 'Collation: '.$resultset->getCollation();

$counter='0';
echo "{";
echo "query:'".$_GET['query']."',";
echo "suggestions:[";
//$res = mysql_query("select name from wp2wp_terms where name like '$query%' ORDER BY name desc");
//while ($row = mysql_fetch_array($res)) {
foreach($suggestions_arr as $suggestionsval) {
	$counter++;
	if ($counter > 1) {
		echo ",";
	}
	$name=$suggestionsval;
	echo "'$name'";
}
echo "],}";

exit();

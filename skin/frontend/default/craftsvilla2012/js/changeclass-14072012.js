function menu(val)
{
	if (val==0)
	{
		document.getElementById('storeic').className = "store-hover";
		document.getElementById('kettleic').className = "";
		document.getElementById('shopic').className = "";
		document.getElementById('searchby').value = "all";
		document.getElementById('search').value = "Search Entire Store";	
		document.getElementById('search_mini_form').action = '/searchresults';
	}
	else if (val==1)
	{
		document.getElementById('storeic').className="";
		document.getElementById('kettleic').className="kettle-hover";
		document.getElementById('shopic').className="";
		document.getElementById('searchby').value = "product";
		document.getElementById('search').value = "Search All Products";
		document.getElementById('search_mini_form').action = '/searchresults';
	}
	else if(val==2)
	{
		document.getElementById('storeic').className="";
		document.getElementById('kettleic').className="";
		document.getElementById('shopic').className="search_shop-hover";
		document.getElementById('searchby').value = "shop";
		document.getElementById('search').value = "Search All Shops";
		document.getElementById('search_mini_form').action = '/shopsearch';
	}
	/*else if (val==5)
	{
  		document.getElementById('Home').className=""
  		document.getElementById('Product').className=""
  		document.getElementById('Buyonline').className=""
  		document.getElementById('Faq').className=""
  		document.getElementById('hypBathroomVanity').className=""
  		document.getElementById('hypSignatureColl').className="selected"
	}*/
}

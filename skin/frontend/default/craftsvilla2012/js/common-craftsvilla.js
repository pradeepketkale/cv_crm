<script src="//[unbxd.s3.amazonaws.com/jquery-unbxdautosuggest.js](http://unbxd.s3.amazonaws.com/jquery-unbxdautosuggest.js)"></script>
<link rel="stylesheet" href="//[unbxd.s3.amazonaws.com/jquery-unbxdautosuggest.css](http://unbxd.s3.amazonaws.com/jquery-unbxdautosuggest.css)">

h3.menuitem.submenuheader 
{ 
cursor: pointer; 
} 

label 
{ 
color: #0192B5; 
} 

.unbxd-as-wrapper ul li.unbxd-as-insuggestion 
{ 
color:#cf4f4a !important; 
} 

.unbxd-as-wrapper ul li.unbxd-as-insuggestion:hover 
{ 
color:white; 
background-color: #cf4f4a; 
}

.unbxd-as-popular-product-cart-button 
{ 
background-color:#cf4f4a !important; 
} 

li.unbxd-as-keysuggestion 
{ 
color:#636363; 
}

.unbxd-as-wrapper ul li.unbxd-as-keysuggestion:hover 
{ 
background-color: #cf4f4a; 
color: white;
}




/* * * CONFIGURATION * * */
var UnbxdSiteName = "craftsvilla_com-u1424696808573";
var UnbxdApiKey = "d2a6528fa27f89dc163cf58532221bf5";

/* * * DO NOT EDIT BELOW THIS LINE * * */
(function() {
 var ubx = document.createElement("script");
 ubx.type = "text/javascript";
 ubx.async = true;
 ubx.src = "//unbxd.s3.amazonaws.com/embed.js";
 (document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(ubx);
})();
          unbxdAutoSuggestFunction(jQuery, Handlebars);

    jQuery(function(){
        //on dom load set autocomplete options
        //Usage: $(<element>).unbxdautocomplete(options);
        jQuery("#search").unbxdautocomplete({
            siteName : 'craftsvilla_com-u1424696808573' //your site key which can be found on dashboard
            ,APIKey : 'd2a6528fa27f89dc163cf58532221bf5' //your api key which is mailed to during account creation or can be found on account section on the dashboard
            ,minChars : 1
            ,delay : 100
            ,loadingClass : 'unbxd-as-loading'
            ,mainWidth : 0
            ,sideWidth : 180
            ,zIndex : 0
            ,position : 'absolute'
            ,template : "1column" 
            ,mainTpl: ['inFields', 'keywordSuggestions', 'topQueries', 'popularProducts']
            ,sideTpl: []
            ,sideContentOn : "right"
            ,showCarts : true
            ,cartType : "separate"
            ,onSimpleEnter : function(){
                console.log("Simple enter :: do a form submit")
                this.input.form.submit();
            }
            ,onItemSelect : function(data,original){
                // 
                if(data.type == "IN_FIELD"){
            // catFilter = jQuery('.UI-CATEGORY').val();
            if(data.filtername){
              if (data.filtername == "categoryname")
                filtername = "category_fq"  


              window.location = window.location.origin + '/craftsvilla/index.html?q='+encodeURI(data.value)+'&filter='+ filtername + ':' + encodeURI(data.filtervalue) ;
            }
            else
              this.input.form.submit();


            }else if(data.type == "POPULAR_PRODUCTS"){
            //console.log(original.link);
            window.location = original.link;
            }else{
            this.input.form.submit();
            }
            }
            ,onCartClick : function(data,original){
                console.log("addtocart", arguments);
                return true;
            }
            ,inFields:{
                count: 2
                ,fields:{
                    
                    'material':2,
                    'brand':2

                    //,'color':3
                }
                ,header: 'Search Suggestions'
                ,tpl: ''
            }
            ,topQueries:{
                count: 3
                // ,header: 'Top Queries'
                ,tpl: ''
            }
            ,keywordSuggestions:{
                count: 3
                ,header: ''
                ,tpl: ''
            }
            ,popularProducts:{
                count: 2
                ,price: true
                ,priceFunctionOrKey : "originalprice"
                ,image: true
                ,imageUrlOrFunction: "imageURL"
                ,currency : "Rs."
                ,header: 'Popular Products'
                ,tpl: ''
            }
        });
    });

  /* * * CONFIGURATION * * */
  var UnbxdSiteName = "craftsvilla_com-u1424696808573"; // Replace the value with the Site Key.
  /* * * DON'T EDIT BELOW THIS LINE * * */
  (function() {
  var ubx = document.createElement('script'); ubx.type = 'text/javascript'; ubx.async = true;
  ubx.src = '//d21gpk1vhmjuf5.cloudfront.net/unbxdAnalytics.js';
  (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ubx);
  })();


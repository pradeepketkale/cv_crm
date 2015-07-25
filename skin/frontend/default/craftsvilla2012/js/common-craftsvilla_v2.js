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
                window.location = window.location.origin + '/craftsvilla/index.html?q=' + encodeURIComponent(this.input.value);
            }
            ,onItemSelect : function(data,original){
                // 
                if(data.type == "IN_FIELD"){
                        // catFilter = jQuery('.UI-CATEGORY').val();
                        if(data.filtername){
              if (data.filtername == "material")
                filtername = "material_fq"  


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
            ,onCartClick : function(data,obj)
            {
                if (obj !== undefined && obj.productid !== undefined)
                {
                    var pid = obj.productid;
                    window.location = '//www.craftsvilla.com/checkout/cart/add/uenc/aHR0cDovL3d3dy5jcmFmdHN2aWxsYS5jb20vY2F0YWxvZy9wcm9kdWN0L3ZpZXcvaWQvMTU5NDA1Ny9zL2thcmlzaG1hLWthcG9vci1yZWQtYW5kLWN                    yZWFtLWVtYnJvaWRlcnktc2Fsd2FyLWthbWVlei8,/product/' + pid + '/' ;
                }
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

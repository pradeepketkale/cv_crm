<?php

class Craftsvilla_Productupdate_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function searchBox()
        {
          
        
            $bodyHtml .=  '
              <table cellspacing="0" cellpadding="0" class="massaction">
                            <tbody style="padding:10%;">
                                <tr>
                                    <td> 
                                    <a href="#" onClick="selectAll(\'qualitycheck\',\'1\');">Check All </a> 
                                     <span class="separator">|</span>
                                    <a href="#" onClick="selectAll(\'qualitycheck\',\'0\');">Uncheck All</a>                                      
                                       
                                        <strong id="productmanagementGrid_massaction-count">0</strong> items selected    </td>
                                    <td></td>
                                    <td></td>
                                    <td> 
                                       <button  type="button"  onclick="reIndex();" style="float:right;"><span>Reindex</span></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>    
                    <form action="" method="post">
			<label>Select Search</label>
			<select  name="select_search" id="select_search" required="required">
					 <option value="none">Select</option>
					<option value="productId">Product Id</option>
					<!--<option value="productName">Product Name</option>-->
					 <option value="vendorName">Vendor Name</option>
					 
				</select>
	
				<br><br>
                        <div class="field">
                            <input type="text" name="product_sku" id="product_sku" placeholder="ID, Name, Vendor Id"  value="" style="width:100%;">
                        </div>
                         <br>
                            <input type="button" name="search" onclick="doSearch();" value="Search" >
                    </form>

           
           
             
                    <script type="text/javascript">
			                 jQuery.noConflict();
			           
                    	    function doSearch()
                            {
				
                      				jQuery("#loading").css("display","block");                              
                      				var searchtext=document.getElementById("product_sku").value;
                      				 var selectedSearch=document.getElementById("select_search").value;
                                                   	// alert(searchtext);

                                       jQuery.ajax({
                                   url : "/productupdate/index/getsearchData",
                                   type : "POST",
                                   data : {searchtext:searchtext,selectedSearch:selectedSearch},
                                   success : function(result){
					                             jQuery("#loading").css("display","none");
                                    	jQuery("#listProducts").html(result);
                                   	//  alert(result);   
                                }
		                       });

                            }

                            function reIndex()
                            {
                           		var values = [];
                                jQuery("input[type=checkbox]:checked").each(function(){
                                    values.push(this.id);
                                      //var pid= this.id;
                                        
                                });
                                var pid = values.join();
                                //alert(pid);
                                 jQuery.ajax({
                                   url : "/productupdate/index/reIndexSelected",
                                   type : "POST",
                                   data : {pid:pid},
                                   success : function(result){
                                   // $("#listProducts").html(result);
                                    alert(result);   
                                }
                               });
                              
                              
                            }
                   </script>';
                $bodyHtml .='<br><br><center><div id="loading" style="display:none;"><img src="/images/loading.gif"></center></div><div id="listProducts"></div>';
                echo $bodyHtml; 
        
        
        }
      public  function reindex(){
			
			echo "hello";
			}

}

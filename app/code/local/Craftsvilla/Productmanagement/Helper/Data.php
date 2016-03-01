<?php

class Craftsvilla_Productmanagement_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function searchBox()
        {         

                $a .=   "
                           <script type='text/javascript'>
        function selectAll(name, value) 
        {
        
                  var forminputs  = document.getElementsByTagName('input'); 
                  
                    for (i = 0; i < forminputs.length; i++) 
                    {
                      var regex = new RegExp(name, 'i');
                      if (regex.test(forminputs[i].getAttribute('name'))) {
                          if (value == '1') {
                              forminputs[i].checked = true;
                          } else {
                              forminputs[i].checked = false;
                          }
                      }
                  }
              }
             </script>
                      ";
        
                $a .=  '<table cellspacing="0" cellpadding="0" class="massaction">
                            <tbody style="padding:10%;">
                                <tr>
                                    <td>  
                                        <a href="#" onClick="selectAll(\'qualitycheck\',\'1\');">Check All </a> 
                                     <span class="separator">|</span>
                                    <a href="#" onClick="selectAll(\'qualitycheck\',\'0\');">Uncheck All</a>
                                                                 
                                       </td>
                                    <td></td>
                                    <td></td>
                                    <td> 
                                        <input type="button" name="search" onclick="disableSelected();" style="padding:5%;background: #ffac47;  border-color: #ed6502 #a04300 #a04300 #ed6502;" value="Disable">
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
                            <option value="productName">Product Name</option>
                            <option value="vendorName">Vendor Name</option>
                             
                        </select>
                        <br><br>
                        <div class="field">
                            <input type="text" name="product_sku" id="product_sku" placeholder="Product ID, Vendor Name,Product Name "  value="" style="width:100%;">
                        </div>
                        <br>
                        <input type="button" name="search" onclick="doSearch();" value="Search">
                    </form>
                    <br>
                  
                    <script type="text/javascript">
                     jQuery.noConflict();
                            function doSearch()
                            {
                                jQuery("#loading").css("display","block");
                                var searchtext=document.getElementById("product_sku").value;
                                 var selectedSearch=document.getElementById("select_search").value;
                                //alert(searchtext);
                                      jQuery.ajax({
                                      url : "/productmanagement/index/getsearchDetail",
                                      type : "POST",
                                      data : {searchtext:searchtext,selectedSearch:selectedSearch},
                                      success : function(result){
                                        jQuery("#loading").css("display","none");
                                        jQuery("#listProducts").html(result);
                                        // alert(result);  

                                      }
                                  });

                            }
                            function disableSelected()
                            {
                              jQuery("#loading").css("display","block");
                                var values = [];
                                jQuery("input[type=checkbox]:checked").each(function(){
                                    values.push(this.id);
                                      var pid= this.id;
                                
                                });
                                var count=jQuery("input[type=checkbox]:checked").length;
                                var pid = values.join();
                                //$("#count").html("+count+");
                                //alert(count);
                                 jQuery.ajax({
                                   url : "/productmanagement/index/disableSelected",
                                   type : "POST",
                                   data : {pid:pid},
                                   success : function(result){
				                           jQuery("#loading").css("display","none");
                                    jQuery("#listProducts").html(result);
                                   //     $("#count").html(result); 
                                    alert(result);   
                                   
                                }
                               });
                           }
                   </script>';
                $a .='<br><br><center><div id="loading" style="display:none;"><img src="/images/loading.gif"></center></div><div id="listProducts"></div>';
                echo $a; 
        }
 
  
}

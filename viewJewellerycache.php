<?php 
error_reporting(E_ALL & ~E_NOTICE);
							$i = 1;
						$pagenum = 750;
						while($i < $pagenum){	
						//echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?p='.$i++);
						echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?p='.$i++);
						$ch1 = curl_init();
						curl_setopt($ch1, CURLOPT_VERBOSE, 0); 
						curl_setopt($ch1,CURLOPT_URL, $url1);
						//curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
						//curl_setopt( $ch, CURLOPT_POST, 1);
						//curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt( $ch1, CURLOPT_HEADER, 0);
						curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
						//curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
						curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
						 $response1 = curl_exec($ch1);
						
						if(curl_errno($ch1))
							{		
								print curl_error($ch1);
							}
													
							curl_close($ch1);
							
						}

						$i = 1;
 						while($i < $pagenum){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?dir=asc&order=price&p='.$i++);
                                                $ch1 = curl_init();
                                                curl_setopt($ch1, CURLOPT_VERBOSE, 0);
                                                curl_setopt($ch1,CURLOPT_URL, $url1);
                                                //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                                                //curl_setopt( $ch, CURLOPT_POST, 1);
                                                //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                                                curl_setopt( $ch1, CURLOPT_HEADER, 0);
                                                curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
                                                //curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                                                curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                                                 $response1 = curl_exec($ch1);

                                                if(curl_errno($ch1))
                                                        {
                                                                print curl_error($ch1);
                                                        }

                                                        curl_close($ch1);

                                                }

						$i = 1;
						while($i < $pagenum){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?dir=desc&order=price&p='.$i++);
                                                $ch1 = curl_init();
                                                curl_setopt($ch1, CURLOPT_VERBOSE, 0);
                                                curl_setopt($ch1,CURLOPT_URL, $url1);
                                                //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                                                //curl_setopt( $ch, CURLOPT_POST, 1);
                                                //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                                                curl_setopt( $ch1, CURLOPT_HEADER, 0);
                                                curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
                                                //curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                                                curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                                                 $response1 = curl_exec($ch1);

                                                if(curl_errno($ch1))
                                                        {
                                                                print curl_error($ch1);
                                                        }

                                                        curl_close($ch1);

                                                }

						$i = 1;
                                                while($i < $pagenum){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?dir=desc&order=discount&p='.$i++);
                                                $ch1 = curl_init();
                                                curl_setopt($ch1, CURLOPT_VERBOSE, 0);
                                                curl_setopt($ch1,CURLOPT_URL, $url1);
                                                //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                                                //curl_setopt( $ch, CURLOPT_POST, 1);
                                                //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                                                curl_setopt( $ch1, CURLOPT_HEADER, 0);
                                                curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
                                                //curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                                                curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                                                 $response1 = curl_exec($ch1);

                                                if(curl_errno($ch1))
                                                        {
                                                                print curl_error($ch1);
                                                        }

                                                        curl_close($ch1);

                                                }


						$i = 1;
                                                while($i < $pagenum){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?dir=asc&order=discount&p='.$i++);
                                                $ch1 = curl_init();
                                                curl_setopt($ch1, CURLOPT_VERBOSE, 0);
                                                curl_setopt($ch1,CURLOPT_URL, $url1);
                                                //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                                                //curl_setopt( $ch, CURLOPT_POST, 1);
                                                //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                                                curl_setopt( $ch1, CURLOPT_HEADER, 0);
                                                curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
                                                //curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                                                curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                                                 $response1 = curl_exec($ch1);

                                                if(curl_errno($ch1))
                                                        {
                                                                print curl_error($ch1);
                                                        }

                                                        curl_close($ch1);

                                                }



						$i = 1;
                                                while($i < $pagenum){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?dir=asc&order=shippingtime&p='.$i++);
                                                $ch1 = curl_init();
                                                curl_setopt($ch1, CURLOPT_VERBOSE, 0);
                                                curl_setopt($ch1,CURLOPT_URL, $url1);
                                                //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                                                //curl_setopt( $ch, CURLOPT_POST, 1);
                                                //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                                                curl_setopt( $ch1, CURLOPT_HEADER, 0);
                                                curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
                                                //curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                                                curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                                                 $response1 = curl_exec($ch1);

                                                if(curl_errno($ch1))
                                                        {
                                                                print curl_error($ch1);
                                                        }

                                                        curl_close($ch1);

                                                }

						$i = 1;
                                                while($i < 122){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?cod=showcod&p='.$i++);
                                                $ch1 = curl_init();
                                                curl_setopt($ch1, CURLOPT_VERBOSE, 0);
                                                curl_setopt($ch1,CURLOPT_URL, $url1);
                                                //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                                                //curl_setopt( $ch, CURLOPT_POST, 1);
                                                //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                                                curl_setopt( $ch1, CURLOPT_HEADER, 0);
                                                curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
                                                //curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                                                curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                                                 $response1 = curl_exec($ch1);

                                                if(curl_errno($ch1))
                                                        {
                                                                print curl_error($ch1);
                                                        }

                                                        curl_close($ch1);

                                                }
 $i = 1;
                                                while($i < 233){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?min_value=10&max_value=500&p='.$i++);
                                                $ch1 = curl_init();
                                                curl_setopt($ch1, CURLOPT_VERBOSE, 0);
                                                curl_setopt($ch1,CURLOPT_URL, $url1);
                                                curl_setopt( $ch1, CURLOPT_HEADER, 0);
                                                curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
                                                curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                                                $response1 = curl_exec($ch1);
                                                if(curl_errno($ch1)) print curl_error($ch1);
                                                curl_close($ch1);
                                                }

						while($i < 100){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/jewellery-jewelry.html'.'?min_value=500&max_value=1000&p='.$i++);
                                                $ch1 = curl_init();
                                                curl_setopt($ch1, CURLOPT_VERBOSE, 0);
                                                curl_setopt($ch1,CURLOPT_URL, $url1);
                                                curl_setopt( $ch1, CURLOPT_HEADER, 0);
                                                curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
                                                curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                                                $response1 = curl_exec($ch1);
                                                if(curl_errno($ch1)) print curl_error($ch1);
                                                curl_close($ch1);
                                                }
?>

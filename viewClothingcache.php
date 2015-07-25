<?php 
error_reporting(E_ALL & ~E_NOTICE);
							$i = 1;
						while($i < 174){	
						echo $url1 =str_replace(' ','','http://www.craftsvilla.com/clothing.html'.'?p='.$i++);
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
						while($i < 174){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/clothing.html'.'?dir=asc&order=price&p='.$i++);
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
                                                while($i < 174){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/clothing.html'.'?dir=desc&order=price&p='.$i++);
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
                                                while($i < 174){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/clothing.html'.'?dir=desc&order=name&p='.$i++);
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
                                                while($i < 174){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/clothing.html'.'?dir=asc&order=name&p='.$i++);
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
                                                while($i < 31){
                                                echo $url1 =str_replace(' ','','http://www.craftsvilla.com/clothing.html'.'?cod=showcod&p='.$i++);
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
?>

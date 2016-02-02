<?php

if(isset($_POST['submit'])){
	echo $uname=$_POST['uname'];
	echo $pwd=$_POST['pwd'];


if($uname == ""){
return true;
}else{
return false;
}
}


?>

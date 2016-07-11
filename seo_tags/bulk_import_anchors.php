<?php
$valid_passwords = array ("seo" => "s3o");
$valid_users = array_keys($valid_passwords);

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

if (!$validated) {
  header('WWW-Authenticate: Basic realm="craftsvilla"');
  header('HTTP/1.0 401 Unauthorized');
  die ("Not authorized");
}
?>
<html>
<body>
<div class="mainContainer">
	
	<div class="importForm">
		<div class="hint">Upload csv file to import the anchors in bulk</div>
		<div class="link_sample"><a href="anchor.csv" target="_blank">Click here to download sample upload file</a></div>
		<form action="import_anchors.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
		<input type="file" name="file">
		<input type="submit" name="btn_submit" value="Upload File" />
		</form>
	</div>
</div>
</body>
</html>

<style type="text/css">
	body {
    font-family: Lato!important;
    font-size: 14px;
    line-height: 19px;
    background-color: #f8f8f8;
    padding: 0;
}
.mainContainer {
    background-color: #fff;
    overflow: auto;
    border: 1px solid #e1e1e1;
    padding: 20px;
    margin-top: 10px;
}
.hint {
    border-bottom: 1px solid #ccc;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 2px;
    text-align: center;
}
.link_sample {
    text-align: center;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 1px;
}
.link_sample a{
	color:#222;
}
form {
    margin: 0 auto;
    width: 350px;
    margin-top: 60px;
    margin-bottom: 60px;
}
</style>
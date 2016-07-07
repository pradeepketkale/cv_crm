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
		<div class="">
			<ul class="">
				<li class="li_link"><a href="manage_anchors.php">Activate Anchors</a></li>
				<li class="li_link"><a href="seo_anchor_feed.php">Manage Anchor Tags</a></li>
				<li class="li_link"><a href="bulk_import_anchors.php">Bulk Import Anchors</a></li>
			</ul>
		</div>
	</div>
</body>
</html>
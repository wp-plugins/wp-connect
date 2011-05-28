<?php
include "../../../wp-config.php";

if ($_SESSION['wp_callback']) {
	$uid = $_POST["uid"];
	$name = $_POST["name"];
	$tinyurl = $_POST["tinyurl"];
	$renren_api_key = $_POST["renren_api_key"];
	$renren_secret = $_POST["renren_secret"];

	if (!is_user_logged_in()) {
		$tmail = $uid . '@renren.com';
		$tid = "rtid";
		wp_connect_login($name . '|' . $uid . '|' . $name . '||||' . $tinyurl, $tmail, $tid);
	} 
} 

?>
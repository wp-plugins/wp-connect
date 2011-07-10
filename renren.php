<?php
include "../../../wp-config.php";
$wptm_connect = get_option('wptm_connect');
require_once( 'OAuth/renren.class.php' );
session_start();
if ($_GET['login'] == "RENREN") {
	// Authorization Code
	$oauth = new RenRenOauth();
	$url = $oauth -> getAuthorizeUrl();
	header("Location: $url");
} else {
	if ($_GET['code']) {
		$code = $_GET['code']; 
		// Access Token
		$oauth = new RenRenOauth();
		$token = $oauth -> getAccessToken($code); 
		// API Session Key
		$oauth = new RenRenOauth();
		$access_token = $token['access_token'];
		$key = $oauth -> getSessionKey($access_token); 
		// users.getInfo
		$client = new RenRenClient();

		$session_key = $key['renren_token']['session_key'];
		$client -> setSessionKey($session_key); 
		// 调用api时的第一个参数是api方法名。
		// 第二个参数请参考config.inc.php文件中的配置进行设置。
		$renren = $client -> POST('users.getInfo'); 
		// var_dump($renren);
		if ($_SESSION['wp_url_back']) {
			$renren = $renren[0];
			$uid = $renren['uid'];
			$name = $renren['name'];
			$head = $renren['tinyurl'];
			$url = 'http://www.renren.com/profile.do?id=' . $uid;
			$_SESSION['wp_url_login'] = "";
			if (!is_user_logged_in()) {
				$tmail = $uid . '@renren.com';
				$tid = "rtid";
				wp_connect_login($head . '|' . $uid . '|' . $name . '|' . $url . '|||renren', $tmail, $tid);
			} 
		} 
	} 
	echo '<script  type="text/javascript"> history.back() ;</script>';
} 

?>
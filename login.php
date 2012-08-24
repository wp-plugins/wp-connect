<?php
include "../../../wp-config.php";
include_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
session_start();
$login = isset($_GET['go']) ? strtolower($_GET['go']) : '';
if ($login) {
	switch ($login) {
		case "sina":
			if (!class_exists('sinaOAuth')) {
				include dirname(__FILE__) . '/OAuth/sina_OAuth.php';
			} 
			$to = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET);
			break;
		case "qq":
			if (!class_exists('qqOAuth')) {
				include dirname(__FILE__) . '/OAuth/qq_OAuth.php';
			} 
			$to = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET);
			break;
		case "sohu":
			if (!class_exists('sohuOAuth')) {
				include dirname(__FILE__) . '/OAuth/sohu_OAuth.php';
			} 
			$to = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET);
			break;
		case "netease":
			if (!class_exists('neteaseOAuth')) {
				include dirname(__FILE__) . '/OAuth/netease_OAuth.php';
			} 
			$to = new neteaseOAuth(APP_KEY, APP_SECRET);
			break;
		case "douban":
			if (!class_exists('doubanOAuth')) {
				include dirname(__FILE__) . '/OAuth/douban_OAuth.php';
			} 
			$to = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET);
			break;
		case "tianya":
			if (!class_exists('tianyaOAuth')) {
				include dirname(__FILE__) . '/OAuth/tianya_OAuth.php';
			} 
			$to = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET);
			break;
		case "twitter":
			if (!class_exists('twitterOAuth')) {
				include dirname(__FILE__) . '/OAuth/twitter_OAuth.php';
			} 
			$to = new twitterOAuth(T_APP_KEY, T_APP_SECRET);
			break;
		default:
			exit();
	} 
	$callback = plugins_url('wp-connect/login.php');
	$tok = $to -> getRequestToken($callback);
	$_SESSION["oauth_token_secret"] = $tok['oauth_token_secret'];
	$request_link = $to -> getAuthorizeURL($tok['oauth_token'], false, $callback);
	$_SESSION['wp_url_login'] = $login;
	header('Location:' . $request_link);
} else {
	$callback = $_SESSION['wp_url_back'] ? $_SESSION['wp_url_back'] : get_bloginfo('url');
	header('Location:' . $callback);
} 

?>
<?php
include "../../../wp-config.php";
include_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
session_start();
switch ($_GET['go']) {
	case "SINA":
		if (!class_exists('sinaOAuth')) {
			include dirname(__FILE__) . '/OAuth/sina_OAuth.php';
		} 
		$to = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET);
		break;
	case "QQ":
		if (!class_exists('qqOAuth')) {
			include dirname(__FILE__) . '/OAuth/qq_OAuth.php';
		} 
		$to = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET);
		break;
	case "SOHU":
		if (!class_exists('sohuOAuth')) {
			include dirname(__FILE__) . '/OAuth/sohu_OAuth.php';
		} 
		$to = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET);
		break;
	case "NETEASE":
		if (!class_exists('neteaseOAuth')) {
			include dirname(__FILE__) . '/OAuth/netease_OAuth.php';
		} 
		$to = new neteaseOAuth(APP_KEY, APP_SECRET);
		break;
	case "DOUBAN":
		if (!class_exists('doubanOAuth')) {
			include dirname(__FILE__) . '/OAuth/douban_OAuth.php';
		} 
		$to = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET);
		break;
	case "TIANYA":
		if (!class_exists('tianyaOAuth')) {
			include dirname(__FILE__) . '/OAuth/tianya_OAuth.php';
		} 
		$to = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET);
		break;
	case "TWITTER":
		if (!class_exists('twitterOAuth')) {
			include dirname(__FILE__) . '/OAuth/twitter_OAuth.php';
		} 
		$to = new twitterOAuth(T_APP_KEY, T_APP_SECRET);
		break;
	default:
} 

if ($_GET['go']) {
	$callback = get_bloginfo('wpurl') . '/wp-content/plugins/wp-connect/login.php';
	$tok = $to -> getRequestToken($callback);
	$_SESSION["oauth_token_secret"] = $tok['oauth_token_secret'];
	$request_link = $to -> getAuthorizeURL($tok['oauth_token'], false, $callback);
	$_SESSION['wp_url_login'] = $_GET['go'];
	header('Location:' . $request_link);
} else {
	if ($_SESSION['wp_url_back']) {
		$callback = $_SESSION['wp_url_back'];
	} else {
		//$callback = admin_url('profile.php');
		$callback = get_bloginfo('url');
	} 
	header('Location:' . $callback);
} 

?>
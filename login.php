<?php
include "../../../wp-config.php";
include_once('config.php');
require_once('OAuth/OAuth.php');
session_start();
if ($_GET['go'] == "SINA") {
	include_once('OAuth/sina_OAuth.php'); 
	$to = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET);

} elseif ($_GET['go'] == "QQ") {
	include_once('OAuth/qq_OAuth.php'); 
	$to = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET);

} elseif ($_GET['go'] == "NETEASE") {
	include_once('OAuth/netease_OAuth.php'); 
	$to = new neteaseOAuth(APP_KEY, APP_SECRET);

} elseif ($_GET['go'] == "DOUBAN") {
	include_once('OAuth/douban_OAuth.php'); 
	$to = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET);

} else {
	if ($_SESSION['wp_callback']) {
		$callback = $_SESSION['wp_callback'];
	} else {
		$callback = get_bloginfo('wpurl');
	} 
	header('Location:' . $callback);
} 

if ($_GET['go']) {
	$callback = get_bloginfo('wpurl') . '/wp-content/plugins/wp-connect/login.php';

	$tok = $to -> getRequestToken($callback);

	$_SESSION["oauth_token_secret"] = $tok['oauth_token_secret'];

	$request_link = $to -> getAuthorizeURL($tok['oauth_token'], false, $callback);

	$_SESSION['wp_go_login'] = $_GET['go'];

	header('Location:' . $request_link);
} 

?>
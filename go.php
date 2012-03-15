<?php
include "../../../wp-config.php";
session_start();
if (empty($_SESSION['wp_url_bind'])){
	header('Location:' . get_bloginfo('url'));
   return;
}
if (is_user_logged_in()) {
	$bind = isset($_GET['bind']) ? strtolower($_GET['bind']) : '';
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
	include_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
	if ($bind == "qq" || $callback == "qq") {
		include_once("OAuth/qq_OAuth.php");
		$a = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET);
		$b = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_qq";
	} elseif ($bind == "sina" || $callback == "sina") {
		include_once("OAuth/sina_OAuth.php");
		$a = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET);
		$b = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_sina";
	} elseif ($bind == "sohu" || $callback == "sohu") {
		include_once("OAuth/sohu_OAuth.php");
		$a = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET);
		$b = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_sohu";
	} elseif ($bind == "netease" || $callback == "netease") {
		include_once("OAuth/netease_OAuth.php");
		$a = new neteaseOAuth(APP_KEY, APP_SECRET);
		$b = new neteaseOAuth(APP_KEY, APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_netease";
	} elseif ($bind == "twitter" || $callback == "twitter") {
		include_once("OAuth/twitter_OAuth.php");
		$a = new twitterOAuth(T_APP_KEY, T_APP_SECRET);
		$b = new twitterOAuth(T_APP_KEY, T_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_twitter_oauth";
	} elseif ($bind == "douban" || $callback == "douban") {
		include_once("OAuth/douban_OAuth.php");
		$a = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET);
		$b = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_douban";
	} elseif ($bind == "tianya" || $callback == "tianya") {
		include_once("OAuth/tianya_OAuth.php");
		$a = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET);
		$b = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_tianya";
	} else {
		return false;
	} 
	if ($bind) {
		$callback = plugins_url('wp-connect/go.php?callback=' . $bind);

		$keys = $a -> getRequestToken($callback);

		$aurl = $a -> getAuthorizeURL($keys['oauth_token'], false, $callback);

		$_SESSION['keys'] = $keys;

		header('Location:' . $aurl);
	} elseif ($callback) {
		$last_key = $b -> getAccessToken($_REQUEST['oauth_verifier']);

		$_SESSION['last_key'] = $last_key;

		$update = array (
			'oauth_token' => $_SESSION['last_key']['oauth_token'],
			'oauth_token_secret' => $_SESSION['last_key']['oauth_token_secret']
			);
		if ($_SESSION['wp_url_bind'] == WP_CONNECT) {
			update_option($tok, $update);
		} elseif ($_SESSION['user_id']) {
			update_usermeta($_SESSION['user_id'], $tok, $update);
		}
		header('Location:' . $_SESSION['wp_url_bind']);
	} 
} 

?>
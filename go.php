<?php
include "../../../wp-config.php";
if (is_user_logged_in()) {
	include_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
	session_start();
	if ($_GET['OAuth'] == "qq" || $_GET['OAuth'] == "QQ" || $_GET['callback'] == "QQ") {
		include_once("OAuth/qq_OAuth.php");
		$a = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET);
		$b = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_qq";
		$tid = "QQ";
	} elseif ($_GET['OAuth'] == "sina" || $_GET['OAuth'] == "SINA" || $_GET['callback'] == "SINA") {
		include_once("OAuth/sina_OAuth.php");
		$a = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET);
		$b = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_sina";
		$tid = "SINA";
	} elseif ($_GET['OAuth'] == "sohu" || $_GET['OAuth'] == "SOHU" || $_GET['callback'] == "SOHU") {
		include_once("OAuth/sohu_OAuth.php");
		$a = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET);
		$b = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_sohu";
		$tid = "SOHU";
	} elseif ($_GET['OAuth'] == "netease" || $_GET['OAuth'] == "NETEASE" || $_GET['callback'] == "NETEASE") {
		include_once("OAuth/netease_OAuth.php");
		$a = new neteaseOAuth(APP_KEY, APP_SECRET);
		$b = new neteaseOAuth(APP_KEY, APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_netease";
		$tid = "NETEASE";
	} elseif ($_GET['OAuth'] == "twitter" || $_GET['OAuth'] == "TWITTER" || $_GET['callback'] == "TWITTER") {
		include_once("OAuth/twitter_OAuth.php");
		$a = new twitterOAuth(T_APP_KEY, T_APP_SECRET);
		$b = new twitterOAuth(T_APP_KEY, T_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_twitter_oauth";
		$tid = "TWITTER";
	} elseif ($_GET['OAuth'] == "douban" || $_GET['OAuth'] == "DOUBAN" || $_GET['callback'] == "DOUBAN") {
		include_once("OAuth/douban_OAuth.php");
		$a = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET);
		$b = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_douban";
		$tid = "DOUBAN";
	} elseif ($_GET['OAuth'] == "tianya" || $_GET['OAuth'] == "TIANYA" || $_GET['callback'] == "TIANYA") {
		include_once("OAuth/tianya_OAuth.php");
		$a = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET);
		$b = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
		$tok = "wptm_tianya";
		$tid = "TIANYA";
	} else {
		return false;
	} 
	if ($_GET['OAuth']) {
		$callback = get_bloginfo('wpurl') . '/wp-content/plugins/wp-connect/go.php?callback=' . $tid;

		$keys = $a -> getRequestToken($callback);

		$aurl = $a -> getAuthorizeURL($keys['oauth_token'], false, $callback);

		$_SESSION['keys'] = $keys;

		if(!$_SESSION['wp_url_bind']){
		    $aurl = get_bloginfo('url');
		}

		header('Location:' . $aurl);
	} elseif ($_GET['callback']) {
		$last_key = $b -> getAccessToken($_REQUEST['oauth_verifier']);

		$_SESSION['last_key'] = $last_key;

		$update = array (
			'oauth_token' => $_SESSION['last_key']['oauth_token'],
			'oauth_token_secret' => $_SESSION['last_key']['oauth_token_secret']
			);
		if ($_SESSION['wp_url_bind'] == WP_CONNECT) {
			update_option($tok, $update);
		} else {
			update_usermeta($_SESSION['user_id'], $tok, $update);
		}
		header('Location:' . $_SESSION['wp_url_bind']);
	} 
} 

?>
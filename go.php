<?php
include "../../../wp-config.php";
session_start();
if (empty($_SESSION['wp_url_bind'])) {
	header('Location:' . get_bloginfo('url'));
	return;
} 
if (is_user_logged_in()) {
	$bind = isset($_GET['bind']) ? strtolower($_GET['bind']) : '';
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
	include_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
	if ($bind) {
		include_once(dirname(__FILE__) . '/OAuth/' . $bind . '_OAuth.php');
		switch ($bind) {
			case "sina":
				$to = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET);
				break;
			case "qq":
				$to = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET);
				break;
			case "sohu":
				if (!SOHU_APP_KEY || !SOHU_APP_SECRET) {
				   wp_die("出错了，请在插件页面的“开放平台”处填写自己申请的Consumer Key和Consumer secret，或者在“同步微博”处勾选“使用灯鹭开放平台提供的同步接口”，请<a href='{$_SESSION['wp_url_bind']}'>返回</a>！");
			    } 
				$to = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET);
				break;
			case "netease":
				$to = new neteaseOAuth(APP_KEY, APP_SECRET);
				break;
			case "douban":
				$to = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET);
				break;
			case "tianya":
				$to = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET);
				break;
			case "twitter":
				$to = new twitterOAuth(T_APP_KEY, T_APP_SECRET);
				break;
			default:
		} 
		$backurl = plugins_url('wp-connect/go.php?callback=' . $bind);
		$keys = $to -> getRequestToken($backurl);
		$aurl = $to -> getAuthorizeURL($keys['oauth_token'], false, $backurl);
		$_SESSION['keys'] = $keys;
		header('Location:' . $aurl);
	} elseif ($callback) {
		include_once(dirname(__FILE__) . '/OAuth/' . $callback . '_OAuth.php');
		switch ($callback) {
			case "sina":
				$to = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
				break;
			case "qq":
				$to = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
				break;
			case "sohu":
				$to = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
				break;
			case "netease":
				$to = new neteaseOAuth(APP_KEY, APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
				break;
			case "douban":
				$to = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
				break;
			case "tianya":
				$to = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
				break;
			case "twitter":
				$to = new twitterOAuth(T_APP_KEY, T_APP_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
				break;
			default:
		} 
		$redirect_to = $_SESSION['wp_url_bind'];
		$last_key = $to -> getAccessToken($_REQUEST['oauth_verifier']);
		if (!$last_key['oauth_token']) {
			wp_die("出错了，没有oauth_token或oauth_token不合法，请<a href='$redirect_to'>返回</a>重试！");
		} 
		$update = array ('oauth_token' => $last_key['oauth_token'],
			'oauth_token_secret' => $last_key['oauth_token_secret']
			);
		$tok = 'wptm_' . $callback;
		if ($redirect_to == WP_CONNECT) {
			update_option($tok, $update);
		} elseif ($_SESSION['user_id']) {
			update_usermeta($_SESSION['user_id'], $tok, $update);
		} 
		header('Location:' . $redirect_to);
	} 
} 

?>
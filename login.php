<?php
header("Content-type: text/html; charset=utf-8");
include "../../../wp-config.php";
session_start();
$bind = (isset($_GET['go'])) ? $_GET['go'] : $_SESSION['wp_url_login'];
$redirect_to = !is_user_logged_in() ? $_SESSION['wp_url_back'] : $_SESSION['wp_url_bind'];
if (!$redirect_to) {
	return;
} 
if ($bind = strtolower($bind)) {
	$action = isset($_GET['act']) ? $_GET['act'] : '';
	if ($action == "bind") {
		$_SESSION['sync_bind'] = true;
	} elseif ($action == "delete" && is_user_logged_in()) {
		if ($wpuid = get_uid_by_url($redirect_to)) {
			if ($bind == 'sina') {
				delete_usermeta($wpuid, 'stid');
			} elseif ($bind == 'douban') {
				delete_usermeta($wpuid, 'dtid');
			} elseif ($bind == 'tianya') {
				delete_usermeta($wpuid, 'tytid');
			} else {
				delete_usermeta($wpuid, $bind . 'id');
			} 
			do_action('delete_user_bind', $wpuid, $bind); // 钩子，方便自定义插件
			header('Location:' . $redirect_to);
		} 
		return;
	} 
	if ($bind == "tqq") $bind = "qq"; // 腾讯微博
	$appkey = $bind . '_app_key';
	$appsecret = $bind . '_app_secret';
	$backurl = plugins_url('wp-connect/login.php');
	include_once(dirname(__FILE__) . '/config.php');
	$bind_array = array('qq', 'sina', 'sohu', 'netease', 'tianya', 'douban', 'twitter', 'renren');
	if (in_array($bind, $bind_array) && $$appkey && $$appsecret) {
		define("WEIBO_APP_KEY" , $$appkey);
		define("WEIBO_SECRET" , $$appsecret);
		if ($bind == "sina" || $bind == "renren") {
			$_SESSION['wp_url_login'] = $bind;
			if ($_GET['go'] == "sina") {
				if (WEIBO_APP_KEY == $sina_app_key_default) { // 默认key
					$aurl = "http://smyx.sinaapp.com/connect.php?client_id=" . WEIBO_APP_KEY . "&redirect_to=" . urlencode($backurl);
				} else { // 自定义key
					$_SESSION['source_receiver'] = 'wp-connect/login.php';
					$aurl = "https://api.weibo.com/oauth2/authorize?client_id=" . WEIBO_APP_KEY . "&redirect_uri=" . urlencode(plugins_url('wp-connect/dl_receiver.php')) . "&response_type=code&with_offical_account=1";
				} 
				header('Location:' . $aurl);
				die();
			} elseif ($_GET['go'] == "renren") {
				if (WEIBO_APP_KEY && WEIBO_SECRET) {
					$url = "https://graph.renren.com/oauth/authorize?response_type=code&client_id=" . WEIBO_APP_KEY . "&redirect_uri=" . $backurl . "&state=1";
					header("Location: $url");
				} else {
					wp_die("请在 WordPress连接微博 插件里面的 <a href='" . admin_url('options-general.php?page=wp-connect#open') . "'>开放平台</a> 页面填写API。[ <a href='javascript:onclick=history.go(-1)'>返回</a> ]");
				} 
				die();
			} 
		} else {
			require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
			include_once(dirname(__FILE__) . '/OAuth/' . $bind . '_OAuth.php');
			$OAuth = $bind . 'OAuth';
		} 
		if (isset($_GET['go'])) {
			$a = new $OAuth(WEIBO_APP_KEY, WEIBO_SECRET);
			$keys = $a -> getRequestToken($backurl);
			$aurl = $a -> getAuthorizeURL($keys['oauth_token'], false, $backurl);
			$_SESSION['keys'] = $keys;
			$_SESSION['wp_url_login'] = $bind;
			header('Location:' . $aurl);
			die();
		} elseif (isset($_GET['oauth_token'])) { // OAuth 1.0
			$b = new $OAuth(WEIBO_APP_KEY, WEIBO_SECRET, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
			$_SESSION['keys'] = '';
			$last_key = $b -> getAccessToken($_REQUEST['oauth_verifier']);
			if (!$last_key['oauth_token']) {
				return var_dump($last_key);
			} 
			$tok1 = $last_key['oauth_token'];
			$tok2 = $last_key['oauth_token_secret'];
			$oauth_token = array('oauth_token' => $tok1, 'oauth_token_secret' => $tok2);
		} elseif (isset($_GET['code'])) { // OAuth 2.0
			if ($_SESSION['wp_url_login'] == "sina") {
				$keys = array();
				class_exists('OAuthV2') or require(dirname(__FILE__) . "/OAuth/OAuthV2.php");
				$o = new OAuthV2(WEIBO_APP_KEY, WEIBO_SECRET);
				$keys['code'] = $_GET['code'];
				$keys['access_token_url'] = 'https://api.weibo.com/oauth2/access_token';
				if (!empty($_SESSION['source_receiver'])) {
					$keys['redirect_uri'] = plugins_url('wp-connect/dl_receiver.php');
					$_SESSION['source_receiver'] = "";
				} else {
					$keys['redirect_uri'] = "http://smyx.sinaapp.com/receiver.php";
				} 
				$token = $o -> getAccessToken($keys);
				if (!$token['access_token']) {
					return var_dump($token);
				} 
				$tok1 = $token['access_token'];
				$tok2 = BJTIMESTAMP + $token['expires_in'];
				$oauth_token = array('access_token' => $tok1, 'expires_in' => $tok2);
			} elseif ($_SESSION['wp_url_login'] == "renren") {
				$config = new stdClass;
				$config -> CALLBACK = $backurl;
				$config -> APIKey = WEIBO_APP_KEY;
				$config -> SecretKey = WEIBO_SECRET;
				class_exists('RenRenOauth') or require(dirname(__FILE__) . '/OAuth/renren.class.php');
				$code = $_GET['code'];
				$oauth = new RenRenOauth();
				$token = $oauth -> getAccessToken($code);
				if (!$token['access_token']) {
					return var_dump($token);
				} 
				$access_token = explode("|", $token['access_token']);
				$session_key = $access_token[1];
				if (!$session_key) {
					$key = $oauth -> getSessionKey($token['access_token']);
					if (!$key['renren_token']['session_key']) {
						return var_dump($key);
					} 
					$session_key = $key['renren_token']['session_key'];
					$expires_in = $key['renren_token']['expires_in'];
					$get_session = true;
				} else {
					$expires_in = $token['expires_in'];
				} 
				// return var_dump($result);
				$oauth_token = array('session_key' => $session_key, 'refresh_token' => $token['refresh_token'], 'expires_in' => BJTIMESTAMP + $expires_in);
			} 
		} 
		if (is_user_logged_in() && $_SESSION['sync_bind']) { // 同步绑定
			$tok = 'wptm_' . $bind;
			if (strpos($redirect_to, admin_url('options-general.php?page=wp-connect')) === 0) { // 插件页面
				update_option($tok, $oauth_token);
			} elseif ($wpuid = get_uid_by_url($redirect_to)) {
				update_usermeta($wpuid, $tok, $oauth_token);
			} 
			$_SESSION['sync_bind'] = "";
			$_SESSION['wp_url_login'] = "";
		} else {
			switch ($_SESSION['wp_url_login']) {
				case "sina": 
					// if (isset($_GET['oauth_token'])) {
					// $to = new sinaClient(WEIBO_APP_KEY, WEIBO_SECRET, $last_key['oauth_token'], $last_key['oauth_token_secret']);
					// $result = $to -> verify_credentials();
					// $only_id = $result['id'];
					// } elseif (isset($_GET['code'])) {
					class_exists('sinaClientV2') or require(dirname(__FILE__) . "/OAuth/sina_OAuthV2.php");
					$to = new sinaClientV2(WEIBO_APP_KEY, WEIBO_SECRET, $token['access_token']);
					$result = $to -> show_user($token['uid']);
					$only_id = $result['idstr']; 
					// }
					// return var_dump($result);
					if ($only_id) {
						$tid = $id = 'stid';
						$username = $result['domain'] ? $result['domain'] : $only_id;
						$email = $only_id . '@weibo.com'; 
						// $old_email = $only_id . '@t.sina.com.cn';
						$name = $at = $result['screen_name'];
						$url = $result['url'] ? $result['url'] : 'http://weibo.com/' . $only_id;
						$head = $only_id;
						$uid = ifab(get_user_by_meta_value($id, $only_id), email_exists($email));
					} 
					break;
				case "renren":
					if ($get_session) {
						$client = new RenRenClient();
						$client -> setSessionKey($session_key);
						$result = $client -> POST('users.getInfo');
						$result = $result[0];
						$only_id = $username = $result['uid'];
						$head = $result['headurl'];
					} else {
						$result = $token['user'];
						$only_id = $username = $result['id'];
						$head = $result['avatar'][0]['url'];
					} 
					if ($only_id) {
						$tid = 'rtid';
						$id = 'renrenid';
						$email = $only_id . '@renren.com';
						$name = $result['name'];
						$url = 'http://www.renren.com/profile.do?id=' . $only_id;
						$uid = ifab(get_user_by_meta_value($id, $only_id), email_exists($email));
						$oauth_token = '';
					} 
					break;
				case "qq":
					$to = new qqClient(WEIBO_APP_KEY, WEIBO_SECRET, $last_key['oauth_token'], $last_key['oauth_token_secret']);
					$result = $to -> verify_credentials();
					$result = $result['data'];
					$only_id = $username = $at = $result['name']; 
					// return var_dump($result);
					if ($only_id) {
						$tid = "qtid";
						$id = 'tqqid'; 
						// $only_id = $last_key['name'];
						$email = $only_id . '@t.qq.com';
						$name = $result['nick'];
						$url = 'http://t.qq.com/' . $only_id;
						$head = $result['head'];
						$uid = ifab(get_user_by_meta_value($id, $only_id), email_exists($email));
					} 
					break;
				case "sohu":
					$to = new sohuClient(WEIBO_APP_KEY, WEIBO_SECRET, $last_key['oauth_token'], $last_key['oauth_token_secret']);
					$result = $to -> verify_credentials();
					$only_id = $username = $result['id']; 
					// return var_dump($result);
					if ($only_id) {
						$tid = "shtid";
						$id = 'sohuid';
						$email = $only_id . '@t.sohu.com';
						$name = $at = $result['screen_name'];
						$url = 'http://t.sohu.com/u/' . $only_id;
						$head = $result['profile_image_url'];
						$uid = ifab(get_user_by_meta_value($id, $only_id), email_exists($email));
					} 
					break;
				case "netease":
					$to = new neteaseClient(WEIBO_APP_KEY, WEIBO_SECRET, $last_key['oauth_token'], $last_key['oauth_token_secret']);
					$result = $to -> verify_credentials();
					$only_id = $username = $result['screen_name']; 
					// return var_dump($result);
					if ($only_id) {
						$tid = "ntid";
						$id = 'neteaseid';
						$email = $result['email'];
						$old_email = $only_id . '@t.163.com';
						$name = $at = $result['name'];
						$url = 'http://t.163.com/' . $only_id;
						$head = $result['profile_image_url'];
						$uid = ifabc(email_exists($email), email_exists($old_email), get_user_by_meta_value($id, $only_id));
					} 
					break;
				case "douban":
					$to = new doubanOAuth(WEIBO_APP_KEY, WEIBO_SECRET, $last_key['oauth_token'], $last_key['oauth_token_secret']);
					$douban = $to -> OAuthRequest('http://api.douban.com/people/%40me', array(), 'GET');
					$douban = simplexml_load_string($douban);
					$douban_xmlns = $douban -> children('http://www.douban.com/xmlns/');
					$only_id = str_replace("http://api.douban.com/people/", "", $douban -> id); 
					// return var_dump($douban);
					if ($only_id) {
						$tid = $id = 'dtid';
						$email = $only_id . '@douban.com';
						$username = $douban_xmlns -> uid;
						$name = $douban -> title;
						$url = "http://www.douban.com/people/" . $username;
						$head = $only_id;
						$uid = ifab(get_user_by_meta_value($id, $only_id), email_exists($email));
					} 
					break;
				case "tianya":
					$to = new tianyaClient(WEIBO_APP_KEY, WEIBO_SECRET, $last_key['oauth_token'], $last_key['oauth_token_secret']);
					$result = $to -> get_user_info();
					$result = $result['user'];
					$only_id = $username = $result['user_id']; 
					// return var_dump($result);
					if ($only_id) {
						$tid = $id = 'tytid';
						$email = $only_id . '@tianya.cn';
						$name = $at = $result['user_name'];
						$url = 'http://my.tianya.cn/' . $only_id;
						$head = $only_id;
						$uid = ifab(get_user_by_meta_value($id, $only_id), email_exists($email));
					} 
					break;
				case "twitter":
					$to = new twitterClient(WEIBO_APP_KEY, WEIBO_SECRET, $last_key['oauth_token'], $last_key['oauth_token_secret']);
					$result = $to -> verify_credentials();
					$result = json_decode($result, true);
					$only_id = $username = $at = $result['screen_name']; 
					// $only_id = $result['id_str'];
					// return var_dump($result);
					if ($only_id) {
						$tid = "ttid";
						$id = 'twitterid';
						$email = $only_id . '@twitter.com';
						$name = $at = $result['name'];
						$url = 'http://twitter.com/' . $only_id;
						$head = $result['profile_image_url'];
						$uid = ifab(get_user_by_meta_value($id, $only_id), email_exists($email));
					} 
					break;
				default:
			} 
			$_SESSION['wp_url_login'] = "";
			if (!$only_id) {
				return var_dump($result);
			} 
			if (!is_user_logged_in()) { // 登录
				$userinfo = array($tid, $username, $name, $head, $url, $only_id, $oauth_token); 
				// return var_dump($userinfo);
				if ($uid) {
					wp_connect_login($userinfo, $email, $uid);
				} else {
					wp_connect_login($userinfo, $email);
				} 
			} else { // 登录绑定
				$wpuid = get_uid_by_url($redirect_to);
				if (!$uid || $uid == $wpuid) {
					update_usermeta($wpuid, $id, $only_id); // bind
					update_usermeta($wpuid, 'login_' . $bind, array($tok1, $tok2, $username, $name)); // 授权信息
					if ($at) {
						$nickname = get_user_meta($wpuid, 'login_name', true);
						$nickname[$bind] = $at;
						update_usermeta($wpuid, 'login_name', $nickname); // @xxx
					} 
				} else {
					$user_login = get_username($uid);
					wp_die("很遗憾！该帐号( $username ) 已被用户名 $user_login 绑定，您可以用该 <a href=\"" . wp_logout_url() . "\">用户名</a> 登录，并到 <a href=\"" . admin_url('profile.php') . "\">我的资料</a> 页面解除绑定，再进行绑定该帐号！<strong>如果不能成功，请删除那个WP帐号，再进行绑定！</strong> <a href='$redirect_to'>返回</a>");
				} 
			} 
		} 
		header('Location:' . $redirect_to);
	} 
} 

?>
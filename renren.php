<?php
/**
 * 此文件自 V1.9.22 起已经作废，已经添加到文件 login.php ，考虑到某些站长直接在网站引用此URL，故暂且保留文件。
 */
include "../../../wp-config.php";
session_start();
$wptm_key = get_option('wptm_key');
$config = new stdClass;
$config -> CALLBACK = plugins_url('wp-connect/renren.php');
$config -> APIKey = $wptm_key[7][0];
$config -> SecretKey = $wptm_key[7][1];
class_exists('RenRenOauth') or require('OAuth/renren.class.php');
$redirect_to = ($_SESSION['wp_url_back']) ? $_SESSION['wp_url_back'] : get_bloginfo('url');
$action = isset($_GET['login']) ? strtolower($_GET['login']) : '';
if ($action == 'renren') {
	$_SESSION['wp_url_login'] = "";
	$_SESSION['bind'] = "";
	$oauth = new RenRenOauth();
	$url = $oauth -> getAuthorizeUrl();
	header("Location: $url");
} else {
	if (isset($_GET['code'])) {
		$code = $_GET['code'];
		$oauth = new RenRenOauth();
		$token = $oauth -> getAccessToken($code);
		$access_token = $token['access_token'];
		$key = $oauth -> getSessionKey($access_token);
		$session_key = $key['renren_token']['session_key']; 
		// return var_dump($session_key);
		if ($session_key) {
			$client = new RenRenClient();
			$client -> setSessionKey($session_key); 
			// 调用api时的第一个参数是api方法名。
			// 第二个参数请参考renren.class.php文件中的配置进行设置。
			$renren = $client -> POST('users.getInfo'); 
			// return var_dump($renren);
			$renren = $renren[0];
			$username = $renren['uid'];
			$name = $renren['name'];
			$head = $renren['headurl'];
			$url = 'http://www.renren.com/profile.do?id=' . $username;
			$email = $username . '@renren.com';
			$tid = "rtid";
			$uid = get_user_by_meta_value('renrenid', $username);
			if (!$uid) $uid = email_exists($email);
			$userinfo = array($tid, $username, $name, $head, $url, $username);
			if ($uid) {
				wp_connect_login($userinfo, $email, $uid);
			} else {
				wp_connect_login($userinfo, $email);
			} 
			header('Location:' . $redirect_to);
		} else {
			wp_die("获取用户授权信息失败，请重新<a href=" . site_url('wp-login.php', 'login') . ">登录</a> 或者 清除浏览器缓存再试!  <a href='$redirect_to'>返回</a>");
		} 
	} else {
		wp_die("获取用户授权信息失败，请重新<a href=" . site_url('wp-login.php', 'login') . ">登录</a> 或者 清除浏览器缓存再试!  <a href='$redirect_to'>返回</a>");
	} 
} 

?>
<?php
include "../../../wp-config.php";
session_start();
$wptm_connect = get_option('wptm_connect');
require_once('OAuth/renren.class.php');
$_SESSION['wp_url_login'] = "";
if ($_GET['login'] == "RENREN") {
	// Authorization Code
	$oauth = new RenRenOauth();
	$url = $oauth -> getAuthorizeUrl();
	header("Location: $url");
} else {
	if ($_SESSION['wp_url_back']) {
		$redirect_to = $_SESSION['wp_url_back'];
	} else {
		$redirect_to = get_bloginfo('url');
	}
	if ($_GET['code']) {
		$code = $_GET['code']; 
		// Access Token
		$oauth = new RenRenOauth();
		$token = $oauth -> getAccessToken($code); 
		// API Session Key
		// $oauth = new RenRenOauth();
		$access_token = $token['access_token'];
		$key = $oauth -> getSessionKey($access_token);
		$session_key = $key['renren_token']['session_key'];
		if ($session_key) {
			// users.getInfo
			$client = new RenRenClient();
			$client -> setSessionKey($session_key); 
			// ����apiʱ�ĵ�һ��������api��������
			// �ڶ���������ο�renren.class.php�ļ��е����ý������á�
			$renren = $client -> POST('users.getInfo'); 
			// var_dump($renren);
			$renren = $renren[0];
			$username = $renren['uid'];
			$name = $renren['name'];
			$head = $renren['tinyurl'];
			$url = 'http://www.renren.com/profile.do?id=' . $username;
			$email = $username . '@renren.com';
			$tid = "rtid";
            $uid = get_user_by_meta_value('renrenid', $username);
			if(!$uid) $uid = email_exists($email);
		    $userinfo = array($tid, $username, $name, $head, $url, $username);
		    if ($uid) {
				wp_connect_login($userinfo, $email, $uid);
		    } else {
				wp_connect_login($userinfo, $email);
		    } 
			header('Location:' . $redirect_to);
		} else {
			wp_die("��ȡ�û���Ȩ��Ϣʧ�ܣ�������<a href=" . site_url('wp-login.php', 'login') . ">��¼</a> ���� ����������������!  <a href='$redirect_to'>����</a>");
		} 
	} else {
		wp_die("��ȡ�û���Ȩ��Ϣʧ�ܣ�������<a href=" . site_url('wp-login.php', 'login') . ">��¼</a> ���� ����������������!  <a href='$redirect_to'>����</a>");
	} 
} 

?>
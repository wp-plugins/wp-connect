<?php
/**
 * ���ļ��� V1.9.22 ���Ѿ����ϣ��Ѿ���ӵ��ļ� login.php �����ǵ�ĳЩվ��ֱ������վ���ô�URL�������ұ����ļ���
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
			// ����apiʱ�ĵ�һ��������api��������
			// �ڶ���������ο�renren.class.php�ļ��е����ý������á�
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
			wp_die("��ȡ�û���Ȩ��Ϣʧ�ܣ�������<a href=" . site_url('wp-login.php', 'login') . ">��¼</a> ���� ����������������!  <a href='$redirect_to'>����</a>");
		} 
	} else {
		wp_die("��ȡ�û���Ȩ��Ϣʧ�ܣ�������<a href=" . site_url('wp-login.php', 'login') . ">��¼</a> ���� ����������������!  <a href='$redirect_to'>����</a>");
	} 
} 

?>
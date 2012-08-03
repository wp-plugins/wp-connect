<?php
include "../../../wp-config.php";
session_start();
if (isset($_GET['go'])) {
	$name = strtolower($_GET['go']);
	if (in_array($name, array('qzone', 'sina', 'tencent', 'renren', 'taobao', 'douban', 'baidu', 'kaixin001', 'sohu', 'netease', 'tianya', 'windowslive', 'alipayquick', 'google', 'yahoo', 'netease163', 'twitter', 'facebook', 'tianyi', 'guard360'))) {
		$_SESSION['wp_url_login'] = $name;
		$wptm_basic = get_option('wptm_basic');
		if (!empty($_SERVER['HTTP_REFERER'])) $redirect_uri = "&redirect_uri=" . urlencode($_SERVER['HTTP_REFERER']);
		$open_url = "http://open.denglu.cc/transfer/" . $name . "?appid=" . $wptm_basic['appid'] . $redirect_uri;
		header('Location:' . $open_url);
	} 
} elseif (is_user_logged_in() && !empty($_SERVER['HTTP_REFERER']) && isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
	if (!$user_ID) $user_ID = get_current_user_id();
	if ($user_id && ($user_ID == $user_id || current_user_can('manage_options'))) {
		if (isset($_GET['bind'])) { // 登录绑定
			$bind = strtolower($_GET['bind']);
			$_SESSION['wp_url_login'] = '';
			$wptm_basic = get_option('wptm_basic');
			$open_url = "http://open.denglu.cc/transfer/" . $bind . "?appid=" . $wptm_basic['appid'] . '&uid=' . $user_id . "&redirect_uri=" . $_SERVER['HTTP_REFERER'];
			header('Location:' . $open_url);
		} elseif (isset($_GET['del'])) { // 解除绑定
			$delete = strtolower($_GET['del']);
			if ($theid = get_theid($delete)) {
				$mid = $theid[0] . 'mid';
				$mediaUID = get_user_meta($user_id, $mid, true);
				if ($mediaUID) {
					set_bind($mediaUID);
					delete_usermeta($user_id, $mid);
				} 
				delete_usermeta($user_id, $theid[1]); //兼容旧版
			} 
			header('Location:' . $_SERVER['HTTP_REFERER']);
		} else {
			wp_die("访问页面出错，请返回！");
		} 
	} else {
		return wp_die(__('You do not have sufficient permissions to access this page.'));
	} 
} else {
	wp_die("访问页面出错，请返回！");
} 

?>
<?php
include "../../../wp-config.php";
if (!is_user_logged_in()) {
	$wptm_basic = get_option('wptm_basic');
	$name = isset($_GET['go']) ? strtolower($_GET['go']) : '';
	$open_url = "http://open.denglu.cc/transfer/" . $name . "?appid=" . $wptm_basic['appid'];
	header('Location:' . $open_url);
} else {
	if ($_SERVER['HTTP_REFERER']) {
		$user_id = isset($_GET['user_id']) ? $_GET['user_id'] :'';
		if ($user_id) {
			if ($_SESSION['user_id'] != $user_id) {
				header('Location:' . $_SERVER['HTTP_REFERER']);
			} 
			$delete = isset($_GET['del']) ? strtolower($_GET['del']) :'';
			if ($delete) { // 解除绑定
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
			} else { // 绑定
				$name = isset($_GET['bind']) ? strtolower($_GET['bind']) : '';
				if ($name) {
					$wptm_basic = get_option('wptm_basic');
					$open_url = "http://open.denglu.cc/transfer/" . $name . "?appid=" . $wptm_basic['appid'] . '&uid=' . $user_id;
					header('Location:' . $open_url);
				} else {
					header('Location:' . $_SERVER['HTTP_REFERER']);
				} 
			} 
		} else {
			header('Location:' . get_bloginfo('url'));
		} 
	} else {
		wp_die("该页面不能直接打开。");
	} 
} 

?>
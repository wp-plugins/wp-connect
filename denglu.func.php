<?php
/**
 * 与灯鹭整合 v2.0
 */
// 通过数字mediaID获得tid
function get_tid($id) {
	$name = array('1' => 'gtid',
		'2' => 'mtid',
		'3' => 'stid',
		'4' => 'qtid',
		'5' => 'shtid',
		'6' => 'ntid',
		'7' => 'rtid',
		'8' => 'ktid',
		'9' => 'dtid',
		'10' => 'sdotid',
		'11' => 'ydtid',
		'12' => 'ytid',
		'13' => 'qqtid',
		'14' => 'dreamtid',
		'15' => 'alitid',
		'16' => 'tbtid',
		'17' => 'tytid',
		'18' => 'alitid',
		'19' => 'bdtid',
		'20' => 'ktid',
		'21' => 'wytid',
		'22' => 'qqtid',
		'23' => 'guard360tid',
		'26' => 'tyitid',
		'27' => 'fbtid',
		'28' => 'ttid'
		);
	return $name[$id];
} 
// 通过平台名称获取微博信息
function get_theid($name, $nunber = '') {
	$o = array('qzone' => array('qq', 'qqid', 13),
		'qq' => array('qq', 'qqid', 13),
		'sina' => array('s', 'stid', 3),
		'tencent' => array('q', 'tqqid', 4),
		'tqq' => array('q', 'tqqid', 4),
		'renren' => array('r', 'renrenid', 7),
		'taobao' => array('tb', 'taobaoid', 16),
		'alipayquick' => array('ali', 'alipayid', 18),
		'douban' => array('d', 'dtid', 9),
		'baidu' => array('bd', 'baiduid', 19),
		'kaixin001' => array('k', 'kaixinid', 8),
		'kaixin' => array('k', 'kaixinid', 8),
		'sohu' => array('sh', 'sohuid', 5),
		'netease' => array('n', 'neteaseid', 6),
		'netease163' => array('n', 'neteaseid', 21),
		'tianya' => array('ty', 'tytid', 17),
		'windowslive' => array('m', 'msnid', 2),
		'msn' => array('m', 'msnid', 2),
		'google' => array('g', 'googleid', 1),
		'yahoo' => array('y', 'yahooid', 12),
		'guard360' => array('guard360', 'guard360id', 23),
		'tianyi' => array('tyi', 'tianyiid', 26),
		'facebook' => array('fb', 'facebookid', 27),
		'twitter' => array('t', 'twitterid', 28)
		);
	if (!$nunber && $nunber !== 0) {
		return $o[$name];
	} else {
		return $o[$name][$nunber];
	} 
}
// 判断插件版本
function this_version() {
	global $wpdb;
	$wptm_basic = get_option('wptm_basic');
	$wptm_options = get_option('wptm_options');
	$wptm_connect = get_option('wptm_connect');
	if ($wptm_basic['denglu'] == 1) {
		$version = 1; //已经安装了最新版
	} elseif ($wptm_basic || $wptm_options || $wptm_connect) {
		if ($wptm_basic['appid'] && $wptm_basic['appkey']) {
			$version = 2;  //wordpress连接微博旧版,需要点击 数据升级
		} else {
			$version = 3;  //wordpress连接微博旧版,需要点击 升级插件
		}
	} elseif ($wpdb->get_var("show tables like 'ecms_denglu_bind_info'") == 'ecms_denglu_bind_info') {
	    $version = 4; //denglu.cc旧版
	} else {
		$version = 5; //全新安装
	}
	return $version;
}
// 是否安装了灯鹭社会化评论
function install_comments() {
	global $wptm_basic, $wptm_comment;
	if ($wptm_comment['enable_comment'] && $wptm_basic['appid'] && $wptm_basic['appkey'])
		return true;
} 
// 是否使用灯鹭的帐号绑定
function use_denglu_bind() {
	global $wptm_connect;
	if (empty($wptm_connect['denglu_bind']) && function_exists('wp_connect_comments')) {
		return false;
	} 
	return true;
} 
// 开放平台KEY,重组
function open_appkey() {
	$keys = get_option('wptm_key');
	$qq = get_option('wptm_openqq');
	$sina = get_option('wptm_opensina');
	if (!$keys) {
		$keys = get_appkey();
	} 
	$keys += array('3' => array(ifab($sina['app_key'], '1624795996'), ifab($sina['secret'], '7ecad0335a50c49a88939149e74ccf81')),
		'4' => array(ifab($qq['app_key'], 'd05d3c9c3d3748b09f231ef6d991d3ac'), ifab($qq['secret'], 'e049e5a4c656a76206e55c91b96805e8')),
		);
	$out = array();
	foreach ($keys as $mediaID => $key) {
		$out[] = array('mediaID' => $mediaID, 'apikey' => $key[0], 'appsecret' => $key[1]);
	} 
	return $out;
}
// 保存设置
function wp_connect_update_denglu() {
	$updated = '<div class="updated"><p><strong>' . __('Settings saved.') . '</strong></p></div>'; 
	if (isset($_POST['basic_options'])) { // 站点设置
		$basic_options = array('appid' => trim($_POST['appid']),
			'appkey' => trim($_POST['appkey']),
			'denglu' => $_POST['denglu']
			);
		update_option("wptm_basic", $basic_options);
		echo $updated;
	} 
	if (isset($_POST['wptm_denglu'])) { // 删除返回的灯鹭帐号、密码
		return delete_option("wptm_denglu");
	} 
	if (isset($_POST['connect_denglu'])) { // 连接denglu.cc，首次安装
		return connect_denglu_first();
	} 
	if (isset($_POST['connect_denglu_update'])) { // 旧的wordpress连接微博插件，升级安装
		return connect_denglu_first_update();
	} 
	if (isset($_POST['update_denglu'])) { // 旧的灯鹭插件升级
		@require(ABSPATH . "denglu/lib/denglu_cache.php");
		if ($denglu_cache) {
			update_option("wptm_basic", array('appid' => $denglu_cache['denglu_appid'], 'appkey' => $denglu_cache['denglu_appkey'], 'denglu' => 1));
		} 
		return update_denglu_old();
	} 
	if (isset($_POST['wptm_data'])) { // 旧的wordpress连接微博插件，数据转换
		return connect_denglu_update();
	} 
	if (isset($_POST['importComment'])) { // 评论导入到灯鹭
		if (function_exists('denglu_importComment')) {
			denglu_importComment();
			echo '<div class="updated"><p><strong>评论导入成功！</strong></p></div>';
		} else {
			echo '<div class="updated"><p><strong>请先开启社会化评论，并填写APP ID和APP Key</strong></p></div>';
		} 
		return;
	} 
	// 评论
	if (isset($_POST['comment_options'])) {
		update_option("wptm_comment", array('enable_comment' => trim($_POST['enable_comment']), 'manual' => trim($_POST['manual']), 'comments_open' => trim($_POST['comments_open']), 'dcToLocal' => trim($_POST['dcToLocal']), 'latest_comments' => trim($_POST['latest_comments']), 'enable_seo' => trim($_POST['enable_seo'])));
		echo $updated;
	} 
}
add_action('save_connent_options', 'wp_connect_update_denglu',5);

// 获取已选择平台供应商
function get_media() {
	global $wptm_basic;
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
	$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
	try {
		$ret = $api -> getMedia();
	} 
	catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
		// wp_die($e->geterrorDescription()); //返回错误信息
	} 
	if (is_array($ret)) {
		return $ret;
	}
}
// 保存已选择登录按钮及顺序
function save_user_denglu_platform() {
	$selected = array_flip(array_filter(array($_POST['qqlogin'], $_POST['sina'], $_POST['qq'], $_POST['renren'], $_POST['taobao'], $_POST['alipay'], $_POST['douban'], $_POST['baidu'], $_POST['kaixin001'], $_POST['sohu'], $_POST['netease'], $_POST['netease163'], $_POST['tianya'], $_POST['guard360'], $_POST['tianyi'], $_POST['msn'], $_POST['google'], $_POST['yahoo'], $_POST['twitter'], $_POST['facebook'])));
	if ($get_media = get_media()) {
		$platform = array();
		foreach($get_media as $media) {
			$platform[$media['mediaNameEn']] = $media['mediaName'];
		} 
	} 
	if (!$platform) {
		$platform = array('qzone' => 'QQ空间',
			'sina' => '新浪微博',
			'tencent' => '腾讯微博',
			'renren' => '人人网',
			'douban' => '豆瓣',
			'taobao' => '淘宝网',
			'alipayquick' => '支付宝',
			'baidu' => '百度',
			'kaixin001' => '开心网',
			'sohu' => '搜狐微博',
			'netease' => '网易微博',
			'netease163' => '网易通行证',
			'tianya' => '天涯微博',
			'guard360' => '360',
			'tianyi' => '天翼189',
			'windowslive' => 'MSN',
			'google' => 'Google',
			'yahoo' => 'Yahoo', 
			'twitter' => 'Twitter',
		    'facebook' => 'Facebook'
			);
	} 
	$denglu_btn = array_intersect_key($platform, $selected);
	update_option("denglu_btn", $denglu_btn);
	return $denglu_btn;
} 
// 获取已选择登录按钮及顺序
function get_user_denglu_platform() {
	$platform = get_option("denglu_btn");
	if (!$platform) {
		if ($get_media = get_media()) {
			foreach($get_media as $media) {
				$platform[$media['mediaNameEn']] = $media['mediaName'];
			}
		    update_option("denglu_btn", $platform);
		}
	} 
	return $platform;
}
// 灯鹭同步 v2.3
function wp_update_share($mediaUserID, $content, $url, $uid = '', $imageurl = '', $videourl = '', $param1 = '', $param2 = '') {
	global $wptm_basic;
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
    $api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
	try {
		return $api -> share( $mediaUserID, $content, $url, $uid, $imageurl, $videourl, $param1, $param2 );
	}
	catch(DengluException $e) {
		wp_die($e->geterrorDescription());
	}
}
// 获取到用户的所有平台账号绑定关系
function get_bindInfo($muid, $uid = '') {
	$wptm_basic = get_option("wptm_basic");
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
	$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
	try {
		$ret = $api -> getBind($muid, $uid);
	} 
	catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
		wp_die($e -> geterrorDescription()); //返回错误信息
	} 
	return $ret;
} 
// 平台账号绑定及解绑
function set_bind($mediaUID, $uid = '', $uname = '', $uemail = '') {
	$wptm_basic = get_option("wptm_basic");
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
	$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
	if ($uid) {
		try {
			$ret = $api -> bind($mediaUID, $uid, $uname, $uemail);
		} 
		catch(DengluException $e) {
		} 
	} else {
		try {
			$ret = $api -> unbind($mediaUID);
		} 
		catch(DengluException $e) {
		} 
	} 
	return $ret;
} 
// 连接denglu.cc，首次安装
function connect_denglu_first() {
	$wptm_basic = get_option("wptm_basic");
	if ($wptm_basic) return;
	$content = array('sitename' => get_bloginfo('name'),
		'siteurl' => get_bloginfo('wpurl') . '/',
		'email' => get_option('admin_email')
		);
	$content = json_encode($content); 
	// return var_dump($content);
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
	$api = new Denglu('', '', 'utf-8');
	try { // 写到denglu.cc服务器(网站名称、网站网址、管理员邮箱)，并返回数据，包括app id、 app key、username、password
		$ret = $api -> register($content);
	} 
	catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
		// return false;
		wp_die($e -> geterrorDescription()); //返回错误信息
	} 
	if ($ret['appid']) {
		update_option("wptm_denglu", array($ret['username'], $ret['password']));
		update_option("wptm_basic", array('appid' => $ret['appid'], 'appkey' => $ret['apikey'], 'denglu' => 1));
	} 
} 
// 旧的灯鹭插件升级
function update_denglu_old() {
	global $wpdb;
	@ini_set("max_execution_time", 120);
	$users = $wpdb -> get_results("select * FROM ecms_denglu_bind_info");
	foreach ($users as $user) {
		$tid = get_tid($user -> mediaID);
		$mid = str_replace('tid', 'mid', $tid);
		update_usermeta($user -> uid, $mid, $user -> mediaUserID);
	} 
} 
// 旧的wordpress连接微博插件，升级安装
function connect_denglu_first_update() {
	$wptm_basic = get_option("wptm_basic");
	if ($wptm_basic) return;
	$content = array('sitename' => get_bloginfo('name'),
		'siteurl' => get_bloginfo('wpurl') . '/',
		'email' => get_option('admin_email'),
		'keys' => open_appkey()
		);
	$content = json_encode($content);
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
	$api = new Denglu('', '', 'utf-8');
	try { // 写到denglu.cc服务器(网站名称、网站网址、管理员邮箱)，并返回数据，包括app id、 app key、username、password
		$ret = $api -> register($content);
	} 
	catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
		wp_die($e -> geterrorDescription()); //返回错误信息
	} 
	if ($ret['appid']) {
		update_option("wptm_denglu", array($ret['username'], $ret['password']));
		update_option("wptm_basic", array('appid' => $ret['appid'], 'appkey' => $ret['apikey']));
		update_option("wptm_key", get_appkey());
	} 
} 
// 旧的wordpress连接微博插件用户数据,只检查新浪微博、腾讯微博、QQ空间、人人网
function connect_olduser() {
	global $wpdb;
	$v = array();
	$users = $wpdb -> get_results("SELECT user_id FROM $wpdb->usermeta WHERE (meta_key = 'stid' OR meta_key = 'tqqid' OR meta_key = 'qqid' OR meta_key = 'renrenid')", ARRAY_A);
	if ($users) {
		foreach ($users as $user) {
			$v[] = $user['user_id'];
		} 
		return array_unique($v);
	} 
} 
// 旧的wordpress连接微博插件数据
function connect_denglu_update_data() {
	global $wpdb;
	$wptm_basic = get_option("wptm_basic");
	if ($wptm_basic['denglu']) return;
	@ini_set("max_execution_time", 300);
	if ($userids = connect_olduser()) {
		foreach ($userids as $uid) {
			$user = get_userdata($uid);
			$ret = array_filter(
				array('3' => ($user -> stid) ? array('mediaID' => '3', 'mediaUID' => $user -> stid, 'profileImageUrl' => 'http://tp2.sinaimg.cn/' . $user -> stid . '/50/0/1', 'oauth_token' => ifac($user -> login_sina[0], $user -> tdata['tid'] == 'stid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_sina[1], $user -> tdata['tid'] == 'stid', $user -> tdata['oauth_token_secret'])) : '',
					'4' => ($user -> qtid) ? array('mediaID' => '4', 'mediaUID' => ifab($user -> tqqid , $user -> user_login), 'profileImageUrl' => $user -> qtid, 'oauth_token' => ifac($user -> login_qq[0], $user -> tdata['tid'] == 'qtid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_qq[1], $user -> tdata['tid'] == 'qtid', $user -> tdata['oauth_token_secret'])) : '',
					'7' => ($user -> rtid) ? array('mediaID' => '7', 'mediaUID' => ifab($user -> renrenid , $user -> user_login), 'profileImageUrl' => $user -> rtid):'',
					'13' => ($user -> qqid) ? array('mediaID' => '13', 'mediaUID' => $user -> qqid, 'profileImageUrl' => $user -> qqtid, 'oauth_token' => $user -> qqid):''
					));
			$result[$uid] = array_values($ret);
			// 更新数据库
			$ids = array('4' => 'tqq', '7' => 'renren');
			foreach ($ids as $mediaID => $name) {
				if ($ret[$mediaID]) {
					update_usermeta($uid, $name . 'id', $ret[$mediaID]['mediaUID']); // 用于SNS URL
				} 
			} 
			// $ids2 = array('3' => 'sina', '4' => 'qq'); // sina,tqq
			// foreach ($ids2 as $mediaID => $name) {
			// if ($ret[$mediaID]) {
			// update_usermeta($uid, 'login_' . $name, array($ret[$mediaID]['oauth_token'], $ret[$mediaID]['oauth_token_secret']));
			// delete_usermeta($uid, 'tdata');
			// }
			// }
		} 
		if (is_array($result)) {
			$content = array_filter($result);
		} 
		return $content;
	} 
} 
// 旧的wordpress连接微博插件，数据转换
function connect_denglu_update() {
	global $wptm_basic;
	@ini_set("max_execution_time", 300);
	$userdata = ($_SESSION['connect_denglu_update_data']) ? $_SESSION['connect_denglu_update_data'] : connect_denglu_update_data();
	// return var_dump($userdata);
	if ($userdata) {
		$content = php_array_slice($userdata, 0, 30, true);
		$_SESSION['connect_denglu_update_data'] = php_array_slice($userdata, 31, null, true);
		// return var_dump($content);
		$content = json_encode($content);
		class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
		$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
		try {
			$output = $api -> importUser($content);
		} 
		catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
			wp_die($e -> geterrorDescription()); //返回错误信息
		} 
		// return var_dump($output);
		// $output = array('1' => array('1' => '13768191' , '3' => '13768192'), '3' => array('15' => '13768193' , '12' => '13768194'), '5' => array('3' => '13768195' , '6' => '13768196'), '7' => array('15' => '13768193' , '12' => '13768198'));
		// $out = array('1' => array('1' => '13768191','3' => '13768193'), '3' => array('13' => '13768194'),'7' => array('17' => '13768198'));
		if ($output) {
			$mid = array('3' => 'smid', '4' => 'qmid', '7' => 'rmid', '13' => 'qqmid');
			foreach ($output as $userid => $mediaUser) {
				foreach ($mediaUser as $mediaID => $mediaUserID) {
					update_usermeta($userid, $mid[$mediaID], $mediaUserID);
					// $results[] = array($userid, $mid[$mediaID], $mediaUserID);
				} 
			} 
			// return var_dump($results);
			if ($_SESSION['connect_denglu_update_data']) {
				connect_denglu_update();
			} else {
				$wptm_basic['denglu'] = 1;
		        update_option("wptm_basic", $wptm_basic);
			}
		} 
	} 
} 

/**
 * 登录整合 v2.0
 */
// 获取用户信息
function denglu_userInfo() {
	global $wptm_basic;
	if (!$wptm_basic['appid'] || !$wptm_basic['appkey']) {
		wp_die("出错了，请先在插件页的 “基本设置” 页面填写 站点设置 必需的APP ID和 APP Key");
	} 
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
	$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
	if (!empty($_GET['token'])) {
		try {
			$user = $api -> getUserInfoByToken($_GET['token']);
		} 
		catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
			wp_die($e -> geterrorDescription()); //返回错误信息
		} 
	} 
	return $user;
} 
// 登录初始化
function connect_denglu() {
	$user = denglu_userInfo(); 
	// return var_dump($user);
	if ($username = $user['mediaUserID']) {
		if (is_user_logged_in() && in_array($_SESSION['wp_url_login'], array('sina', 'tencent', 'sohu', 'netease', 'renren', 'tianya')) && ($redirect_to = $_SESSION['wp_url_bind'])) { // V2.3，同步帐号绑定
			if ($_SESSION['wp_url_login'] == 'tencent') {
				$tok = 'wptm_qq';
			} else {
				$tok = 'wptm_' . $_SESSION['wp_url_login'];
			} 
			if ($redirect_to == WP_CONNECT) {
				update_option($tok, array('mediaUserID' => $username));
			} elseif ($_SESSION['user_id']) {
				update_usermeta($_SESSION['user_id'], $tok, array('mediaUserID' => $username));
			} 
			$_SESSION['wp_url_login'] = '';
			header('Location:' . $redirect_to);
			return;
		} 
		$homepage = $user['homepage'];
		$mediaID = $user['mediaID'];
		$tid = get_tid($mediaID);
		$weibo = get_weibo($tid);
		$mid = str_replace('tid', 'mid', $tid);
		if ($homepage) {
			$id = $weibo[1] . 'id';
			$userid = str_replace($weibo[3], '', $homepage);
		} elseif ($tid == 'qqtid') {
			$id = $weibo[1] . 'id';
			$path = explode('/', $user['profileImageUrl']);
			$userid = $path[5];
		} else {
			$id = $mid;
			$userid = $username;
		} 
		if ($user['email']) {
			$email = $user['email'];
			if ($id == $mid) { // taobao, MSN
				$uid = ifab(email_exists($email), get_user_by_meta_value($id, $userid));
			} else { // netease
				$uid = ifabc(email_exists($email), get_user_by_meta_value($id, $userid), get_user_by_meta_value($mid, $username));
			} 
		} else {
			$domain = ifab($weibo[4], 'denglu.cc');
			if ($homepage) {
				$email = $userid . '@' . $domain;
			} else {
				$email = $username . '@' . $domain;
			} 
			if ($id == $mid) {
				$uid = get_user_by_meta_value($id, $userid);
			} else {
				$uid = ifabc(get_user_by_meta_value($id, $userid), email_exists($email), get_user_by_meta_value($mid, $username));
			} 
		} 
		if (is_user_logged_in()) { // V2.1，登录绑定
			if (($wpuid = $_SESSION['user_id']) && ($redirect_to = $_SESSION['wp_url_bind'])) {
				if ($uid) {
					$userinfo = wp_get_user_info($uid);
					$user_login = $userinfo['user_login'];
					wp_die("很遗憾！该帐号已被用户名 $user_login 绑定，您可以用该 <a href=\"" . wp_logout_url() . "\">用户名</a> 登录，并到 <a href=\"" . admin_url('profile.php') . "\">我的资料</a> 页面解除绑定，再进行绑定该帐号！<strong>如果不能成功，请删除那个WP帐号，再进行绑定！</strong> <a href='$redirect_to'>返回</a>");
				} else {
					update_usermeta($wpuid, $mid, $username);
					if ($homepage || $tid == 'qqtid') { // sina,tqq,sohu,netease,renren,kaixin,douban,qq,tianya
						update_usermeta($wpuid, $weibo[1] . 'id', $userid);
						if ($tid == 'qqtid') {
							update_usermeta($wpuid, $tid, $user['profileImageUrl']);
						} 
						if (in_array($tid, array('qtid', 'stid', 'ntid', 'shtid'))) { //  微博帐号
							$nickname = get_user_meta($wpuid, 'login_name', true);
							$nickname[$weibo[0]] = ($tid == 'qtid') ? $userid : $user['screenName'];
							update_usermeta($wpuid, 'login_name', $nickname);
						} 
					} 
				} 
			} 
		} else {
			$url = ifab($user['url'], $homepage);
			$userinfo = array($tid, $username, $user['screenName'], $user['profileImageUrl'], $url, $userid, $username); //tid,username,nick,head,url,userid,mediaUserID
			if ($uid) {
				wp_connect_login($userinfo, $email, $uid);
			} else {
				wp_connect_login($userinfo, $email);
			} 
		} 
	} 
} 

/**
 * 绑定登录帐号 v2.1
 */
// 获取已绑定帐号
function denglu_bind_account($user) {
	$account = array('qzone' => array(ifab($user -> qqid, $user -> qqmid), '腾讯QQ'),
		'sina' => array(ifab($user -> stid, $user -> smid), '新浪微博'),
		'tencent' => array(ifab($user -> tqqid, $user -> qmid), '腾讯微博'),
		'renren' => array(ifab($user -> renrenid, $user -> rmid), '人人网'),
		'taobao' => array($user -> tbmid, '淘宝网'),
		'alipayquick' => array($user -> alimid, '支付宝'),
		'douban' => array(ifab($user -> dtid, $user -> dmid), '豆瓣'),
		'baidu' => array($user -> bdmid, '百度'),
		'kaixin001' => array(ifab($user -> kaixinid , $user -> kmid), '开心网'),
		'sohu' => array(ifab($user -> sohuid, $user -> shmid), '搜狐微博'),
		'netease' => array(ifab($user -> neteaseid, $user -> nmid), '网易微博'),
		'netease163' => array($user -> wymid, '网易通行证'),
		'tianya' => array(ifab($user -> tytid, $user -> tymid), '天涯微博'),
		'guard360' => array($user -> guard360mid, '360'),
		'tianyi' => array($user -> tyimid, '天翼189'),
		'windowslive' => array($user -> mmid, 'MSN'),
		'google' => array($user -> gmid, 'Google'),
		'yahoo' => array($user -> ymid, 'Yahoo'),
		'twitter' => array(ifab($user -> twitterid, $user -> twittermid), 'Twitter'),
		'facebook' => array($user -> facebookmid, 'Facebook')
		);
	if ($platform = get_user_denglu_platform()) { // V2.3
		return array_intersect_key($account, $platform);
	} 
	return $account;
} 
// 绑定UI
function denglu_bindInfo($user) {
	global $plugin_url;
	$user_id = $user -> ID;
	$_SESSION['user_id'] = $user_id;
	$_SESSION['wp_url_bind'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	$url = $plugin_url . '/login.php?user_id=' . $user_id;
	$account = denglu_bind_account($user);
	$binds = array_filter($account, filter_value) + $account;
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$plugin_url/css/style.css\" />";
	echo "<tr><th>绑定帐号</th><td><span id=\"login_bind\">";
	foreach($binds as $key => $vaule) {
		if ($vaule[0]) {
			echo "<a href=\"{$url}&del={$key}\" title=\"$vaule[1] (已绑定)\" class=\"btn_{$key} bind\" onclick=\"return confirm('Are you sure? ')\"><b></b></a>\r\n";
		} else {
			echo "<a href=\"{$url}&bind={$key}\" title=\"$vaule[1]\" class=\"btn_{$key}\"></a>\r\n";
		} 
	} 
	echo "</span><p>( 说明：绑定后，您可以使用用户名或者用合作网站帐号登录本站。)</p></td></tr>";
} 
// 删除用户绑定
if (!use_denglu_bind()) {
	function delete_denglu_user_bind($user_id, $name) {
		if ($theid = get_theid($name)) {
			$mid = $theid[0] . 'mid';
			$mediaUID = get_user_meta($user_id, $mid, true);
			if ($mediaUID) {
				set_bind($mediaUID);
				delete_usermeta($user_id, $mid);
			} 
		} 
	} 
	add_action('delete_user_bind', 'delete_denglu_user_bind', 3, 2);
}

/**
 * 评论函数 v2.3
 */
if (!function_exists('dengluComments') && install_comments()) {
	function dengluComments() {
		global $post;
	    $_SESSION['wp_url_bind'] = '';
	    $_SESSION['wp_url_back'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	    $wptm_basic = get_option('wptm_basic');
	    $wptm_comment = get_option('wptm_comment');
	    if (empty($wptm_comment['comments_open']) || (!empty($wptm_comment['comments_open']) && comments_open())) {
			$wptm_connect = get_option('wptm_connect');
			if (is_object($post)) {
				$media_url = wp_multi_media_url($post -> post_content, $post -> ID);
			} 
?>
<script type='text/javascript' charset='utf-8' src='http://open.denglu.cc/connect/commentcode?appid=<?php echo $wptm_basic['appid'];?>&v=1.0.1'></script>
<script type="text/javascript" charset='utf-8'>
    var param = {};
    param.title = "<?php echo urlencode(get_the_title());?>"; // 文章标题
    param.postid = "<?php the_ID();?>"; // 文章ID
<?php
	if ($media_url) { // 是否有视频、图片
    echo "param.image = \"" . $media_url[0] ."\";\n"; // 需要同步的图片地址
    echo "param.video = \"" . $media_url[1] ."\";\n"; // 需要同步的视频地址，支持土豆优酷等
    }
	if ($wptm_connect['enable_connect']) { // 是否开启了社会化登录
	echo (!is_user_logged_in()) ? "param.login = false;\n":"param.login = true;\n"; // 是否已经登录
	echo "param.exit = \"".urlencode(wp_logout_url(get_permalink()))."\";\n"; // 退出链接
}?>
    _dl_comment_widget.show(param);
</script>
<?php
	} 
	// 搜索引擎爬虫
		if ($wptm_comment['enable_seo'] && preg_match("/(Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla)/i", $_SERVER['HTTP_USER_AGENT']) && have_comments()) { ?>
<div id="dengluComments">
	<h3 id="comments"><?php	printf( '《%2$s》有 %1$s 条评论', number_format_i18n( get_comments_number() ), '<em>' . get_the_title() . '</em>' );?></h3>
	<div class="navigation">
		<div class="alignleft"><?php previous_comments_link() ?></div>
		<div class="alignright"><?php next_comments_link() ?></div>
	</div>
	<ol class="commentlist">
	<?php wp_list_comments();?>
	</ol>
	<div class="navigation">
		<div class="alignleft"><?php previous_comments_link() ?></div>
		<div class="alignright"><?php next_comments_link() ?></div>
	</div>
</div>
<script type="text/javascript">
    document.getElementById('dengluComments').style.display="none";
</script>
<?php }}}

/**
 * 评论导入 v2.1.2
 */
if (!function_exists('denglu_importComment') && install_comments()) {
	// 通过Userid或者email获取tid
	function get_usertid($email, $user_id = '') {
		if ($last_login = get_user_meta($user_id, 'last_login', true)) {
			return $last_login;
		} 
		$mail = strstr($email, '@');
		if ($mail == '@t.sina.com.cn') {
			return 'stid';
		} elseif ($mail == '@t.qq.com') {
			return 'qtid';
		} elseif ($mail == '@t.sohu.com') {
			return 'shtid';
		} elseif ($mail == '@t.163.com') {
			return 'ntid';
		} elseif ($mail == '@renren.com') {
			return 'rtid';
		} elseif ($mail == '@kaixin001.com') {
			return 'ktid';
		} elseif ($mail == '@douban.com') {
			return 'dtid';
		} elseif ($mail == '@tianya.cn') {
			return 'tytid';
		} elseif ($mail == '@baidu.com') {
			return 'bdtid';
		} elseif ($mail == '@twitter.com') {
			return 'ttid';
		} elseif (get_user_meta($user_id, 'qqtid', true)) {
			return 'qqtid';
		} elseif (get_user_meta($user_id, 'tbtid', true)) {
			return 'tbtid';
		}
	} 

	function get_row_userinfo($uid, $tid) {
		$user = get_userdata($uid);
		if ($tid == 'gtid') {
			return ($user -> gmid) ? array('mediaUserID' => $user -> gmid) : '';
		} elseif ($tid == 'mtid') {
			return ($user -> mmid) ? array('mediaUserID' => $user -> mmid) : (($user -> msnid) ? array('mediaID' => '2', 'mediaUID' => $user -> msnid, 'email' => $user -> user_email) : '');
		} elseif ($tid == 'stid') {
			return ($user -> smid) ? array('mediaUserID' => $user -> smid) : (($user -> stid) ? array('mediaID' => '3', 'mediaUID' => $user -> stid, 'profileImageUrl' => 'http://tp2.sinaimg.cn/' . $user -> stid . '/50/0/1', 'oauth_token' => ifac($user -> login_sina[0], $user -> tdata['tid'] == 'stid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_sina[1], $user -> tdata['tid'] == 'stid', $user -> tdata['oauth_token_secret'])) : '');
		} elseif ($tid == 'qtid') {
			return ($user -> qmid) ? array('mediaUserID' => $user -> qmid) : (($user -> qtid) ? array('mediaID' => '4', 'mediaUID' => ifab($user -> tqqid , $user -> user_login), 'profileImageUrl' => $user -> qtid, 'oauth_token' => ifac($user -> login_qq[0], $user -> tdata['tid'] == 'qtid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_qq[1], $user -> tdata['tid'] == 'qtid', $user -> tdata['oauth_token_secret'])) : '');
		} elseif ($tid == 'shtid') {
			return ($user -> shmid) ? array('mediaUserID' => $user -> shmid) : (($user -> shtid) ? array('mediaID' => '5', 'mediaUID' => ifab($user -> sohuid , $user -> user_login), 'profileImageUrl' => $user -> shtid, 'oauth_token' => ifac($user -> login_sohu[0], $user -> tdata['tid'] == 'shtid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_sohu[1], $user -> tdata['tid'] == 'shtid', $user -> tdata['oauth_token_secret'])) : '');
		} elseif ($tid == 'ntid') {
			return ($user -> nmid) ? array('mediaUserID' => $user -> nmid) : ((is_numeric($user -> neteaseid) && $user -> neteaseid < 0) ? array('mediaID' => '6', 'mediaUID' => $user -> neteaseid, 'profileImageUrl' => $user -> ntid, 'oauth_token' => ifac($user -> login_netease[0], $user -> tdata['tid'] == 'ntid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_netease[1], $user -> tdata['tid'] == 'ntid', $user -> tdata['oauth_token_secret'])) : '');
		} elseif ($tid == 'rtid') {
			return ($user -> rmid) ? array('mediaUserID' => $user -> rmid) : (($user -> rtid) ? array('mediaID' => '7', 'mediaUID' => ifab($user -> renrenid , $user -> user_login), 'profileImageUrl' => $user -> rtid):'');
		} elseif ($tid == 'ktid') {
			return ($user -> kmid) ? array('mediaUserID' => $user -> kmid) : (($user -> ktid) ? array('mediaID' => '8', 'mediaUID' => ifab($user -> kaxinid , $user -> user_login), 'profileImageUrl' => $user -> ktid):'');
		} elseif ($tid == 'dtid') {
			return ($user -> dmid) ? array('mediaUserID' => $user -> dmid) : (($user -> dtid) ? array('mediaID' => '9', 'mediaUID' => $user -> dtid, 'profileImageUrl' => 'http://t.douban.com/icon/u' . $user -> dtid . '-1.jpg', 'oauth_token' => ifac($user -> login_douban[0], $user -> tdata['tid'] == 'dtid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_douban[1], $user -> tdata['tid'] == 'dtid', $user -> tdata['oauth_token_secret'])) : '');
		} elseif ($tid == 'ytid') {
			return ($user -> ymid) ? array('mediaUserID' => $user -> ymid) : '';
		} elseif ($tid == 'qqtid') {
			return ($user -> qqmid) ? array('mediaUserID' => $user -> qqmid) : (($user -> qqid) ? array('mediaID' => '13', 'mediaUID' => $user -> qqid, 'profileImageUrl' => $user -> qqtid, 'oauth_token' => $user -> qqid):'');
		} elseif ($tid == 'tbtid') {
			return ($user -> tbmid) ? array('mediaUserID' => $user -> tbmid) : (($user -> tbtid && is_numeric($user -> user_login)) ? array('mediaID' => '16', 'mediaUID' => $user -> user_login, 'email' => $user -> user_email, 'profileImageUrl' => $user -> tbtid):'');
		} elseif ($tid == 'tytid') {
			return ($user -> tymid) ? array('mediaUserID' => $user -> tymid) : (($user -> tytid) ? array('mediaID' => '17', 'mediaUID' => $user -> tytid, 'profileImageUrl' => 'http://tx.tianyaui.com/logo/small/' . $user -> tytid, 'oauth_token' => $user -> login_tianya[0], 'oauth_token_secret' => $user -> login_tianya[1]):'');
		} elseif ($tid == 'alitid') {
			return ($user -> alimid) ? array('mediaUserID' => $user -> alimid) : '';
		} elseif ($tid == 'bdtid') {
			return ($user -> bdmid) ? array('mediaUserID' => $user -> bdmid) : (($user -> bdtid) ? array('mediaID' => '19', 'mediaUID' => ifab($user -> baiduid , $user -> user_login), 'profileImageUrl' => 'http://himg.bdimg.com/sys/portraitn/item/' . $user -> bdtid . '.jpg'):'');
		} elseif ($tid == 'wytid') { // 网易通行证
			return ($user -> wymid) ? array('mediaUserID' => $user -> wymid) : '';
		} elseif ($tid == 'guard360tid') { // 360
			return ($user -> guard360mid) ? array('mediaUserID' => $user -> guard360mid) : '';
		} elseif ($tid == 'tyitid') { // 天翼
			return ($user -> tyimid) ? array('mediaUserID' => $user -> tyimid) : '';
		} elseif ($tid == 'fbtid') { // Facebook
			return ($user -> fbmid) ? array('mediaUserID' => $user -> fbmid) : '';
		} elseif ($tid == 'ttid') { // twitter
			return ($user -> tmid) ? array('mediaUserID' => $user -> tmid) : '';
		} 
	} 
	// 回复
	function get_childrenComments($comment_id) {
		global $wpdb;
		$comments = $wpdb -> get_results("SELECT comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_content, user_id FROM $wpdb->comments WHERE comment_parent = $comment_id AND comment_approved=1 AND comment_agent not like '%Denglu%'", "ARRAY_A");
		$ret = array();
		if ($comments) {
			foreach($comments as $comment) {
				if ($comment['user_id']) {
					if ($tid = get_usertid($comment['comment_author_email'], $comment['user_id'])) {
						$user = get_row_userinfo($comment['user_id'], $tid);
						if (is_array($user)) {
							$comment = array_merge($user, $comment);
						} 
					} 
				} 
				$ret[] = $comment;
			} 
		} 
		return $ret;
	} 
	function get_descendantComments($comment_id) {
		$ret = array();
		$children = get_childrenComments($comment_id);
		foreach ($children as $child) {
			$grand_children = get_descendantComments($child['comment_ID']);
			$ret = array_merge($grand_children, $ret);
		} 
		$ret = array_merge($ret, $children);
		return $ret;
	} 
	// 评论，包括回复
	function import_comments_to_denglu() {
		global $wpdb;
		$comments = $wpdb -> get_results("SELECT comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_content, user_id FROM $wpdb->comments WHERE comment_parent = 0 AND comment_approved=1 AND comment_agent not like '%Denglu%' LIMIT 10", "ARRAY_A");
		foreach($comments as $comment) {
			if ($comment['user_id']) {
				if ($tid = get_usertid($comment['comment_author_email'], $comment['user_id'])) {
					$user = get_row_userinfo($comment['user_id'], $tid);
					if (is_array($user)) {
						$comment = array_merge($user, $comment);
					} 
				} 
			} 
			$result[] = array_merge($comment, array('comment_post_url' => get_permalink($comment['comment_post_ID']), 'children' => get_descendantComments($comment['comment_ID'])));
		} 
		return $result;
	} 

	function wp_update_comment_agent($comment_ID) {
		global $wpdb;
		$comments = $wpdb -> get_row("SELECT comment_agent FROM $wpdb->comments WHERE comment_ID = {$comment_ID} AND comment_agent not like '%Denglu%'", ARRAY_A);
		if ($comments) {
			$result = wp_update_comment_key($comment_ID, 'comment_agent', trim($comments['comment_agent'] . ' Denglu'));
			return $result;
		} 
	} 
	// 导入评论
	function denglu_importComment() {
		@ini_set("max_execution_time", 300);
		$data = import_comments_to_denglu();
		// return var_dump($data);
		if ($data) {
			$wptm_basic = get_option('wptm_basic');
			$data = json_encode($data);
			class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
			$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
			try {
				$comments = $api -> importComment($data);
			} 
			catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
				// return false;
				wp_die($e -> geterrorDescription()); //返回错误信息
			} 
			// return var_dump($comments);
			if (is_array($comments)) {
				foreach ($comments as $comment) {
					if ($comment['id']) wp_update_comment_agent($comment['comment_ID']);
					if (is_array($comment['children'])) {
						foreach ($comment['children'] as $children) {
							if ($children['id']) wp_update_comment_agent($children['comment_ID']);
						} 
					} 
				} 
				denglu_importComment();
			} 
		} 
	} 
} 

/**
 * 最新评论 v2.3
 */
if (!function_exists('denglu_recent_comments') && install_comments()) {
	// 获取最新评论
	function get_denglu_recent_comments($count = '') {
		$recentComments = get_option('denglu_recentComments');
		if ($recentComments['comments'] && time() - $recentComments['time'] > 300) {
			return $recentComments;
		} 
		global $wptm_basic;
		class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
		$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
		try {
			$output = $api -> latestComment($count);
		} 
		catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
			// wp_die($e -> geterrorDescription()); //返回错误信息
		} 
		if ($output && is_array($output)) {
			update_option('denglu_recentComments', array('comments' => $output, 'time' => time()));
			return array('comments' => $output);
		} elseif ($recentComments['comments']) {
			$recentComments['time'] = time() + 200;
			update_option('denglu_recentComments', $recentComments);
			return $recentComments;
		} 
	} 
	// 设置cookies
	function denglu_recent_comments_cookie() {
		global $wptm_comment;
		if (!is_admin()) {
			if ($wptm_comment['latest_comments'] && used_widget('wp-connect-comment-widget') && !$_COOKIE["denglu_recent_comments"]) {
				if ($comments = get_denglu_recent_comments()) {
					setcookie("denglu_recent_comments", json_encode($comments['comments']), time() + 300); //缓存5分钟
				} 
			} 
		} 
	} 
	add_action('init', 'denglu_recent_comments_cookie', 0);
	function denglu_recent_comments($comments) {
		if (is_array($comments)) {
			echo '<ul id="denglu_recentcomments">';
			foreach($comments as $comment) {
				echo "<li>" . $comment['name'] . ": <a href=\"{$comment['url']}\">" . $comment['content'] . "...</a></li>";
			} 
			echo '</ul>';
		} 
	} 

	if (!function_exists('used_widget')) {
		function used_widget($widget) {
			$vaule = get_option('widget_' . $widget);
			if (is_array($vaule) && count($vaule) > 1) {
				return true;
			} 
		} 
	} 

	if ($wptm_comment['latest_comments']) {
		include_once(dirname(__FILE__) . '/comments-widgets.php'); // 最新评论 小工具
	} 
} 

/**
 * 1.评论保存到本地服务器
 * 2.评论状态同步到本地服务器。
 * V2.3
 */
if (!function_exists('dcToLocal') && install_comments()) {
	function get_weiboInfo($name) {
		$o = array('1' => array('g'),
			'2' => array('m'),
			'3' => array('s', 'stid', '@weibo.com', 'http://weibo.com/'),
			'4' => array('q', 'tqqid', '@t.qq.com', 'http://t.qq.com/'),
			'5' => array('sh', 'sohuid', '@t.sohu.com', 'http://t.sohu.com/u/'),
			'6' => array('n', 'neteaseid', '@t.163.com', 'http://t.163.com/'),
			'7' => array('r', 'renrenid', '@renren.com', 'http://www.renren.com/profile.do?id='),
			'8' => array('k', 'kaixinid', '@kaixin001.com', 'http://www.kaixin001.com/home/?uid='),
			'9' => array('d', 'dtid', '@douban.com', 'http://www.douban.com/people/'),
			'12' => array('y'),
			'13' => array('qq'),
			'15' => array('ali'),
			'16' => array('tb'),
			'17' => array('ty', 'tytid', '@tianya.cn', 'http://my.tianya.cn/'),
			'19' => array('bd'),
			'21' => array('wy'),
			'23' => array('guard360'),
			'26' => array('tyi'),
			'27' => array('fb'),
			'28' => array('t', 'ttid', '@twitter.com', 'http://twitter.com/'),
			);
		return $o[$name];
	} 
	// 获取评论列表
	function get_comments_by_denglu($cid) {
		global $wptm_basic;
		class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
		$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
		try {
			$ret = $api -> getComments($cid);
		} 
		catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
			// wp_die($e->geterrorDescription()); //返回错误信息
		} 
		if (is_array($ret)) {
			return $ret;
		} 
	} 
	// 评论状态
	function dcState($state) {
		$s = array('1', '0', 'spam', 'trash');
		return $s[$state];
	} 
	// 通过灯鹭cid得到WordPress评论ID
	function get_commentID($cid) {
		global $wpdb;
		return $wpdb -> get_var("SELECT comment_ID FROM $wpdb->comments WHERE comment_agent = 'Denglu_{$cid}' LIMIT 1");
	} 
	// 保存单条评论
	function save_dengluComment($comment, $parent = 0) {
		global $wpdb;
		$cid = $comment['cid'];
		if ($ret = get_commentID($cid))
			return $ret;
		$weiboinfo = get_weiboInfo($comment['mediaID']);
		$mid = $weiboinfo[0] . 'mid';
		$id = $weiboinfo[1];
		if (empty($comment['email'])) {
			if (in_array($comment['mediaID'], array(3, 4, 5, 6, 7, 8, 9, 17)) && $comment['url']) {
				$weibo_uid = str_replace($weiboinfo[3], '', $comment['url']);
				$user_id = get_user_by_meta_value($id, $weibo_uid);
			} 
			$email = ($weiboinfo[2]) ? $weibo_uid . $weiboinfo[2] : $comment['uid'] . '@denglu.cc';
		} else {
			$email = $comment['email'];
		} 
		if (!$user_id) {
			$user_id = get_user_by_meta_value($mid, $comment['uid']);
		}
		$commentdata = array('comment_post_ID' => $comment['postid'],
			'comment_author' => $comment['nick'],
			'comment_author_email' => $email,
			'comment_author_url' => $comment['url'],
			'comment_content' => $comment['content'],
			'comment_type' => '',
			'comment_parent' => $parent,
			'user_id' => ($user_id) ? $user_id : 0,
			'comment_author_IP' => $comment['ip'],
			'comment_agent' => 'Denglu_' . $cid,
			'comment_date' => $comment['date'],
			'comment_approved' => dcState($comment['state']),
			);
		$commentID = get_commentID($cid);
		if (!$commentID) {
			$commentID = wp_insert_comment($commentdata);
		}
		return $commentID;
	} 
	// 保存评论，包括父级评论
	function save_dengluComments($children, $comment) {
		if ($comment) {
			$comment_ID = save_dengluComment($comment); //父级
		} 
		$children_ID = save_dengluComment($children, $comment_ID);
	} 
	// 保存所有评论
	function save_dcToLocal($denglu_last_id) {
		$cid = $denglu_last_id['cid'];
		$comments = get_comments_by_denglu($cid);
		if ($comments) {
			$number = count($comments) - 1;
			$last_cid = $comments[$number]['commentID'];
			update_option('denglu_last_id', array('cid' => $last_cid, 'time' => time()));
			$state = array();
			foreach ($comments as $comment) {
				save_dengluComments(array('postid' => $comment['postid'], 'mediaID' => $comment['mediaID'], 'uid' => $comment['mediaUserID'], 'nick' => $comment['userName'], 'email' => $comment['userEmail'], 'url' => $comment['homepage'], 'cid' => $comment['commentID'], 'content' => $comment['content'], 'state' => $comment['state'], 'ip' => $comment['ip'], 'date' => $comment['createTime']), ($c = $comment['parent']) ? array('postid' => $c['postid'], 'mediaID' => $c['mediaID'], 'uid' => $c['mediaUserID'], 'nick' => $c['userName'], 'email' => $c['userEmail'], 'url' => $c['homepage'], 'cid' => $c['commentID'], 'content' => $c['content'], 'state' => $c['state'], 'ip' => $c['ip'], 'date' => $c['createTime']):'');
			} 
			save_dcToLocal(array('cid' => $last_cid));
		} else {
			$denglu_last_id['time'] = time();
			update_option('denglu_last_id', $denglu_last_id);
		} 
	} 
	// 评论状态与本地对接
	function dc_setCommentsStatus($cid, $status) {
		switch ($status) {
			case "0":
				wp_set_comment_status($cid, 'approve'); //以获准
				break;
			case "1":
				wp_set_comment_status($cid, 'hold'); //待审
				break;
			case "2":
				wp_set_comment_status($cid, 'spam'); //垃圾评论
				break;
			case "3":
				wp_set_comment_status($cid, 'trash'); //回收站
				break;
			case "4":
				wp_delete_comment($cid); //永久删除
				break;
			default:
		} 
	} 
	// 获取评论状态
	function get_commentState_by_denglu($time) {
		global $wptm_basic;
		class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
		$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
		try {
			$ret = $api -> getCommentState($time);
		} 
		catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
			// wp_die($e->geterrorDescription()); //返回错误信息
		} 
		if (is_array($ret)) {
			return $ret;
		} 
	} 
	// 保存评论状态
	function save_dcStateToLocal($comments = '', $time = '') {
		global $wpdb;
		if ($time) {
			$time = (int) ($time / 3600 + 1); // 转为小时，并延长一小时
		} 
		$commentState = array();
		$commentState = get_commentState_by_denglu($time);
		if ($commentState) {
			if ($comments) { // 首次不必更新状态
				$comment_diff = array_diff_assoc($commentState, $comments);
				if ($comment_diff) {
					foreach ($comment_diff as $cid => $state) {
						$ret = $wpdb -> get_row("SELECT comment_ID FROM $wpdb->comments WHERE comment_agent = 'Denglu_{$cid}' LIMIT 1", ARRAY_A);
						if ($ret['comment_ID']) {
							dc_setCommentsStatus($ret['comment_ID'], $state);
						} 
					} 
				} 
			} 
			update_option('denglu_commentState', $commentState);
		} 
	} 
	// 触发动作
	function dcToLocal() {
		global $wptm_comment;
		$denglu_last_id = get_option('denglu_last_id'); //读取数据库
		$denglu_commentState = get_option('denglu_commentState'); //读取数据库
		if (!$denglu_last_id['time'] || time() - $denglu_last_id['time'] > 300) { // 5min
			save_dcToLocal($denglu_last_id); // 同步评论到本地服务器
			save_dcStateToLocal($denglu_commentState, $denglu_last_id['time']); // 同步评论状态到本地服务器
		} 
	} 
	if (default_values('dcToLocal', 1, $wptm_comment)) {
		add_action('init', 'dcToLocal');
	} 
} 

?>
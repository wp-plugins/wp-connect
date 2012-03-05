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
		'22' => 'qqtid'
		);
	return $name[$id];
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
	if (is_array($ret))
		return $ret;
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
// 旧的wordpress连接微博插件数据
function connect_denglu_update_data() {
	global $wpdb;
	$wptm_basic = get_option("wptm_basic");
	if ($wptm_basic['denglu']) return;
	@ini_set("max_execution_time", 300);
	$userids = $wpdb -> get_col($wpdb -> prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY ID ASC"));
	foreach ($userids as $uid) {
		$user = get_userdata($uid);
		$ret = array_filter(array('2' => ($user -> mmid) ? '' : (($user -> msnid) ? array('mediaID' => '2', 'mediaUID' => $user -> msnid, 'email' => $user -> user_email) : ''),

				'3' => ($user -> smid) ? '' : (($user -> stid) ? array('mediaID' => '3', 'mediaUID' => $user -> stid, 'profileImageUrl' => 'http://tp2.sinaimg.cn/' . $user -> stid . '/50/0/1', 'oauth_token' => ifac($user -> login_sina[0], $user -> tdata['tid'] == 'stid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_sina[1], $user -> tdata['tid'] == 'stid', $user -> tdata['oauth_token_secret'])) : ''),

				'4' => ($user -> qmid) ? '' : (($user -> qtid) ? array('mediaID' => '4', 'mediaUID' => ifab($user -> tqqid , $user -> user_login), 'profileImageUrl' => $user -> qtid, 'oauth_token' => ifac($user -> login_qq[0], $user -> tdata['tid'] == 'qtid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_qq[1], $user -> tdata['tid'] == 'qtid', $user -> tdata['oauth_token_secret'])) : ''),

				'5' => ($user -> shmid) ? '' : (($user -> shtid) ? array('mediaID' => '5', 'mediaUID' => ifab($user -> sohuid , $user -> user_login), 'profileImageUrl' => $user -> shtid, 'oauth_token' => ifac($user -> login_sohu[0], $user -> tdata['tid'] == 'shtid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_sohu[1], $user -> tdata['tid'] == 'shtid', $user -> tdata['oauth_token_secret'])) : ''),

				'6' => ($user -> nmid) ? '' : ((is_numeric($user -> neteaseid) && $user -> neteaseid < 0) ? array('mediaID' => '6', 'mediaUID' => $user -> neteaseid, 'profileImageUrl' => $user -> ntid, 'oauth_token' => ifac($user -> login_netease[0], $user -> tdata['tid'] == 'ntid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_netease[1], $user -> tdata['tid'] == 'ntid', $user -> tdata['oauth_token_secret'])) : ''),

				'7' => ($user -> rmid) ? '' : (($user -> rtid) ? array('mediaID' => '7', 'mediaUID' => ifab($user -> renrenid , $user -> user_login), 'profileImageUrl' => $user -> rtid):''),

				'8' => ($user -> kmid) ? '' : (($user -> ktid) ? array('mediaID' => '8', 'mediaUID' => ifab($user -> kaxinid , $user -> user_login), 'profileImageUrl' => $user -> ktid):''),

				'9' => ($user -> dmid) ? '' : (($user -> dtid) ? array('mediaID' => '9', 'mediaUID' => $user -> dtid, 'profileImageUrl' => 'http://t.douban.com/icon/u' . $user -> dtid . '-1.jpg', 'oauth_token' => ifac($user -> login_douban[0], $user -> tdata['tid'] == 'dtid', $user -> tdata['oauth_token']), 'oauth_token_secret' => ifac($user -> login_douban[1], $user -> tdata['tid'] == 'dtid', $user -> tdata['oauth_token_secret'])) : ''),

				'13' => ($user -> qqmid) ? '' : (($user -> qqid) ? array('mediaID' => '13', 'mediaUID' => $user -> qqid, 'profileImageUrl' => $user -> qqtid, 'oauth_token' => $user -> qqid):''),

				'16' => ($user -> tbmid) ? '' : (($user -> tbtid && is_numeric($user -> user_login)) ? array('mediaID' => '16', 'mediaUID' => $user -> user_login, 'email' => $user -> user_email, 'profileImageUrl' => $user -> tbtid):''),

				'17' => ($user -> tymid) ? '' : (($user -> tytid) ? array('mediaID' => '17', 'mediaUID' => $user -> tytid, 'profileImageUrl' => 'http://tx.tianyaui.com/logo/small/' . $user -> tytid, 'oauth_token' => $user -> login_tianya[0], 'oauth_token_secret' => $user -> login_tianya[1]):''),

				'19' => ($user -> bdmid) ? '' : (($user -> bdtid) ? array('mediaID' => '19', 'mediaUID' => ifab($user -> baiduid , $user -> user_login), 'profileImageUrl' => 'http://himg.bdimg.com/sys/portraitn/item/' . $user -> bdtid . '.jpg'):'')
				));
		$result[$uid] = array_values($ret); 
		// 更新数据库
		$ids = array('4' => 'tqq', '5' => 'sohu', '7' => 'renren', '8' => 'kaixin');
		foreach ($ids as $mediaID => $name) {
			if ($ret[$mediaID]) {
				update_usermeta($uid, $name . 'id', $ret[$mediaID]['mediaUID']); // 用于SNS URL
			} 
		} 
		// $ids2 = array('3' => 'sina', '4' => 'qq', '5' => 'sohu', '6' => 'netease', '9' => 'douban'); // sina,tqq,sohu,netease,douban
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
	if (!$content) {
		$wptm_basic['denglu'] = 1;
		update_option("wptm_basic", $wptm_basic);
		return;
	} 
	return $content;
} 
// 旧的wordpress连接微博插件，数据转换
function connect_denglu_update() {
	global $wptm_basic;
	@ini_set("max_execution_time", 300);
	$userdata = connect_denglu_update_data(); 
	// return var_dump($userdata);
	if ($userdata) {
		$content = array_slice($userdata, 0, 50, true); 
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
			$mid = array('2' => 'mmid', '3' => 'smid', '4' => 'qmid', '5' => 'shmid', '6' => 'nmid', '7' => 'rmid', '8' => 'kmid', '9' => 'dmid', '13' => 'qqmid', '16' => 'tbmid', '17' => 'tymid', '19' => 'bdmid');
			foreach ($output as $userid => $mediaUser) {
				foreach ($mediaUser as $mediaID => $mediaUserID) {
					update_usermeta($userid, $mid[$mediaID], $mediaUserID); 
					// $results[] = array($userid, $mid[$mediaID], $mediaUserID);
				} 
			} 
			// return var_dump($results);
			connect_denglu_update();
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
	$username = $user['mediaUserID'];
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
		if ($id == $mid) {
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
			$uid = ifab(get_user_by_meta_value($id, $userid), get_user_by_meta_value($mid, $username));
		} 
	} 
	if (is_user_logged_in()) { // v2.1
		if (($wpuid = $_SESSION['user_id']) && ($url_back = $_SESSION['wp_url_bind'])) {
			if ($uid) {
				$userinfo = wp_get_user_info($uid);
				$user_login = $userinfo['user_login'];
				wp_die("很遗憾！该帐号已被用户名 $user_login 绑定，您可以用该 <a href=\"" . wp_logout_url() . "\">用户名</a> 登录，并到 <a href=\"" . admin_url('profile.php') . "\">我的资料</a> 页面解除绑定，再进行绑定该帐号！<strong>如果不能成功，请删除那个WP帐号，再进行绑定！</strong> <a href='$url_back'>返回</a>");
			} else {
				update_usermeta($wpuid, $mid, $username);
				if ($homepage || $tid == 'qqtid') { // sina,tqq,sohu,netease,renren,kaixin,douban,qq,tianya
					update_usermeta($wpuid, $weibo[1] . 'id', $userid);
					if ($tid == 'qqtid')
						update_usermeta($wpuid, $tid, $user['profileImageUrl']);
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
/**
 * 绑定登录帐号 v2.1
 */
// 获取已绑定帐号
function denglu_bind_account($user) {
	$account = array('qzone' => array(ifab($user -> qqid, $user -> qqmid), '腾讯QQ', 13),
		'sina' => array(ifab($user -> stid, $user -> smid), '新浪微博', 3),
		'tencent' => array(ifab($user -> tqqid, $user -> qmid), '腾讯微博', 4),
		'renren' => array(ifab($user -> renrenid, $user -> rmid), '人人网', 7),
		'taobao' => array($user -> tbmid, '淘宝网', 16),
		'alipayquick' => array($user -> alimid, '支付宝', 18),
		'douban' => array(ifab($user -> dtid, $user -> dmid), '豆瓣', 9),
		'baidu' => array($user -> bdmid, '百度', 19),
		'kaixin001' => array(ifab($user -> kaixinid , $user -> kmid), '开心网', 8),
		'sohu' => array(ifab($user -> sohuid, $user -> shmid), '搜狐微博', 5),
		'netease' => array(ifab($user -> neteaseid, $user -> nmid), '网易微博', 6),
		'netease163' => array($user -> wymid, '网易通行证', 21),
		'tianya' => array(ifab($user -> tytid, $user -> tymid), '天涯微博', 17),
		'windowslive' => array($user -> mmid, 'MSN', 2),
		'google' => array($user -> gmid, 'Google', 1),
		'yahoo' => array($user -> ymid, 'Yahoo', 12)
		);
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
			echo "<a href=\"{$url}&meida_id={$vaule[2]}\" title=\"$vaule[1] (已绑定)\" class=\"btn_{$key} bind\" onclick=\"return confirm('Are you sure? ')\"><b></b></a>\r\n";
		} else {
			echo "<a href=\"{$url}&bind={$key}\" title=\"$vaule[1]\" class=\"btn_{$key}\"></a>\r\n";
		} 
	} 
	echo "</span><p>( 说明：绑定后，您可以使用用户名或者用合作网站帐号登录本站。)</p></td></tr>";
} 
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
			$comment_agent = trim($comments['comment_agent'] . ' Denglu');
			$result = $wpdb -> update($wpdb -> comments, compact('comment_agent'), compact('comment_ID'));
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
 * 评论数 + 最新评论 v2.1.6
 */
if (!function_exists('denglu_comments_number') && install_comments()) {
	// 获取评论数
	function get_denglu_comment_counts($postid = '') {
		global $wptm_basic;
		class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
		$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
		try {
			$output = $api -> commentCount($postid);
		} 
		catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
			// wp_die($e -> geterrorDescription()); //返回错误信息
		} 
		// return var_dump($output);
		// $output = array(array("id"=>"160", "url"=>"http://open.denglu.cc", "count"=>160),array("id"=>"144", "url"=>"http://open.denglu.cc", "count"=>144));
		if ($output) {
			foreach($output as $vaule) {
				$count[$vaule['id']] = $vaule['count'];
			} 
			return $count;
		} 
	} 

	function get_denglu_comments_number($postid = '') {
		if (!$postid) $postid = get_the_ID();
		if ($_COOKIE["denglu_comment_counts"]) {
			$count = json_decode(stripslashes($_COOKIE["denglu_comment_counts"]), true);
		} elseif ($_SESSION['denglu_comment_counts']) {
			$count = $_SESSION['denglu_comment_counts'];
		} else {
			$count = get_denglu_comment_counts();
			$_SESSION['denglu_comment_counts'] = $count;
		} 
		return $count[$postid] ? $count[$postid] : 0;
	} 
	// 获取最新评论
	function get_denglu_recent_comments($count = '') {
		global $wptm_basic;
		class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
		$api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
		try {
			$output = $api -> latestComment($count);
		} 
		catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
			// wp_die($e -> geterrorDescription()); //返回错误信息
		} 
		return $output;
	} 
	// 设置cookies
	function denglu_comment_counts_cookie() {
		global $wptm_comment;
		if (!is_admin()) {
			if (default_values('comments_count', 1, $wptm_comment) && !$_COOKIE["denglu_comment_counts"]) {
				if ($count = get_denglu_comment_counts()) {
					setcookie("denglu_comment_counts", json_encode($count), time() + 1800); //缓存30分钟
				} 
			} 
			if ($wptm_comment['latest_comments'] && used_widget('wp-connect-comment-widget') && !$_COOKIE["denglu_recent_comments"]) {
				if ($comments = get_denglu_recent_comments()) {
					setcookie("denglu_recent_comments", json_encode($comments), time() + 300); //缓存5分钟
				} 
			} 
		} 
	} 
	add_action('init', 'denglu_comment_counts_cookie', 0);

	function denglu_recent_comments($comments) {
		if (is_array($comments)) {
			echo '<ul id="denglu_recentcomments">';
			foreach($comments as $comment) {
				echo "<li>" . $comment['name'] . ": <a href=\"{$comment['url']}\">" . $comment['content'] . "...</a></li>";
			} 
			echo '</ul>';
		} 
	} 
	// 替换自带的评论数函数
	function denglu_comments_number($zero = false, $one = false, $more = false, $deprecated = '') {
		global $id;
		$number = get_denglu_comments_number($id);
		if ($number > 1)
			$output = str_replace('%', number_format_i18n($number), (false === $more) ? __('% Comments') : $more);
		elseif ($number == 0)
			$output = (false === $zero) ? __('No Comments') : $zero;
		else // must be one
			$output = (false === $one) ? __('1 Comment') : $one;

		echo apply_filters('denglu_comments_number', $output, $number);
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

	if (default_values('comments_count', 1, $wptm_comment)) {
		add_filter('comments_number', 'denglu_comments_number');
		add_filter('get_comments_number', 'get_denglu_comments_number', 0);
	} 
} 

?>
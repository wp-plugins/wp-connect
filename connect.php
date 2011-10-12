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

function wp_login_account($uid) {
	return array(get_user_meta($uid, 'login_sina', true), get_user_meta($uid, 'login_qq', true), get_user_meta($uid, 'login_sohu', true), get_user_meta($uid, 'login_netease', true), get_user_meta($uid, 'login_douban', true));
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
		echo '<script type="text/javascript">window.close();</script>';
		return;
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
	$uid = (email_exists($email)) ? email_exists($email) : get_user_by_meta_value('stid', $sinaid);
	if ($uid) {
		wp_connect_login($sinaid.'|'.$username.'|'.$sina->screen_name.'|'.$sina->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $email, $tid, $uid);
	} else {
		$user_id = username_exists($username);
	    $oid = get_user_meta($user_id, 'stid', true); // 出现同名帐号时判断
		if($oid) {
			wp_connect_error($sinaid.'|'.$username.'|'.$sina->screen_name.'|'.$sina->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $email, $tid, $oid);
		} else {
			wp_connect_login($sinaid.'|'.$username.'|'.$sina->screen_name.'|'.$sina->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $email, $tid);
		}
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
		echo '<script type="text/javascript">window.close();</script>';
		return;
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
    $uid = (email_exists($email)) ? email_exists($email) : get_user_by_meta_value('tqqid', $username);
	if ($uid) {
		wp_connect_login($qq->head.'|'.$username.'|'.$qq->nick.'|'.$url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$username, $email, $tid, $uid);
	} else {
		$user_id = username_exists($username);
	    $oid = get_user_meta($user_id, 'tqqid', true); // 出现同名帐号时判断
		if($oid) {
			wp_connect_error($qq->head.'|'.$username.'|'.$qq->nick.'|'.$url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$username, $email, $tid, $oid);
		} else {
			wp_connect_login($qq->head.'|'.$username.'|'.$qq->nick.'|'.$url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$username, $email, $tid);
		}
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
		echo '<script type="text/javascript">window.close();</script>';
		return;
	}

	$sohu = json_decode($sohu);

	$username = $sohu->id;
	$tmail = $username.'@t.sohu.com';
	$url = "http://t.sohu.com/u/".$username;
	$tid = "shtid";
    $uid = (email_exists($tmail)) ? email_exists($tmail) : get_user_by_meta_value('sohuid', $username);
	if ($uid) {
		wp_connect_login($sohu->profile_image_url.'|'.$username.'|'.$sohu->screen_name.'|'.$url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$username, $tmail, $tid, $uid);
	} else {
		wp_connect_login($sohu->profile_image_url.'|'.$username.'|'.$sohu->screen_name.'|'.$url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$username, $tmail, $tid);
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
		echo '<script type="text/javascript">window.close();</script>';
		return;
	}

	$netease = json_decode($netease);
    $username = $netease->screen_name;
	$tmail = $username.'@t.163.com';
	//$tmail = $netease->email;
	$oid = $netease->id;
	$tid = "ntid";
    //$uid = (email_exists($tmail)) ? email_exists($tmail) : get_user_by_meta_value('neteaseid', $oid);
	$uid = ($e = email_exists($tmail)) ? $e : (($o = get_user_by_meta_value('neteaseid', $oid)) ? $o : get_user_by_meta_value('neteaseid', $username));

	if ($uid) {
		wp_connect_login($netease->profile_image_url.'|'.$username.'|'.$netease->name.'|'.$netease->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$oid, $tmail, $tid, $uid);
	} else {
		wp_connect_login($netease->profile_image_url.'|'.$username.'|'.$netease->name.'|'.$netease->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$oid, $tmail, $tid);
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
		echo '<script type="text/javascript">window.close();</script>';
		return;
	}

	$twitter = json_decode($twitter);

    $username = $sina->screen_name;
	$tmail = $username.'@twitter.com';
	$tid = "ttid";
    $uid = (email_exists($tmail)) ? email_exists($tmail) : get_user_by_meta_value('twitterid', $username);
	if ($uid) {
		wp_connect_login($twitter->profile_image_url.'|'.$username.'|'.$twitter->name.'|'.$twitter->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$username, $tmail, $tid, $uid);
	} else {
		wp_connect_login($twitter->profile_image_url.'|'.$username.'|'.$twitter->name.'|'.$twitter->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'].'|'.$username, $tmail, $tid);
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
		echo '<script type="text/javascript">window.close();</script>';
		return;
	}
	
	$douban = simplexml_load_string($douban);
	
	$douban_xmlns = $douban->children('http://www.douban.com/xmlns/');	

	$douban_id = str_replace("http://api.douban.com/people/","",$douban->id);
	$username = $douban_xmlns->uid;
	$douban_url = "http://www.douban.com/people/".$username;

	$tmail = $douban_id.'@douban.com';
	$tid = "dtid";
    $uid = (email_exists($tmail)) ? email_exists($tmail) : get_user_by_meta_value('dtid', $douban_id);
	if ($uid) {
		wp_connect_login($douban_id.'|'.$username.'|'.$douban->title.'|'.$douban_url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $tmail, $tid, $uid);
	} else {
		wp_connect_login($douban_id.'|'.$username.'|'.$douban->title.'|'.$douban_url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $tmail, $tid);
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

	if($tianya == "no auth"){
		echo '<script type="text/javascript">window.close();</script>';
		return;
	}

	$tianya = json_decode($tianya);

	$tianya = $tianya->user;
	$username = $tianya->user_id;
	$tmail = $username.'@tianya.cn';
	$tid = "tytid";
    $uid = (email_exists($tmail)) ? email_exists($tmail) : get_user_by_meta_value('tytid', $username);
	if ($uid) {
		wp_connect_login($username.'|'.$username.'|'.$tianya->user_name.'||'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $tmail, $tid, $uid);
	} else {
		wp_connect_login($username.'|'.$username.'|'.$tianya->user_name.'||'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $tmail, $tid);
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

function get_weibo($tid) {
	$name = array('qqtid' => array('qqid', 'QQ', ''),
		'stid' => array('sinaid', '新浪微博', '1', 'http://weibo.com/'),
		'qtid' => array('tqqid', '腾讯微博', '1', 'http://t.qq.com/'),
		'rtid' => array('renrenid', '人人网', '1'),
		'ktid' => array('kaixinid', '开心网', '1'),
		'tbtid' => array('taobaoid', '淘宝', '1'),
		'dtid' => array('doubanid', '豆瓣', '1'),
		'shtid' => array('sohuid', '搜狐微博', '1'),
		'ntid' => array('neteaseid', '网易微博', '1'),
		'bdtid' => array('baiduid', '百度', '1'),
		'gtid' => array('googleid', '谷歌', ''),
		'ytid' => array('yahooid', '雅虎', ''),
		'mtid' => array('msnid', 'MSN', ''),
		'tytid' => array('tianyaid', '天涯', '1'),
		'ttid' => array('twitterid', 'Twitter', '1')
		);
	if (array_key_exists($tid, $name)) {
		return $name[$tid];
	} 
}

function wp_connect_error($userinfo, $tmail, $tid, $wpuid = '', $user_email = '') {
	global $plugin_url;
	if ($_SESSION['wp_url_back']) {
		$redirect_to = $_SESSION['wp_url_back'];
	} else {
		$redirect_to = get_bloginfo('url');
	}
	if(!is_array($userinfo)){
		$userinfo = explode('|', $userinfo);
	}
	$weibo = get_weibo($tid); // 当前登录
	$tmp_name = str_replace('id', '', $weibo[0]) . '_' . $userinfo[1];
	$_SESSION['wp_login_userinfo'] = array($userinfo[0] . '|' . $tmp_name . '|' . $userinfo[2] . '|' . $userinfo[3] . '|' . $userinfo[4] . '|' . $userinfo[5] . '|' . $userinfo[6], $tmail, $tid);
	if (!$wpuid) {
		$tip = "很遗憾！用户名 $userinfo[1] 被系统保留，请更换帐号<a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a>！";
	} elseif (!$user_email) {
		$tip = "很遗憾！用户名 $userinfo[1] 已经跟 $weibo[1]帐号(<a href=\"{$weibo[3]}{$wpuid}\" target=\"_blank\">$wpuid</a>) 绑定了，您可以用该微博帐号 或者该 <a href=\"" . site_url('wp-login.php', 'login') . "\">用户名</a> 登录后到 <a href=\"" . admin_url('profile.php') . "\">我的资料</a> 页面解除绑定，再重新绑定帐号！";
	} else {
		$last_login = get_user_meta($wpuid, 'last_login', true); //最后一次登录的微博
		$user_weibo = get_weibo($last_login);
		if ($user_weibo[2]) {
			$tip = "很遗憾！用户名 $userinfo[1] 已被 $user_email 绑定，如果你以前用 $user_weibo[1] 登录过，请继续使用该微博/社区登录，如果您想要用其他微博的同名帐号登录，请先用 $user_weibo[1] 登录或者用该 <a href=\"" . site_url('wp-login.php', 'login') . "\">用户名</a> 登录后到 <a href=\"" . admin_url('profile.php') . "\">我的资料</a> 页面勾选该同名帐号($weibo[1])。";
		} elseif ($user_weibo[1]) {
			$tip = "很遗憾！用户名 $userinfo[1] 已被占用，如果你以前用 $user_weibo[1] 登录过，请继续使用该微博/社区登录，如果您想要用其他微博的同名帐号登录，请先用 $user_weibo[1] 登录或者用该 <a href=\"" . site_url('wp-login.php', 'login') . "\">用户名</a> 登录后到 <a href=\"" . admin_url('profile.php') . "\">我的资料</a> 页面勾选该同名帐号($weibo[1])。";
		} else {
			$tip = "很遗憾！用户名 $userinfo[1] 已被占用，请使用该 <a href=\"" . site_url('wp-login.php', 'login') . "\">WP用户名</a> 登录后到 <a href=\"" . admin_url('profile.php') . "\">我的资料</a> 页面勾选该同名帐号($weibo[1])。";
		} 
	}
	wp_die($tip . "<strong>或者点击下面的登录按钮，我们将为您创建新的WP用户名 $tmp_name </strong> [ <a href='$redirect_to'>返回</a> ]<p style=\"text-align:center;\"><a href=\"{$plugin_url}/save.php?do=login\" title=\"点击登录即可创建新用户\"><img src=\"{$plugin_url}/images/login.png\" /></a></p>");
}

/**
 * 登录
 * @since 1.9
 */
function wp_connect_login($userinfo, $tmail, $tid, $uid = '') {
	global $wpdb, $wptm_connect;
	$userinfo = explode('|', $userinfo);
	if (count($userinfo) < 6) {
		wp_die("An error occurred!");
	} 
	$redirect_to = $_SESSION['wp_url_back'];
	$disable_username = explode(',', $wptm_connect['disable_username']);
	if ($userinfo[1]){
		if(in_array($userinfo[1],$disable_username) && !$uid) {
			wp_connect_error($userinfo, $tmail, $tid);
		}
	} else {
		wp_die("获取用户授权信息失败，请重新<a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a> 或者 清除浏览器缓存再试! [ <a href='$redirect_to'>返回</a> ]");
	}

	$avatar = $userinfo[0];
	$t = strtolower($_SESSION['wp_url_login']);
	$password = wp_generate_password();
    if ($uid) {
		$user = get_userdata($uid);
		$wpuid = $uid;
	} else {
		$user = get_userdatabylogin($userinfo[1]);
		$wpuid = $user->ID;
    }
	if ($wpuid) {
		$user_login = $user -> user_login;
		$password = $user -> user_pass;
		$user_email = $user -> user_email;
		$user_url = $user -> user_url;
		$bind = get_user_meta($wpuid, 'bind', true);
		if ($userinfo[1] == $user_login) {
			$is_login = 'true';
		} 
		if (is_array($bind)) {
			$bind = array_filter($bind);
		}
		if (!$uid) {
			if ($t && $bind) { // 判断是否勾选了同名帐号
				$sina = $bind['sina'];
				$qq = $bind['qq'];
				$sohu = $bind['sohu'];
				$netease = $bind['netease'];
				$douban = $bind['douban'];
				$renren = $bind['renren'];
				$kaixin001 = $bind['kaixin001'];
				$google = $bind['google'];
				$yahoo = $bind['yahoo'];
				$twitter = $bind['twitter'];
				$msn = $bind['msn'];
				$taobao = $bind['taobao'];
				$baidu = $bind['baidu'];
				$tianya = $bind['tianya'];
				if (!$$t) {
					if ($is_login) {
						wp_connect_error($userinfo, $tmail, $tid, $wpuid, $user_email);
					} else {
						wp_die("很遗憾！邮箱 $user_email 已被用户名 $user_login 绑定，您可以使用该用户名 <a href=\"" . site_url('wp-login.php', 'login') . "\">登录</a>，并到 <a href=\"" . admin_url('profile.php') . "\">我的资料</a> 页面勾选该同名帐号，或者更换微博帐号，或者 <a href=\"" . site_url('wp-login.php?action=lostpassword', 'login') . "\">找回密码</a>！[ <a href='$redirect_to'>返回</a> ]");
					} 
				} 
			} else {
				$level = get_user_meta($wpuid, $wpdb -> prefix . 'user_level', true); //判断用户级别
				if ($level > 0) { // 0 订阅者 1 投稿者 2 作者
					wp_connect_error($userinfo, $tmail, $tid, $wpuid, $user_email);
				} 
			} 
		} 
	} else {
		$wpuid = '';
	}

	if (!$user_url) {
		$user_url = $userinfo[3];
	} 

	$userdata = array('ID' => $wpuid,
		'user_pass' => $password,
		'user_login' => $userinfo[1],
		'nickname' => $userinfo[2],
		'display_name' => $userinfo[2],
		'user_url' => $user_url,
		'user_email' => $tmail);

	if (!function_exists('wp_insert_user')) {
		include_once(ABSPATH . WPINC . '/registration.php');
	} 

	if ($userinfo[1]) {
		if ($tmail != $user_email && ($is_login || !$user_login)) {
			$wpuid = wp_insert_user($userdata);
		} 
		if (!$bind && $tid != 'qqtid') {
			update_usermeta($wpuid, 'bind', array($t => '1'));
		}
	} 
	if ($wpuid) {
		if($avatar)
		update_usermeta($wpuid, $tid, $avatar);
		update_usermeta($wpuid, 'last_login', $tid);
        if( $userinfo[4] && $userinfo[5] ) { // 保存授权信息
			update_usermeta($wpuid, 'login_'.$t, array($userinfo[4],$userinfo[5]));
			if( in_array($t, array('qq','sina','netease','sohu')) ) {
				$nickname = get_user_meta($wpuid, 'login_name', true);
			    $nickname[$t] = ($t == 'qq') ? $userinfo[1] : $userinfo[2];
			    update_usermeta($wpuid, 'login_name', $nickname);
			}
		}
	    $openid = get_weibo($tid);
		if($userinfo[6]) {
			update_usermeta($wpuid, $openid[0], $userinfo[6]);
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
if($wptm_connect['enable_connect']) {
	add_action( 'show_user_profile', 'wp_connect_profile_fields' );
	add_action( 'edit_user_profile', 'wp_connect_profile_fields' );
	add_action( 'personal_options_update', 'wp_connect_save_profile_fields' );
	add_action( 'edit_user_profile_update', 'wp_connect_save_profile_fields' );
} 

function wp_connect_profile_fields( $user ) {
	global $wptm_connect;
    $user_id = $user->ID;
    $bind = get_user_meta($user_id, 'bind', true);
?>
<h3>帐号登录</h3>
<table class="form-table">
<tr>
	<th>同名帐号</th>
	<td>
	<label><input name="without" type="checkbox" value="1" <?php if($bind['without']) echo "checked"; ?> />都不同名</label>
	<label><input name="sina" type="checkbox" value="1" <?php if($bind['sina']) echo "checked"; ?> />新浪微博</label> <label><input name="qq" type="checkbox" value="1" <?php if($bind['qq']) echo "checked"; ?> />腾讯微博</label> <label><input name="taobao" type="checkbox" value="1" <?php if($bind['taobao']) echo "checked"; ?> />淘宝网</label> <label><input name="baidu" type="checkbox" value="1" <?php if($bind['baidu']) echo "checked"; ?> />百度</label> <label><input name="sohu" type="checkbox" value="1" <?php if($bind['sohu']) echo "checked"; ?> />搜狐微博</label> <label><input name="netease" type="checkbox" value="1" <?php if($bind['netease']) echo "checked"; ?> />网易微博</label><br /><label><input name="renren" type="checkbox" value="1" <?php if($bind['renren']) echo "checked"; ?> />人人网</label> <label><input name="kaixin001" type="checkbox" value="1" <?php if($bind['kaixin001']) echo "checked"; ?> />开心网</label> <label><input name="douban" type="checkbox" value="1" <?php if($bind['douban']) echo "checked"; ?> />豆瓣</label> <label><input name="tianya" type="checkbox" value="1" <?php if($bind['tianya']) echo "checked"; ?> />天涯</label> <label><input name="google" type="checkbox" value="1" <?php if($bind['google']) echo "checked"; ?> />Google</label> <label><input name="yahoo" type="checkbox" value="1" <?php if($bind['yahoo']) echo "checked"; ?> />Yahoo</label> <label><input name="msn" type="checkbox" value="1" <?php if($bind['msn']) echo "checked"; ?> />MSN</label> <label><input name="twitter" type="checkbox" value="1" <?php if($bind['twitter']) echo "checked"; ?> />Twitter</label><br /><span class="description">提示: 为了您的帐号安全，用户名跟第三方网站帐号相同时请勾选，<b>不同名的切记不要勾选！</b></span></td>
</tr>
<?php if (function_exists('wp_connect_bind_qq') && $wptm_connect['qqlogin'] && $wptm_connect['qq_app_key']) {wp_connect_bind_qq( $user );}?>
</table>
<?php
}
$$wpdontpeep = $_POST['fields'];
function wp_connect_save_profile_fields( $user_id ) {

if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	$bind = array(
	'qq' => $_POST['qq'],
	'sina' => $_POST['sina'],
	'sohu' => $_POST['sohu'],
	'netease' => $_POST['netease'],
	'douban' => $_POST['douban'],
	'renren' => $_POST['renren'],
	'kaixin001' => $_POST['kaixin001'],
	'google' => $_POST['google'],
	'yahoo' => $_POST['yahoo'],
	'twitter' => $_POST['twitter'],
	'msn' => $_POST['msn'],
	'taobao' => $_POST['taobao'],
	'baidu' => $_POST['baidu'],
	'tianya' => $_POST['tianya'],
	'without' => $_POST['without']);
    update_usermeta( $user_id, 'bind', $bind );
	if (function_exists('wp_connect_bind_qq'))
	update_usermeta( $user_id, 'qqavatar', $_POST['qqavatar'] );
}
// 头像
add_filter("get_avatar", "wp_connect_avatar",10,4);
function wp_connect_avatar($avatar, $id_or_email = '', $size = '32') {
	global $comment;
    if ( is_numeric($id_or_email) ) {
		$userid = (int) $id_or_email;
        $user = get_userdata($userid);
		if ($user) $user_email = $user->user_email;
	} elseif ( is_object($comment) ) {
		$id_or_email = $comment -> user_id;
		$user_email = $comment -> comment_author_email;
	} elseif ( is_object($id_or_email) ) {
		$user_email = $id_or_email -> user_email;
		$id_or_email = $id_or_email -> user_id;
	}

	if (!$id_or_email) {
		return $avatar;
	}

	$email = ($user_email) ? $user_email : $id_or_email;
	$tmail = strstr($email, '@');
	$uid = str_replace($tmail, '', $email);
	$tname = array(//'@qq.com' => 'http://face6.qun.qq.com/cgi/svr/face/getface?type=1&uin=[head]',
		'@t.sina.com.cn' => array('http://tp3.sinaimg.cn/[head]/50/1.jpg','http://weibo.com/'),
		'@douban.com' => array('http://t.douban.com/icon/u[head]-1.jpg','http://www.douban.com/people/'),
		'@tianya.cn' => array('http://tx.tianyaui.com/logo/small/[head]','http://my.tianya.cn/')
		);
	if ( $tmail && array_key_exists($tmail, $tname) && is_numeric($uid) ) {
		$head = $tname[$tmail];
		$out = str_replace('[head]', $uid, $head[0]);
		$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
		if(!$userid && $user_email) $avatar = "<a href='{$head[1]}{$uid}' target='_blank'>$avatar</a>";
	} elseif($user_email) {
		$name = array('@t.sina.com.cn' => array('stid', 'http://tp3.sinaimg.cn/[head]/50/1.jpg','http://weibo.com/'),
			'@t.qq.com' => array('qtid', '[head]/40','http://t.qq.com/'),
			'@renren.com' => array('rtid', '[head]','http://www.renren.com/profile.do?id='),
			'@kaixin001.com' => array('ktid', '[head]','http://www.kaixin001.com/home/?uid='),
			'@douban.com' => array('dtid', 'http://t.douban.com/icon/u[head]-1.jpg','http://www.douban.com/people/'),
			'@t.sohu.com' => array('shtid', '[head]','http://t.sohu.com/'),
			'@t.163.com' => array('ntid', '[head]','http://t.163.com/'),
			'@baidu.com' => array('bdtid', 'http://himg.bdimg.com/sys/portraitn/item/[head].jpg',''),
			'@tianya.cn' => array('tytid', 'http://tx.tianyaui.com/logo/small/[head]','http://my.tianya.cn/'),
			'@twitter.com' => array('ttid', '[head]','http://twitter.com/')
			);
		if (get_user_meta($id_or_email, 'qqavatar', true)) {
			if ($tid = get_user_meta($id_or_email, 'qqtid', true)) {
				$out = $tid;
				$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
			} 
		} elseif ($tmail && array_key_exists($tmail, $name)) {
			$head = $name[$tmail];
			if ($tid = get_user_meta($id_or_email, $head[0], true)) {
				$out = str_replace('[head]', $tid, $head[1]);
				$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
				if(!$userid && $head[2]) {
					$avatar = "<a href='{$head[2]}{$uid}' target='_blank'>$avatar</a>";
				}
			} 
		} elseif (get_user_meta($id_or_email, 'taobaoid', true)) {
			if ($tid = get_user_meta($id_or_email, 'tbtid', true)) {
				$out = $tid;
				$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
			} 
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
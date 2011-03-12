<?php
include_once('config.php');

add_action('init', 'wp_connect_init');

if ($wptm_connect['enable_connect']) { // 是否开启连接微博功能
	add_action('comment_form', 'wp_connect');
    add_action("login_form", "wp_connect");
    add_action("register_form", "wp_connect",12);
}

function wp_connect_init(){
	if (session_id() == "") {
		session_start();
	}
	if(!is_user_logged_in()) {		
        if(isset($_GET['oauth_token'])){
			require_once('OAuth/OAuth.php');
			if($_SESSION['wp_go_login'] == "SINA")    {wp_connect_sina();}
			if($_SESSION['wp_go_login'] == "QQ")      {wp_connect_qq();}
			if($_SESSION['wp_go_login'] == "NETEASE") {wp_connect_netease();}
			if($_SESSION['wp_go_login'] == "DOUBAN")  {wp_connect_douban();}
        } 
    } 
}

function wp_connect($id=""){
    global $plugin_url, $wptm_connect;
	$_SESSION['wp_callback'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

if (is_user_logged_in()) {
	global $user_ID;
	$stid = get_user_meta($user_ID, 'stid', true);
	$qtid = get_user_meta($user_ID, 'qtid', true);
	$ntid = get_user_meta($user_ID, 'ntid', true);
	$dtid = get_user_meta($user_ID, 'dtid', true);
	$tdata = get_user_meta($user_ID, 'tdata', true);

	if ($stid && $tdata['tid'] == "stid") {
		echo '<p><input name="comment_to_sina" type="checkbox" id="comment_to_sina" value="1" /><label for="comment_to_sina">同步评论到新浪微博</label></p>';
	} 
	if ($qtid && $tdata['tid'] == "qtid") {
		echo '<p><label for="comment_to_qq">同步评论到腾讯微博</label><input name="comment_to_qq" type="checkbox" id="comment_to_qq" value="1" style="width:30px;" /></p>';
	} 
	if ($ntid && $tdata['tid'] == "ntid") {
		echo '<p><label for="comment_to_netease">同步评论到网易微博</label><input name="comment_to_netease" type="checkbox" id="comment_to_netease" value="1" style="width:30px;" /></p>';
	} 
	if ($dtid && $tdata['tid'] == "dtid") {
		echo '<p><label for="comment_to_douban">同步评论到豆瓣</label><input name="comment_to_douban" type="checkbox" id="comment_to_douban" value="1" style="width:30px;" /></p>';
	} 
	return;
}
?>
	<style type="text/css"> 
	.t_login_button { padding-bottom: 5px;}
	.t_login_button img{ border:none;}
    </style>
<?php
	if(is_singular() && !get_option('comment_registration')) {
	echo '<p>您可以登录以下帐号发表评论：</p>';
	}
	echo '<p class="t_login_button">';
	if($wptm_connect['sina']) {
	echo '<a href="'.$plugin_url.'/login.php?go=SINA" rel="nofollow"><img src="'.$plugin_url.'/images/btn_sina.png" alt="使用新浪微博登录" /></a> ';
	}
	if($wptm_connect['qq']) {
	echo '<a href="'.$plugin_url.'/login.php?go=QQ" rel="nofollow"><img src="'.$plugin_url.'/images/btn_qq.png" alt="使用腾讯微博登录" /></a> ';
	}
	if($wptm_connect['douban']) {
	echo '<a href="'.$plugin_url.'/login.php?go=DOUBAN" rel="nofollow"><img src="'.$plugin_url.'/images/btn_douban.png" alt="使用豆瓣帐号登录" /></a> ';
	}
	if($wptm_connect['netease']) {
	echo '<a href="'.$plugin_url.'/login.php?go=NETEASE" rel="nofollow"><img src="'.$plugin_url.'/images/btn_netease.png" alt="使用网易微博登录" /></a> ';
	}
	echo '</p>';
}

//sina
function wp_connect_sina(){
	include_once('OAuth/sina_OAuth.php');
	
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
	
	if((string)$sina->domain){
		$username = $sina->domain;
	} else {
		$username = $sina->id;
	}

	$tmail = $username.'@t.sina.com.cn';
	$tid = "stid";
		
	wp_connect_login($sina->id.'|'.$username.'|'.$sina->screen_name.'|'.$sina->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $tmail, $tid); 
}
//qq
function wp_connect_qq(){
	include_once('OAuth/qq_OAuth.php');
	
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

	$tmail = $qq->name.'@t.qq.com';
	$tid = "qtid";
		
	wp_connect_login($qq->head.'|'.$qq->name.'|'.$qq->nick.'||'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $tmail, $tid); 
}
//netease
function wp_connect_netease(){
	include_once('OAuth/netease_OAuth.php');

	$to = new neteaseOAuth(APP_KEY, APP_SECRET, $_GET['oauth_token'],$_SESSION['oauth_token_secret']);
	
	$tok = $to ->getAccessToken($_REQUEST['oauth_verifier']);

	$to = new neteaseOAuth(APP_KEY, APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$netease = $to->OAuthRequest('http://api.t.163.com/account/verify_credentials.json', 'GET',array());

	if($netease == "no auth"){
		echo '<script type="text/javascript">window.close();</script>';
		return;
	}

	$netease = json_decode($netease);

	$tmail = $netease->screen_name.'@t.163.com';
	$tid = "ntid";
		
	wp_connect_login($netease->profile_image_url.'|'.$netease->screen_name.'|'.$netease->name.'|'.$netease->url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $tmail, $tid); 
}
//douban
function wp_connect_douban(){
	include_once('OAuth/douban_OAuth.php');

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
	$douban_url = "http://www.douban.com/people/".$douban_xmlns->uid;

	$tmail = $douban_xmlns->uid.'@douban.com';
	$tid = "dtid";
		
	wp_connect_login($douban_id.'|'.$douban_xmlns->uid.'|'.$douban->title.'|'.$douban_url.'|'.$tok['oauth_token'] .'|'.$tok['oauth_token_secret'], $tmail, $tid); 
}

function wp_connect_login($userinfo, $tmail, $tid) {
	global $wptm_connect;
	$userinfo = explode('|', $userinfo);
	if (count($userinfo) < 6) {
		wp_die("An error occurred while trying to contact Sina Connect.");
	} 
	$callback = $_SESSION['wp_callback'];
	if (preg_match("/\b$userinfo[1]\b/i", $wptm_connect['disable_username'])) {
		wp_die("很遗憾，”$userinfo[1]” 被系统保留，请更换微博帐号登录！返回 <a href='$callback'>$callback</a>");
	} 

	$wpurl = get_bloginfo('wpurl');
	$user = get_user_by_user_login($userinfo[1]);
	$wpuid = $user['ID'];
	$user_email = $user['user_email'];
	$user_url = $user['user_url'];
	$tdata = get_user_meta($wpuid, 'tdata', true);
	$bind = get_user_meta($wpuid, 'bind', true);
	if($bind) {
		$bind = array_filter($bind);
	}
	$sina = $bind['sina'];
	$qq = $bind['qq'];
	$netease = $bind['netease'];
	$douban = $bind['douban'];
	$t = strtolower($_SESSION['wp_go_login']);
	$password = wp_generate_password();
	if ($wpuid) {
		if ($bind) {
			if ($$t) {
				$password = $user['user_pass'];
			} else {
				wp_die("很遗憾，”$userinfo[1]” 已被 $user_email 绑定，您可以使用该用户 <a href='$wpurl/wp-login.php'>登录</a> 并到‘我的资料’页绑定同名帐号，或者更换微博帐号，或者 <a href='$wpurl/wp-login.php?action=lostpassword'>找回密码</a>！<br />返回: <a href='$callback'>$callback</a>");
			} 
		} 
	} else {
		$wpuid = '';
	}
	
	if(!$user_url) {
	    $user_url = $userinfo[3];
	}

	$userdata = array(
		'ID' => $wpuid,
		'user_pass' => $password,
		'user_login' => $userinfo[1],
		'display_name' => $userinfo[2],
		'user_url' => $user_url,
		'user_email' => $tmail);

	if (!function_exists('wp_insert_user')) {
		include_once(ABSPATH . WPINC . '/registration.php');
	}

	if ($userinfo[0]) {
		if($tmail != $user_email) {
			$wpuid = wp_insert_user($userdata);
		}
		if(!$bind) {
		    update_usermeta($wpuid, 'bind', array($t => '1'));
		}
	}

	if ($wpuid) {
		update_usermeta($wpuid, $tid, $userinfo[0]);
		$t_array = array (
			"tid" => $tid,
			"oauth_token" => $userinfo[4],
			"oauth_token_secret" => $userinfo[5]);
		update_usermeta($wpuid, 'tdata', $t_array);
	} 

	if ($wpuid) {
		wp_set_auth_cookie($wpuid, true, false);
		wp_set_current_user($wpuid);
	} 
}

add_filter('user_contactmethods', 'wp_connect_author_page');
function wp_connect_author_page($input) {
	// add
	$input['imqq'] = 'QQ';
	// del
	unset($input['yim']);
	unset($input['aim']);
	unset($input['jabber']);
	return $input;
}

add_action( 'show_user_profile', 'wp_connect_profile_fields' );
add_action( 'edit_user_profile', 'wp_connect_profile_fields' );
add_action( 'personal_options_update', 'wp_connect_save_profile_fields' );
add_action( 'edit_user_profile_update', 'wp_connect_save_profile_fields' );

function wp_connect_profile_fields( $user ) {
	global $user_id;
    $bind = get_user_meta($user_id, 'bind', true);
?>
<h3>微博登录</h3>
<table class="form-table">
<tr>
	<th>同名帐号</th>
	<td><input name="sina" type="checkbox" value="1" <?php if($bind['sina']) echo "checked"; ?> />新浪微博 <input name="qq" type="checkbox" value="1" <?php if($bind['qq']) echo "checked"; ?> />腾讯微博 <input name="netease" type="checkbox" value="1" <?php if($bind['netease']) echo "checked"; ?> />网易微博 <input name="douban" type="checkbox" value="1" <?php if($bind['douban']) echo "checked"; ?> />豆瓣帐号 <input name="without" type="checkbox" value="1" <?php if($bind['without']) echo "checked"; ?> />都不同名<br /><span class="description">提示: 微博帐号跟用户名相同时请勾选</span></td>
</tr>
</table>
<?php
}

function wp_connect_save_profile_fields( $user_id ) {

if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	$bind = array(
	'qq' => $_POST['qq'],
	'sina' => $_POST['sina'],
	'netease' => $_POST['netease'],
	'douban' => $_POST['douban'],
	'without' => $_POST['without']);
    update_usermeta( $user_id, 'bind', $bind );
}

add_filter("get_avatar", "wp_connect_avatar",10,4);
function wp_connect_avatar($avatar, $email = '', $size = '32') {
	global $comment,$wptm_connect;
	if (is_object($comment)) {
		$email = $comment -> user_id;
		$comment_email = $comment -> comment_author_email;
	} 
	if (is_object($email)) {
		$email = $email -> user_id;
	} 
	if (preg_match("/@t.sina.com.cn/i", $comment_email)) {
		if ($stid = get_usermeta($email, 'stid')) {
			$out = 'http://tp3.sinaimg.cn/' . $stid . '/50/1.jpg';
			$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
			return $avatar;
		} 
	} 
	if (preg_match("/@t.qq.com/i", $comment_email)) {
		if ($qtid = get_usermeta($email, 'qtid')) {
			$out = $qtid . '/40';
			$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
			return $avatar;
		} 
	} 
	if (preg_match("/@t.163.com/i", $comment_email) && $wptm_connect['netease_avatar']) {
		if ($ntid = get_usermeta($email, 'ntid')) {
			$out = $ntid;
			$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
			return $avatar;
		} 
	} 
	if (preg_match("/@douban.com/i", $comment_email)) {
		if ($dtid = get_usermeta($email, 'dtid')) {
			$out = 'http://t.douban.com/icon/u' . $dtid . '-1.jpg';
			$avatar = "<img alt='' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
			return $avatar;
		} 
	} else {
		return $avatar;
	} 
} 

add_action('comment_post', 'wp_connect_comment',1000);
function wp_connect_comment($id){
	global $wptm_connect;
	$comment_post_id = $_POST['comment_post_ID'];
	
	if(!$comment_post_id){
		return;
	}
	$comments = get_comment($id);
	$stid = get_user_meta($comments->user_id, 'stid',true);
	$qtid = get_user_meta($comments->user_id, 'qtid',true);
	$ntid = get_user_meta($comments->user_id, 'ntid',true);
	$dtid = get_user_meta($comments->user_id, 'dtid',true);
	$tdata = get_user_meta($comments->user_id, 'tdata',true);
	
	$content = strip_tags($comments->comment_content);
	$link = get_permalink($comment_post_id)."#comment-".$id;

    require_once('OAuth/OAuth.php');
	if($stid){
		if($_POST['comment_to_sina']){
			include_once('OAuth/sina_OAuth.php');
			$to = new sinaClient(SINA_APP_KEY, SINA_APP_SECRET,$tdata['oauth_token'], $tdata['oauth_token_secret']);
            if($wptm_connect['sina_username']) { $content = '@'.$wptm_connect['sina_username'].' '.$content; }
			$status = wp_status($content, $link, 140, 1);
			$result = $to -> update($status);
		}
	}
	if($qtid){
		if($_POST['comment_to_qq']){
			include_once('OAuth/qq_OAuth.php');
	        $to = new qqClient(QQ_APP_KEY, QQ_APP_SECRET,$tdata['oauth_token'], $tdata['oauth_token_secret']);
            if($wptm_connect['qq_username']) { $content = '@'.$wptm_connect['qq_username'].' '.$content; }
			$status = wp_status($content, $link, 140, 1);
	        $result = $to -> update($status);
		}
	}
	if($ntid){
		if($_POST['comment_to_netease']){
			include_once('OAuth/netease_OAuth.php');
			$to = new neteaseClient(APP_KEY, APP_SECRET,$tdata['oauth_token'], $tdata['oauth_token_secret']);
            if($wptm_connect['netease_username']) { $content = '@'.$wptm_connect['netease_username'].' '.$content; }
			$status = wp_status($content, $link, 163);
			$result = $to -> update($status);
		}
	}
	if($dtid){
		if($_POST['comment_to_douban']){
			include_once('OAuth/douban_OAuth.php');
			$to = new doubanClient(DOUBAN_APP_KEY, DOUBAN_APP_SECRET,$tdata['oauth_token'], $tdata['oauth_token_secret']);
			$status = wp_status($content, $link, 128);
			$result = $to -> update($status);
		}
	}
}

function get_user_by_meta_value($meta_key, $meta_value) { // 获得user_id
	global $wpdb;
	$sql = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s' AND meta_value = '%s'";
	return $wpdb -> get_var($wpdb -> prepare($sql, $meta_key, $meta_value));
}

function get_user_by_user_login($user_login) { // 获得user_value
	global $wpdb;
	$row = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '$user_login'");
	$userinfo = array('ID' => $row->ID, 'user_pass' => $row->user_pass, 'user_email' => $row->user_email, 'user_url' => $row->user_url);
	return $userinfo;
}

if(!function_exists('connect_login_form_login')){
	add_action("login_form_login", "connect_login_form_login");
	add_action("login_form_register", "connect_login_form_login");
	function connect_login_form_login(){
		if(is_user_logged_in()){
			$redirect_to = admin_url('profile.php');
			wp_safe_redirect($redirect_to);
		}
	}
}
?>
<?php
include_once(dirname(__FILE__) . '/config.php');
// 同步列表
function wp_update_list($title, $postlink, $pic, $account) {
	global $wptm_options;
	require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
	if ($wptm_options['t_cn']) { // 是否使用t.cn短网址
		$t_cn = get_t_cn($postlink);
		if ($wptm_options['t_cn_twitter']) { // 只用于Twitter
			$t_url = $t_cn;
		} else {
			$postlink = $t_url = $t_cn;
		} 
	}
	$twitter = wp_status($title, $t_url, 140);
	$status = wp_status($title, $postlink, 140);
	$status1 = wp_status($title, $postlink, 128);
	$status2 = wp_status($title, $postlink, 140, 1);
	$status3 = wp_status($title, $postlink, 200);
	$status4 = wp_status($title, $postlink, 200, 1);
	if($account['qq']) { wp_update_t_qq($account['qq'], $status2, $pic); } //140*
	if($account['sina']) { wp_update_t_sina($account['sina'], $status2, $pic); } //140*
	if($account['netease']) { wp_update_t_163($account['netease'], $status, $pic); } //163
	if($account['twitter'] || $account['twitter_oauth']) { wp_update_twitter($twitter); } //140
	if($account['sohu']) { wp_update_t_sohu($account['sohu'], $status4); } //+
	if($account['renren']) { wp_update_renren($account['renren'], $status); } //140
	if($account['kaixin001']) { wp_update_kaixin001($account['kaixin001'], $status3); } //380
	if($account['digu']) { wp_update_digu($account['digu'], $status); } //140
	if($account['douban']) { wp_update_douban($account['douban'], $status1); } //128
	if($account['baidu']) { wp_update_baidu($account['baidu'], $status2); } //140*
	if($account['fanfou']) { wp_update_fanfou($account['fanfou'], $status); } //140
	if($account['renjian']) { wp_update_renjian($account['renjian'], $status4); } //+
	if($account['zuosa']) { wp_update_zuosa($account['zuosa'], $status); } //140
	if($account['follow5']) { wp_update_follow5($account['follow5'], $status4); } //200*
}
// 腾讯微博
function wp_update_t_qq($qq, $status, $pic) {
	if (!class_exists('qqOAuth')) {
		include dirname(__FILE__) . '/OAuth/qq_OAuth.php';
	} 
	$to = new qqClient(QQ_APP_KEY, QQ_APP_SECRET, $qq['oauth_token'], $qq['oauth_token_secret']);
	if ($pic) {
		$result = $to -> upload($status , $pic);
	} else {
		$result = $to -> update($status);
	}
} 
// 新浪微博
function wp_update_t_sina($sina, $status, $pic) {
	if (!class_exists('sinaOAuth')) {
		include dirname(__FILE__) . '/OAuth/sina_OAuth.php';
	} 
	$to = new sinaClient(SINA_APP_KEY, SINA_APP_SECRET, $sina['oauth_token'], $sina['oauth_token_secret']);
	if ($pic) {
		$result = $to -> upload($status , $pic);
	} else {
		$result = $to -> update($status);
	} 
} 
// 网易微博
function wp_update_t_163($netease, $status, $pic) {
	if (!class_exists('neteaseOAuth')) {
		include dirname(__FILE__) . '/OAuth/netease_OAuth.php';
	} 
	$to = new neteaseClient(APP_KEY, APP_SECRET, $netease['oauth_token'], $netease['oauth_token_secret']);
	if ($pic) {
		$result = $to -> upload($status , $pic);
	} else {
		$result = $to -> update($status);
	}
} 
// Twitter
function wp_update_twitter($status) {
	global $wptm_options;
	if ($wptm_options['enable_proxy']) {
		$twitter = get_option('wptm_twitter');
		$api_url = 'http://smyxapi.appspot.com/api/statuses/update.xml';
		if ($wptm_options['custom_proxy']) {
			$api_url = $wptm_options['custom_proxy'];
		} 
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, "$api_url");
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "status=$status");
		curl_setopt($curl_handle, CURLOPT_USERPWD, "{$twitter['username']}:{$twitter['password']}");
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
	} else {
		if (!class_exists('twitterOAuth')) {
			include dirname(__FILE__) . '/OAuth/twitter_OAuth.php';
		} 
		$twitter = get_option('wptm_twitter_oauth');
		$to = new twitterClient(T_APP_KEY, T_APP_SECRET, $twitter['oauth_token'], $twitter['oauth_token_secret']);
		$result = $to -> update($status);
	} 
} 
// 豆瓣
function wp_update_douban($douban, $status) {
	if (!class_exists('doubanOAuth')) {
		include dirname(__FILE__) . '/OAuth/douban_OAuth.php';
	} 
	$to = new doubanClient(DOUBAN_APP_KEY, DOUBAN_APP_SECRET, $douban['oauth_token'], $douban['oauth_token_secret']);
	$result = $to -> update($status);
} 
// 嘀咕
function wp_update_digu($digu, $status) {
	$api_url = 'http://api.minicloud.com.cn/statuses/update.xml';
	$body = array('content' => $status);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$digu['username']}:{$digu['password']}"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 饭否
function wp_update_fanfou($fanfou, $status) {
	$api_url = 'http://api.fanfou.com/statuses/update.xml';
	$body = array('status' => $status);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$fanfou['username']}:{$fanfou['password']}"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 搜狐微博
function wp_update_t_sohu($sohu, $status) {
	$api_url = 'http://api.t.sohu.com/statuses/update.xml';
	$body = array('status' => $status);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$sohu['username']}:{$sohu['password']}"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 人间网
function wp_update_renjian($renjian, $status) {
	$api_url = 'http://api.renjian.com/statuses/update.xml';
	$body = array('text' => $status);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$renjian['username']}:{$renjian['password']}"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 做啥网
function wp_update_zuosa($zuosa, $status) {
	$api_url = 'http://api.zuosa.com/statuses/update.xml';
	$body = array('status' => $status);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$zuosa['username']}:{$zuosa['password']}"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// Follow5
function wp_update_follow5($follow5, $status) {
	$api_url = 'http://api.follow5.com/api/statuses/update.xml?api_key=C1D656C887DB993D6FB6CA4A30754ED8';
	$body = array('status' => $status, 'source' => 'qq_wp_follow5');
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$follow5['username']}:{$follow5['password']}"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 人人网
function wp_update_renren($renren, $status) {
	$cookie = tempnam('./tmp', 'renren');
	$ch = wp_getCurl($cookie, "http://passport.renren.com/PLogin.do");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=' . urlencode($renren["username"]) . '&password=' . urlencode($renren["password"]) . '&autoLogin=true&origURL=http%3A%2F%2Fwww.renren.com%2FHome.do&domain=renren.com');
	$str = wp_update_result($ch);
	$pattern = "/get_check:'([^']+)'/";
	preg_match($pattern, $str, $matches);
	$get_check = $matches[1];
	$ch = wp_getCurl($cookie, "http://status.renren.com/doing/update.do");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'c=' . urlencode($status) . '&raw=' . urlencode($status) . '&isAtHome=1&publisher_form_ticket=' . $get_check . '&requestToken=' . $get_check);
	curl_setopt($ch, CURLOPT_REFERER, 'http://status.renren.com/ajaxproxy.htm');
	$ret = wp_update_result($ch);
} 
// 开心网
function wp_update_kaixin001($kaixin001, $status) {
	$cookie = tempnam('./tmp', 'kaixin001');
	$ch = wp_getCurl($cookie, "http://wap.kaixin001.com/home/?id=");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=' . urlencode($kaixin001["username"]) . '&password=' . urlencode($kaixin001["password"]) . '&remember=1&from=&refuid=0&refcode=&bind=&gotourl=&login=+%E7%99%BB+%E5%BD%95+');
	$str = wp_update_result($ch);
	$pattern = "/state.php\?verify=([^\"]+)\"/";
	preg_match($pattern, $str, $matches);
	$verify = $matches[1];
	$ch = wp_getCurl($cookie, "http://wap.kaixin001.com/home/state_submit.php?verify=" . $verify);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'state=' . urlencode($status));
	curl_setopt($ch, CURLOPT_REFERER, '   http://wap.kaixin001.com/home/');
	$ret = wp_update_result($ch);
}
// 百度说吧
function wp_update_baidu($baidu, $status) {
	$cookie = tempnam('./tmp', 'baidu');
	$ch = wp_getCurl($cookie, "http://t.baidu.com/userlogin");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'UserLoginForm%5Busername%5D=' . urlencode($baidu["username"]) . '&UserLoginForm%5Bpassword%5D=' . urlencode($baidu["password"]) . '&UserLoginForm%5BrememberMe%5D=0');
	curl_setopt($ch, CURLOPT_REFERER, 'http://t.baidu.com/');
	$str = wp_update_result($ch);

	preg_match('/logmt=(.*);.*/U', $str, $matches);
	$verify = $matches[1];
	$ch = wp_getCurl($cookie, 'https://passport.baidu.com/logm?tpl=sn&t=' . $verify . '&u=http%3A%2F%2Ft.baidu.com%2F');
	$ret = wp_update_result($ch);

	$ch = wp_getCurl($cookie, "http://t.baidu.com/message/post?");
	$params = 'm_content=' . $status . '&pic_id=0&pic_filename=&pic_id_water=0&pic_filename_water=';
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_REFERER, 'http://t.baidu.com/');
	$out = wp_update_result($ch);
}

function wp_getCurl($cookie, $url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12');
	curl_setopt($ch, CURLOPT_POST, 1);
	return $ch;
}

function wp_update_result($ch) {
	$str = curl_exec($ch);
	curl_close($ch);
	unset($ch);
	return $str;
}

?>
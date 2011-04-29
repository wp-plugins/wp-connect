<?php
include_once(dirname(__FILE__) . '/config.php');
// 同步列表
function wp_update_list($title, $postlink, $pic, $account) {
	global $wptm_options;
	require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
	if ($wptm_options['t_cn']) { // 是否使用t.cn短网址
		$t_url = get_t_cn($postlink);
		if (!$wptm_options['t_cn_twitter']) { // 只用于Twitter
			$postlink = $t_url;
		} 
	} else {
	    $t_url = $postlink;
	}
	$output = array('qq', 'sina');
	$twitter = wp_status($title, $t_url, 140);
	$status = wp_status($title, $postlink, 140);
	$status1 = wp_status($title, $postlink, 128);
	$status2 = wp_status($title, $postlink, 140, 1);
	$status3 = wp_status($title, $postlink, 200);
	$status4 = wp_status($title, $postlink, 200, 1);
	$api_title = wp_status($title, '', 200, 1);
    if(!$wptm_options['api'] && $wptm_options['enable_proxy'] && $account['twitter']) {
    	$text = "twitter={$twitter}&t1={$account['twitter']['oauth_token']}&t2={$account['twitter']['oauth_token_secret']}";
		wp_update_api($text);
	} else {
	    if($account['twitter']) { wp_update_twitter($account['twitter'], $twitter); } //140
	}
	if($wptm_options['api'] && ($account['qq'] || $account['sina'] || $account['netease'] || $account['sohu'] || $account['twitter'] || $account['douban'])) {
    	$text = "title={$api_title}&postlink={$postlink}&pic={$pic}&q1={$account['qq']['oauth_token']}&q2={$account['qq']['oauth_token_secret']}&s1={$account['sina']['oauth_token']}&s2={$account['sina']['oauth_token_secret']}&sh1={$account['sohu']['oauth_token']}&sh2={$account['sohu']['oauth_token_secret']}&n1={$account['netease']['oauth_token']}&n2={$account['netease']['oauth_token_secret']}&t1={$account['twitter']['oauth_token']}&t2={$account['twitter']['oauth_token_secret']}&d1={$account['douban']['oauth_token']}&d2={$account['douban']['oauth_token_secret']}";
		wp_update_api($text);
	} else {
		if($account['sina']) { $output['sina'] = wp_update_t_sina($account['sina'], $status2, $pic); } //140*
		if($account['qq']) { $output['qq'] = wp_update_t_qq($account['qq'], $status2, $pic); } //140*
		if($account['netease']) { wp_update_t_163($account['netease'], $status, $pic); } //163
		if($account['sohu']) { wp_update_t_sohu($account['sohu'], $status4, $pic); } //+
		if($account['twitter']) { wp_update_twitter($account['twitter'], $twitter); } //140
		if($account['douban']) { wp_update_douban($account['douban'], $status1); } //128
	}
	if($account['renren']) { wp_update_renren($account['renren'], $status); } //140
	if($account['kaixin001']) { wp_update_kaixin001($account['kaixin001'], $status3); } //380
	if($account['digu']) { wp_update_digu($account['digu'], $status); } //140
	if($account['baidu']) { wp_update_baidu($account['baidu'], $status2); } //140*
	if($account['fanfou']) { wp_update_fanfou($account['fanfou'], $status); } //140
	if($account['renjian']) { wp_update_renjian($account['renjian'], $status4, $pic); } //+
	if($account['zuosa']) { wp_update_zuosa($account['zuosa'], $status); } //140
	if($account['wbto']) { wp_update_wbto($account['wbto'], $status2, $pic); } //140+
	if($account['follow5']) { wp_update_follow5($account['follow5'], $status4, $pic); } //200*
	if($account['leihou']) { wp_t_update($account['leihou'], $status, $pic); } //140
	return $output;
}
// 字符长度(一个汉字代表一个字符，两个字母代表一个字符)
function wp_strlen($text) {
	$a = mb_strlen($text, 'UTF-8');
	$b = strlen($text);
	$c = $b / 3 ;
	$d = ($a + $b) / 4;
	if ($a == $b) { // 纯英文、符号、数字
		return $b / 2; 
	} elseif ($a == $c) { // 纯中文
		return $a;
	} elseif ($a != $c) { // 混合
		return $d;
	} 
}
// 截取字数
function wp_status($content, $url, $length, $num = '') {
	$temp_length = (mb_strlen($content, 'utf-8')) + (mb_strlen($url, 'utf-8'));
	if ($num) {
		$temp_length = (wp_strlen($content)) + (wp_strlen($url));
	} 
	if ($temp_length > $length - 3) { // ...
		$chars = $length - 6 - mb_strlen($url, 'utf-8'); // ' - '
		if ($num) {
			$chars = $length - 3 - wp_strlen($url);
			$str = mb_substr($content, 0, $chars, 'utf-8');
			preg_match_all("/([\x{0000}-\x{00FF}]){1}/u", $str, $half_width); // 半角字符
			$chars = $chars + count($half_width[0])/2;
		} 
		$content = mb_substr($content, 0, $chars, 'utf-8');
		$content = $content . "...";
	} 
	$status = $content . ' ' . $url;
	return trim($status);
}

function wp_in_array($a, $b) {
	$arrayA = explode(',', $a);
	$arrayB = explode(',', $b);
	foreach($arrayB as $val) {
		if (in_array($val, $arrayA))
			return true;
	} 
	return false;
}

function wp_urlencode($url) {
	$a = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D');
	$b = array("!", "*", "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "#", "[", "]");
	$url = str_replace($a, $b, urlencode($url));
	return strtolower($url);
}

if (!function_exists('get_t_cn')) {
// 以下代码来自 t.cn 短域名WordPress 插件
	function get_t_cn($long_url) {
		$api_url = 'http://api.t.sina.com.cn/short_url/shorten.json?source=744243473&url_long=' . $long_url;
		$request = new WP_Http;
		$result = $request -> request($api_url);
		$result = $result['body'];
		$result = json_decode($result);
		return $result[0] -> url_short;
	} 
}

// api
function wp_update_api($status) {
	$api_url = 'http://www.smyx.net/apps/api.php';
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $status));
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
	return $result['data']['id'];
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
	return $result['id'];
} 
// 搜狐微博
function wp_update_t_sohu($sohu, $status, $pic) {
	if (!class_exists('sohuOAuth')) {
		include dirname(__FILE__) . '/OAuth/sohu_OAuth.php';
	} 
	$to = new sohuClient(SOHU_APP_KEY, SOHU_APP_SECRET, $sohu['oauth_token'], $sohu['oauth_token_secret']);
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
function wp_update_twitter($twitter, $status) {
	if (!class_exists('twitterOAuth')) {
		include dirname(__FILE__) . '/OAuth/twitter_OAuth.php';
	}
	$to = new twitterClient(T_APP_KEY, T_APP_SECRET, $twitter['oauth_token'], $twitter['oauth_token_secret']);
	$result = $to -> update($status);
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
// 人间网
function wp_update_renjian($renjian, $status, $pic) {
	$api_url = 'http://api.renjian.com/v2/statuses/create.xml';
	$body = array();
	$body['text'] = $status;
	if ($pic) {
		$body['status_type'] = "PICTURE";
		$body['url'] = $pic;
	}
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
function wp_update_follow5($follow5, $status, $pic) {
	$api_url = 'http://api.follow5.com/api/statuses/update.xml?api_key=C1D656C887DB993D6FB6CA4A30754ED8';
	$body = array();
	$body['source'] = 'qq_wp_follow5';
	$body['status'] = $status;
	if ($pic) {
		$body['link'] = $pic;
	} 
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$follow5['username']}:{$follow5['password']}"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}
// wbto
function wp_update_wbto($wbto, $status, $pic) {
	$body = array();
	$body['source'] = 'wordpress';
	$body['content'] = urlencode($status);
	if ($pic) {
		$body['imgurl'] = $pic;
		$api_url = 'http://wbto.cn/api/upload.json';
	} else {
	    $api_url = 'http://wbto.cn/api/update.json';
	}
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$wbto['username']}:{$wbto['password']}"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}

function wp_t_update($user, $status, $pic) {
	if ($pic) {
		$file = file_get_contents($pic);
		$filename = reset(explode('?' , basename($pic)));
		$mime = wp_get_image_mime($pic);
	} 
	$boundary = uniqid('------------------');
	$MPboundary = '--' . $boundary;
	$endMPboundary = $MPboundary . '--';
	if ($pic) {
		$multipartbody .= $MPboundary . "\r\n";
		$multipartbody .= 'Content-Disposition: form-data; name="pic"; filename="' . $filename . '"' . "\r\n";
		$multipartbody .= "Content-Type: {$mime}\r\n\r\n";
		$multipartbody .= $file . "\r\n";
	} 
	$multipartbody .= $MPboundary . "\r\n";
	$multipartbody .= 'content-disposition: form-data; name="status"' . "\r\n\r\n";
	$multipartbody .= $status . "\r\n";
	$multipartbody .= "\r\n" . $endMPboundary;
	// 雷猴
	wp_curl_multi("leihou.com", $user, $multipartbody, $boundary);
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

function wp_curl_multi($url, $user, $multipartbody, $boundary) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://{$url}/statuses/update.json");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $multipartbody);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=$boundary"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERPWD, $user["username"] . ':' . $user["password"]);
	$content = curl_exec($ch);
	return $content;
}

function wp_get_image_mime($file) {
	$ext = strtolower(pathinfo($file , PATHINFO_EXTENSION));
	switch ($ext) {
		case 'jpg':
		case 'jpeg':
			$mime = 'image/jpg';
			break;
		case 'png';
			$mime = 'image/png';
			break;
		case 'gif';
		default:
			$mime = 'image/gif';
			break;
	} 
	return $mime;
} 

?>
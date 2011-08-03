<?php
include_once(dirname(__FILE__) . '/config.php');
// 同步列表
function wp_update_list($title, $postlink, $pic, $account) {
	global $wptm_options;
	require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
	$sina = wp_status($title, urlencode($postlink), 140, 1);
	$output = array();
	if($account['sina']) { $ms = wp_update_t_sina($account['sina'], $sina, $pic); } //140*
	$output['sina'] = $ms['id'];
    // 是否使用t.cn短网址
	if ($wptm_options['t_cn']) {
		$postlink = get_t_cn(urlencode($postlink));
	}
	$status = wp_status($title, $postlink, 140); //网易/人人/饭否/做啥/雷猴
	$status2 = wp_status($title, $postlink, 200, 1); //搜狐/follow5
	$qq = wp_status($title, $postlink, 140, 1); //腾讯
	$kaixin001 = wp_status($title, $postlink, 200); //开心
	$digu = wp_status($title, urlencode($postlink), 140); //嘀咕
	$twitter = wp_status($title, wp_urlencode($postlink), 140); //Twitter
    $wbto = wp_status($title, $postlink, 140, 1); //微博通
    $baidu = wp_status($title, urlencode($postlink), 140, 1); //百度
	$douban = wp_status($title, $postlink, 128); //豆瓣
	$renjian = wp_status($title, urlencode($postlink), 200, 1); //人间网

	if($account['qq']) { $output['qq'] = wp_update_t_qq($account['qq'], $qq, $pic); } //140*
	if($account['netease']) { wp_update_t_163($account['netease'], $status, $pic); } //163
	if($account['sohu']) { wp_update_t_sohu($account['sohu'], $status2, $pic); } //+
	if($account['douban']) { wp_update_douban($account['douban'], $douban); } //128
	if($account['digu']) { wp_update_digu($account['digu'], $digu); } //140
	if($account['fanfou']) { wp_update_fanfou($account['fanfou'], $status); } //140
	if($account['renjian']) { wp_update_renjian($account['renjian'], $renjian, $pic); } //+
	if($account['zuosa']) { wp_update_zuosa($account['zuosa'], $status); } //140
	if($account['wbto']) { wp_update_wbto($account['wbto'], $wbto, $pic); } //140+
	if($account['follow5']) { wp_update_follow5($account['follow5'], $status2, $pic); } //200*
	if($account['twitter']) { wp_update_twitter($account['twitter'], $twitter); }
	if($account['renren']) { wp_update_renren($account['renren'], $status); } //140
	if($account['kaixin001']) { wp_update_kaixin001($account['kaixin001'], $kaixin001); } //380
	if($account['baidu']) { wp_update_baidu($account['baidu'], $baidu); } //140*
	//if($account['leihou']) { wp_update_leihou($account['leihou'], $status, $pic); } //140
	return $output;
}

if (!function_exists('mb_substr')) {
	function mb_substr($str, $start = 0, $length = 0, $encode = 'utf-8') {
		$encode_len = ($encode == 'utf-8') ? 3 : 2;
		for($byteStart = $i = 0; $i < $start; ++$i) {
			$byteStart += ord($str{$byteStart}) < 128 ? 1 : $encode_len; 
			if ($str{$byteStart} == '') return '';
		} 
		for($i = 0, $byteLen = $byteStart; $i < $length; ++$i)
		$byteLen += ord($str{$byteLen}) < 128 ? 1 : $encode_len;
		return substr($str, $byteStart, $byteLen - $byteStart);
	}
} 
if (!function_exists('mb_strlen')) {
	function mb_strlen($str, $encode = 'utf-8') {
		return ($encode == 'utf-8') ? strlen(utf8_decode($str)) : strlen($str);
	}
} 
// 字符长度(一个汉字代表一个字符，两个字母代表一个字符)
function wp_strlen($text) {
	$a = mb_strlen($text, 'utf-8');
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
	if ($url) {
		$length = $length - 4; // ' - '
		$url = ' '.$url;
	}
	if ($temp_length > $length) {
		$chars = $length - 3 - mb_strlen($url, 'utf-8'); // '...'
		if ($num) {
			$chars = $length - wp_strlen($url);
			$str = mb_substr($content, 0, $chars, 'utf-8');
			preg_match_all("/([\x{0000}-\x{00FF}]){1}/u", $str, $half_width); // 半角字符
			$chars = $chars + count($half_width[0])/2;
		} 
		$content = mb_substr($content, 0, $chars, 'utf-8');
		$content = $content . "...";
	} 
	$status = $content . $url;
	return trim($status);
}

function wp_replace($str) {
	$a = array('&#160;', '&#038;', '&#8211;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt', '&ldquo;', '&rdquo;', '&nbsp;', 'Posted by Wordmobi');
	$b = array(' ', '&', '-', '‘', '’', '“', '”', '&', '<', '>', '“', '”', ' ', '');
	$str = str_replace($a, $b, strip_tags($str));
	return trim($str);
}

function wp_urlencode($url) {
	$a = array('+', '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
	$b = array(" ", "!", "*", "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
	$url = str_replace($a, $b, urlencode($url));
	return strtolower($url);
}

// 匹配视频、图片
function wp_multi_media_url($content) {
	preg_match_all('/<embed[^>]+src=[\"\']{1}(([^\"\'\s]+)\.swf)[\"\']{1}[^>]+>/isU', $content, $video);
	preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"].*>/isU', $content, $image);
	$v_sum = count($video[1]);
	$p_sum = count($image[1]);
	if ($v_sum > 0) { //优先级 视频 > 图片
		$url = array("video", $video[1][0]);
	} elseif ($p_sum > 0) {
		$url = array("image", $image[1][0]);
	} 
	return $url;
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

function key_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = ($key) ? md5($key) : '';
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), - $ckey_length)) : '';

	$cryptkey = $keya . md5($keya . $keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	} 

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	} 

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	} 

	if ($operation == 'DECODE') {
		if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		} 
	} else {
		return $keyc . str_replace('=', '', base64_encode($result));
	} 
} 

function key_encode($string) {
	return key_authcode($string, 'ENCODE', 'WP-CONNECT');
} 

function key_decode($string) {
	return key_authcode($string, 'DECODE', 'WP-CONNECT');
} 

if (!function_exists('get_t_cn')) {
// 以下代码来自 t.cn 短域名WordPress 插件
	function get_t_cn($long_url) {
		$source = SINA_APP_KEY;
		$api_url = 'http://api.t.sina.com.cn/short_url/shorten.json?source='.$source.'&url_long='.$long_url;
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
function wp_update_t_qq($qq, $status, $value) {
	if (!class_exists('qqOAuth')) {
		include dirname(__FILE__) . '/OAuth/qq_OAuth.php';
	} 
	$to = new qqClient(QQ_APP_KEY, QQ_APP_SECRET, $qq['oauth_token'], $qq['oauth_token_secret']);
	$result = $to -> update($status, $value);
	return $result['data']['id'];
}
// 新浪微博
function wp_update_t_sina($sina, $status, $value) {
	if (!class_exists('sinaOAuth')) {
		include dirname(__FILE__) . '/OAuth/sina_OAuth.php';
	} 
	$to = new sinaClient(SINA_APP_KEY, SINA_APP_SECRET, $sina['oauth_token'], $sina['oauth_token_secret']);
    $result = $to -> update($status, $value);
	return $result;
} 
// 搜狐微博
function wp_update_t_sohu($sohu, $status, $value) {
	if (!class_exists('sohuOAuth')) {
		include dirname(__FILE__) . '/OAuth/sohu_OAuth.php';
	} 
	$to = new sohuClient(SOHU_APP_KEY, SOHU_APP_SECRET, $sohu['oauth_token'], $sohu['oauth_token_secret']);
	$result = $to -> update($status, $value);
}
// 网易微博
function wp_update_t_163($netease, $status, $value) {
	if (!class_exists('neteaseOAuth')) {
		include dirname(__FILE__) . '/OAuth/netease_OAuth.php';
	}
	$to = new neteaseClient(APP_KEY, APP_SECRET, $netease['oauth_token'], $netease['oauth_token_secret']);
	$result = $to -> update($status, $value);
} 
// Twitter
function wp_update_twitter($twitter, $status) {
	global $wptm_options;
	if ($wptm_options['enable_proxy']) {
		$text = "twitter={$status}&t1={$twitter['oauth_token']}&t2={$twitter['oauth_token_secret']}";
		wp_update_api($text);
	} else {
		if (!class_exists('twitterOAuth')) {
			include dirname(__FILE__) . '/OAuth/twitter_OAuth.php';
		}
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
	$api_url = 'http://api.minicloud.com.cn/statuses/update.json';
	$body = array('content' => $status);
	$password = key_decode($digu['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$digu['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 饭否
function wp_update_fanfou($fanfou, $status) {
	$api_url = 'http://api.fanfou.com/statuses/update.json';
	$body = array('status' => $status);
	$password = key_decode($fanfou['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$fanfou['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}
// 人间网
function wp_update_renjian($renjian, $status, $value) {
	$api_url = 'http://api.renjian.com/v2/statuses/create.json';
	$body = array();
	$body['text'] = $status;
	if ($value[0] == "image" && $value[1]) {
		$body['status_type'] = "PICTURE";
		$body['url'] = $value[1];
	}
	$password = key_decode($renjian['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$renjian['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 做啥网
function wp_update_zuosa($zuosa, $status) {
	$api_url = 'http://api.zuosa.com/statuses/update.json';
	$body = array('status' => $status);
	$password = key_decode($zuosa['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$zuosa['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// Follow5
function wp_update_follow5($follow5, $status, $value) {
	$api_url = 'http://api.follow5.com/api/statuses/update.xml?api_key=C1D656C887DB993D6FB6CA4A30754ED8';
	$body = array();
	$body['source'] = 'qq_wp_follow5';
	$body['status'] = $status;
	if ($value[1]) {
		$body['link'] = $value[1];
	} 
	$password = key_decode($follow5['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$follow5['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}
// wbto
function wp_update_wbto($wbto, $status, $value) {
	$body = array();
	$body['source'] = 'wordpress';
	$body['content'] = urlencode($status);
	if ($value[0] == "image" && $value[1]) {
		$body['imgurl'] = $value[1];
		$api_url = 'http://wbto.cn/api/upload.json';
	} else {
	    $api_url = 'http://wbto.cn/api/update.json';
	}
	$password = key_decode($wbto['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$wbto['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}
// 雷猴
//function wp_update_leihou($leihou, $status, $value) {
//	wp_t_update("http://leihou.com/statuses/update.json", $leihou, $status, $value);
//}
// 人人网
function wp_update_renren($renren, $status) {
	$cookie = tempnam('./tmp', 'renren');
	$password = key_decode($renren['password']);
	$ch = wp_getCurl($cookie, "http://passport.renren.com/PLogin.do");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=' . urlencode($renren["username"]) . '&password=' . urlencode($password) . '&autoLogin=true&origURL=http%3A%2F%2Fwww.renren.com%2FHome.do&domain=renren.com');
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
	$password = key_decode($kaixin001['password']);
	$ch = wp_getCurl($cookie, "http://wap.kaixin001.com/home/?id=");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=' . urlencode($kaixin001["username"]) . '&password=' . urlencode($password) . '&remember=1&from=&refuid=0&refcode=&bind=&gotourl=&login=+%E7%99%BB+%E5%BD%95+');
	$str = wp_update_result($ch);
	$pattern = "/state.php\?verify=([^\"]+)\"/";
	preg_match($pattern, $str, $matches);
	$verify = $matches[1];
	$ch = wp_getCurl($cookie, "http://wap.kaixin001.com/records/submit.php?verify=" . $verify . "&url=%2Fhome%2F%3Fid%3D");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'content=' . urlencode($status));
	curl_setopt($ch, CURLOPT_REFERER, '   http://wap.kaixin001.com/home/');
	$ret = wp_update_result($ch);
}
// 百度说吧
function wp_update_baidu($baidu, $status) {
	$cookie = tempnam('./tmp', 'baidu');
	$password = key_decode($baidu['password']);
	$ch = wp_getCurl($cookie, "http://t.baidu.com/userlogin");
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'UserLoginForm%5Busername%5D=' . urlencode($baidu["username"]) . '&UserLoginForm%5Bpassword%5D=' . urlencode($password) . '&UserLoginForm%5BrememberMe%5D=0');
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
/*
function wp_t_update($url, $user, $status, $value) {
	$password = key_decode($user['password']);
	if ($value[0] == "image" && $value[1]) {
		$content = file_get_contents($value[1]);
		$filename = reset(explode('?' , basename($value[1])));
		$mime = wp_get_image_mime($value[1]);
	} 
	$boundary = uniqid('------------------');
	$MPboundary = '--' . $boundary;
	$endMPboundary = $MPboundary . '--';
	if ($value[0] == "image" && $value[1]) {
		$multipartbody .= $MPboundary . "\r\n";
		$multipartbody .= 'Content-Disposition: form-data; name="pic"; filename="' . $filename . '"' . "\r\n";
		$multipartbody .= "Content-Type: {$mime}\r\n\r\n";
		$multipartbody .= $content . "\r\n";
	} 
	$multipartbody .= $MPboundary . "\r\n";
	$multipartbody .= 'content-disposition: form-data; name="status"' . "\r\n\r\n";
	$multipartbody .= $status . "\r\n";
	$multipartbody .= "\r\n" . $endMPboundary;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $multipartbody);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=$boundary"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERPWD, $user["username"] . ':' . $password);
	$ret = curl_exec($ch);
	return $ret;
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
*/
// 社会化分享按钮，共52个
function wp_social_share_title() {
	return array("qzone" => "QQ空间",
		"sina" => "新浪微博",
		"baidu" => "百度搜藏",
		"renren" => "人人网",
		"qq" => "腾讯微博",
		"kaixin001" => "开心网",
		"sohu" => "搜狐微博",
		"hibaidu" => "百度空间",
		"t163" => "网易微博",
		"douban" => "豆瓣",
		"taojianghu" => "淘江湖",
		"msn" => "MSN",
		"buzz" => "谷歌Buzz",
		"qqshuqian" => "QQ书签",
		"tieba" => "百度贴吧",
		"shequ51" => "51社区",
		"shouji" => "手机",
		"zhuaxia" => "抓虾",
		"baishehui" => "搜狐白社会",
		"ifeng" => "凤凰微博",
		"pengyou" => "腾讯朋友",
		"facebook" => "Facebook",
		"twitter" => "Twitter",
		"tianya" => "天涯社区",
		"fanfou" => "饭否",
		"sc115" => "115收藏",
		"feixin" => "飞信",
		"digu" => "嘀咕",
		"follow5" => "Follow5",
		"tongxue" => "同学网",
		"youdao" => "有道书签",
		"google" => "Google",
		"delicious" => "Delicious",
		"digg" => "Digg",
		"yahoo" => "Yahoo!",
		"live" => "微软live",
		"hexun" => "和讯微博",
		"xianguo" => "鲜果",
		"zuosa" => "做啥",
		"shuoke" => "139说客",
		"myspace" => "聚友网",
		"waakee" => "挖客",
		"leshou" => "乐收",
		"mop" => "猫扑推客",
		"cnfol" => "中金微博",
		"douban9" => "豆瓣9点",
		"dream163" => "梦幻人生",
		"taonan" => "淘男网",
		"club189" => "天翼社区",
		"baohe" => "宝盒网",
		"renmaiku" => "人脉库",
		"ushi" => "优士网");
} 

?>
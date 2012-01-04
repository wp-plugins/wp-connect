<?php
include_once(dirname(__FILE__) . '/config.php');
/**
 * 同步列表
 * @since 1.9.10
 */
function wp_update_list($title, $postlink, $pic, $account) {
	global $wptm_options;
	if($pic[0] == 'video' && $pic[1]) { // 是否有视频
		$vurl = $pic[1];
		$url = $postlink;
	} elseif($pic[0] == 'music' && $pic[1]) {
		if($pic[2] && $pic[3]) {
			$vurl = '#'.$pic[1].'#'.$pic[2].' '.$pic[3]; // #歌手# 歌曲 url
		} else {
			$vurl = $pic[1]; // url
		}
		$url = $postlink;
	} else {
		$url = $postlink;
	}
    // 是否使用t.cn短网址
	if ($wptm_options['t_cn']) {
		$url = url_short_t_cn($url);
	}
    // 处理完毕输出链接
	$postlink = trim($vurl.' '.$url);
    // 截取字数
	$status = wp_status($title, $postlink, 140); //网易/人人/饭否/做啥/雷猴
	$status2 = wp_status($title, $postlink, 200, 1); //搜狐/follow5
	$sina = wp_status($title, urlencode($postlink), 140, 1); //新浪
	$qq = wp_status($title, $postlink, 140, 1); //腾讯
	//$kaixin001 = wp_status($title, $postlink, 200); //开心
	$digu = wp_status($title, urlencode($postlink), 140); //嘀咕
	$twitter = wp_status($title, wp_urlencode($postlink), 140); //Twitter
    $wbto = wp_status($title, $postlink, 140, 1); //微博通
	$douban = wp_status($title, $postlink, 128); //豆瓣
	$renjian = wp_status($title, urlencode($postlink), 200, 1); //人间网
    // 开始同步
	require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
	$output = array();
	if($account['sina']) { $ms = wp_update_t_sina($account['sina'], $sina, $pic); $output['sina'] = $ms['mid'];} //140*
	if($account['qq']) { $output['qq'] = wp_update_t_qq($account['qq'], $qq, $pic); } //140*
	if($account['netease']) { wp_update_t_163($account['netease'], $status, $pic); } //163
	if($account['sohu']) { wp_update_t_sohu($account['sohu'], $status2, $pic); } //+
	if($account['douban']) { wp_update_douban($account['douban'], $douban); } //128
	if($account['digu']) { wp_update_digu($account['digu'], $digu); } //140
	if($account['fanfou']) { wp_update_fanfou($account['fanfou'], $status); } //140
	if($account['renjian']) { wp_update_renjian($account['renjian'], $renjian, $pic); } //+
	if($account['zuosa']) { wp_update_zuosa($account['zuosa'], $status); } //140
	if($account['wbto']) { wp_update_wbto($account['wbto'], $wbto, $pic); } //140+
	if($account['tianya']) { wp_update_tianya($account['tianya'], $sina, $pic); } //140*
	if($account['twitter']) { wp_update_twitter($account['twitter'], $twitter); }
	if($account['renren']) { wp_update_renren($account['renren'], $status); } //140
	if($account['kaixin001']) { wp_update_kaixin001($account['kaixin001'], $qq, $pic); } //140+
	return $output;
}
// 自定义函数 start
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

function close_curl() {
	if (!extension_loaded('curl')) {
		return " <span style=\"color:blue\">请在php.ini中打开扩展extension=php_curl.dll</span>";
	} else {
		$func_str = '';
		if (!function_exists('curl_init')) {
			$func_str .= "curl_init() ";
		} 
		if (!function_exists('curl_setopt')) {
			$func_str .= "curl_setopt() ";
		} 
		if (!function_exists('curl_exec')) {
			$func_str .= "curl_exec()";
		} 
		if ($func_str)
			return " <span style=\"color:blue\">不支持 $func_str 等函数，请在php.ini里面的disable_functions中删除这些函数的禁用！</span>";
	} 
} 

function close_socket() {
	if (function_exists('fsockopen')) {
		$fp = 'fsockopen()';
	} elseif (function_exists('pfsockopen')) {
		$fp = 'pfsockopen()';
	} elseif (function_exists('stream_socket_client')) {
		$fp = 'stream_socket_client()';
	} 
	if (!$fp) {
		return " <span style=\"color:blue\">必须支持以下函数中的其中一个： fsockopen() 或者 pfsockopen() 或者 stream_socket_client() 函数，请在php.ini里面的disable_functions中删除这些函数的禁用！</span>";
	} 
} 

function sfsockopen($host, $port, $errno, $errstr, $timeout) {
	if(function_exists('fsockopen')) {
		$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('pfsockopen')) {
		$fp = @pfsockopen($host, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('stream_socket_client'))  {
		$fp = @stream_socket_client($host.':'.$port, $errno, $errstr, $timeout);
	}
	return $fp;
}

function get_url_array($url) {
	return json_decode(get_url_contents($url), true);
}

function get_url_contents($url) {
	if (!close_curl()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	} else {
		$params = array();
		if (@ini_get('allow_url_fopen')) {
			if (function_exists('file_get_contents')) {
				return file_get_contents($url);
			}
			if (function_exists('fopen')) {
				$params['http'] = 'streams';
			}
		} elseif (function_exists('fsockopen')) {
			$params['http'] = 'fsockopen';
		} else {
			return wp_die('没有可以完成请求的 HTTP 传输器，请查看<a href="' . MY_PLUGIN_URL . '/check.php">环境检查</a>');
		}
		$params += array("method" => 'GET',
			"timeout" => 30,
			"sslverify" => false
			);
		return class_http($url, $params);
	}
}

function http_ssl($url) {
	$arrURL = parse_url($url);
	$r['ssl'] = $arrURL['scheme'] == 'https' || $arrURL['scheme'] == 'ssl';
	$is_ssl = isset($r['ssl']) && $r['ssl'];
	if ($is_ssl && !extension_loaded('openssl'))
		return wp_die('您的主机不支持openssl，请查看<a href="' . MY_PLUGIN_URL . '/check.php">环境检查</a>');
}

function class_http($url, $params = array()) {
	if ($params['http']) {
		$class = 'WP_Http_' . ucfirst($params['http']);
	} else {
		if (!close_curl()) {
			$class = 'WP_Http_Curl';
		} else {
			http_ssl($url);
			if (@ini_get('allow_url_fopen') && function_exists('fopen')) {
				$class = 'WP_Http_Streams';
		    } elseif (function_exists('fsockopen')) {
			    $class = 'WP_Http_Fsockopen';
		    } else {
			    return wp_die('没有可以完成请求的 HTTP 传输器，请查看<a href="' . MY_PLUGIN_URL . '/check.php">环境检查</a>');
		    }
		}
	}
	$http = new $class;
	$response = $http -> request($url, $params);
	if (!is_array($response)) {
		$errors = $response -> errors;
		$error = $errors['http_request_failed'][0];
		if (!$error)
			$error = $errors['http_failure'][0];
		wp_die('出错了: ' . $error . '<br /><br />可能是您的主机不支持，请查看<a href="' . MY_PLUGIN_URL . '/check.php">环境检查</a>');
	} 
	return $response['body'];
}

function post_user($username, $password, $pwd) { // $pwd为旧密码
	$username = trim($username);
    $password = trim($password);
	return array($username, (!$username) ? '' : (($password) ? key_encode($password) : $pwd));
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

function key_encode($string, $expiry = 0) {
	return key_authcode($string, 'ENCODE', 'WP-CONNECT', $expiry);
} 

function key_decode($string) {
	return key_authcode($string, 'DECODE', 'WP-CONNECT');
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

function ifab($a, $b) {
	return $a ? $a : $b;
} 

function ifb($a, $b) {
	return $a ? $b : '';
} 

function ifac($a, $b, $c) {
	return $a ? $a : ($b ? $c : '');
} 

function ifabc($a, $b, $c) {
	return $a ? $a : ($b ? $b : $c);
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
	$a = array('+', '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
	$b = array(" ", "!", "*", "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
	$url = str_replace($a, $b, urlencode($url));
	return $url;
}

function wp_replace($str) {
	$a = array('&#160;', '&#038;', '&#8211;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt', '&ldquo;', '&rdquo;', '&nbsp;', 'Posted by Wordmobi');
	$b = array(' ', '&', '-', '‘', '’', '“', '”', '&', '<', '>', '“', '”', ' ', '');
	$str = str_replace($a, $b, strip_tags($str));
	return trim($str);
}
// 自定义函数 end

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

add_action('admin_footer', 'set_admin_footer_define', 1);
add_action('wp_footer', 'set_admin_footer_define', 1);
function set_admin_footer_define() {
	global $wp_version;
	define('IS_ADMIN_FOOTER', true);
	if (version_compare($wp_version, '3.2.1', '>'))  //WordPress V3.3
		echo "<style type=\"text/css\">#wp-admin-bar-user-info .avatar-64 {width:64px}</style>\n";
}

function is_admin_footer() {
	if ( defined('IS_ADMIN_FOOTER'))
		return true;
}

//t.cn短网址
function url_short_t_cn($long_url) {
	$source = SINA_APP_KEY;
	$api_url = 'http://api.t.sina.com.cn/short_url/shorten.json?source=' . $source . '&url_long=' . urlencode($long_url);
	$request = new WP_Http;
	$result = $request -> request($api_url);
	$result = $result['body'];
	$result = json_decode($result, true);
	$url = $result[0]['url_short'];
	if (!$url)
		$url = $long_url;
	return $url;
} 
//兼容旧版
if (!function_exists('get_t_cn')) {
// 以下代码来自 t.cn 短域名WordPress 插件
	function get_t_cn($long_url) {
		return url_short_t_cn(urldecode($long_url));
	}
}

// api
function wp_update_api($status) {
	$api_url = 'http://www.smyx.net/apps/api.php';
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $status));
}
// v2.0
function wp_update_share($mediaUserID, $content, $url) {
	global $wptm_basic;
	require(dirname(__FILE__) . "/class/Denglu.php");
    $api = new Denglu($wptm_basic['appid'], $wptm_basic['appkey'], 'utf-8');
	try {
		return $api -> share( $mediaUserID, $content, $url, '' );
	}
	catch(DengluException $e) {
		wp_die($e->geterrorDescription());
	}
}

// 腾讯微博
function wp_update_t_qq($tok, $status, $value) {
	if (!class_exists('qqOAuth')) {
		include dirname(__FILE__) . '/OAuth/qq_OAuth.php';
	} 
	$to = new qqClient(QQ_APP_KEY, QQ_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$result = $to -> update($status, $value);
	return $result['data']['id'];
}
// 新浪微博
function wp_update_t_sina($tok, $status, $value) {
	if (!class_exists('sinaOAuth')) {
		include dirname(__FILE__) . '/OAuth/sina_OAuth.php';
	} 
	$to = new sinaClient(SINA_APP_KEY, SINA_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
    $result = $to -> update($status, $value);
	return $result;
} 
// 搜狐微博
function wp_update_t_sohu($tok, $status, $value) {
	if (!class_exists('sohuOAuth')) {
		include dirname(__FILE__) . '/OAuth/sohu_OAuth.php';
	} 
	$to = new sohuClient(SOHU_APP_KEY, SOHU_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$result = $to -> update($status, $value);
	return $result;
}
// 网易微博
function wp_update_t_163($tok, $status, $value) {
	if (!class_exists('neteaseOAuth')) {
		include dirname(__FILE__) . '/OAuth/netease_OAuth.php';
	}
	$to = new neteaseClient(APP_KEY, APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$result = $to -> update($status, $value);
	return $result;
} 
// Twitter
function wp_update_twitter($tok, $status, $value = '') {
	global $wptm_options;
	if ($wptm_options['enable_proxy']) {
		$text = "twitter={$status}&pic={$value}&t1={$tok['oauth_token']}&t2={$tok['oauth_token_secret']}";
		wp_update_api($text);
	} else {
		if (!class_exists('twitterOAuth')) {
			include dirname(__FILE__) . '/OAuth/twitter_OAuth.php';
		}
		$to = new twitterClient(T_APP_KEY, T_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
		$result = $to -> update($status, $value);
		return $result;
	}
}
// 豆瓣
function wp_update_douban($tok, $status) {
	if (!class_exists('doubanOAuth')) {
		include dirname(__FILE__) . '/OAuth/douban_OAuth.php';
	} 
	$to = new doubanClient(DOUBAN_APP_KEY, DOUBAN_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$result = $to -> update($status);
	return $result;
} 
// 天涯
function wp_update_tianya($tok, $status, $value) {
	if (!class_exists('tianyaOAuth')) {
		include dirname(__FILE__) . '/OAuth/tianya_OAuth.php';
	}
	$to = new tianyaClient(TIANYA_APP_KEY, TIANYA_APP_SECRET, $tok['oauth_token'], $tok['oauth_token_secret']);
	$result = $to -> update($status, $value);
	return $result;
} 
// 嘀咕
function wp_update_digu($user, $status) {
	$api_url = 'http://api.minicloud.com.cn/statuses/update.json';
	$body = array('content' => $status);
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 饭否
function wp_update_fanfou($user, $status) {
	$api_url = 'http://api.fanfou.com/statuses/update.json';
	$body = array('status' => $status);
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}
// 人间网
function wp_update_renjian($user, $status, $value) {
	$api_url = 'http://api.renjian.com/v2/statuses/create.json';
	$body = array();
	$body['text'] = $status;
	if ($value[0] == "image" && $value[1]) {
		$body['status_type'] = "PICTURE";
		$body['url'] = $value[1];
	}
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
} 
// 做啥网
function wp_update_zuosa($user, $status) {
	$api_url = 'http://api.zuosa.com/statuses/update.json';
	$body = array('status' => $status);
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}
/*
// Follow5
function wp_update_follow5($user, $status, $value) {
	$api_url = 'http://api.follow5.com/api/statuses/update.xml?api_key=C1D656C887DB993D6FB6CA4A30754ED8';
	$body = array();
	$body['source'] = 'qq_wp_follow5';
	$body['status'] = $status;
	if ($value[1]) {
		$body['link'] = $value[1];
	} 
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}
*/
// wbto
function wp_update_wbto($user, $status, $value) {
	$body = array();
	$body['source'] = 'wordpress';
	$body['content'] = rawurlencode($status);
	if ($value[0] == "image" && $value[1]) {
		$body['imgurl'] = $value[1];
		$api_url = 'http://wbto.cn/api/upload.json';
	} else {
	    $api_url = 'http://wbto.cn/api/update.json';
	}
	$password = key_decode($user['password']);
	$headers = array('Authorization' => 'Basic ' . base64_encode("{$user['username']}:$password"));
	$request = new WP_Http;
	$result = $request -> request($api_url , array('method' => 'POST', 'body' => $body, 'headers' => $headers));
}
// 人人网
function wp_update_renren($user, $status) {
	if (function_exists('wp_renren_status') && $user['session_key']) {
		return wp_renren_status($user['session_key'], $status);
	} elseif ($user["username"] && $user['password']) {
		$cookie = tempnam('./tmp', 'renren');
		$password = key_decode($user['password']);
		$ch = wp_getCurl($cookie, "http://passport.renren.com/PLogin.do");
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=' . rawurlencode($user["username"]) . '&password=' . rawurlencode($password) . '&autoLogin=true&origURL=http%3A%2F%2Fwww.renren.com%2FHome.do&domain=renren.com');
		$str = wp_update_result($ch);
		$pattern = "/get_check:'([^']+)'/";
		preg_match($pattern, $str, $matches);
		$get_check = $matches[1];
		$ch = wp_getCurl($cookie, "http://status.renren.com/doing/update.do");
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'c=' . rawurlencode($status) . '&raw=' . rawurlencode($status) . '&isAtHome=1&publisher_form_ticket=' . $get_check . '&requestToken=' . $get_check);
		curl_setopt($ch, CURLOPT_REFERER, 'http://status.renren.com/ajaxproxy.htm');
		$ret = wp_update_result($ch);
	} 
}
// 开心网
function wp_update_kaixin001($user, $status, $pic) {
	if (function_exists('wp_kaixin_status') && $user['session_key']) {
		wp_kaixin_status($user['session_key'], $status, $pic); 
	}
}

function wp_getCurl($cookie, $url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
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
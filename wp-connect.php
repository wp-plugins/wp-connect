<?php
/*
Plugin Name: WordPress连接微博
Author: 水脉烟香
Author URI: http://www.smyx.net/
Plugin URI: http://www.smyx.net/wp-connect.html
Description: 支持使用微博帐号登录 WordPress 博客，并且支持同步文章的 标题和链接 到各大微博和社区。
Version: 1.3.3
*/

$plugin_url = get_bloginfo('wpurl').'/wp-content/plugins/wp-connect';
$wptm_options = get_option('wptm_options');
$wptm_connect = get_option('wptm_connect');

add_action('admin_menu', 'wp_connect_add_page');

include_once( dirname(__FILE__) . '/sync.php' );
include_once( dirname(__FILE__) . '/functions.php' );
include_once( dirname(__FILE__) . '/connect.php' );
include_once( dirname(__FILE__) . '/page.php' );

function wp_strlen($text) { // 字符长度(一个汉字代表一个字符，两个字母代表一个字符)
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
	$b = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "#", "[", "]");
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

if ($wptm_options['enable_wptm']) { // 是否开启微博同步功能
	add_action('publish_post', 'wp_connect_publish', 1);
}

function wp_connect_add_page() {
	add_options_page('WordPress连接微博', 'WordPress连接微博', 'manage_options', 'wp-connect', 'wp_connect_do_page');
} 

function wp_connect_reauthorize() {
	if (!get_option('wptm_options') && !get_option('wptm_connect')) {
		echo "<div class='update-nag'><center><p>您还没有对“WordPress连接微博”进行设置，<a href='options-general.php?page=wp-connect'>现在去设置</a></p></center></div>";
	} 
}

add_action('admin_head', 'wp_connect_reauthorize');

function wp_connect_curl_warning(){
	if (!function_exists( 'curl_init' )) {
	echo "<div class='updated'><p><strong>很遗憾！您的服务器(主机)当前配置不支持curl，不能正常使用该插件！请联系空间商重新配置。</strong></p></div>";
	}
}

add_action('admin_notices', 'wp_connect_curl_warning');

// 设置
function wp_connect_do_page() {
	global $plugin_url;
	wp_connect_update();
	$wptm_options = get_option('wptm_options');
	$wptm_connect = get_option('wptm_connect');
	$account = wp_option_account();
	$_SESSION['wp_admin_go_url'] = get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=wp-connect';
?>
<div class="wrap">
<h2>WordPress连接微博</h2>
<form method="post" action="<?php echo get_bloginfo('wpurl');?>/wp-admin/options-general.php?page=wp-connect">
    <?php wp_nonce_field('connect-options');?>
    <p><strong>连接设置</strong></p>
	<table class="form-table">
		<tr>
			<td width="25%" valign="top">是否开启“连接微博”功能</td>
			<td><input name="enable_connect" type="checkbox" value="1" <?php if($wptm_connect['enable_connect']) echo "checked "; ?>></td>
		</tr>
		<tr>
			<td width="25%" valign="top">添加按钮</td>
			<td><label><input name="sina" type="checkbox" value="1" <?php if($wptm_connect['sina']) echo "checked "; ?> />新浪微博</label>
			<label><input name="qq" type="checkbox" value="1" <?php if($wptm_connect['qq']) echo "checked "; ?> />腾讯微博</label>
			<label><input name="netease" type="checkbox" value="1" <?php if($wptm_connect['netease']) echo "checked "; ?> />网易微博</label>
			<label><input name="renren" type="checkbox" value="1" <?php if($wptm_connect['renren']) echo "checked "; ?> />人人连接</label>
			<label><input name="douban" type="checkbox" value="1" <?php if($wptm_connect['douban']) echo "checked "; ?> />豆瓣</label></td>
		</tr>
		<tr>
			<td width="25%" valign="top">人人连接APP</td>
			<td>API Key: <input name="renren_api_key" type="text" value='<?php echo $wptm_connect['renren_api_key'];?>' /> Secret: <input name="renren_secret" type="text" value='<?php echo $wptm_connect['renren_secret'];?>' /> [ <a href="http://www.smyx.net/wp-connect.html#renrenapp" target="_blank">如何获取?</a> ] </td>
		</tr>
		<tr>
			<td width="25%" valign="top">绑定微博帐号</td>
			<td>新浪微博昵称: <input name="sina_username" type="text" size="10" value='<?php echo $wptm_connect['sina_username'];?>' /> 腾讯微博帐号: <input name="qq_username" type="text" size="10" value='<?php echo $wptm_connect['qq_username'];?>' /> 网易微博昵称: <input name="netease_username" type="text" size="10" value='<?php echo $wptm_connect['netease_username'];?>' /> <br />(说明：有新的评论时将以 @微博帐号 的形式显示在您跟评论者相对应的微博上，仅对方勾选了同步评论到微博时才有效！注：腾讯微博帐号不是QQ号码)</td>
		</tr>
		<tr>
			<td width="25%" valign="top">网易微博评论者头像</td>
			<td><input name="netease_avatar" type="checkbox" value="1" <?php if($wptm_connect['netease_avatar']) echo "checked "; ?>>已显示</td>
		</tr>
		<tr>
			<td width="25%" valign="top">禁止注册的用户名</td>
			<td><input name="disable_username" type="text" size="60" value='<?php echo $wptm_connect['disable_username'];?>' /> 用半角逗号(,)隔开</td>
		</tr>
    </table>
<p class="submit">
<input type="submit" name="wptm_connect" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>
<form method="post" action="<?php echo get_bloginfo('wpurl');?>/wp-admin/options-general.php?page=wp-connect">
    <?php wp_nonce_field('sync-options');?>
    <p><strong>同步设置</strong></p>
	<table class="form-table">
		<tr>
			<td width="25%" valign="top">是否开启“微博同步”功能</td>
			<td><input name="enable_wptm" type="checkbox" value="1" <?php if($wptm_options['enable_wptm']) echo "checked "; ?>></td>
		</tr>
		<tr>
			<td width="25%" valign="top">Twitter是否使用代理？</td>
			<td><input name="enable_proxy" type="checkbox" value="1" <?php if($wptm_options['enable_proxy']) echo "checked "; ?>> (国内主机用户必须勾选才能使用)</td>
		</tr>
		<tr>
			<td width="25%" valign="top">自定义代理API</td>
			<td><input name="custom_proxy" type="text" size="60" value="<?php echo $wptm_options['custom_proxy']; ?>" /><br />不填则默认为http://smyxapi.appspot.com/api/statuses/update.xml [ <a href="http://www.smyx.net/wp-connect.html#twitterapi" target="_blank">使用说明</a> ]</td>
		</tr>
		<tr>
			<th>同步内容设置</th>
			<td><input name="sync_option" type="text" size="1" maxlength="1" value="<?php echo $wptm_options['sync_option']; ?>" onkeyup="value=value.replace(/[^1-4]/g,'')" /> (填数字，留空为不同步，只对本页绑定的帐号有效！) <input name="enable_tags" type="checkbox" value="1" <?php if($wptm_options['enable_tags']) echo "checked "; ?>> 是否将文章标签当成话题<br />提示：1. 前缀+标题+链接 2. 前缀+标题+摘要/内容+链接 3.文章摘要/内容 4. 文章摘要/内容+链接
			</td>
		</tr>
		<tr>
			<th>自定义消息</th>
			<td>新文章前缀：<input name="new_prefix" type="text" size="10" value="<?php echo $wptm_options['new_prefix']; ?>" /> 更新文章前缀：<input name="update_prefix" type="text" size="10" value="<?php echo $wptm_options['update_prefix']; ?>" /> 更新间隔：<input name="update_days" type="text" size="2" maxlength="4" value="<?php echo ($wptm_options['update_days']) ? $wptm_options['update_days'] : '0'; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 天 [0=更新时不同步]
			</td>
		</tr>
		<tr>
			<td width="25%" valign="top">禁止同步的文章分类ID</td>
			<td><input name="cat_ids" type="text" value="<?php echo $wptm_options['cat_ids']; ?>" /> 用半角逗号(,)隔开 (设置后该ID分类下的文章将不会同到微博)</td>
		</tr>
		<tr>
			<td width="25%" valign="top">自定义页面</td>
			<td>密码：<input name="page_password" type="password" value="<?php echo $wptm_options['page_password']; ?>" /> <input name="disable_ajax" type="checkbox" value="1" <?php if($wptm_options['disable_ajax']) echo "checked "; ?>> 禁用AJAX无刷新</td>
		</tr>
		<tr>
			<td width="25%" valign="top">多作者博客</td>
			<td><input name="multiple_authors" type="checkbox" value="1" <?php if($wptm_options['multiple_authors']) echo "checked "; ?>> (是否让每个作者发布的文章同步到他们各自绑定的微博上，可以通知他们在 <a href="<?php echo admin_url('profile.php');?>">我的资料</a> 里面设置。)</td>
		</tr>
		<tr>
			<td width="25%" valign="top">自定义短网址</td>
			<td><input name="enable_shorten" type="checkbox"  value="1" <?php if($wptm_options['enable_shorten']) echo "checked "; ?>> 博客默认 ( http://yourblog.com/?p=1 ) <input name="t_cn" type="checkbox"  value="1" <?php if($wptm_options['t_cn']) echo "checked "; ?>> http://t.cn/xxxxxx ( <input name="t_cn_twitter" type="checkbox"  value="1" <?php if($wptm_options['t_cn_twitter']) echo "checked "; ?>> 只应用于Twitter )</td>
		</tr>
    </table>
<p class="submit">
<input type="submit" name="update_options" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>
<?php
include( dirname(__FILE__) . '/bind.php' );
echo '<p><span style="font-size:14px; color: #440; padding:0 5px;">喜欢这个插件吗？你可以考虑捐赠支持我继续开发，您可以使用 <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZWMTWK2DGHCYS" target="_blank">PayPal</a> 或者查看 <a href="http://www.smyx.net/wp-connect.html#donate" target="_blank">其他</a> 捐赠方式。</span></p>';
}
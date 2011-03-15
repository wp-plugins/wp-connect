<?php
if ($_GET['do'] == "microblog") {
	include_once "../../../wp-config.php";
	$wptm_options = get_option('wptm_options');
	$password = $_POST['password'];
	if (isset($_POST['message'])) {
		if ($wptm_options['page_password'] && $password == $wptm_options['page_password']) {
			wp_update_page();
		} else { echo 'pwderror'; } 
	} 
}

function wp_update_page() {
	$account = wp_option_account();
	$status = mb_substr(stripslashes($_POST['message']), 0, 140, 'utf-8');
	require_once(dirname(__FILE__) . '/OAuth/OAuth.php');
	if (isset($_POST['pic'])) {
		$pic = $_POST['pic'];
	} 
	if (isset($_POST['twitter']) && $account['twitter']) {
		wp_update_twitter($status);
	} 
	if (isset($_POST['qq']) && $account['qq']) {
		wp_update_t_qq($account['qq'], $status, $pic);
	} 
	if (isset($_POST['sina']) && $account['sina']) {
		wp_update_t_sina($account['sina'], $status, $pic);
	} 
	if (isset($_POST['netease']) && $account['netease']) {
		wp_update_t_163($account['netease'], $status, $pic);
	} 
	if (isset($_POST['sohu']) && $account['sohu']) {
		wp_update_t_sohu($account['sohu'], $status);
	} 
	if (isset($_POST['renren']) && $account['renren']) {
		wp_update_renren($account['renren'], $status);
	} 
	if (isset($_POST['kaixin001']) && $account['kaixin001']) {
		wp_update_kaixin001($account['kaixin001'], $status);
	} 
	if (isset($_POST['digu']) && $account['digu']) {
		wp_update_digu($account['digu'], $status);
	} 
	if (isset($_POST['douban']) && $account['douban']) {
		wp_update_douban($account['douban'], $status);
	} 
	if (isset($_POST['baidu']) && $account['baidu']) {
		wp_update_baidu($account['baidu'], $status);
	} 
	if (isset($_POST['fanfou']) && $account['fanfou']) {
		wp_update_fanfou($account['fanfou'], $status);
	} 
	if (isset($_POST['renjian']) && $account['renjian']) {
		wp_update_renjian($account['renjian'], $status);
	} 
	if (isset($_POST['zuosa']) && $account['zuosa']) {
		wp_update_zuosa($account['zuosa'], $status);
	} 
	if (isset($_POST['follow5']) && $account['follow5']) {
		wp_update_follow5($account['follow5'], $status);
	} 
} 

function wp_connect_script_page () {
	wp_deregister_script('jquery');
	wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js', false, '1.5.1');
	wp_register_script('wp-connect-page', plugins_url('wp-connect/js/page.js'), array('jquery'), '0.1');
    wp_print_scripts('wp-connect-page');
}
add_action('wp_connect_action', 'wp_connect_script_page');

function wp_connect_action() {
	do_action('wp_connect_action');
} 

function wp_to_microblog() {
	global $plugin_url;
	$wptm_options = get_option('wptm_options');
	if(!$wptm_options['disable_ajax']) {
		wp_connect_action();
	}
	$password = $_POST['password'];
	if (isset($_POST['message'])) {
		if ($wptm_options['page_password'] && $password == $wptm_options['page_password']) {
			wp_update_page();
		} else {
			$pwderror = ' style="display:inline;"';
			$message = $_POST['message'];
		} 
	}

echo '
<script type="text/javascript">
function textCounter(field,maxlimit){if(field.value.length>maxlimit){field.value=field.value.substring(0,maxlimit)}else{document.getElementById("wordage").childNodes[1].innerHTML=maxlimit-field.value.length}}
var wpurl = "'.get_bloginfo('wpurl').'";
</script>
<link type="text/css" href="'.$plugin_url.'/page.css" rel="stylesheet" />
<form action="" method="post" id="tform">
  <fieldset>
    <div id="say">说说你的新鲜事
      <div id="wordage">你还可以输入 <span>140</span> 字</div>
    </div>
    <p id="v1"><textarea cols="60" rows="5" name="message" id="message" onblur="textCounter(this.form.message,140);" onKeyDown="textCounter(this.form.message,140);" onKeyUp="textCounter(this.form.message,140);">'.$message.'</textarea></p>
    图片地址：<p>
    <p id="v2"><input name="pic" id="pic" size="50" type="text" />（仅支持腾讯、新浪、网易微博）</p>
    发布到：
    <p><input name="twitter" id="twitter" type="checkbox" value="checkbox" checked />
    <label for="twitter">Twitter</label>
    <input name="qq" id="qq" type="checkbox" value="checkbox" checked />
    <label for="qq">腾讯微博</label>
    <input name="sina" id="sina" type="checkbox" value="checkbox" checked />
    <label for="sina">新浪微博</label>
    <input name="netease" id="netease" type="checkbox" value="checkbox" checked />
    <label for="netease">网易微博</label>
    <input name="sohu" id="sohu" type="checkbox" value="checkbox" checked />
    <label for="sohu">搜狐微博</label>
    <input name="renren" id="renren" type="checkbox" value="checkbox" checked />
    <label for="renren">人人网</label><br />
    <input name="kaixin001" id="kaixin001" type="checkbox" value="checkbox" checked />
    <label for="kaixin001">开心网</label>
    <input name="digu" id="digu" type="checkbox" value="checkbox" checked />
    <label for="digu">嘀咕</label>
    <input name="douban" id="douban" type="checkbox" value="checkbox" checked />
    <label for="douban">豆瓣</label>
    <input name="baidu" id="baidu" type="checkbox" value="checkbox" checked />
    <label for="baidu">百度说吧</label>
    <input name="fanfou" id="fanfou" type="checkbox" value="checkbox" checked />
    <label for="fanfou">饭否</label>
    <input name="renjian" id="renjian" type="checkbox" value="checkbox" checked />
    <label for="renjian">人间网</label>
    <input name="zuosa" id="zuosa" type="checkbox" value="checkbox" checked />
    <label for="zuosa">做啥</label>
    <input name="follow5" id="follow5" type="checkbox" value="checkbox" checked />
    <label for="follow5">Follow5</label></p>
    <p id="v3">密码：
    <input name="password" id="password" type="password" value="" /> <span'.$pwderror.'>密码错误！</span>
	</p>
    <p><input type="submit" id="publish" value="发表" /></p>
    <p class="loading"><img src="'.$plugin_url.'/images/loading.gif" alt="Loading" /></p>
	<p class="success">发表成功！</p>
  </fieldset>
</form>';
}
add_shortcode('wp_to_microblog', 'wp_to_microblog'); //简码
?>
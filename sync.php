<?php
function wp_connect_header () {
	global $plugin_url;
	if (isset($_POST['add_twitter'])) {
		header('Location:' . $plugin_url. '/go.php?OAuth=TWITTER');
	}
	if (isset($_POST['add_qq'])) {
		header('Location:' . $plugin_url. '/go.php?OAuth=QQ');
	}
	if (isset($_POST['add_sina'])) {
		header('Location:' . $plugin_url. '/go.php?OAuth=SINA');
	}
	if (isset($_POST['add_sohu'])) {
		header('Location:' . $plugin_url. '/go.php?OAuth=SOHU');
	}
	if (isset($_POST['add_netease'])) {
		header('Location:' . $plugin_url. '/go.php?OAuth=NETEASE');
	}
	if (isset($_POST['add_douban'])) {
		header('Location:' . $plugin_url. '/go.php?OAuth=DOUBAN');
	}
	// 删除数据库+停用插件
	if (isset($_POST['wptm_delete'])) {
		delete_option("wptm_options");
		delete_option("wptm_connect");
		delete_option("wptm_advanced");
		delete_option("wptm_share");
		delete_option("wptm_openqq");
		delete_option("wptm_opensina");
		delete_option("wptm_opensohu");
		delete_option("wptm_twitter");/*old*/
		delete_option("wptm_twitter_oauth");
		delete_option("wptm_qq");
		delete_option("wptm_sina");
		delete_option("wptm_sohu");
		delete_option("wptm_netease");
		delete_option("wptm_douban");
		delete_option("wptm_renren");
		delete_option("wptm_kaixin001");
		delete_option("wptm_digu");
		delete_option("wptm_baidu");
		delete_option("wptm_fanfou");
		delete_option("wptm_renjian");
		delete_option("wptm_zuosa");
		delete_option("wptm_follow5");
		delete_option("wptm_leihou");
		delete_option("wptm_wbto");
		$deactivate_url = 'plugins.php?action=deactivate&plugin=wp-connect/wp-connect.php';
		if(function_exists('wp_nonce_url')) {
			$deactivate_url = str_replace('&amp;', '&', wp_nonce_url($deactivate_url, 'deactivate-plugin_wp-connect/wp-connect.php'));
		    header('Location:' . $deactivate_url);
		}
	}
}
add_action('init', 'wp_connect_header');
/*
 * 插件页面
 * 写入数据库
 */
function wp_connect_update() {
    $update_days = (trim($_POST['update_days'])) ? trim($_POST['update_days']) : '0';
	$update_options = array(
		'enable_wptm' => trim($_POST['enable_wptm']),
		'enable_proxy' => trim($_POST['enable_proxy']),
		'bind' => trim($_POST['bind']),
		'sync_option' => trim($_POST['sync_option']),
		'enable_cats' => trim($_POST['enable_cats']),
		'enable_tags' => trim($_POST['enable_tags']),
		'new_prefix' => trim($_POST['new_prefix']),
		'update_prefix' => trim($_POST['update_prefix']),
		'update_days' => $update_days,
		'cat_ids' => trim($_POST['cat_ids']),
		'page_password' => trim($_POST['page_password']),
		'disable_ajax' => trim($_POST['disable_ajax']),
		'multiple_authors' => trim($_POST['multiple_authors']),
		'enable_shorten' => trim($_POST['enable_shorten']),
		't_cn' => trim($_POST['t_cn'])
		);
	$disable_username = (trim($_POST['disable_username'])) ? trim($_POST['disable_username']) : 'admin';
	$wptm_connect = array(
		'enable_connect' => trim($_POST['enable_connect']),
		'qqlogin' => trim($_POST['qqlogin']),
		'sina' => trim($_POST['sina']),
		'qq' => trim($_POST['qq']),
		'sohu' => trim($_POST['sohu']),
		'netease' => trim($_POST['netease']),
		'renren' => trim($_POST['renren']),
		'kaixin001' => trim($_POST['kaixin001']),
		'douban' => trim($_POST['douban']),
		'google' => trim($_POST['google']),
		'yahoo' => trim($_POST['yahoo']),
		'twitter' => trim($_POST['twitter']),
		'sina_username' => trim($_POST['sina_username']),
		'qq_username' => trim($_POST['qq_username']),
		'sohu_username' => trim($_POST['sohu_username']),
		'netease_username' => trim($_POST['netease_username']),
		'widget' => trim($_POST['widget']),
		'qq_app_id' => trim($_POST['qq_app_id']),
		'qq_app_key' => trim($_POST['qq_app_key']),
		'renren_api_key' => trim($_POST['renren_api_key']),
		'renren_secret' => trim($_POST['renren_secret']),
		'kaixin001_api_key' => trim($_POST['kaixin001_api_key']),
		'kaixin001_secret' => trim($_POST['kaixin001_secret']),
		'netease_avatar' => trim($_POST['netease_avatar']),
		'disable_username' => $disable_username
		);
	$update = array(
		'username' => trim($_POST['username']),
		'password' => trim($_POST['password'])
		);
	$appkey = array(
		'app_key' => trim($_POST['username']),
		'secret'  => trim($_POST['password'])
		);
	$token = array(
		'oauth_token' => trim($_POST['username']),
		'oauth_token_secret' => trim($_POST['password'])
		);
	$updated = '<div class="updated"><p><strong>' . __('Settings saved.') . '</strong></p></div>';
	if (isset($_POST['update_options'])) {
		update_option("wptm_options", $update_options);
		echo $updated;
	} 
	if (isset($_POST['wptm_connect'])) {
		update_option("wptm_connect", $wptm_connect);
		echo $updated;
	}
	if (isset($_POST['update_openqq'])) {
		update_option("wptm_openqq", $appkey);
		echo $updated;
	}
	if (isset($_POST['update_opensina'])) {
		update_option("wptm_opensina", $appkey);
		echo $updated;
	}
	if (isset($_POST['update_opensohu'])) {
		update_option("wptm_opensohu", $appkey);
		echo $updated;
	}
	if (isset($_POST['update_twitter'])) {
		update_option("wptm_twitter_oauth", $token);
		echo $updated;
	}
	if (isset($_POST['update_qq'])) {
		update_option("wptm_qq", $token);
		echo $updated;
	} 
	if (isset($_POST['update_sina'])) {
		update_option("wptm_sina", $token);
		echo $updated;
	}
	if (isset($_POST['update_sohu'])) {
		update_option("wptm_sohu", $token);
		echo $updated;
	}
	if (isset($_POST['update_netease'])) {
		update_option("wptm_netease", $token);
		echo $updated;
	} 
	if (isset($_POST['update_douban'])) {
		update_option("wptm_douban", $token);
		echo $updated;
	}
	if (isset($_POST['update_renren'])) {
		update_option("wptm_renren", $update);
		echo $updated;
	}
	if (isset($_POST['update_kaixin001'])) {
		update_option("wptm_kaixin001", $update);
		echo $updated;
	}
	if (isset($_POST['update_digu'])) {
		update_option("wptm_digu", $update);
		echo $updated;
	} 
	if (isset($_POST['update_baidu'])) {
		update_option("wptm_baidu", $update);
		echo $updated;
	} 
	if (isset($_POST['update_fanfou'])) {
		update_option("wptm_fanfou", $update);
		echo $updated;
	} 
	if (isset($_POST['update_renjian'])) {
		update_option("wptm_renjian", $update);
		echo $updated;
	} 
	if (isset($_POST['update_zuosa'])) {
		update_option("wptm_zuosa", $update);
		echo $updated;
	} 
	if (isset($_POST['update_follow5'])) {
		update_option("wptm_follow5", $update);
		echo $updated;
	}
	if (isset($_POST['update_leihou'])) {
		update_option("wptm_leihou", $update);
		echo $updated;
	}
	if (isset($_POST['update_wbto'])) {
		update_option("wptm_wbto", $update);
		echo $updated;
	}
	// delete
	if (isset($_POST['delete_openqq'])) {
		update_option("wptm_openqq", '');
		echo $updated;
	}
	if (isset($_POST['delete_opensina'])) {
		update_option("wptm_opensina", '');
		echo $updated;
	}
	if (isset($_POST['delete_opensohu'])) {
		update_option("wptm_opensohu", '');
		echo $updated;
	}
	if (isset($_POST['delete_twitter'])) {
		update_option("wptm_twitter_oauth", '');
	}
	if (isset($_POST['delete_qq'])) {
		update_option("wptm_qq", '');
	}
	if (isset($_POST['delete_sina'])) {
		update_option("wptm_sina", '');
	}
	if (isset($_POST['delete_sohu'])) {
		update_option("wptm_sohu", '');
	}
	if (isset($_POST['delete_netease'])) {
		update_option("wptm_netease", '');
	}
	if (isset($_POST['delete_douban'])) {
		update_option("wptm_douban", '');
	}
	if (isset($_POST['delete_renren'])) {
		update_option("wptm_renren", '');
	}
	if (isset($_POST['delete_kaixin001'])) {
		update_option("wptm_kaixin001", '');
	}
	if (isset($_POST['delete_digu'])) {
		update_option("wptm_digu", '');
	} 
	if (isset($_POST['delete_baidu'])) {
		update_option("wptm_baidu", '');
	} 
	if (isset($_POST['delete_fanfou'])) {
		update_option("wptm_fanfou", '');
	} 
	if (isset($_POST['delete_renjian'])) {
		update_option("wptm_renjian", '');
	} 
	if (isset($_POST['delete_zuosa'])) {
		update_option("wptm_zuosa", '');
	}
	if (isset($_POST['delete_follow5'])) {
		update_option("wptm_follow5", '');
	}
	if (isset($_POST['delete_leihou'])) {
		update_option("wptm_leihou", '');
	}
	if (isset($_POST['delete_wbto'])) {
		update_option("wptm_wbto", '');
	}
}
// 读取数据库
function wp_option_account() { 
	$account = array(
	'openqq' => get_option('wptm_openqq'),
	'opensina' => get_option('wptm_opensina'),
	'opensohu' => get_option('wptm_opensohu'),
	'qq' => get_option('wptm_qq'),
	'sina' => get_option('wptm_sina'),
	'sohu' => get_option('wptm_sohu'),
	'netease' => get_option('wptm_netease'),
	'twitter' => get_option('wptm_twitter_oauth'),
	'renren' => get_option('wptm_renren'),
	'kaixin001' => get_option('wptm_kaixin001'),
	'digu' => get_option('wptm_digu'),
	'douban' => get_option('wptm_douban'),
	'baidu' => get_option('wptm_baidu'),
	'renjian' => get_option('wptm_renjian'),
	'fanfou' => get_option('wptm_fanfou'),
	'zuosa' => get_option('wptm_zuosa'),
	'leihou' => get_option('wptm_leihou'),
	'wbto' => get_option('wptm_wbto'),
	'follow5' => get_option('wptm_follow5'));
	return $account;
}
// 我的资料
if($wptm_options['multiple_authors'] || (function_exists('wp_connect_advanced') && $wptm_advanced['registered_users'])) {
   add_action( 'show_user_profile', 'wp_user_profile_fields' , 12);
   add_action( 'edit_user_profile', 'wp_user_profile_fields' , 12);
   add_action( 'personal_options_update', 'wp_save_user_profile_fields' );
   add_action( 'edit_user_profile_update', 'wp_save_user_profile_fields' );
}

function wp_save_user_profile_fields( $user_id ) {
 
if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
    $update_days = (trim($_POST['update_days'])) ? trim($_POST['update_days']) : '0';
	$wptm_profile = array(
		'sync_option' => trim($_POST['sync_option']),
		'new_prefix' => trim($_POST['new_prefix']),
		'update_prefix' => trim($_POST['update_prefix']),
		'update_days' => $update_days
		);
    update_usermeta( $user_id, 'wptm_profile', $wptm_profile );
}
// 读取数据库
function wp_usermeta_account( $user_id ) { 
	$account = array(
	'qq' => get_user_meta($user_id, 'wptm_qq', true),
	'sina' => get_user_meta($user_id, 'wptm_sina', true),
	'sohu' => get_user_meta($user_id, 'wptm_sohu', true),
	'netease' => get_user_meta($user_id, 'wptm_netease', true),
	'twitter' => get_user_meta($user_id, 'wptm_twitter_oauth', true),
	'renren' => get_user_meta($user_id, 'wptm_renren', true),
	'kaixin001' => get_user_meta($user_id, 'wptm_kaixin001', true),
	'digu' => get_user_meta($user_id, 'wptm_digu', true),
	'douban' => get_user_meta($user_id, 'wptm_douban', true),
	'baidu' => get_user_meta($user_id, 'wptm_baidu', true),
	'renjian' => get_user_meta($user_id, 'wptm_renjian', true),
	'fanfou' => get_user_meta($user_id, 'wptm_fanfou', true),
	'zuosa' => get_user_meta($user_id, 'wptm_zuosa', true),
	'follow5' => get_user_meta($user_id, 'wptm_follow5', true),
	'leihou' => get_user_meta($user_id, 'wptm_leihou', true),
	'wbto' => get_user_meta($user_id, 'wptm_wbto', true));
	return $account;
}
define("WP_DONTPEEP" , 'Yp64QLB0Ho8ymIRs');
// 写入数据库
function wp_user_profile_update( $user_id ) {
	$update = array(
		'username' => trim($_POST['username']),
		'password' => trim($_POST['password'])
		);
	$token = array(
		'oauth_token' => trim($_POST['username']),
		'oauth_token_secret' => trim($_POST['password'])
		);
	if (isset($_POST['update_twitter'])) {
		update_usermeta( $user_id, "wptm_twitter_oauth", $token);
	}
	if (isset($_POST['update_qq'])) {
		update_usermeta( $user_id, "wptm_qq", $token);
	} 
	if (isset($_POST['update_sina'])) {
		update_usermeta( $user_id, "wptm_sina", $token);
	}
	if (isset($_POST['update_sohu'])) {
		update_usermeta( $user_id, "wptm_sohu", $token);
	}
	if (isset($_POST['update_netease'])) {
		update_usermeta( $user_id, "wptm_netease", $token);
	} 
	if (isset($_POST['update_douban'])) {
		update_usermeta( $user_id, "wptm_douban", $token);
	}
	if (isset($_POST['update_renren'])) {
		update_usermeta( $user_id, 'wptm_renren', $update);
	}
	if (isset($_POST['update_kaixin001'])) {
		update_usermeta( $user_id, 'wptm_kaixin001', $update);
	}
	if (isset($_POST['update_digu'])) {
		update_usermeta( $user_id, 'wptm_digu', $update);
	} 
	if (isset($_POST['update_baidu'])) {
		update_usermeta( $user_id, 'wptm_baidu', $update);
	} 
	if (isset($_POST['update_renjian'])) {
		update_usermeta( $user_id, 'wptm_renjian', $update);
	} 
	if (isset($_POST['update_fanfou'])) {
		update_usermeta( $user_id, 'wptm_fanfou', $update);
	} 
	if (isset($_POST['update_zuosa'])) {
		update_usermeta( $user_id, 'wptm_zuosa', $update);
	} 
	if (isset($_POST['update_follow5'])) {
		update_usermeta( $user_id, 'wptm_follow5', $update);
	}
	if (isset($_POST['update_leihou'])) {
		update_usermeta( $user_id, 'wptm_leihou', $update);
	}
	if (isset($_POST['update_wbto'])) {
		update_usermeta( $user_id, 'wptm_wbto', $update);
	}
	// delete
	if (isset($_POST['delete_twitter'])) {
		update_usermeta( $user_id, 'wptm_twitter_oauth', '');
	}
	if (isset($_POST['delete_qq'])) {
		update_usermeta( $user_id, 'wptm_qq', '');
	}
	if (isset($_POST['delete_sina'])) {
		update_usermeta( $user_id, 'wptm_sina', '');
	}
	if (isset($_POST['delete_sohu'])) {
		update_usermeta( $user_id, 'wptm_sohu', '');
	}
	if (isset($_POST['delete_netease'])) {
		update_usermeta( $user_id, 'wptm_netease', '');
	}
	if (isset($_POST['delete_douban'])) {
		update_usermeta( $user_id, 'wptm_douban', '');
	}
	if (isset($_POST['delete_renren'])) {
		update_usermeta( $user_id, 'wptm_renren', '');
	}
	if (isset($_POST['delete_kaixin001'])) {
		update_usermeta( $user_id, 'wptm_kaixin001', '');
	}
	if (isset($_POST['delete_digu'])) {
		update_usermeta( $user_id, 'wptm_digu', '');
	} 
	if (isset($_POST['delete_baidu'])) {
		update_usermeta( $user_id, 'wptm_baidu', '');
	} 
	if (isset($_POST['delete_renjian'])) {
		update_usermeta( $user_id, 'wptm_renjian', '');
	} 
	if (isset($_POST['delete_fanfou'])) {
		update_usermeta( $user_id, 'wptm_fanfou', '');
	} 
	if (isset($_POST['delete_zuosa'])) {
		update_usermeta( $user_id, 'wptm_zuosa', '');
	}
	if (isset($_POST['delete_follow5'])) {
		update_usermeta( $user_id, 'wptm_follow5', '');
	}
	if (isset($_POST['delete_leihou'])) {
		update_usermeta( $user_id, 'wptm_leihou', '');
	}
	if (isset($_POST['delete_wbto'])) {
		update_usermeta( $user_id, 'wptm_wbto', '');
	}
}

// 设置
function wp_user_profile_fields( $user ) {
	global $plugin_url, $user_level, $wptm_options;
	$user_id = $user->ID;
	wp_user_profile_update($user_id);
	$account = wp_usermeta_account($user_id);
	$wptm_profile = get_user_meta($user_id, 'wptm_profile', true);
	$_SESSION['user_id'] = $user_id;
	$_SESSION['wp_url_bind'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	if ($wptm_options['multiple_authors'] && $user_level > 1) { //是否开启多作者和判断用户等级
?>
<h3>同步设置</h3>
<table class="form-table">
<tr>
	<th>同步内容设置</th>
	<td><input name="sync_option" type="text" size="1" maxlength="1" value="<?php echo $wptm_profile['sync_option']; ?>" onkeyup="value=value.replace(/[^1-4]/g,'')" /> (填数字，留空为不同步) <br />提示：1. 前缀+标题+链接 2. 前缀+标题+摘要/内容+链接 3.文章摘要/内容 4. 文章摘要/内容+链接
	</td>
</tr>
<tr>
	<th>自定义消息</th>
	<td>新文章前缀：<input name="new_prefix" type="text" size="10" value="<?php echo $wptm_profile['new_prefix']; ?>" /> 更新文章前缀：<input name="update_prefix" type="text" size="10" value="<?php echo $wptm_profile['update_prefix']; ?>" /> 更新间隔：<input name="update_days" type="text" size="2" maxlength="4" value="<?php echo $wptm_profile['update_days']; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 天 [0=更新时不同步]
	</td>
</tr>
</table>
<?php
	}
?>
<p class="show_botton"></p>
</form>
</div>
<?php include( dirname(__FILE__) . '/bind.php' );?>
<div class="hide_botton">
<?php
} 

function wp_connect_sidebox() {
	global $post;
	if ($post -> post_status != 'publish') {
		echo '<p><label><input type="checkbox" name="publish_no_sync" value="1" />不同步 (保存为草稿、待审也不会同步)</label></p>';
	} else {
		echo '<p><label><input type="checkbox" name="publish_update_sync" value="1" />同步 (不勾选则以文章更新间隔判断)</label></p>';
		echo '<p><label><input type="checkbox" name="publish_new_sync" value="1" />当作新文章同步</label></p>';
	}
} 

function wp_connect_add_sidebox() {
	if (function_exists('add_meta_box')) {
		add_meta_box('wp-connect-sidebox', '微博同步设置 [只对本页面有效]', 'wp_connect_sidebox', 'post', 'side', 'high');
	} 
} 
add_action('admin_menu', 'wp_connect_add_sidebox');

// 发布
function wp_connect_publish($post_ID) {
	global $wptm_options;
	if (get_option('timezone_string')) {
		date_default_timezone_set(get_option('timezone_string'));
		$time = time();
	} elseif (get_option('gmt_offset')) {
		$time = time() + (get_option('gmt_offset') * 3600);
	} 
	$title = wp_replace(get_the_title($post_ID));
	$postlink = get_permalink($post_ID);
	$shortlink = get_bloginfo('url') . "/?p=" . $post_ID;
	$thePost = get_post($post_ID);
	$content = $thePost -> post_content;
	$excerpt = $thePost -> post_excerpt;
	$post_author_ID = $thePost -> post_author;
	$post_date = strtotime($thePost -> post_date);
    $post_content = wp_replace($content);
    // 是否有摘要
	if($excerpt) {
		$post_content = wp_replace($excerpt);
	}
	$wptm_profile = get_user_meta($post_author_ID, 'wptm_profile', true);
	$account = wp_usermeta_account($post_author_ID);
	// 是否开启了多作者博客
    if ( $wptm_options['multiple_authors'] && $wptm_profile['sync_option'] && (array_filter($account))) {
		$account = $account;
		$sync_option = $wptm_profile['sync_option'];
	    $new_prefix = $wptm_profile['new_prefix'];
	    $update_prefix = $wptm_profile['update_prefix'];
	    $update_days = $wptm_profile['update_days'] * 60 * 60 * 24;
	} else {
		if(!$wptm_options['sync_option']) {
			return;
		}
		$account = wp_option_account();
	    $sync_option = $wptm_options['sync_option'];
	    $new_prefix = $wptm_options['new_prefix'];
	    $update_prefix = $wptm_options['update_prefix'];
	    $update_days = $wptm_options['update_days'] * 60 * 60 * 24;
	}
	// 是否绑定了帐号
	if($account) {
		$account = array_filter($account);
	}
	if (!$account) {
		return;
	}
    // 不想同步的文章分类ID
	if ($wptm_options['cat_ids']) {
		$cat_ids = $wptm_options['cat_ids'];
		$categories = get_the_category($post_ID);
		foreach($categories as $category) {
			$cat_id .= $category -> cat_ID . ',';
		}
		if (wp_in_array($cat_ids, $cat_id)) {
			return;
		} 
	}
	// 是否将文章分类当成话题
	$postcats = get_the_category($post_ID);
	if ($postcats && $wptm_options['enable_cats']) {
		foreach($postcats as $cat) {
			$cats .= '#' . $cat -> cat_name . '# ';
		} 
		$cats = ' ' . $cats;
	} 
	// 是否将文章标签当成话题
	$posttags = get_the_tags($post_ID);
	if ($posttags && $wptm_options['enable_tags']) {
		foreach($posttags as $tag) {
			$tags .= '#' . $tag -> name . '# ';
		} 
		$tags = ' ' . $tags;
	} 
	$tags = $cats . $tags;
	// 匹配视频、图片
	$pic = wp_multi_media_url($content);
	if($pic[0] == "video" && $pic[1]) {
		$tags = $pic[1].$tags;
    }
	// 是否为新发布
	if (($thePost -> post_status == 'publish' || $_POST['publish'] == 'Publish') && ($_POST['prev_status'] == 'draft' || $_POST['original_post_status'] == 'draft' || $_POST['original_post_status'] == 'auto-draft' || $_POST['prev_status'] == 'pending' || $_POST['original_post_status'] == 'pending')) {
		if ($_POST['publish_no_sync']) {
			return;
		} 
		$title = $new_prefix . $title;
	} elseif ((($_POST['originalaction'] == "editpost") && (($_POST['prev_status'] == 'publish') || ($_POST['original_post_status'] == 'publish'))) && $thePost -> post_status == 'publish') { // 是否已发布
		if (!$_POST['publish_update_sync']) {
			if (($time - $post_date < $update_days) || $update_days == 0) { // 判断当前时间与文章发布时间差
				return;
			} 
		} 
		if ($_POST['publish_new_sync']) {
			$update_prefix = $new_prefix;
		} 
		$title = $update_prefix . $title;
	} elseif ($_POST['_inline_edit']) { // 是否是快速编辑
		$quicktime = $_POST['aa'] . '-' . $_POST['mm'] . '-' . $_POST['jj'] . ' ' . $_POST['hh'] . ':' . $_POST['mn'] . ':00';
		$post_date = strtotime($quicktime);
		if (($time - $post_date < $update_days) || $update_days == 0) { // 判断当前时间与文章发布时间差
			return;
		} 
		$title = $update_prefix . $title;
	} elseif(defined('DOING_CRON')) { // 定时发布
		$title = $new_prefix . $title;
	} else { // 后台快速发布，xmlrpc等发布
		if ($thePost -> post_status == 'publish') {
			if ($time - $post_date <= 30) {  // 新文章(包括延迟<=30秒)
				$title = $new_prefix . $title;
			} elseif ($time - $post_date >= 60) {
				if (($time - $post_date < $update_days) || $update_days == 0) { // 判断当前时间与文章发布时间差
					return;
				} 
				$title = $update_prefix . $title;
			} else { // > 30 || < 60
				$title = $title;
			} 
		} 
	} 
	if ($wptm_options['enable_shorten']) { // 是否使用博客默认短网址
		$postlink = $shortlink;
	}
	if ($sync_option == '2') { // 同步 前缀+标题+摘要/内容+链接
		$title = $title . $tags . " - " . $post_content;
	} elseif ($sync_option == '3') { // 同步 文章摘要/内容
		$title = $tags . $post_content;
		$postlink = "";
		$connect = "";
	} elseif ($sync_option == '4') { // 同步 文章摘要/内容+链接
		$title = $tags . $post_content;
	} else {
	    $title = $title . $tags;
	}
	wp_update_list($title, $postlink, $pic, $account);
} 

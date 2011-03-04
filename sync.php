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
	if (isset($_POST['add_netease'])) {
		header('Location:' . $plugin_url. '/go.php?OAuth=NETEASE');
	}
	if (isset($_POST['add_douban'])) {
		header('Location:' . $plugin_url. '/go.php?OAuth=DOUBAN');
	}
}
// 写入数据库
function wp_connect_update() {
    $update_days = (trim($_POST['update_days'])) ? trim($_POST['update_days']) : '0';
	$update_options = array(
		'enable_wptm' => trim($_POST['enable_wptm']),
		'enable_proxy' => trim($_POST['enable_proxy']),
		'custom_proxy' => trim($_POST['custom_proxy']),
		'sync_option' => trim($_POST['sync_option']),
		'enable_tags' => trim($_POST['enable_tags']),
		'new_prefix' => trim($_POST['new_prefix']),
		'update_prefix' => trim($_POST['update_prefix']),
		'update_days' => $update_days,
		'cat_ids' => trim($_POST['cat_ids']),
		'page_password' => trim($_POST['page_password']),
		'disable_ajax' => trim($_POST['disable_ajax']),
		'multiple_authors' => trim($_POST['multiple_authors']),
		'enable_shorten' => trim($_POST['enable_shorten']),
		't_cn' => trim($_POST['t_cn']),
		't_cn_twitter' => trim($_POST['t_cn_twitter'])
		);
	$disable_username = (trim($_POST['disable_username'])) ? trim($_POST['disable_username']) : 'admin';
	$wptm_connect = array(
		'enable_connect' => trim($_POST['enable_connect']),
		'sina' => trim($_POST['sina']),
		'qq' => trim($_POST['qq']),
		'netease' => trim($_POST['netease']),
		'douban' => trim($_POST['douban']),
		'sina_username' => trim($_POST['sina_username']),
		'qq_username' => trim($_POST['qq_username']),
		'netease_username' => trim($_POST['netease_username']),
		'netease_avatar' => trim($_POST['netease_avatar']),
		'disable_username' => $disable_username
		);
	$update = array(
		'username' => trim($_POST['username']),
		'password' => trim($_POST['password'])
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
	if (isset($_POST['update_twitter'])) {
		update_option("wptm_twitter", $update);
		echo $updated;
	} 
	if (isset($_POST['update_sohu'])) {
		update_option("wptm_sohu", $update);
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
	if (isset($_POST['update_douban'])) {
		update_option("wptm_douban", $update);
		echo $updated;
	} 
	if (isset($_POST['update_renjian'])) {
		update_option("wptm_renjian", $update);
		echo $updated;
	} 
	if (isset($_POST['update_fanfou'])) {
		update_option("wptm_fanfou", $update);
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
	// delete
	if (isset($_POST['delete_twitter_oauth'])) {
		update_option("wptm_twitter_oauth", '');
	}
	if (isset($_POST['delete_qq_oauth'])) {
		update_option("wptm_qq", '');
	}
	if (isset($_POST['delete_sina_oauth'])) {
		update_option("wptm_sina", '');
	}
	if (isset($_POST['delete_netease_oauth'])) {
		update_option("wptm_netease", '');
	}
	if (isset($_POST['delete_douban_oauth'])) {
		update_option("wptm_douban", '');
	}
	if (isset($_POST['delete_twitter'])) {
		update_option("wptm_twitter", '');
	} 
	if (isset($_POST['delete_sohu'])) {
		update_option("wptm_sohu", '');
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
	if (isset($_POST['delete_douban'])) {
		update_option("wptm_douban", '');
	} 
	if (isset($_POST['delete_renjian'])) {
		update_option("wptm_renjian", '');
	} 
	if (isset($_POST['delete_fanfou'])) {
		update_option("wptm_fanfou", '');
	} 
	if (isset($_POST['delete_zuosa'])) {
		update_option("wptm_zuosa", '');
	}
	if (isset($_POST['delete_follow5'])) {
		update_option("wptm_follow5", '');
	}
}
// 读取数据库
function wp_option_account() { 
	$account = array(
	'qq' => get_option('wptm_qq'),
	'sina' => get_option('wptm_sina'),
	'netease' => get_option('wptm_netease'),
	'twitter' => get_option('wptm_twitter'),
	'twitter_oauth' => get_option('wptm_twitter_oauth'),
	'sohu' => get_option('wptm_sohu'),
	'renren' => get_option('wptm_renren'),
	'kaixin001' => get_option('wptm_kaixin001'),
	'digu' => get_option('wptm_digu'),
	'douban' => get_option('wptm_douban'),
	'renjian' => get_option('wptm_renjian'),
	'fanfou' => get_option('wptm_fanfou'),
	'zuosa' => get_option('wptm_zuosa'),
	'follow5' => get_option('wptm_follow5'));
	return $account;
}
// 我的资料
if($wptm_options['multiple_authors']) {
   add_action( 'show_user_profile', 'wp_user_profile_fields' , 12);
   add_action( 'edit_user_profile', 'wp_user_profile_fields' , 12);
   add_action( 'personal_options_update', 'wp_save_user_profile_fields' );
   add_action( 'edit_user_profile_update', 'wp_save_user_profile_fields' );
}
 
function wp_save_user_profile_fields( $user_id ) {
 
if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	$wptm_profile = array(
		'sync_option' => trim($_POST['sync_option']),
		'new_prefix' => trim($_POST['new_prefix']),
		'update_prefix' => trim($_POST['update_prefix']),
		'update_days' => trim($_POST['update_days']),
		);
    update_usermeta( $user_id, 'wptm_profile', $wptm_profile );
}

// 设置
function wp_user_profile_fields( $user ) {
	global $plugin_url, $user_id, $user_level;
	if ($user_level > 1) { //判断用户等级
		wp_user_profile_update($user_id);
		$account = wp_usermeta_account($user_id);
		$wptm_profile = get_user_meta($user_id, 'wptm_profile', true);
		$_SESSION['user_ID'] = $user_id;
		$_SESSION['wp_admin_go_url'] = admin_url('profile.php');

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

<p class="submit">
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($user_id); ?>" />
	<input type="submit" class="button-primary" value="<?php IS_PROFILE_PAGE ? esc_attr_e('Update Profile') : esc_attr_e('Update User') ?>" name="submit" />
</p>
</form>
</div>
<?php include( dirname(__FILE__) . '/bind.php' );?>
<div class="remove_botton">
<form>
<?php
	} 
} 
// 读取数据库
function wp_usermeta_account( $user_ID ) { 
	$account = array(
	'qq' => get_user_meta($user_ID, 'wptm_qq', true),
	'sina' => get_user_meta($user_ID, 'wptm_sina', true),
	'netease' => get_user_meta($user_ID, 'wptm_netease', true),
	'twitter' => get_user_meta($user_ID, 'wptm_twitter', true),
	'twitter_oauth' => get_user_meta($user_ID, 'wptm_twitter_oauth', true),
	'sohu' => get_user_meta($user_ID, 'wptm_sohu', true),
	'renren' => get_user_meta($user_ID, 'wptm_renren', true),
	'kaixin001' => get_user_meta($user_ID, 'wptm_kaixin001', true),
	'digu' => get_user_meta($user_ID, 'wptm_digu', true),
	'douban' => get_user_meta($user_ID, 'wptm_douban', true),
	'renjian' => get_user_meta($user_ID, 'wptm_renjian', true),
	'fanfou' => get_user_meta($user_ID, 'wptm_fanfou', true),
	'zuosa' => get_user_meta($user_ID, 'wptm_zuosa', true),
	'follow5' => get_user_meta($user_ID, 'wptm_follow5', true));
	return $account;
}
// 写入数据库
function wp_user_profile_update( $user_ID ) {
	$update = array(
		'username' => trim($_POST['username']),
		'password' => trim($_POST['password'])
		);
	if (isset($_POST['update_twitter'])) {
		update_usermeta( $user_ID, 'wptm_twitter', $update);
		echo $updated;
	} 
	if (isset($_POST['update_sohu'])) {
		update_usermeta( $user_ID, 'wptm_sohu', $update);
		echo $updated;
	}
	if (isset($_POST['update_renren'])) {
		update_usermeta( $user_ID, 'wptm_renren', $update);
		echo $updated;
	}
	if (isset($_POST['update_kaixin001'])) {
		update_usermeta( $user_ID, 'wptm_kaixin001', $update);
		echo $updated;
	}
	if (isset($_POST['update_digu'])) {
		update_usermeta( $user_ID, 'wptm_digu', $update);
		echo $updated;
	} 
	if (isset($_POST['update_douban'])) {
		update_usermeta( $user_ID, 'wptm_douban', $update);
		echo $updated;
	} 
	if (isset($_POST['update_renjian'])) {
		update_usermeta( $user_ID, 'wptm_renjian', $update);
		echo $updated;
	} 
	if (isset($_POST['update_fanfou'])) {
		update_usermeta( $user_ID, 'wptm_fanfou', $update);
		echo $updated;
	} 
	if (isset($_POST['update_zuosa'])) {
		update_usermeta( $user_ID, 'wptm_zuosa', $update);
		echo $updated;
	} 
	if (isset($_POST['update_follow5'])) {
		update_usermeta( $user_ID, 'wptm_follow5', $update);
		echo $updated;
	}
	// delete
	if (isset($_POST['delete_twitter_oauth'])) {
		update_usermeta( $user_ID, 'wptm_twitter_oauth', '');
	}
	if (isset($_POST['delete_qq_oauth'])) {
		update_usermeta( $user_ID, 'wptm_qq', '');
	}
	if (isset($_POST['delete_sina_oauth'])) {
		update_usermeta( $user_ID, 'wptm_sina', '');
	}
	if (isset($_POST['delete_netease_oauth'])) {
		update_usermeta( $user_ID, 'wptm_netease', '');
	}
	if (isset($_POST['delete_douban_oauth'])) {
		update_usermeta( $user_ID, 'wptm_douban', '');
	}
	if (isset($_POST['delete_twitter'])) {
		update_usermeta( $user_ID, 'wptm_twitter', '');
	} 
	if (isset($_POST['delete_sohu'])) {
		update_usermeta( $user_ID, 'wptm_sohu', '');
	}
	if (isset($_POST['delete_renren'])) {
		update_usermeta( $user_ID, 'wptm_renren', '');
	}
	if (isset($_POST['delete_kaixin001'])) {
		update_usermeta( $user_ID, 'wptm_kaixin001', '');
	}
	if (isset($_POST['delete_digu'])) {
		update_usermeta( $user_ID, 'wptm_digu', '');
	} 
	if (isset($_POST['delete_douban'])) {
		update_usermeta( $user_ID, 'wptm_douban', '');
	} 
	if (isset($_POST['delete_renjian'])) {
		update_usermeta( $user_ID, 'wptm_renjian', '');
	} 
	if (isset($_POST['delete_fanfou'])) {
		update_usermeta( $user_ID, 'wptm_fanfou', '');
	} 
	if (isset($_POST['delete_zuosa'])) {
		update_usermeta( $user_ID, 'wptm_zuosa', '');
	}
	if (isset($_POST['delete_follow5'])) {
		update_usermeta( $user_ID, 'wptm_follow5', '');
	}
}
// 发布
function wp_connect_publish($post_ID) {
	global $wptm_options;
	if (get_option('timezone_string')) {
		date_default_timezone_set(get_option('timezone_string'));
		$time = time();
	} elseif (get_option('gmt_offset')) {
		$time = time() + (get_option('gmt_offset') * 3600);
	} 
	$title = strip_tags(get_the_title($post_ID));
	$postlink = get_permalink($post_ID);
	$shortlink = get_bloginfo('url') . "/?p=" . $post_ID;
	$thePost = get_post($post_ID);
	$content = $thePost -> post_content;
	$excerpt = $thePost -> post_excerpt;
	$post_author_ID = $thePost -> post_author;
	$post_content = strip_tags($content);
	$wptm_profile = get_user_meta($post_author_ID, 'wptm_profile', true);
	$account = wp_usermeta_account($post_author_ID);
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
	if (!array_filter($account)) {
		return;
	}

	if ($wptm_options['cat_ids']) { // 不想同步的文章分类ID
		$cat_ids = $wptm_options['cat_ids'];
		$categories = get_the_category($post_ID);
		foreach($categories as $category) {
			$cat_id .= $category -> cat_ID . ',';
		}
		if (wp_in_array($cat_ids, $cat_id)) {
			return;
		} 
	}

	$posttags = get_the_tags($post_ID); // 是否将文章标签当成话题
	if ($posttags && $wptm_options['enable_tags']) {
		foreach($posttags as $tag) {
			$tags .= '#' . $tag -> name . '# ';
		} 
		$tags = ' ' . $tags;
	} 

	if($excerpt) { // 判断是否有摘要
	   $post_content = strip_tags($excerpt);
	}
	$connect = " - ";
	preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
    $sum = count($matches[1]);
    if ($sum > 0) {
	    $pic = $matches[1][0];
    } 
	if (($thePost -> post_status == 'publish' || $_POST['publish'] == 'Publish') && ($_POST['prev_status'] == 'draft' || $_POST['original_post_status'] == 'draft' || $_POST['original_post_status'] == 'auto-draft' || $_POST['prev_status'] == 'pending' || $_POST['original_post_status'] == 'pending')) { // 判断是否为新发布
		$title = $new_prefix . $title;
	} else if ((($_POST['originalaction'] == "editpost") && (($_POST['prev_status'] == 'publish') || ($_POST['original_post_status'] == 'publish'))) && $thePost -> post_status == 'publish') { //判断是否已发布
		if (($time - strtotime($thePost -> post_date) < $update_days) || $update_days == 0) {
			return; //判断当前时间与文章发布时间差
		} 
		$title = $update_prefix . $title;
	} else if ( $_POST['_inline_edit'] ){ // 判断是否是快速编辑
	    $quicktime = $_POST['aa'] . '-' . $_POST['mm'] . '-' .$_POST['jj'] . ' ' .$_POST['hh'] . ':' .$_POST['mn'] . ':00';
	    $quicktime = strtotime($quicktime);
		if (($time - $quicktime < $update_days) || $update_days == 0) {
			return; //判断当前时间与文章发布时间差
		} 
		$title = $update_prefix . $title;
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
	wp_update_list($title, $postlink, $pic , $account);
} 

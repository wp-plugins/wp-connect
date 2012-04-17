<?php
$_SESSION['wp_url_bind'] = '';
$_SESSION['wp_url_back'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$wptm_basic = get_option('wptm_basic');
$wptm_comment = get_option('wptm_comment');
if (empty($wptm_comment['comments_open']) || (!empty($wptm_comment['comments_open']) && comments_open())) {
	$wptm_connect = get_option('wptm_connect');
?>
<script type='text/javascript' charset='utf-8' src='http://open.denglu.cc/connect/commentcode?appid=<?php echo $wptm_basic['appid'];?>&postid=<?php the_ID();?>&title=<?php the_title();?><?php if ($wptm_connect['enable_connect']) { echo (!is_user_logged_in()) ? "&login=false":"&login=true"; echo "&exit=".urlencode(wp_logout_url(get_permalink())); }?>'></script>
<?php } 
?>
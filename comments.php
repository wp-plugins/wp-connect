<?php
$wptm_basic = get_option('wptm_basic');
$wptm_comment = get_option('wptm_comment');
if (empty($wptm_comment['comments_open']) || (!empty($wptm_comment['comments_open']) && comments_open())) {
	$wptm_connect = get_option('wptm_connect');
?>
<script type='text/javascript' charset='utf-8' src='http://open.denglu.cc/connect/commentcode?appid=44161denJJWUCmGkB1fvDf1PCoUD1A&postid=<?php the_ID();?>&title=<?php the_title();?>'></script>
<?php } 
?>
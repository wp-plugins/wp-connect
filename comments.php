<?php
$wptm_basic = get_option('wptm_basic');
$wptm_comment = get_option('wptm_comment');
if (empty($wptm_comment['comments_open']) || (!empty($wptm_comment['comments_open']) && comments_open())) { ?>
<script type='text/javascript' charset='utf-8' src='http://open.denglu.cc/connect/commentcode?appid=<?php echo $wptm_basic['appid'];?>&postid=<?php the_ID();?>&title=<?php the_title();?>'></script>
<?php } ?>
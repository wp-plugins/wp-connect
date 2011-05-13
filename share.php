<?php
function wp_social_share_title() { // 共52个
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

function wp_social_share_url($post_id) {
	$siteurl = urlencode(get_bloginfo('wpurl'));
	$get_the_title = get_the_title($post_id);
	$title = urlencode($get_the_title);
	$permalink = urlencode(get_permalink($post_id));
	$thePost = get_post($post_id);
	$post_content = $thePost -> post_content;
	preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches);
	$sum = count($matches[1]);
	if ($sum > 0) {
		$pic = $pic_qq = $matches[1][0];
		for ($i = 1; $i < $sum ; $i++) {
			$pic_qq .= '|' . $matches[1][$i];
		} 
	} 
	$content = strip_tags($post_content);
	$text = $get_the_title . " - " . $content;
	$content = urlencode(mb_substr($content, 0, 100, 'utf-8') . "……");
	$num = 135 - mb_strlen($permalink, 'UTF-8');
	$t = urlencode(mb_substr($text, 0, $num, 'utf-8') . "……");
	return array("qzone" => "http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=$permalink",
		"sina" => "http://service.t.sina.com.cn/share/share.php?url=$permalink&title=$t&pic=$pic",
		"qq" => "http://v.t.qq.com/share/share.php?title=$t&url=$permalink&site=$siteurl&pic=$pic_qq",
		"sohu" => "http://t.sohu.com/third/post.jsp?title=$t&content=utf-8&url=$permalink",
		"t163" => "http://t.163.com/article/user/checkLogin.do?info={$t} {$permalink}&source=网易微博",
		"pengyou" => "http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?to=pengyou&url=$permalink",
		"hibaidu" => "http://apps.hi.baidu.com/share/?url=$permalink&title=$title",
		"tieba" => "http://tieba.baidu.com/i/app/open_share_api?link=$permalink",
		"msn" => "http://profile.live.com/badge/?url=$permalink&title=$get_the_title&screenshot=$pic",
		"shouji" => "http://go.139.com/ishare.do?shareUrl=$permalink&title=$title",
		"tianya" => "http://share.tianya.cn/openapp/restpage/activity/appendDiv.jsp?ccTitle=$title&ccUrl=$permalink",
		"fanfou" => "http://fanfou.com/sharer?u=$permalink&t=$title",
		"ifeng" => "http://t.ifeng.com/interface.php?_c=share&_a=share&sourceUrl=$permalink&title=$title&pic=&source=1",
		"shequ51" => "http://share.51.com/share/share.php?type=8&title=$title&vaddr=$permalink",
		"sc115" => "http://sc.115.com/add?title=$title&url=$permalink&from=web",
		"zhuaxia" => "http://www.zhuaxia.com/add_channel.php?sourceid=102&url=$siteurl",
		"feixin" => "http://space2.feixin.10086.cn/api/share?source=$siteurl&title=$title&url=$permalink",
		"tongxue" => "http://share.tongxue.com/share/buttonshare.php?link=$permalink&title=$title",
		"youdao" => "http://shuqian.youdao.com/manage?a=popwindow&title=$title&url=$permalink",
		"baidu" => "http://cang.baidu.com/do/add?it=$title&iu=$permalink&dc=$content&fr=ien#nw=1",
		"renren" => "http://share.renren.com/share/buttonshare.do?link=$permalink&title=$title",
		"kaixin001" => "http://www.kaixin001.com/~repaste/repaste.php?rtitle=$title&rurl=$permalink&rcontent=",
		"taojianghu" => "http://share.jianghu.taobao.com/share/addShare.htm?title=$title&url=$permalink&content=",
		"douban" => "http://www.douban.com/recommend/?url=$permalink&title=$title&v=1",
		"qqshuqian" => "http://shuqian.qq.com/post?from=3&title=$title&uri=$permalink&jumpback=2&noui=1",
		"digu" => "http://www.diguff.com/diguShare/fireFox_login.jsp?&title2=$t&url2=$permalink",
		"facebook" => "http://www.facebook.com/sharer.php?u=$permalink&t=$title",
		"twitter" => "http://twitter.com/home?status=$permalink $title",
		"delicious" => "http://del.icio.us/post?url=$permalink&title=$title",
		"digg" => "http://digg.com/submit?phase=2&url=$permalink&title=$title",
		"google" => "http://www.google.com/bookmarks/mark?op=add&bkmk=$permalink&title=$title",
		"buzz" => "http://www.google.com/buzz/post?url=$permalink&imageurl=$pic",
		"yahoo" => "http://myweb.cn.yahoo.com/popadd.html?url=$permalink&title=$title",
		"baishehui" => "http://bai.sohu.com/share/blank/addbutton.do?from=$siteurl&link=$permalink",
		"follow5" => "http://www.follow5.com/f5/discuz/sharelogin.jsp?title=$t&url=$permalink",
		"live" => "https://skydrive.live.com/sharefavorite.aspx/.SharedFavorites??url=$permalink&title=$title",
		"hexun" => "http://t.hexun.com/channel/shareweb.aspx?url=$permalink&title=$title&source=bookmark",
		"xianguo" => "http://xianguo.com/service/submitfav/?link=$permalink&title=$title&notes=",
		"zuosa" => "http://zuosa.com/collect/Collect.aspx?t=$title&u=$permalink",
		"shuoke" => "http://shequ.10086.cn/share/share.php?source=shareto139_Passit&tl=&title=$title&tourl=$permalink",
		"myspace" => "http://www.myspace.com/Modules/PostTo/Pages/?u=$permalink&t=$title&c=",
		"waakee" => "http://www.waakee.com/submit.php?url=$permalink",
		"leshou" => "http://leshou.com/post?act=shou&reuser=&url=$permalink&title=$title&intro=",
		"mop" => "http://tk.mop.com/api/post.htm?url=$permalink&title=$title&desc=",
		"cnfol" => "http://t.cnfol.com/share.php?url=$permalink&title=$title&source=$siteurl",
		"douban9" => "http://www.douban.com/recommend/?url=$permalink&title=$title",
		"dream163" => "http://dream.163.com/share/link/?url=$permalink&title=$title&content=",
		"taonan" => "http://dev.51taonan.com/?page=share_dialog&url=$permalink",
		"club189" => "http://club.189.cn/share/?act=outsite&title=$title&url=$permalink&content=",
		"baohe" => "http://www.baohe.com/?c=article-index&a=crawlarticle&title=$title&url=$permalink&clipboard=0",
		"renmaiku" => "http://www.renmaiku.com/s/outsideShare.html?url=$permalink&title=$title",
		"ushi" => "http://www.ushi.cn/feedShare/feedShare!sharetomicroblog.jhtml?type=button&loginflag=share&title=$title&url=$permalink");
} 

function wp_social_share_js() {
	global $wptm_share;
	$plugin_url = WP_PLUGIN_URL . '/wp-connect';
	$wpurl = get_bloginfo('wpurl');
	$id = $wptm_share['id'];
	echo ($wptm_share['css']) ? "<link rel=\"stylesheet\" href=\"$plugin_url/share.css\" type=\"text/css\" media=\"all\" />\n" : "";
	echo <<<EOT
<script type="text/javascript">
function social_share(post_id, share) {
    _gaq.push(["_trackEvent", "ShareSocial", "Share", share, 1]);
    window.open('$wpurl/?share=' + share + ',' + post_id, share, "width=600,height=400,left=150,top=100,scrollbar=no,resize=no");
}
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '$id']);
_gaq.push(['_trackPageview']);

(function () {
    var ga = document.createElement('script');
    ga.type = 'text/javascript';
    ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(ga, s);
})();
</script>
EOT;
} 
add_action('wp_head', 'wp_social_share_js');

function sociables_google_analytics() {
	$share = explode(",", $_GET['share']);
	$get_the_title = get_the_title($share[1]);
	$urls = wp_social_share_url($share[1]);
	if (empty($get_the_title)) {
	} else {
		foreach($urls as $key => $url) {
			if ($share[0] == $key) {
				header("Location: $url");
			} 
		} 
	} 
} 
// 使用 Google Analytics
if ($wptm_share['analytics']) {
	add_action('get_header', 'sociables_google_analytics');
} 

function wp_social_share_button($number = '') {
	global $wptm_share;
	$post_id = get_the_ID();
	$title = wp_social_share_title();
	$select = explode(",", $wptm_share['select']);
	//$select = array_keys($title);
	if($number) $select = array_slice($select, 0, $number);
	if ($wptm_share['button'] == 1 || $wptm_share['button'] == 2) {
		if ($wptm_share['size'] == 16) {
			$css = ' class="icon16"';
		} elseif ($wptm_share['size'] == 32) {
			$css = ' class="icon32"';
		} 
	} 
	$share = "<div id=\"sociables\"$css><span>{$wptm_share['text']}</span>";
	foreach($select as $key) {
		$text = ($wptm_share['button'] == 2 || $wptm_share['button'] == 3) ? $title[$key] : '';
		if ($wptm_share['analytics']) {
			$share .= "<a href=\"javascript:;\" onclick=\"social_share('$post_id','$key');\" class=\"$key\" title=\"$title[$key]\" rel=\"nofollow\">$text</a>";
		} else {
			$url = wp_social_share_url($post_id);
			$share .= "<a href=\"$url[$key]\" class=\"$key\" title=\"$title[$key]\" target=\"_blank\" rel=\"nofollow\" >$text</a>";
		} 
	} 
	$share .= "</div>";
	return $share;
} 

function wp_social_share($number) {
	echo wp_social_share_button($number);
} 

function wp_social_share_add($content) {
	if (is_singular()) {
		$content .= wp_social_share_button();
	} 
	return $content;
} 
// 添加到文章末尾
if ($wptm_share['enable_share'] == 1) {
	add_action('the_content', 'wp_social_share_add');
} 

?>
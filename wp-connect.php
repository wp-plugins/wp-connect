<?php
/*
Plugin Name: WordPress连接微博
Author: 水脉烟香
Author URI: http://www.smyx.net/
Plugin URI: http://www.smyx.net/wp-connect.html
Description: 支持使用15家合作网站帐号登录 WordPress 博客，并且支持同步文章的 标题和链接 到14大微博和社区。<strong>注意：捐赠版已经更新到1.6.5 版本，请到群内下载升级！</strong>
Version: 1.9.19
*/

define('WP_CONNECT_VERSION', '1.9.19');
$wpurl = get_bloginfo('wpurl');
$siteurl = get_bloginfo('url');
$plugin_url = plugins_url('wp-connect');
$wptm_options = get_option('wptm_options');
$wptm_connect = get_option('wptm_connect');
$wptm_advanced = get_option('wptm_advanced');
$wptm_share = get_option('wptm_share');
$wptm_version = get_option('wptm_version');
$wptm_key = get_option('wptm_key');
$wp_connect_advanced_version = "1.6.5";

if ($wptm_version && $wptm_version != WP_CONNECT_VERSION) {
	if (version_compare($wptm_version, '2.2.1', '<')) { // 搜狐微博替换app key
		$keybug = 1;
	}
	update_option('wptm_version', WP_CONNECT_VERSION);
}

add_action('admin_menu', 'wp_connect_add_page');

include_once(dirname(__FILE__) . '/functions.php');
include_once(dirname(__FILE__) . '/update.php');
include_once(dirname(__FILE__) . '/sync.php');
include_once(dirname(__FILE__) . '/connect.php');
include_once(dirname(__FILE__) . '/page.php');

if (!$wptm_key) {
	update_option('wptm_key', get_appkey());
} elseif ($keybug) { // 1.9.18
	if ($wptm_key[5][0]) {
		$wptm_key[5] = array(ifold($wptm_key[5][0], 'O9bieKU1lSKbUBI9O0Nf', 'UfnmJanXwQZjD1TvZwTd'), ifold($wptm_key[5][1], 'k328Nm7cfUq0kY33solrWufDr(Tsordf1ek=bO5u', 'Ur7MxoeTc7tegk11!1mTvHg-rp0yJdR5G8mZi7c2'));
		if (update_option('wptm_key', $wptm_key))
			update_option('wptm_sohu', '');
	} 
}

if ($wptm_connect['enable_connect'] && $wptm_connect['widget']) {
	include_once(dirname(__FILE__) . '/widget.php');
}

function wp_connect_add_page() {
	add_options_page('WordPress连接微博', 'WordPress连接微博', 'manage_options', 'wp-connect', 'wp_connect_do_page');
}

function donate_version($version, $operator = '<') {
	if (function_exists('wp_connect_advanced') && version_compare(WP_CONNECT_ADVANCED_VERSION, $version, $operator)) {
		return true;
	}
}

function is_donate() {
	if (function_exists('wp_connect_advanced') && WP_CONNECT_ADVANCED == "true") {
		return true;
	}
}

function wp_connect_warning() {
	global $wp_version,$wp_connect_advanced_version,$wptm_options, $wptm_connect, $wptm_version;
	if (version_compare($wp_version, '3.0', '<') || (donate_version($wp_connect_advanced_version) && WP_CONNECT_ADVANCED_VERSION != '1.4.3') || (($wptm_options || $wptm_connect) && !$wptm_version) || (!$wptm_connect && !$wptm_options)) {
		echo '<div class="updated">';
		if (version_compare($wp_version, '3.0', '<')) {
			echo '<p><strong>您的WordPress版本太低，请升级到WordPress3.0或者更高版本，否则不能正常使用“WordPress连接微博”。</strong></p>';
		} 
		if (donate_version($wp_connect_advanced_version) && WP_CONNECT_ADVANCED_VERSION != '1.4.3') {
			echo "<p><strong>您的“WordPress连接微博 高级设置”(捐赠版)版本太低，请到QQ群内下载最新版，解压后用ftp工具上传升级！</strong></p>";
		} 
		if (($wptm_options || $wptm_connect) && !$wptm_version) {
			echo '<p><strong>重要更新：从1.7.3版本开始，加入对同步帐号密码的加密处理，非OAuth授权的网站，请重新填写帐号和密码！然后请点击一次“同步设置”下面的“保存更改”按钮关闭提示。<a href="options-general.php?page=wp-connect">现在去更改</a></strong></p>';
		}
		if (!$wptm_options && !$wptm_connect) {
			echo '<p><strong>您还没有对“WordPress连接微博”进行设置，<a href="options-general.php?page=wp-connect">现在去设置</a></strong></p>';
		} 
		echo '</div>';
	}
}
add_action('admin_notices', 'wp_connect_warning');

// 设置
function wp_connect_do_page() {
	global $wpurl,$plugin_url,$wptm_donate;
	wp_connect_update();
	$wptm_options = get_option('wptm_options');
	$wptm_connect = get_option('wptm_connect');
	$wptm_key = get_option('wptm_key');
	$blog_token = get_option('blog_token');
    $qq = get_option('wptm_openqq');
    $sina = get_option('wptm_opensina');
	if (function_exists('wp_connect_advanced')) {
	    wp_connect_advanced();
		$wptm_blog = get_option('wptm_blog');
		$blog_options = get_option('wptm_blog_options');
		$wptm_share = get_option('wptm_share');
		$wptm_advanced = get_option('wptm_advanced');
		if (WP_CONNECT_ADVANCED != "true"){
			$error = '<div id="tml-tips" class="updated"><p>请先在高级设置项填写正确授权码！</p></div>';
			if (donate_version('1.5', '>')) {
				$update_tips = '<div id="tml-tips" class="updated"><p>更新提示：2011年10月8日更新了捐赠版授权码的算法，在这之前获得的授权码需要更新，请<a href="http://loginsns.com/key.php" target="_blank">点击这里</a>。</p></div>';
			}
		} else {
			if (donate_version('1.5.2')) {
				$donate_152 = '<div id="tml-tips" class="updated"><p>该捐赠版本不能使用该功能！</p></div>';
			}
		}
	} else {
		$error = '<div id="tml-tips" class="updated"><p>该功能属于<a href="http://loginsns.com/wiki/wordpress/donate" target="_blank">高级设置</a>的一部分(捐赠版)。</p></div>';
	    $disabled = " disabled";
	}
	$account = wp_option_account();
	$_SESSION['user_id'] = '';
	$_SESSION['wp_url_bind'] = WP_CONNECT;
?>
<div class="wrap">
  <div id="icon-themes" class="icon32"><br /></div><h2>WordPress连接微博</h2><div style="float:right;"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZWMTWK2DGHCYS" target="_blank" title="PayPal"><img src="<?php echo $plugin_url;?>/images/donate_paypal.gif" /></a>/ <a href="https://me.alipay.com/smyx" target="_blank">支付宝</a></div>
  <div class="tabs">
    <ul class="nav">
      <li><a href="#sync" class="sync">同步微博</a></li>
      <li><a href="#connect" class="connect">登录设置</a></li>
	  <li><a href="#open" class="open">开放平台</a></li>
	  <li><a href="#blog" class="blog">同步博客</a></li>
      <li><a href="#share" class="share">分享设置</a></li>
      <li><a href="#advanced" class="advanced">高级设置</a></li>
      <li><a href="#check" class="check">环境检查</a></li>
      <li><a href="http://loginsns.com/wiki/wordpress/function" target="_blank">帮助文档</a></li>
    </ul>
    <div id="sync">
      <form method="post" action="options-general.php?page=wp-connect">
        <?php wp_nonce_field('sync-options');?>
        <h3>同步微博</h3>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">是否开启“微博同步”功能</td>
            <td><input name="enable_wptm" type="checkbox" value="1" <?php if($wptm_options['enable_wptm']) echo "checked "; ?>></td>
          </tr>
          <tr>
            <th>同步内容设置</th>
            <td><input name="sync_option" type="text" size="1" maxlength="1" value="<?php echo (!$wptm_options) ? '2' : $wptm_options['sync_option']; ?>" onkeyup="value=value.replace(/[^1-5]/g,'')" /> (填数字，留空为不同步，只对本页绑定的帐号有效！)<br />提示: 1. 标题+链接 2. 标题+摘要/内容+链接 3.文章摘要/内容 4. 文章摘要/内容+链接 5. 标题 + 内容 <br /> 自定义标题格式：<input name="format" type="text" size="25" value="<?php echo $wptm_options['format']; ?>" /> ( 标题: <code>%title%</code>，会继承上面的设置，可留空。 )<br />把以下内容当成微博话题 (<label><input name="enable_cats" type="checkbox" value="1" <?php if($wptm_options['enable_cats']) echo "checked "; ?>>文章分类</label> <label><input name="enable_tags" type="checkbox" value="1" <?php if($wptm_options['enable_tags']) echo "checked "; ?>>文章标签</label>) <label><input name="disable_pic" type="checkbox" value="1" <?php checked($wptm_options['disable_pic']); ?>>不同步图片</label> <label><input name="thumbnail" type="checkbox" value="1" <?php checked($wptm_options['thumbnail']); ?>/>优先同步特色图像</label></td>
          </tr>
          <tr>
            <th>自定义消息</th>
            <td>新文章前缀: <input name="new_prefix" type="text" size="10" value="<?php echo $wptm_options['new_prefix']; ?>" /> 更新文章前缀: <input name="update_prefix" type="text" size="10" value="<?php echo $wptm_options['update_prefix']; ?>" /> 更新间隔: <input name="update_days" type="text" size="2" maxlength="4" value="<?php echo ($wptm_options['update_days']) ? $wptm_options['update_days'] : '0'; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 天 [0=修改文章时不同步] </td>
          </tr>
          <tr>
            <td width="25%" valign="top">禁止同步的文章分类ID (<a href="http://loginsns.com/wiki/wordpress/faqs#cat-ids" target="_blank">数字ID</a>)</td>
            <td><input name="cat_ids" type="text" value="<?php echo $wptm_options['cat_ids']; ?>" /> 用英文逗号(,)分开 (设置后该ID分类下的文章将不会同到微博)</td>
          </tr>
          <tr>
            <td width="25%" valign="top">自定义页面(一键发布到微博)</td>
            <td>自定义密码: <input name="page_password" type="password" value="<?php echo $wptm_options['page_password']; ?>" autocomplete="off" />
               [ <a href="http://loginsns.com/wiki/wordpress/faqs#page" target="_blank">如何使用？</a> ] <label><input name="disable_ajax" type="checkbox" value="1" <?php if($wptm_options['disable_ajax']) echo "checked "; ?>>禁用AJAX无刷新提交</label></td>
          </tr>
          <tr>
            <td width="25%" valign="top">多作者博客</td>
            <td><label><input name="multiple_authors" type="checkbox" value="1" <?php if($wptm_options['multiple_authors']) echo "checked "; ?>>是否让每个作者发布的文章同步到他们各自绑定的微博上，可以通知他们在 <a href="<?php echo admin_url('profile.php');?>">我的资料</a> 里面设置。</label></td>
          </tr>
          <tr>
            <td width="25%" valign="top">自定义短网址</td>
            <td><label><input name="enable_shorten" type="checkbox"  value="1" <?php checked(!$wptm_options || $wptm_options['enable_shorten']); ?>>博客默认 ( http://yourblog.com/?p=1 )</label> <label><strong>短网址</strong> <select name="t_cn"><option value="">选择</option><option value="1"<?php selected($wptm_options['t_cn'] == "1");?>>t.cn (新浪)</option><option value="2"<?php selected($wptm_options['t_cn'] == "2");?>>dwz.cn (百度)</option></select></label></td>
          </tr>
          <tr>
            <td width="25%" valign="top">Twitter是否使用代理？</td>
            <td><label title="国外主机用户不要勾选噢！"><input name="enable_proxy" type="checkbox" value="1" <?php if($wptm_options['enable_proxy']) echo "checked "; ?>>(选填) 国内主机用户必须勾选才能使用Twitter</label> [ <a href="http://www.smyx.net/apps/oauth.php" target="_blank">去获取授权码</a> ]</td>
          </tr>
          <tr>
            <td width="25%" valign="top">我不能绑定帐号</td>
            <td><label title="帐号绑定出错时才勾选噢！"><input name="bind" type="checkbox" value="1" <?php if($wptm_options['bind']) echo "checked "; ?>>(选填) 勾选后可以在帐号绑定下面手动填写授权码</label> [ <a href="http://www.smyx.net/apps/oauth.php" target="_blank">去获取授权码</a> ]</td>
          </tr>
          <tr>
            <td width="25%" valign="top">服务器时间校正</td>
            <td>假如在使用 腾讯微博 时出现 “没有oauth_token或oauth_token不合法，请返回重试！” 才需要填写。请点击上面的“环境检查”，里面有一个当前服务器时间，跟你电脑(北京时间)比对一下，看相差几分钟！[ <a href="http://loginsns.com/wiki/wordpress/faqs#phptime" target="_blank">查看详细</a> ] <br />( 比北京时间 <select name="char"><option value="-1"<?php selected($wptm_options['char'] == "-1");?>>快了</option><option value="1"<?php selected($wptm_options['char'] == "1");?> >慢了</option></select> <input name="minutes" type="text" size="2" value="<?php echo $wptm_options['minutes'];?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 分钟 )</td>
          </tr>
        </table>
        <p class="submit">
          <input type="submit" name="update_options" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
      <?php include( dirname(__FILE__) . '/bind.php' );?>
    </div>
    <div id="connect">
      <form method="post" action="options-general.php?page=wp-connect#connect">
        <?php wp_nonce_field('connect-options');?>
        <h3>登录设置</h3>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">是否开启“社会化登录”功能</td>
            <td><input name="enable_connect" type="checkbox" value="1" <?php if($wptm_connect['enable_connect']) echo "checked "; ?>></td>
          </tr>
		  <tr>
			<td width="25%" valign="top">注册信息</td>
			<td><label><input type="checkbox" name="reg" value="1" <?php if($wptm_connect['reg']) echo "checked "; ?>/>用户首次登录时，强制要求用户填写个人信息</label></td>
		  </tr>
          <tr>
            <td width="25%" valign="top">显示设置</td>
            <td><label><input name="manual" type="radio" value="2" <?php checked(!$wptm_connect['manual'] || $wptm_connect['manual'] == 2); ?>>评论框处(默认)</label> <label><input name="manual" type="radio" value="1" <?php checked($wptm_connect['manual'] == 1);?>>调用函数</label> ( <code>&lt;?php wp_connect();?&gt;</code> ) [ <a href="http://loginsns.com/wiki/wordpress/faqs#connect-manual" target="_blank">详细说明</a> ]</td>
          </tr>
          <tr>
            <td width="25%" valign="top">添加按钮</td>
            <td><label><input name="qqlogin" type="checkbox" value="1" <?php if($wptm_connect['qqlogin']) echo "checked ";?><?php echo $disabled;?> />QQ登录</label>
			  <label><input name="sina" type="checkbox" value="1" <?php if($wptm_connect['sina']) echo "checked ";?> />新浪微博</label>
              <label><input name="qq" type="checkbox" value="1" <?php if($wptm_connect['qq']) echo "checked ";?> />腾讯微博</label>
			  <label><input name="taobao" type="checkbox" value="1" <?php if($wptm_connect['taobao']) echo "checked ";?><?php echo $disabled;?> />淘宝网</label>
			  <label><input name="baidu" type="checkbox" value="1" <?php if($wptm_connect['baidu']) echo "checked ";?><?php echo $disabled;?> />百度</label>
              <label><input name="sohu" type="checkbox" value="1" <?php if($wptm_connect['sohu']) echo "checked ";?> />搜狐微博</label>
              <label><input name="netease" type="checkbox" value="1" <?php if($wptm_connect['netease']) echo "checked ";?> />网易微博</label><br />
              <label><input name="renren" type="checkbox" value="1" <?php if($wptm_connect['renren']) echo "checked ";?> />人人网</label>
              <label><input name="kaixin001" type="checkbox" value="1" <?php if($wptm_connect['kaixin001']) echo "checked ";?><?php echo $disabled;?> />开心网</label>
              <label><input name="douban" type="checkbox" value="1" <?php if($wptm_connect['douban']) echo "checked ";?> />豆瓣</label>
              <label><input name="tianya" type="checkbox" value="1" <?php if($wptm_connect['tianya']) echo "checked "; ?><?php echo $disabled;?> />天涯</label>
			  <label><input name="msn" type="checkbox" value="1" <?php if($wptm_connect['msn']) echo "checked ";?><?php echo $disabled;?> />MSN</label>
			  <label><input name="google" type="checkbox" value="1" <?php if($wptm_connect['google']) echo "checked ";?><?php echo $disabled;?> />谷歌</label>
			  <label><input name="yahoo" type="checkbox" value="1" <?php if($wptm_connect['yahoo']) echo "checked ";?><?php echo $disabled;?> />雅虎</label>
			  <label><input name="twitter" type="checkbox" value="1" <?php if($wptm_connect['twitter']) echo "checked ";?> />Twitter</label>
			  <br /><span style="color:green;">在使用社会化登录时，部分网站需要去合作网站的开放平台处申请app key，然后在 本插件的 <a href="#open" class="open">开放平台</a> 页面填写。</span>
            </td>
          </tr>
          <tr>
            <td width="25%" valign="top">@微博帐号</td>
            <td>新浪微博昵称: <input name="sina_username" type="text" size="10" value='<?php echo $wptm_connect['sina_username'];?>' /> 腾讯微博帐号: <input name="qq_username" type="text" size="10" value='<?php echo $wptm_connect['qq_username'];?>' /><br />搜狐微博昵称: <input name="sohu_username" type="text" size="10" value='<?php echo $wptm_connect['sohu_username'];?>' /> 网易微博昵称: <input name="netease_username" type="text" size="10" value='<?php echo $wptm_connect['netease_username'];?>' /><br />(说明：有新的评论时将以 @微博帐号 的形式显示在您跟评论者相对应的微博上，仅对方勾选了同步评论到微博时才有效！注：腾讯微博帐号不是QQ号码)</td>
          </tr>
		  <tr>
			<td width="25%" valign="top">小工具</td>
			<td><label><input type="checkbox" name="widget" value="1" <?php if($wptm_connect['widget']) echo "checked "; ?>/>是否开启边栏登录按钮 (开启后到<a href="widgets.php">小工具</a>拖拽激活)</label></td>
		  </tr>
		  <tr>
			<td width="25%" valign="top">同步帐号</td>
			<td><label><input type="checkbox" name="sync" value="1" <?php if($wptm_connect['sync']) echo "checked "; ?>/>用户首次登录的时候也绑定同步帐号（个人资料的帐号绑定）</label></td>
		  </tr>
		  <tr>
			<td width="25%" valign="top">禁止头像</td>
			<td><label><input type="checkbox" name="head" value="1" <?php if($wptm_connect['head']) echo "checked "; ?>/>不使用登录者的微博/社区头像作为她的头像</label></td>
		  </tr>
		  <tr>
			<td width="25%" valign="top">中文用户名</td>
			<td><label><input type="checkbox" name="chinese_username" value="1" <?php if(default_values('chinese_username', 1, $wptm_connect)) echo "checked "; ?>/>支持中文用户名</label></td>
		  </tr>
          <tr>
            <td width="25%" valign="top">禁止注册的用户名</td>
            <td><input name="disable_username" type="text" size="60" value='<?php echo $wptm_connect['disable_username'];?>' /> 用英文逗号(,)分开</td>
          </tr>
        </table>
        <p class="submit">
          <input type="submit" name="wptm_connect" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
	  <h3>其他登录插件</h3>
	  <p style="color:green">假如你以前使用过其他类似的登录插件（<a href="http://loginsns.com/wiki/wordpress/plugins" target="_blank">查看列表</a>），可以点击以下按钮进行数据转换，以便旧用户能使用本插件正常登录。</p>
      <form method="post" action="options-general.php?page=wp-connect#connect">
	    <?php wp_nonce_field('other-plugins');?>
		<span class="submit"><input type="submit" name="other_plugins" value="其他登录插件数据转换" /> (可能需要一些时间，请耐心等待！)</span>
	  </form>
      <div id="tml-tips" class="updated">
		<p><strong>高级评论</strong></p>
		<p><strong>该功能属于<a href="http://loginsns.com/wiki/wordpress/donate" target="_blank">高级设置</a>的一部分(捐赠版)。</strong></p>
		<p>捐赠用户还可以这样玩转评论：[ <a href="http://loginsns.com/wiki/wordpress/comment" target="_blank">查看详细</a> ]</p>
        <p>假设A是管理员，B和C是新浪微博用户，D是腾讯微博用户。</p>
        <p>①新浪微博用户 B 在网站上评论并勾选了同步到微博，假设同步后的微博消息为 F ，那么管理员A和同是新浪微博用户的C回复时，可以不必勾选同步(系统将自动判断)，会直接在你的网站和B的微博消息 F 下评论。<br />②假如腾讯微博用户 D 回复了A在网站上的评论，那么他会借用 <span style="color:green;">高级设置 填写的 默认用户ID 对应的WP帐号下绑定的新浪微博帐号</span>通知B，B的微博消息 F 下会显示如下评论：“腾讯微博网友(D)在网站上的评论: 评论内容”。<br />注意：①中提到的功能只支持腾讯微博和新浪微博，其他微博以 @帐号 的形式同步回复。</p>
        <p><strong>所有非捐赠用户仅支持 @微博帐号 的形式同步评论。</strong></p>
        <p><strong>提示：管理员请用 高级设置填写的 默认用户ID 对应的WP帐号 <?php echo get_username($wptm_advanced['user_id']);?> 登录本站，然后在<a href="<?php echo admin_url('profile.php');?>">我的资料</a>页面绑定登录帐号(腾讯、新浪微博)！</strong></p>
      </div>
    </div>
    <div id="open">
      <form method="post" action="options-general.php?page=wp-connect#open">
        <?php wp_nonce_field('openkey-options');?>
		<h3>开放平台</h3>
        请在下面填写开放平台的key，填写后，同步时可以显示来源，即显示微博的“来自XXX”，在使用合作网站登录时能显示您的网站信息，<span style="color: red;">加*号的为使用时必填！</span>
		<p><strong>QQ登录</strong> ( APP ID: <input name="qq1" type="text" value='<?php echo $wptm_key[13][0];?>' /> APP Key: <input name="qq2" type="text" value='<?php echo $wptm_key[13][1];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/qq" target="_blank">如何获取?</a> ] ) *</p>
		<p><strong>新浪微博</strong> ( App Key: <input name="sina1" type="text" value='<?php echo $sina['app_key'];?>' /> App Secret: <input name="sina2" type="text" value='<?php echo $sina['secret'];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs#key" target="_blank">如何获取?</a> ] )</p>
		<p><strong>腾讯微博</strong> ( App Key: <input name="tqq1" type="text" value='<?php echo $qq['app_key'];?>' /> App Secret: <input name="tqq2" type="text" value='<?php echo $qq['secret'];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs#key" target="_blank">如何获取?</a> ] )</p>
		<p><strong>搜狐微博</strong> ( Consumer Key: <input name="sohu1" type="text" value='<?php echo $wptm_key[5][0];?>' /> Consumer secret: <input name="sohu2" type="text" value='<?php echo $wptm_key[5][1];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs#key" target="_blank">如何获取?</a> ] )</p>
		<p><strong>网易微博</strong> ( Consumer Key: <input name="netease1" type="text" value='<?php echo $wptm_key[6][0];?>' /> Consumer secret: <input name="netease2" type="text" value='<?php echo $wptm_key[6][1];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs#key" target="_blank">如何获取?</a> ] )</p>
		<p><strong>人人网</strong> ( API Key: <input name="renren1" type="text" value='<?php echo $wptm_key[7][0];?>' /> Secret Key: <input name="renren2" type="text" value='<?php echo $wptm_key[7][1];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/renren" target="_blank">如何获取?</a> ] ) *</p>
		<p><strong>开心网</strong> ( API Key: <input name="kaixin1" type="text" value='<?php echo $wptm_key[8][0];?>' /> Secret Key: <input name="kaixin2" type="text" value='<?php echo $wptm_key[8][1];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/kaixin001" target="_blank">如何获取?</a> ] ) *</p>
		<p><strong>百度</strong> ( API Key: <input name="baidu1" type="text" value='<?php echo $wptm_key[19][0];?>' /> Secret Key: <input name="baidu2" type="text" value='<?php echo $wptm_key[19][1];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/baidu" target="_blank">如何获取?</a> ] ) *</p>
		<p><strong>MSN</strong> ( Client ID: <input name="msn1" type="text" value='<?php echo $wptm_key[2][0];?>' /> Client secret: <input name="msn2" type="text" value='<?php echo $wptm_key[2][1];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/msn" target="_blank">如何获取?</a> ] ) *</p>
		<p><strong>淘宝网</strong> ( App Key: <input name="taobao1" type="text" value='<?php echo $wptm_key[16][0];?>' /> App Secret: <input name="taobao2" type="text" value='<?php echo $wptm_key[16][1];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/taobao" target="_blank">如何获取?</a> ] ) *</p>
        <div id="tml-tips" class="updated"><p>淘宝网回调地址：<code><?php echo $plugin_url.'-advanced/login.php';?></code></p></div>
        <p class="submit">
          <input type="submit" name="wptm_key" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
	</div>
    <div id="blog">
      <form method="post" action="options-general.php?page=wp-connect#blog">
        <?php wp_nonce_field('blog-options');?>
        <h3>同步博客</h3>
		<?php echo $error.$donate_152;?>
		<p>( 友情提醒：同时开启同步微博和同步博客会导致发布文章缓慢或者响应超时！)</p>
	    <table class="form-table">
            <tr>
                <td width="25%" valign="top">是否开启“同步博客”功能</td>
                <td><input name="enable_blog" type="checkbox" value="1" <?php if($blog_options[0]) echo "checked "; ?>></td>
            </tr>
		    <tr>
			    <td width="25%" valign="top">是否添加文章版权信息</td>
			    <td><input type="checkbox" name="copyright" value="1" <?php if($blog_options[1]) echo "checked "; ?>/></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">绑定帐号 (开放平台接口)</td>
			    <td>
				<?php 
	            if ($blog_token['qq']) {$b1 = "del"; $b2 = '(已绑定)';} else {$b1 = "bind"; $b2 = '';}
                if ($blog_token['renren']) {$b3 = "del"; $b4 = '(已绑定)';} else {$b3 = "bind"; $b4 = '';}
                if ($blog_token['kaixin']) {$b5 = "del"; $b6 = '(已绑定)';} else {$b5 = "bind"; $b6 = '';}?>
				<a href="<?php echo $plugin_url;?>-advanced/blogbind.php?<?php echo $b1;?>=qzone">QQ空间<?php echo $b2;?></a> 、 <a href="<?php echo $plugin_url;?>-advanced/blogbind.php?<?php echo $b3;?>=renren">人人网<?php echo $b4;?></a> 、 <a href="<?php echo $plugin_url;?>-advanced/blogbind.php?<?php echo $b5;?>=kaixin">开心网<?php echo $b6;?></a> (使用前，请先到 <a href="#open" class="open">开放平台</a> 页面填写申请的key)</td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">新浪博客</td>
			    <td><label>邮 箱: <input type="text" name="user_sina" value="<?php echo $wptm_blog[0][1];?>" /></label> <label>密 码: <input type="password" name="pass_sina" /></label><?php if($wptm_blog[0][2]) echo ' (密码留空表示不修改)';?></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">网易博客</td>
			    <td><label>邮 箱: <input type="text" name="user_163" value="<?php echo $wptm_blog[1][1];?>" /></label> <label>密 码: <input type="password" name="pass_163" /></label><?php if($wptm_blog[1][2]) echo ' (密码留空表示不修改)';?></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">QQ空间 (邮箱接口，建议使用开放平台接口)</td>
			    <td><label>Q Q: <input type="text" name="user_qzone" value="<?php echo $wptm_blog[2][1];?>" /></label> <label>密 码: <input type="password" name="pass_qzone" /></label><?php if($wptm_blog[2][2]) echo ' (密码留空表示不修改)';?></td>
		    </tr>
        </table>
        <p class="submit">
		  <input type="hidden" name="expiry" value="<?php echo $blog_options[2]; ?>" />
          <input type="submit" name="blog_options" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
      <form method="post" action="options-general.php?page=wp-connect#blog">
	    <p>如果你觉得QQ空间同步需要申请APP key比较麻烦，您可以使用邮箱接口，点击下面按钮进行检测。</p>
        <p><?php if (isset($_POST['verify_qzone'])) verify_qzone();?></p>
		<p class="submit"><input type="submit" name="verify_qzone" value="检查是否支持同步到QQ空间(邮箱接口)" /></p>
	  </form>
      <div id="tml-tips" class="updated">
	    <p><strong>注意事项</strong></p>
        <p>1、新浪博客、网易博客修改文章时会同步修改对应的博客文章，而不是创建新的博客文章。<br />2、QQ空间、人人网、开心网只会同步一次，下次修改文章时不会再同步。<br />3、快速编辑和密码保护的文章不会同步或更新。<br />4、同步时在新浪等博客文章末尾会添加插件作者版权链接，使用30天后将不再添加！<br />5、当开启多作者博客时，只有在“高级设置”填写的 默认用户ID对应的WP帐号 <?php echo get_username($wptm_advanced['user_id']);?> 发布文章时才会同步到博客。<br />6、有效期：人人网和开心网1个月，QQ空间3个月，发现不能同步时请重新绑定帐号。<br />7、使用QQ空间开放平台接口同步时，请确保已经激活 <code>add_one_blog</code>，否则请解除绑定！<br /><strong>8、绑定人人网、开心网帐号时，也会绑定“同步微博”下人人网、开心网的新鲜事/状态同步。你可以根据情况删除其中的一个。</strong></p>
	  </div>
    </div>
    <div id="share">
      <form method="post" id="formdrag" action="options-general.php?page=wp-connect#share">
        <?php wp_nonce_field('share-options');?>
        <h3>分享设置</h3>
		<?php echo $error;?>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">添加按钮</td>
            <td><label><input name="enable_share" type="radio" value="3" <?php checked($wptm_share['enable_share'] == 3); ?>> 文章前面</label> <label><input name="enable_share" type="radio" value="1" <?php checked(!$wptm_share['enable_share'] || $wptm_share['enable_share'] == 1); ?>> 文章末尾</label> <label><input name="enable_share" type="radio" value="2" <?php checked($wptm_share['enable_share'] == 2); ?>> 调用函数</label> ( <code>&lt;?php wp_social_share();?&gt;</code> ) [ <a href="http://loginsns.com/wiki/wordpress/share" target="_blank">详细说明</a> ]</td>
          </tr>
          <tr>
            <td width="25%" valign="top">样式选择</td>
            <td><label title="假如没有复制到主题样式中，请务必勾选！"><input name="css" type="checkbox" value="1" <?php checked(!$wptm_share || $wptm_share['css']); ?> />使用插件自带share.css文件 (建议复制样式到主题css文件中，以免升级时被覆盖！)</label>
            </td>
          </tr>
          <tr>
            <td width="25%" valign="top">显示设置</td>
            <td><label>分享按钮前面的文字: <input name="text" type="text" value='<?php echo $wptm_share['text'];?>' /></label><br /><label><input name="button" type="radio" value="1" <?php checked(!$wptm_share['button'] || $wptm_share['button'] == 1); ?> />显示图标按钮</label> ( 选择尺寸 <select name="size"><option value="16"<?php if($wptm_share['size'] == 16) echo " selected";?>>小图标</option><option value="32"<?php if($wptm_share['size'] == 32) echo " selected";?> >大图标</option></select> ) <label><input name="button" type="radio" value="2" <?php if($wptm_share['button'] == 2) echo "checked "; ?> />显示图文按钮</label> <label><input name="button" type="radio" value="3" <?php if($wptm_share['button'] == 3) echo "checked "; ?> />显示文字按钮</label></td>
          </tr>
		  <tr>
			<td width="25%" valign="top">Google Analytics</td>
			<td><label><input type="checkbox" name="analytics" value="1" <?php if($wptm_share['analytics']) echo "checked "; ?>/>使用 Google Analytics 跟踪社会化分享按钮的使用效果</label> [ <a href="http://loginsns.com/wiki/wordpress/share#ga" target="_blank">查看说明</a> ]<br /><label>配置文件ID: <input type="text" name="id" value="<?php echo $wptm_share['id'];?>" /></label></td>
		  </tr>
		  <?php if(!$donate_152) { ?>
		  <tr>
			<td width="25%" valign="top">选择文本分享</td>
			<td><label><input type="checkbox" name="selection" value="1" <?php if($wptm_share['selection']) echo "checked "; ?>/><strong>在文章页面选中任何一段文本可以点击按钮分享到QQ空间、新浪微博、腾讯微博。</strong></label></td>
		  </tr>
		  <?php } ?>
        </table>
        <h3>Google+1</h3>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">是否开启“Google+1”功能</td>
            <td><input name="enable_plusone" type="checkbox" value="1" <?php checked($wptm_share['enable_plusone']); ?>> (提示: Google+1在国内使用不稳定，如果发现网站打开速度变慢，请关闭该功能。)</td>
          </tr>
          <tr>
            <td width="25%" valign="top">添加按钮</td>
            <td><label><input name="plusone" type="radio" value="1" <?php checked($wptm_share['plusone'] == 1); ?>>文章前面</label> <label><input name="plusone" type="radio" value="2" <?php checked(!$wptm_share['plusone'] || $wptm_share['plusone'] == 2); ?>>文章末尾</label> <label><input name="plusone" type="radio" value="3" <?php checked($wptm_share['plusone'] == 3); ?>> 调用函数</label> ( <code>&lt;?php wp_google_plusone();?&gt;</code> )</td>
          </tr>
          <tr>
            <td width="25%" valign="top">显示设置</td>
            <td><label>添加到 <select name="plusone_add"><option value="1"<?php selected($wptm_share['plusone_add'] == 1);?>>所有页面</option><option value="2"<?php selected($wptm_share['plusone_add'] == 2);?>>首页</option><option value="3"<?php selected($wptm_share['plusone_add'] == 3);?> >文章页和页面</option><option value="4"<?php selected(!$wptm_share['plusone_add'] || $wptm_share['plusone_add'] == 4);?> >文章页</option><option value="5"<?php selected($wptm_share['plusone_add'] == 5);?> >页面</option></select></label> <label>选择尺寸 <select name="plusone_size"><option value="small"<?php selected($wptm_share['plusone_size'] == 'small');?>>小（15 像素）</option><option value="medium"<?php selected($wptm_share['plusone_size'] == 'medium');?> >中（20 像素）</option><option value="standard"<?php selected(!$wptm_share['plusone_size'] || $wptm_share['plusone_size'] == 'standard');?> >标准（24 像素）</option><option value="tall"<?php selected($wptm_share['plusone_size'] == 'tall');?> >高（60 像素）</option></select><label> <input name="plusone_count" type="checkbox" value="1" <?php checked($wptm_share['plusone_count']); ?> />包含计数</label></td>
          </tr>
        </table>
        <h3>添加社会化分享按钮，可以上下左右拖拽排序(记得保存！) <span style="color:#440">[如果不能拖拽请刷新页面]</span>：</h3>
		  <ul id="dragbox">
		  <?php if (WP_CONNECT_ADVANCED == "true") {wp_social_share_options();echo '<img src="http://smyx.sinaapp.com/t.php?img='.$wptm_donate.'" style="display:none" />';} else {$social = wp_social_share_title();foreach($social as $key => $title) {echo "<li id=\"drag\"><input name=\"$key\" type=\"checkbox\" value=\"$key\" />$title</li>";}}?>
		    <div class="clear"></div>
		  </ul>
		  <div id="dragmarker">
		    <img src="<?php echo $plugin_url;?>/images/marker_top.gif">
		    <img src="<?php echo $plugin_url;?>/images/marker_middle.gif" id="dragmarkerline">
		    <img src="<?php echo $plugin_url;?>/images/marker_bottom.gif">
		  </div>
        <p class="submit">
		  <input type="hidden" name="all">
          <input type="hidden" name="select">
          <input type="submit" name="share_options" onclick="saveData()" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
    </div>
    <div id="advanced">
      <form method="post" action="options-general.php?page=wp-connect#advanced">
        <?php wp_nonce_field('advanced-options');?>
        <h3>高级设置</h3>
		<?php if (!function_exists('wp_connect_advanced')) {?>
		 <div id="tml-tips" class="updated">
         <p>高级设置只针对捐赠用户，目前增加功能如下：</p>
         <p>1、增加支持使用QQ帐号、开心网帐号、淘宝网帐号、百度帐号、天涯社区帐号、MSN、Google、Yahoo等登录WordPress博客。</p>
         <p>2、在个人资料页面，支持将WP用户名与13家合作网站帐号绑定，绑定后您可以使用用户名或者使用合作网站帐号登录你的网站。而且绑定后<strong>新浪微博、腾讯微博还能支持使用<a href="http://loginsns.com/wiki/wordpress/comment" target="_blank">高级评论功能</a>。</strong><span style="color: red;">NEW!</span></p>
         <p><strong>3、同步博客，支持同步到新浪博客、网易博客、QQ空间、人人网、开心网。</strong><span style="color: red;">HOT!</span> [ <a href="http://loginsns.com/wiki/wordpress/function#同步博客" target="_blank">查看</a> ]</p>
         <p>4、登录提示文字包括简体中文、繁体中文、英文，根据浏览器的语言判断显示。</p>
         <p>5、去掉登录二次点击。</p>
         <p>6、支持使用网页或者手机wap发布WordPress文章和一键发布到微博。<span style="color: red;">NEW!</span> [ <a href="http://loginsns.com/wiki/wordpress/wap" target="_blank">查看</a> ]</p>
         <p><strong>7、支持使用社会化分享按钮功能[52个]</strong>，同时在腾讯微博、新浪微博、网易微博、搜狐微博的分享中加入@微博帐号。(微博帐号在“连接设置”中填写)。<br /><strong>在文章页面选中任何一段文本可以点击按钮分享到QQ空间、新浪微博、腾讯微博。</strong><span style="color: red;">HOT!</span> [ <a href="http://loginsns.com/wiki/wordpress/share" target="_blank">查看</a> ]</p>
         <p>8、支持使用Google+1按钮(在“分享设置”中开启)。</p>
         <p><strong>9、支持让注册用户绑定多个微博和SNS，用户登录后可以在您创建的自定义页面，一键发布信息到他们的微博上。</strong></p>
         <p>10、整合了新浪微博和腾讯微博的微博秀，侧边栏显示更方便！[ <a href="http://loginsns.com/wiki/wordpress/show" target="_blank">查看</a> ]</p>
         <p>11、支持使用<a href="http://loginsns.com/robot.php" target="_blank">IM机器人</a>(包括<a href="http://loginsns.com/wiki/gtalk" target="_blank">gtalk机器人</a>)发布/修改文章(支持同步)，获得最新评论，发布/回复评论，修改评论状态(获准、待审、垃圾评论、回收站、删除)，发布自定义信息到多个微博和SNS。</p>
         <p>12、支持在捐赠者间用gtalk机器人 获得某个站点的最新文章，最新评论，支持发布/回复评论，如果你拥有某个站点特殊权限，还可以发布文章，发布自定义信息到多个微博和SNS等。[ <a href="http://loginsns.com/wiki/gtalk#gtalk_11" target="_blank">查看</a> ]</p>
         <p>13、<a href="http://loginsns.com/wiki/wordpress/donate" target="_blank">查看更多功能</a></p>
		 <p><strong>最低捐赠：<a href="https://me.alipay.com/smyx" target="_blank">支付宝</a>(15元人民币起) 或者 <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZWMTWK2DGHCYS" target="_blank">PayPal</a>($5美元起) ，就当做是支持我继续开发插件的费用吧！<a href="http://loginsns.com/wiki/wordpress/donate" target="_blank">查看详细描述</a></strong></p></p>
		 <p>或许您用不到捐赠版的功能，您觉得这个插件好用，您也可以考虑捐赠(任意金额)支持我继续开发更多实用的免费插件！谢谢！</p>
      </ul>
	  </div>
	  <?php } else { ?>
	    <table class="form-table">
		    <tr>
			    <td width="25%" valign="top">授权码</td>
			    <td><label>API Key: <input type="text" name="apikey" value="<?php echo $wptm_advanced['apikey'];?>" /></label> <label>Secret Key: <input type="text" name="secret" size="32" value="<?php echo $wptm_advanced['secret'];?>" /></label></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">Google Talk</td>
			    <td><input name="gtalk" type="text" size="32" value="<?php echo $wptm_advanced['gtalk'];?>" /> (必填)</td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">默认用户ID</td>
			    <td><label><input name="user_id" type="text" size="2" maxlength="4" value="<?php echo $wptm_advanced['user_id'];?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 这是为Google Talk发布文章设置的</label> ( 提示: 当前登录的用户ID是<?php echo get_current_user_id();?> )</td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">自定义页面</td>
			    <td><label><input type="checkbox" name="registered_users" id="registered_users" value="1" <?php if($wptm_advanced['registered_users']) echo "checked "; ?>/>支持所有注册用户 (用户登陆后可以在自定义页面发布信息到他们绑定的微博上。)</label></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">微博秀</td>
			    <td><label><input type="checkbox" name="widget" value="1" <?php if($wptm_advanced['widget']) echo "checked "; ?>/>是否开启侧边栏微博秀 (开启后到<a href="widgets.php">小工具</a>拖拽激活)</label> [ <a href="http://ishow.sinaapp.com/" target="_blank">获得代码</a> ]</td>
		    </tr>
        </table>
        <p class="submit">
          <input type="submit" name="advanced_options" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
        <?php echo $update_tips;} ?>
		<div id="tml-tips" class="updated"><p>提示：高级设置版本 支持根域名了（相同的授权码，支持该域名下的所有网站）[ <a href="http://loginsns.com/wiki/wordpress/donate" target="_blank">详细说明</a> ]</p></div>
      </form>
      <form method="post" action="">
	    <?php wp_nonce_field('wptm-delete');?>
		<p class="submit"><input type="submit" name="wptm_delete" value="卸载 WordPress连接微博" onclick="return confirm('您确定要卸载WordPress连接微博？')" /></p>
	  </form>
    </div>
    <div id="check">
	<p><iframe width="100%" height="660" frameborder="0" scrolling="no" src="<?php echo $plugin_url.'/check.php'?>"></iframe></p>
    </div>
  </div>
</div>
<?php
}
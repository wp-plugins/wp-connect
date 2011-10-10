<?php
/*
Plugin Name: WordPress连接微博
Author: 水脉烟香
Author URI: http://www.smyx.net/
Plugin URI: http://www.smyx.net/wp-connect.html
Description: 支持使用15个第三方网站帐号登录 WordPress 博客，并且支持同步文章的 标题和链接 到14大微博和社区。<strong>注意：捐赠版已经更新到1.5.2 版本，请到群内下载升级！</strong>
Version: 1.9.2
*/

define('WP_CONNECT_VERSION', '1.9.2');
$wpurl = get_bloginfo('wpurl');
$siteurl = get_bloginfo('url');
$plugin_url = $wpurl.'/wp-content/plugins/wp-connect';
$wptm_options = get_option('wptm_options');
$wptm_connect = get_option('wptm_connect');
$wptm_advanced = get_option('wptm_advanced');
$wptm_share = get_option('wptm_share');
$wptm_version = get_option('wptm_version');
$wp_connect_advanced_version = "1.5.2";

if ($wptm_version && $wptm_version != WP_CONNECT_VERSION) {
	update_option('wptm_version', WP_CONNECT_VERSION);
}

add_action('admin_menu', 'wp_connect_add_page');

include_once(dirname(__FILE__) . '/sync.php');
include_once(dirname(__FILE__) . '/functions.php');
include_once(dirname(__FILE__) . '/connect.php');
include_once(dirname(__FILE__) . '/page.php');

if ($wptm_connect['widget']) {
	include_once(dirname(__FILE__) . '/widget.php');
}

if ($wptm_options['enable_wptm']) { // 是否开启微博同步功能
    add_action('admin_menu', 'wp_connect_add_sidebox');
	add_action('publish_post', 'wp_connect_publish');
	add_action('publish_page', 'wp_connect_publish');
}

function wp_connect_add_page() {
	add_options_page('WordPress连接微博', 'WordPress连接微博', 'manage_options', 'wp-connect', 'wp_connect_do_page');
}

function wp_connect_warning() {
	global $wp_version,$wp_connect_advanced_version,$wptm_options, $wptm_connect, $wptm_version;
	if (!function_exists('curl_init') || version_compare($wp_version, '3.0', '<') || (($wptm_options || $wptm_connect) && !$wptm_version) || (!$wptm_connect && !$wptm_options) || function_exists('wp_connect_advanced') && version_compare(WP_CONNECT_ADVANCED_VERSION, $wp_connect_advanced_version, '<') && WP_CONNECT_ADVANCED_VERSION != '1.4.3') {
		echo '<div class="updated">';
		if (!function_exists('curl_init')) {
			echo '<p><strong>很遗憾！您的服务器(主机)当前配置不支持curl，会影响“WordPress连接微博”插件的部分功能！请联系空间商重新配置。</strong></p>';
		} 
		if (version_compare($wp_version, '3.0', '<')) {
			echo '<p><strong>您的WordPress版本太低，请升级到WordPress3.0或者更高版本，否则不能正常使用“WordPress连接微博”。</strong></p>';
		} 
		if (function_exists('wp_connect_advanced') && version_compare(WP_CONNECT_ADVANCED_VERSION, $wp_connect_advanced_version, '<') && WP_CONNECT_ADVANCED_VERSION != '1.4.3') {
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

function verify_qzone() {
	if (function_exists('fsockopen')) {
		error_reporting(0);
		ini_set('display_errors', 0);
		$fp = fsockopen("smtp.qq.com", 25, $errno, $errstr, 10);
		if (!$fp) {
			echo "很抱歉！您的服务器不能同步到QQ空间，因为腾讯邮件客户端的 smtp.qq.com:25 禁止您的服务器访问！请不要在上面填写QQ号码和密码，以免发布文章时出错或者拖慢您的服务器，谢谢支持！";
		} else {
			echo "恭喜！检查通过，请在上面填写QQ号码和密码，然后发布一篇文章试试，如果不能同步(多试几次)，请务必删除刚刚填写QQ号码和密码，并保存修改，以免发布文章时出错或者拖慢您的服务器，谢谢支持！";
		} 
	} else {
		echo "很抱歉！您的服务器不支持fsockopen()函数，不能同步到QQ空间，请联系空间商开启！请暂时不要在上面填写QQ号码和密码，以免发布文章时出错或者拖慢您的服务器，谢谢支持！";
	} 
} 

// 设置
function wp_connect_do_page() {
	global $wpurl,$plugin_url,$wptm_donate;
	wp_connect_update();
	$wptm_options = get_option('wptm_options');
	$wptm_connect = get_option('wptm_connect');
	if(function_exists('wp_connect_advanced')) {
	    wp_connect_advanced();
		$wptm_blog = get_option('wptm_blog');
		$blog_options = get_option('wptm_blog_options');
		$wptm_advanced = get_option('wptm_advanced');
		$wptm_share = get_option('wptm_share');
		if (WP_CONNECT_ADVANCED != "true"){
			$error = '<p><span style="color:#D54E21;"><strong>请先在高级设置项填写正确授权码！</strong></span></p>';
		}
	} else {
		$error = '<p><span style="color:#D54E21;"><strong>该功能只针对捐赠用户！</strong></span></p>';
	    $disabled = " disabled";
	}
	$account = wp_option_account();
	$_SESSION['wp_url_bind'] = WP_CONNECT;
?>
<div class="wrap">
  <h2>WordPress连接微博</h2>
  <div class="tabs">
    <ul class="nav">
      <li><a href="#sync" class="sync">同步微博</a></li>
      <li><a href="#blog" class="blog">同步博客</a></li>
      <li><a href="#connect" class="connect">连接设置</a></li>
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
            <td><input name="sync_option" type="text" size="1" maxlength="1" value="<?php echo (!$wptm_options) ? '2' : $wptm_options['sync_option']; ?>" onkeyup="value=value.replace(/[^1-5]/g,'')" /> (填数字，留空为不同步，只对本页绑定的帐号有效！)<br />提示: 1. 前缀+标题+链接 2. 前缀+标题+摘要/内容+链接 3.文章摘要/内容 4. 文章摘要/内容+链接 <br /> 把以下内容当成微博话题 (<label><input name="enable_cats" type="checkbox" value="1" <?php if($wptm_options['enable_cats']) echo "checked "; ?>>文章分类</label> <label><input name="enable_tags" type="checkbox" value="1" <?php if($wptm_options['enable_tags']) echo "checked "; ?>>文章标签</label>) <label><input name="disable_pic" type="checkbox" value="1" <?php checked($wptm_options['disable_pic']); ?>>不同步图片</label></td>
          </tr>
          <tr>
            <th>自定义消息</th>
            <td>新文章前缀: <input name="new_prefix" type="text" size="10" value="<?php echo $wptm_options['new_prefix']; ?>" /> 更新文章前缀: <input name="update_prefix" type="text" size="10" value="<?php echo $wptm_options['update_prefix']; ?>" /> 更新间隔: <input name="update_days" type="text" size="2" maxlength="4" value="<?php echo ($wptm_options['update_days']) ? $wptm_options['update_days'] : '0'; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 天 [0=修改文章时不同步] </td>
          </tr>
          <tr>
            <td width="25%" valign="top">禁止同步的文章分类ID</td>
            <td><input name="cat_ids" type="text" value="<?php echo $wptm_options['cat_ids']; ?>" /> 用英文逗号(,)分开 (设置后该ID分类下的文章将不会同到微博) [ <a href="http://loginsns.com/wiki/wordpress/faqs#cat-ids" target="_blank">查看详细</a> ]</td>
          </tr>
          <tr>
            <td width="25%" valign="top">自定义页面(一键发布到微博)</td>
            <td>自定义密码: <input name="page_password" type="password" value="<?php echo $wptm_options['page_password']; ?>" />
               [ <a href="http://loginsns.com/wiki/wordpress/faqs#page" target="_blank">如何使用？</a> ] <label><input name="disable_ajax" type="checkbox" value="1" <?php if($wptm_options['disable_ajax']) echo "checked "; ?>>禁用AJAX无刷新提交</label></td>
          </tr>
          <tr>
            <td width="25%" valign="top">多作者博客</td>
            <td><label><input name="multiple_authors" type="checkbox" value="1" <?php if($wptm_options['multiple_authors']) echo "checked "; ?>>是否让每个作者发布的文章同步到他们各自绑定的微博上，可以通知他们在 <a href="<?php echo admin_url('profile.php');?>">我的资料</a> 里面设置。</label></td>
          </tr>
          <tr>
            <td width="25%" valign="top">自定义短网址</td>
            <td><label><input name="enable_shorten" type="checkbox"  value="1" <?php checked(!$wptm_options || $wptm_options['enable_shorten']); ?>>博客默认 ( http://yourblog.com/?p=1 )</label> <label><input name="t_cn" type="checkbox"  value="1" <?php if($wptm_options['t_cn']) echo "checked "; ?>>http://t.cn/xxxxxx ( 新浪微博短网址 )</label></td>
          </tr>
          <tr>
            <td width="25%" valign="top">Twitter是否使用代理？</td>
            <td><label title="国外主机用户不要勾选噢！"><input name="enable_proxy" type="checkbox" value="1" <?php if($wptm_options['enable_proxy']) echo "checked "; ?>>(选填) 国内主机用户必须勾选才能使用Twitter</label></td>
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
    <div id="blog">
      <form method="post" action="options-general.php?page=wp-connect#blog">
        <?php wp_nonce_field('blog-options');?>
        <h3>同步博客</h3>
		<?php echo $error;?>
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
			    <td width="25%" valign="top">新浪博客</td>
			    <td><label>邮 箱: <input type="text" name="user_sina" value="<?php echo $wptm_blog[0][1];?>" /></label> <label>密 码: <input type="password" name="pass_sina" /></label><?php if($wptm_blog[0][2]) echo ' (密码留空表示不修改)';?></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">网易博客</td>
			    <td><label>邮 箱: <input type="text" name="user_163" value="<?php echo $wptm_blog[1][1];?>" /></label> <label>密 码: <input type="password" name="pass_163" /></label><?php if($wptm_blog[1][2]) echo ' (密码留空表示不修改)';?></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">QQ空间</td>
			    <td><label>Q Q: <input type="text" name="user_qzone" value="<?php echo $wptm_blog[2][1];?>" /></label> <label>密 码: <input type="password" name="pass_qzone" /></label><?php if($wptm_blog[2][2]) echo ' (密码留空表示不修改)';?></td>
		    </tr>
        </table>
        <p class="submit">
		  <input type="hidden" name="expiry" value="<?php echo $blog_options[2]; ?>" />
          <input type="submit" name="blog_options" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
      <form method="post" action="options-general.php?page=wp-connect#blog">
        <p><?php if (isset($_POST['verify_qzone'])) verify_qzone();?></p>
		<p class="submit"><input type="submit" name="verify_qzone" value="检查是否支持同步到QQ空间" /></p>
	  </form>
	  <p style="color:green;">注意事项：<br />1、修改文章时会同步修改对应的博客文章，而不是创建新的博客文章，快速编辑和密码保护的文章不会同步或更新。<br />QQ空间只会同步一次，并且修改文章时不会更新到QQ空间。<br />2、同步时在新浪等博客文章末尾会添加插件作者版权链接，使用15天后将不再添加！<br />3、当开启多作者博客时，只有在“高级设置”填写的 默认用户ID对应的WP帐号 <?php echo get_username($wptm_advanced['user_id']);?> 发布文章时才会同步到博客。</p>
    </div>
    <div id="connect">
      <form method="post" action="options-general.php?page=wp-connect#connect">
        <?php wp_nonce_field('connect-options');?>
        <h3>连接设置</h3>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">是否开启“连接设置”功能</td>
            <td><input name="enable_connect" type="checkbox" value="1" <?php if($wptm_connect['enable_connect']) echo "checked "; ?>></td>
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
              <label><input name="renren" type="checkbox" value="1" <?php if($wptm_connect['renren']) echo "checked ";?> />人人连接</label>
              <label><input name="kaixin001" type="checkbox" value="1" <?php if($wptm_connect['kaixin001']) echo "checked ";?><?php echo $disabled;?> />开心网</label>
              <label><input name="douban" type="checkbox" value="1" <?php if($wptm_connect['douban']) echo "checked ";?> />豆瓣</label>
              <label><input name="tianya" type="checkbox" value="1" <?php if($wptm_connect['tianya']) echo "checked "; ?><?php echo $disabled;?> />天涯</label>
			  <label><input name="msn" type="checkbox" value="1" <?php if($wptm_connect['msn']) echo "checked ";?><?php echo $disabled;?> />MSN</label>
			  <label><input name="google" type="checkbox" value="1" <?php if($wptm_connect['google']) echo "checked ";?><?php echo $disabled;?> />谷歌</label>
			  <label><input name="yahoo" type="checkbox" value="1" <?php if($wptm_connect['yahoo']) echo "checked ";?><?php echo $disabled;?> />雅虎</label>
			  <label><input name="twitter" type="checkbox" value="1" <?php if($wptm_connect['twitter']) echo "checked ";?> />Twitter</label>
            </td>
          </tr>
          <tr>
            <td width="25%" valign="top"><strong>开放平台</strong></td>
            <td>使用以下网站登录WP，请务必填写API，其他网站可以不必填写，或者在“同步设置”里面的“开放平台”下面填写！
			<p><strong>QQ登录</strong> ( APP ID: <input name="qq_app_id" type="text" value='<?php echo $wptm_connect['qq_app_id'];?>' />
              APP KEY: <input name="qq_app_key" type="text" value='<?php echo $wptm_connect['qq_app_key'];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/qq" target="_blank">如何获取?</a> ] )<p>
			<p><strong>人人连接</strong> ( API Key: <input name="renren_api_key" type="text" value='<?php echo $wptm_connect['renren_api_key'];?>' />
              Secret Key: <input name="renren_secret" type="text" value='<?php echo $wptm_connect['renren_secret'];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/renren" target="_blank">如何获取?</a> ] )<p>
			<p><strong>开心网</strong> ( API Key: <input name="kaixin001_api_key" type="text" value='<?php echo $wptm_connect['kaixin001_api_key'];?>' />
              Secret Key: <input name="kaixin001_secret" type="text" value='<?php echo $wptm_connect['kaixin001_secret'];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/kaixin001" target="_blank">如何获取?</a> ] )<p>
			<p><strong>淘宝网</strong> ( App Key: <input name="taobao_api_key" type="text" value='<?php echo $wptm_connect['taobao_api_key'];?>' />
              App Secret: <input name="taobao_secret" type="text" value='<?php echo $wptm_connect['taobao_secret'];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/taobao" target="_blank">如何获取?</a> ] )<p>
			<p><strong>百度</strong> ( API Key: <input name="baidu_api_key" type="text" value='<?php echo $wptm_connect['baidu_api_key'];?>' />
              Secret Key: <input name="baidu_secret" type="text" value='<?php echo $wptm_connect['baidu_secret'];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/baidu" target="_blank">如何获取?</a> ] )<p>
			<p><strong>MSN</strong> ( Client ID: <input name="msn_api_key" type="text" value='<?php echo $wptm_connect['msn_api_key'];?>' />
              Client secret: <input name="msn_secret" type="text" value='<?php echo $wptm_connect['msn_secret'];?>' /> [ <a href="http://loginsns.com/wiki/wordpress/faqs/msn" target="_blank">如何获取?</a> ] )<p></td>
          </tr>
		  <tr>
			<td width="25%" valign="top">Widget</td>
			<td><label><input type="checkbox" name="widget" value="1" <?php if($wptm_connect['widget']) echo "checked "; ?>/>是否开启边栏登录按钮 (开启后到<a href="widgets.php">小工具</a>拖拽激活)</label></td>
		  </tr>
          <tr>
            <td width="25%" valign="top">禁止注册的用户名</td>
            <td><input name="disable_username" type="text" size="60" value='<?php echo $wptm_connect['disable_username'];?>' /> 用英文逗号(,)分开</td>
          </tr>
          <tr>
            <td width="25%" valign="top">设置@帐号</td>
            <td>新浪微博昵称: <input name="sina_username" type="text" size="10" value='<?php echo $wptm_connect['sina_username'];?>' /> 腾讯微博帐号: <input name="qq_username" type="text" size="10" value='<?php echo $wptm_connect['qq_username'];?>' /><br />搜狐微博昵称: <input name="sohu_username" type="text" size="10" value='<?php echo $wptm_connect['sohu_username'];?>' /> 网易微博昵称: <input name="netease_username" type="text" size="10" value='<?php echo $wptm_connect['netease_username'];?>' /><br />(说明：有新的评论时将以 @微博帐号 的形式显示在您跟评论者相对应的微博上，仅对方勾选了同步评论到微博时才有效！注：腾讯微博帐号不是QQ号码)</td>
          </tr>
        </table>
        <p class="submit">
          <input type="submit" name="wptm_connect" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
        <h3>高级评论</h3>
		<?php echo $error;?>
<p>捐赠用户还可以这样玩转评论：[ <a href="http://loginsns.com/wiki/wordpress/comment" target="_blank">查看详细</a> ]</p>
<p>假设A是管理员，B和C是新浪微博用户，D是腾讯微博用户。</p>
<p>①新浪微博用户 B 在网站上评论并勾选了同步到微博，假设同步后的微博消息为 F ，那么管理员A和同是新浪微博用户的C回复时，可以不必勾选同步(系统将自动判断)，会直接在你的网站和B的微博消息 F 下评论。<br />②假如腾讯微博用户 D 回复了A在网站上的评论，那么他会借用 <span style="color:green;">高级设置 填写的 默认用户ID 对应的WP帐号下绑定的新浪微博帐号</span>通知B，B的微博消息 F 下会显示如下评论：“腾讯微博网友(D)在网站上的评论: 评论内容”。<br />注意：①中提到的功能只支持腾讯微博和新浪微博，其他微博以 @帐号 的形式同步回复。</p>
<p><strong>所有非捐赠用户仅支持 @微博帐号 的形式同步评论。</strong></p>
<p><strong>提示：管理员请用 高级设置填写的 默认用户ID 对应的WP帐号 <?php echo get_username($wptm_advanced['user_id']);?> 登录本站，然后在<a href="<?php echo admin_url('profile.php');?>">我的资料</a>页面绑定登录帐号(腾讯、新浪微博)！</strong></p>
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
		  <tr>
			<td width="25%" valign="top">选择文本分享</td>
			<td><label><input type="checkbox" name="selection" value="1" <?php if($wptm_share['selection']) echo "checked "; ?>/><strong>在文章页面选中任何一段文本可以点击按钮分享到QQ空间、新浪微博、腾讯微博。</strong></label></td>
		  </tr>
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
      <ul>
         <li>高级设置只针对捐赠用户，目前增加功能如下：</li>
         <li><strong>1、增加支持使用QQ帐号、开心网帐号、淘宝网帐号、百度帐号、天涯社区帐号、MSN、Google、Yahoo等登录WordPress博客。</strong><span style="color: red;">NEW!</span></li>
         <li><strong>2、支持在“我的个人资料”页面绑定QQ帐号、腾讯微博、新浪微博(可以是任意帐号，不需要跟WP用户名同名)，绑定后您可以使用用户名或者微博帐号登录你的网站。而且绑定后还能支持使用<a href="http://loginsns.com/wiki/wordpress/comment" target="_blank">高级评论功能</a>。</strong><span style="color: red;">NEW!</span></li>
         <li><strong>3、同步博客</strong>，支持同步到新浪博客、网易博客、QQ空间。<span style="color: red;">NEW!</span> [ <a href="http://loginsns.com/wiki/wordpress/function#同步博客" target="_blank">查看</a> ]</li>
         <li>4、登录提示文字包括简体中文、繁体中文、英文，根据浏览器的语言判断显示。<span style="color: red;">NEW!</span></li>
         <li>5、去掉登录二次点击。<span style="color: red;">NEW!</span></li>
         <li>6、支持使用网页或者手机wap发布WordPress文章和一键发布到微博。<span style="color: red;">NEW!</span> [ <a href="http://loginsns.com/wiki/wordpress/wap" target="_blank">查看</a> ]</li>
         <li>7、支持使用社会化分享按钮功能[52个]，同时在腾讯微博、新浪微博、网易微博、搜狐微博的分享中加入@微博帐号。(微博帐号在“连接设置”中填写)。<br /><strong>在文章页面选中任何一段文本可以点击按钮分享到QQ空间、新浪微博、腾讯微博。</strong><span style="color: red;">NEW!</span> [ <a href="http://loginsns.com/wiki/wordpress/share" target="_blank">查看</a> ]</li>
         <li>8、支持使用Google+1按钮(在“分享设置”中开启)。</li>
         <li><strong>9、支持让注册用户绑定多个微博和SNS，用户登录后可以在您创建的自定义页面，一键发布信息到他们的微博上。</strong></li>
         <li>10、整合了新浪微博和腾讯微博的微博秀，侧边栏显示更方便！[ <a href="http://loginsns.com/wiki/wordpress/show" target="_blank">查看</a> ]</li>
         <li>11、支持使用<a href="http://loginsns.com/robot.php" target="_blank">IM机器人</a>(包括<a href="http://loginsns.com/wiki/qqrobot" target="_blank">QQ机器人</a>、<a href="http://loginsns.com/wiki/gtalk" target="_blank">gtalk机器人</a>)发布/修改文章(支持同步)，获得最新评论，发布/回复评论，修改评论状态(获准、待审、垃圾评论、回收站、删除)，发布自定义信息到多个微博和SNS。</li>
         <li>12、支持在捐赠者间用gtalk机器人 获得某个站点的最新文章，最新评论，支持发布/回复评论，如果你拥有某个站点特殊权限，还可以发布文章，发布自定义信息到多个微博和SNS等。[ <a href="http://loginsns.com/wiki/gtalk#gtalk_11" target="_blank">查看</a> ]</li>
         <li>13、<a href="http://loginsns.com/wiki/wordpress#more" target="_blank">查看更多功能</a></li>
		 <li>最低捐赠：15元人民币起，就当做是支持我继续开发插件的费用吧！<a href="http://loginsns.com/wiki/donate" target="_blank">查看详细描述</a></li>
		 <li><strong>或许您用不到捐赠版的功能，您觉得这个插件好用，您也可以考虑捐赠(任意金额)支持我继续开发更多实用的免费插件！谢谢！</strong></li>
		 <li><strong>本人承接各类网站制作(包括WordPress主题和插件)，价格优惠！</strong><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=3249892&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:3249892:42" alt="联系我！" title="联系我！"></a></li>
      </ul>
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
        <?php if (function_exists('wp_connect_comments')) { echo '<p style="color:green;"><strong>更新提示：2011年10月8日更新了捐赠版授权码的算法，在这之前获得的授权码需要更新，请<a href="http://loginsns.com/key.php" target="_blank">点击这里</a>。</strong></p>'; }} ?>
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
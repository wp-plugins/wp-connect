<?php
/*
Plugin Name: WordPress连接微博
Author: 水脉烟香
Author URI: http://www.smyx.net/
Plugin URI: http://www.smyx.net/wp-connect.html
Description: 支持使用微博帐号登录 WordPress 博客，并且支持同步文章的 标题和链接 到各大微博和社区。
Version: 1.4.1
*/

$plugin_url = get_bloginfo('wpurl').'/wp-content/plugins/wp-connect';
$wptm_options = get_option('wptm_options');
$wptm_connect = get_option('wptm_connect');
$wptm_advanced = get_option('wptm_advanced');

add_action('admin_menu', 'wp_connect_add_page');

include_once(dirname(__FILE__) . '/sync.php');
include_once(dirname(__FILE__) . '/functions.php');
include_once(dirname(__FILE__) . '/connect.php');
include_once(dirname(__FILE__) . '/page.php');

if ($wptm_options['enable_wptm']) { // 是否开启微博同步功能
	add_action('publish_post', 'wp_connect_publish', 1);
}

function wp_connect_add_page() {
	add_options_page('WordPress连接微博', 'WordPress连接微博', 'manage_options', 'wp-connect', 'wp_connect_do_page');
}

function wp_connect_warning() {
	global $wp_version;
	if (!function_exists('curl_init') || version_compare($wp_version, '3.0', '<') || (!get_option('wptm_options') && !get_option('wptm_connect'))) {
		echo '<div class="updated">';
		if (!function_exists('curl_init')) {
			echo '<p><strong>很遗憾！您的服务器(主机)当前配置不支持curl，会影响“WordPress连接微博”插件的部分功能！请联系空间商重新配置。</strong></p>';
		} 
		if (version_compare($wp_version, '3.0', '<')) {
			echo '<p><strong>您的WordPress版本太低，请升级到WordPress3.0或者更高版本，否则不能正常使用“WordPress连接微博”。</strong></p>';
		} 
		if (!get_option('wptm_options') && !get_option('wptm_connect')) {
			echo '<p><strong>您还没有对“WordPress连接微博”进行设置，<a href="options-general.php?page=wp-connect">现在去设置</a></strong></p>';
		} 
		echo '</div>';
	}
}
add_action('admin_notices', 'wp_connect_warning');

// 设置
function wp_connect_do_page() {
	global $plugin_url;
	wp_connect_update();
	$wptm_options = get_option('wptm_options');
	$wptm_connect = get_option('wptm_connect');
	if(function_exists('wp_connect_advanced')) {
	    wp_connect_advanced();
		$wptm_advanced = get_option('wptm_advanced');
	}
	$account = wp_option_account();
	$_SESSION['wp_url_bind'] = WP_CONNECT;
?>
<div class="wrap">
  <h2>WordPress连接微博</h2>
  <div class="tabs">
    <ul class="nav">
      <li><a href="#sync">同步设置</a></li>
      <li><a href="#connect">连接设置</a></li>
      <li><a href="#advanced">高级设置</a></li>
      <li><a href="#check">环境检查</a></li>
    </ul>
    <div id="sync">
      <form method="post" action="options-general.php?page=wp-connect">
        <?php wp_nonce_field('sync-options');?>
        <h3>同步设置</h3>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">是否开启“微博同步”功能</td>
            <td><input name="enable_wptm" type="checkbox" value="1" <?php if($wptm_options['enable_wptm']) echo "checked "; ?>></td>
          </tr>
          <tr>
            <td width="25%" valign="top">Twitter是否使用代理？</td>
            <td><input name="enable_proxy" type="checkbox" value="1" <?php if($wptm_options['enable_proxy']) echo "checked "; ?>>(国内主机用户必须勾选才能使用)</td>
          </tr>
          <tr>
            <td width="25%" valign="top"><span<?php if (!function_exists('openssl_open') || !function_exists('curl_init')) echo ' style="color:red;"';?>>使用插件异常？</span></td>
            <td><input name="api" type="checkbox" value="1" <?php if($wptm_options['api']) echo "checked "; ?>>我不能同步 <input name="bind" type="checkbox" value="1" <?php if($wptm_options['bind']) echo "checked "; ?>>我不能绑定帐号[ <a href="http://www.smyx.net/apps/oauth.php" target="_blank">去获取授权码</a>，然后在下面绑定帐号<?php if (!function_exists('openssl_open') || !function_exists('curl_init')) echo ' <a href="http://www.smyx.net/help/#4" target="_blank">详细描述</a>';?> ]</td>
          </tr>
          <tr>
            <th>同步内容设置</th>
            <td><input name="sync_option" type="text" size="1" maxlength="1" value="<?php echo $wptm_options['sync_option']; ?>" onkeyup="value=value.replace(/[^1-4]/g,'')" /> (填数字，留空为不同步，只对本页绑定的帐号有效！)<br />提示: 1. 前缀+标题+链接 2. 前缀+标题+摘要/内容+链接 3.文章摘要/内容 4. 文章摘要/内容+链接 <br /> 把以下内容当成微博话题 (<input name="enable_cats" type="checkbox" value="1" <?php if($wptm_options['enable_cats']) echo "checked "; ?>>文章分类 <input name="enable_tags" type="checkbox" value="1" <?php if($wptm_options['enable_tags']) echo "checked "; ?>>文章标签)</td>
          </tr>
          <tr>
            <th>自定义消息</th>
            <td>新文章前缀: <input name="new_prefix" type="text" size="10" value="<?php echo $wptm_options['new_prefix']; ?>" /> 更新文章前缀: <input name="update_prefix" type="text" size="10" value="<?php echo $wptm_options['update_prefix']; ?>" /> 更新间隔: <input name="update_days" type="text" size="2" maxlength="4" value="<?php echo ($wptm_options['update_days']) ? $wptm_options['update_days'] : '0'; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 天 [0=更新时不同步] </td>
          </tr>
          <tr>
            <td width="25%" valign="top">禁止同步的文章分类ID</td>
            <td><input name="cat_ids" type="text" value="<?php echo $wptm_options['cat_ids']; ?>" /> 用半角逗号(,)隔开 (设置后该ID分类下的文章将不会同到微博)</td>
          </tr>
          <tr>
            <td width="25%" valign="top">自定义页面</td>
            <td>密码: <input name="page_password" type="password" value="<?php echo $wptm_options['page_password']; ?>" />
              <input name="disable_ajax" type="checkbox" value="1" <?php if($wptm_options['disable_ajax']) echo "checked "; ?>>禁用AJAX无刷新提交 [ <a href="http://www.smyx.net/help/#7_2" target="_blank">如何使用？</a> ]</td>
          </tr>
          <tr>
            <td width="25%" valign="top">多作者博客</td>
            <td><input name="multiple_authors" type="checkbox" value="1" <?php if($wptm_options['multiple_authors']) echo "checked "; ?>>(是否让每个作者发布的文章同步到他们各自绑定的微博上，可以通知他们在 <a href="<?php echo admin_url('profile.php');?>">我的资料</a> 里面设置。)</td>
          </tr>
          <tr>
            <td width="25%" valign="top">自定义短网址</td>
            <td><input name="enable_shorten" type="checkbox"  value="1" <?php if($wptm_options['enable_shorten']) echo "checked "; ?>>博客默认 ( http://yourblog.com/?p=1 )
              <input name="t_cn" type="checkbox"  value="1" <?php if($wptm_options['t_cn']) echo "checked "; ?>>http://t.cn/xxxxxx (
              <input name="t_cn_twitter" type="checkbox"  value="1" <?php if($wptm_options['t_cn_twitter']) echo "checked "; ?>>只应用于Twitter )</td>
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
        <h3>连接设置</h3>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">是否开启“连接微博”功能</td>
            <td><input name="enable_connect" type="checkbox" value="1" <?php if($wptm_connect['enable_connect']) echo "checked "; ?>></td>
          </tr>
          <tr>
            <td width="25%" valign="top">添加按钮</td>
            <td><label><input name="sina" type="checkbox" value="1" <?php if($wptm_connect['sina']) echo "checked "; ?> />新浪微博</label>
              <label><input name="qq" type="checkbox" value="1" <?php if($wptm_connect['qq']) echo "checked "; ?> />腾讯微博</label>
              <label><input name="sohu" type="checkbox" value="1" <?php if($wptm_connect['sohu']) echo "checked "; ?> />搜狐微博</label>
              <label><input name="netease" type="checkbox" value="1" <?php if($wptm_connect['netease']) echo "checked "; ?> />网易微博</label>
              <label><input name="renren" type="checkbox" value="1" <?php if($wptm_connect['renren']) echo "checked "; ?> />人人连接</label>
              <label><input name="douban" type="checkbox" value="1" <?php if($wptm_connect['douban']) echo "checked "; ?> />豆瓣</label></td>
          </tr>
          <tr>
            <td width="25%" valign="top">人人连接APP</td>
            <td>API Key: <input name="renren_api_key" type="text" value='<?php echo $wptm_connect['renren_api_key'];?>' />
              Secret Key: <input name="renren_secret" type="text" value='<?php echo $wptm_connect['renren_secret'];?>' /> [ <a href="http://www.smyx.net/help/#2" target="_blank">如何获取?</a> ] </td>
          </tr>
          <tr>
            <td width="25%" valign="top">绑定微博帐号</td>
            <td>新浪微博昵称: <input name="sina_username" type="text" size="10" value='<?php echo $wptm_connect['sina_username'];?>' /> 腾讯微博帐号: <input name="qq_username" type="text" size="10" value='<?php echo $wptm_connect['qq_username'];?>' /><br />搜狐微博昵称: <input name="sohu_username" type="text" size="10" value='<?php echo $wptm_connect['sohu_username'];?>' /> 网易微博昵称: <input name="netease_username" type="text" size="10" value='<?php echo $wptm_connect['netease_username'];?>' /><br />(说明：有新的评论时将以 @微博帐号 的形式显示在您跟评论者相对应的微博上，<br />仅对方勾选了同步评论到微博时才有效！注：腾讯微博帐号不是QQ号码)</td>
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
	  <p><span style="color:red;"><strong>FAQs（管理员必看）: </strong></span><a href="http://www.smyx.net/help/#7" target="_blank">为什么我用了插件后，我的博客不能登录了，提示密码错误？</a></p>
    </div>
    <div id="advanced">
      <form method="post" action="options-general.php?page=wp-connect#advanced">
        <?php wp_nonce_field('advanced-options');?>
        <h3>高级设置</h3>
<?php if (!function_exists('wp_connect_advanced')) {?>
      <ul>
         <li>高级设置只针对捐赠用户，目前增加功能如下：</li>
         <li>1、支持让注册用户绑定多个微博和SNS，用户登录后可以在您创建的自定义页面，一键发布信息到他们的微博上。</li>
         <li>2、整合了新浪微博和腾讯微博的微博秀，侧边栏显示更方便！</li>
         <li>3、支持使用 Gtalk指令 发布/修改文章(支持同步)，发布/回复评论，修改评论状态(获准、待审、垃圾评论、回收站、删除)，发布自定义信息到多个微博和SNS。</li>
         <li>4、支持在捐赠者间用 Gtalk指令 获得某个站点的最新文章，最新评论，支持发布/回复评论，如果你拥有某个站点特殊权限，还可以发布文章，发布自定义信息到多个微博和SNS等。</li>
		 <li>最低捐赠：5元人民币起，就当做是支持我继续开发插件的费用吧！<a href="http://www.smyx.net/help/#8" target="_blank">查看详细描述</a></li>
		 <li><strong>假如您不需要上述功能，您觉得这个插件好用，您也可以考虑捐赠(任意金额)支持我继续开发更多实用的免费插件！谢谢！</strong></li>
		 <li>本人承接各类网站制作，价格优惠，童叟无欺！介绍者可以获得10%回扣。<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=3249892&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:3249892:42" alt="联系我！" title="联系我！"></a></li>
      </ul>
<?php } else { ?>
	    <table class="form-table">
		    <tr>
			    <td width="25%" valign="top">授权码</td>
			    <td><label>API Key: <input type="text" name="apikey" value="<?php echo $wptm_advanced['apikey'];?>" /></label> <label>Secret Key: <input type="text" name="secret" size="32" value="<?php echo $wptm_advanced['secret'];?>" /></label></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">Google Talk</td>
			    <td><input name="gtalk" type="text" size="32" value="<?php echo $wptm_advanced['gtalk'];?>" /></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">默认用户ID</td>
			    <td><input name="user_id" type="text" size="2" maxlength="4" value="<?php echo $wptm_advanced['user_id'];?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> (这是为Google Talk发布文章设置的)</td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">自定义页面</td>
			    <td><label><input type="checkbox" name="registered_users" id="registered_users" value="1" <?php if($wptm_advanced['registered_users']) echo "checked "; ?>/>支持所有注册用户 (用户登陆后可以在自定义页面发布信息到他们绑定的微博上。)</label></td>
		    </tr>
		    <tr>
			    <td width="25%" valign="top">微博秀</td>
			    <td><label><input type="checkbox" name="widget" value="1" <?php if($wptm_advanced['widget']) echo "checked "; ?>/>是否开启侧边栏微博秀 (开启后到“小工具”拖拽激活)</label> [ <a href="http://show.girlcss.com/show.php" target="_blank">获得代码</a> ]</td>
		    </tr>
        </table>
        <p class="submit">
          <input type="submit" name="advanced_options" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
<?php } ?>
      </form>
      <form method="post" action="">
	    <?php wp_nonce_field('wptm-delete');?>
		<p class="submit"><input type="submit" name="wptm_delete" value="卸载 WordPress连接微博" onclick="return confirm('您确定要卸载WordPress连接微博？')" /></p>
	  </form>
    </div>
    <div id="check">　
        <iframe width="100%" height="620" frameborder="0" scrolling="no" src="<?php echo $plugin_url . '/check.php'?>"></iframe>
    </div>
  </div>
</div>
<?php
}
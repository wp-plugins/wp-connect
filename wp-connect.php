<?php
/*
Plugin Name: WordPress连接微博
Author: 水脉烟香
Author URI: http://www.smyx.net/
Plugin URI: http://www.smyx.net/wp-connect.html
Description: 支持使用11个第三方网站帐号登录 WordPress 博客，并且支持同步文章的 标题和链接 到16大微博和社区。
Version: 1.7.3
*/

define('WP_CONNECT_VERSION', '1.7.3');
$wpurl = get_bloginfo('wpurl');
$plugin_url = $wpurl.'/wp-content/plugins/wp-connect';
$wptm_options = get_option('wptm_options');
$wptm_connect = get_option('wptm_connect');
$wptm_advanced = get_option('wptm_advanced');
$wptm_share = get_option('wptm_share');
$wptm_version = get_option('wptm_version');

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
	add_action('publish_post', 'wp_connect_publish', 1);
	add_action('publish_page', 'wp_connect_publish', 1);
}

function wp_connect_add_page() {
	add_options_page('WordPress连接微博', 'WordPress连接微博', 'manage_options', 'wp-connect', 'wp_connect_do_page');
}

function wp_connect_warning() {
	global $wp_version,$wptm_options, $wptm_connect, $wptm_version;
	if (!function_exists('curl_init') || version_compare($wp_version, '3.0', '<') || (($wptm_options || $wptm_connect) && !$wptm_version) || (!$wptm_connect && !$wptm_options)) {
		echo '<div class="updated">';
		if (!function_exists('curl_init')) {
			echo '<p><strong>很遗憾！您的服务器(主机)当前配置不支持curl，会影响“WordPress连接微博”插件的部分功能！请联系空间商重新配置。</strong></p>';
		} 
		if (version_compare($wp_version, '3.0', '<')) {
			echo '<p><strong>您的WordPress版本太低，请升级到WordPress3.0或者更高版本，否则不能正常使用“WordPress连接微博”。</strong></p>';
		} 
		if (($wptm_options || $wptm_connect) && !$wptm_version) {
			echo '<p><strong>重要更新：从1.7.3版本开始，加入对同步帐号密码的加密处理，非OAuth授权的网站，请重新填写帐号和密码！然后请点击一次“同步设置”下面的“保存更改”按钮。<a href="options-general.php?page=wp-connect">现在去更改</a></strong></p>';
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
	global $plugin_url;
	wp_connect_update();
	$wptm_options = get_option('wptm_options');
	$wptm_connect = get_option('wptm_connect');
	if(function_exists('wp_connect_advanced')) {
	    wp_connect_advanced();
		$wptm_advanced = get_option('wptm_advanced');
		$wptm_share = get_option('wptm_share');
	} else {
	    $disabled = "title=\"捐赠版才能使用\" disabled";
	}
	$account = wp_option_account();
	$_SESSION['wp_url_bind'] = WP_CONNECT;
?>
<div class="wrap">
  <h2>WordPress连接微博</h2>
  <div class="tabs">
    <ul class="nav">
      <li><a href="#sync" class="sync">同步设置</a></li>
      <li><a href="#connect" class="connect">连接设置</a></li>
      <li><a href="#share" class="share">分享设置</a></li>
      <li><a href="#advanced" class="advanced">高级设置</a></li>
      <li><a href="#check" class="check">环境检查</a></li>
      <li><a href="http://loginsns.com/" target="_blank">帮助文档</a></li>
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
            <td><input name="enable_proxy" type="checkbox" value="1" <?php if($wptm_options['enable_proxy']) echo "checked "; ?>>(国内主机用户必须勾选才能使用Twitter)</td>
          </tr>
          <tr>
            <td width="25%" valign="top">我不能绑定帐号</td>
            <td><input name="bind" type="checkbox" value="1" <?php if($wptm_options['bind']) echo "checked "; ?>>勾选后可以在帐号绑定下面手动填写授权码 [ <a href="http://www.smyx.net/apps/oauth.php" target="_blank">去获取授权码</a> ]</td>
          </tr>
          <tr>
            <th>同步内容设置</th>
            <td><input name="sync_option" type="text" size="1" maxlength="1" value="<?php echo $wptm_options['sync_option']; ?>" onkeyup="value=value.replace(/[^1-5]/g,'')" /> (填数字，留空为不同步，只对本页绑定的帐号有效！)<br />提示: 1. 前缀+标题+链接 2. 前缀+标题+摘要/内容+链接 3.文章摘要/内容 4. 文章摘要/内容+链接 <br /> 把以下内容当成微博话题 (<input name="enable_cats" type="checkbox" value="1" <?php if($wptm_options['enable_cats']) echo "checked "; ?>>文章分类 <input name="enable_tags" type="checkbox" value="1" <?php if($wptm_options['enable_tags']) echo "checked "; ?>>文章标签)</td>
          </tr>
          <tr>
            <th>自定义消息</th>
            <td>新文章前缀: <input name="new_prefix" type="text" size="10" value="<?php echo $wptm_options['new_prefix']; ?>" /> 更新文章前缀: <input name="update_prefix" type="text" size="10" value="<?php echo $wptm_options['update_prefix']; ?>" /> 更新间隔: <input name="update_days" type="text" size="2" maxlength="4" value="<?php echo ($wptm_options['update_days']) ? $wptm_options['update_days'] : '0'; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> 天 [0=修改文章时不同步] </td>
          </tr>
          <tr>
            <td width="25%" valign="top">禁止同步的文章分类ID</td>
            <td><input name="cat_ids" type="text" value="<?php echo $wptm_options['cat_ids']; ?>" /> 用英文逗号(,)分开 (设置后该ID分类下的文章将不会同到微博) [ <a href="http://loginsns.com/#faqs_7" target="_blank">查看</a> ]</td>
          </tr>
          <tr>
            <td width="25%" valign="top">自定义页面</td>
            <td>密码: <input name="page_password" type="password" value="<?php echo $wptm_options['page_password']; ?>" />
               [ <a href="http://loginsns.com/#faqs_4" target="_blank">如何使用？</a> ] <input name="disable_ajax" type="checkbox" value="1" <?php if($wptm_options['disable_ajax']) echo "checked "; ?>>禁用AJAX无刷新提交</td>
          </tr>
          <tr>
            <td width="25%" valign="top">多作者博客</td>
            <td><input name="multiple_authors" type="checkbox" value="1" <?php if($wptm_options['multiple_authors']) echo "checked "; ?>>(是否让每个作者发布的文章同步到他们各自绑定的微博上，可以通知他们在 <a href="<?php echo admin_url('profile.php');?>">我的资料</a> 里面设置。)</td>
          </tr>
          <tr>
            <td width="25%" valign="top">自定义短网址</td>
            <td><input name="enable_shorten" type="checkbox"  value="1" <?php if($wptm_options['enable_shorten']) echo "checked "; ?>>博客默认 ( http://yourblog.com/?p=1 )
              <input name="t_cn" type="checkbox"  value="1" <?php if($wptm_options['t_cn']) echo "checked "; ?>>http://t.cn/xxxxxx ( 新浪微博短网址 )</td>
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
            <td width="25%" valign="top">基本设置</td>
            <td><label><input name="enable_connect" type="checkbox" value="1" <?php if($wptm_connect['enable_connect']) echo "checked "; ?>>开启功能</label> <label><input name="manual" type="checkbox" value="1" <?php checked($wptm_connect['manual']);?>>调用函数</label> ( <code>&lt;?php wp_connect();?&gt;</code> )</td>
          </tr>
          <tr>
            <td width="25%" valign="top">添加按钮</td>
            <td><label><input name="qqlogin" type="checkbox" value="1" <?php if($wptm_connect['qqlogin']) echo "checked "; ?><?php echo $disabled;?> />QQ登录</label>
			  <label><input name="sina" type="checkbox" value="1" <?php if($wptm_connect['sina']) echo "checked "; ?> />新浪微博</label>
              <label><input name="qq" type="checkbox" value="1" <?php if($wptm_connect['qq']) echo "checked "; ?> />腾讯微博</label>
              <label><input name="sohu" type="checkbox" value="1" <?php if($wptm_connect['sohu']) echo "checked "; ?> />搜狐微博</label>
              <label><input name="netease" type="checkbox" value="1" <?php if($wptm_connect['netease']) echo "checked "; ?> />网易微博</label><br />
              <label><input name="renren" type="checkbox" value="1" <?php if($wptm_connect['renren']) echo "checked "; ?> />人人连接</label>
              <label><input name="kaixin001" type="checkbox" value="1" <?php if($wptm_connect['kaixin001']) echo "checked "; ?><?php echo $disabled;?> />开心网</label>
              <label><input name="douban" type="checkbox" value="1" <?php if($wptm_connect['douban']) echo "checked "; ?> />豆瓣</label>
			  <label><input name="google" type="checkbox" value="1" <?php if($wptm_connect['google']) echo "checked "; ?><?php echo $disabled;?> />谷歌</label>
			  <label><input name="yahoo" type="checkbox" value="1" <?php if($wptm_connect['yahoo']) echo "checked "; ?><?php echo $disabled;?> />雅虎</label>
			  <label><input name="twitter" type="checkbox" value="1" <?php if($wptm_connect['twitter']) echo "checked "; ?> />Twitter</label>
            </td>
          </tr>
          <tr>
            <td width="25%" valign="top">QQ登录</td>
            <td>APP ID: <input name="qq_app_id" type="text" value='<?php echo $wptm_connect['qq_app_id'];?>' />
              APP KEY: <input name="qq_app_key" type="text" value='<?php echo $wptm_connect['qq_app_key'];?>' /> [ <a href="http://loginsns.com/#faqs_qq" target="_blank">如何获取?</a> ] </td>
          </tr>
          <tr>
            <td width="25%" valign="top">人人连接</td>
            <td>API Key: <input name="renren_api_key" type="text" value='<?php echo $wptm_connect['renren_api_key'];?>' />
              Secret Key: <input name="renren_secret" type="text" value='<?php echo $wptm_connect['renren_secret'];?>' /> [ <a href="http://loginsns.com/#faqs_rr" target="_blank">如何获取?</a> ] </td>
          </tr>
          <tr>
            <td width="25%" valign="top">开心网</td>
            <td>API Key: <input name="kaixin001_api_key" type="text" value='<?php echo $wptm_connect['kaixin001_api_key'];?>' />
              Secret Key: <input name="kaixin001_secret" type="text" value='<?php echo $wptm_connect['kaixin001_secret'];?>' /> [ <a href="http://loginsns.com/#faqs_kx001" target="_blank">如何获取?</a> ] </td>
          </tr>
		  <tr>
			<td width="25%" valign="top">Widget</td>
			<td><label><input type="checkbox" name="widget" value="1" <?php if($wptm_connect['widget']) echo "checked "; ?>/>是否开启边栏登录按钮 (开启后到<a href="widgets.php">小工具</a>拖拽激活)</label></td>
		  </tr>
          <tr>
            <td width="25%" valign="top">绑定微博帐号</td>
            <td>新浪微博昵称: <input name="sina_username" type="text" size="10" value='<?php echo $wptm_connect['sina_username'];?>' /> 腾讯微博帐号: <input name="qq_username" type="text" size="10" value='<?php echo $wptm_connect['qq_username'];?>' /><br />搜狐微博昵称: <input name="sohu_username" type="text" size="10" value='<?php echo $wptm_connect['sohu_username'];?>' /> 网易微博昵称: <input name="netease_username" type="text" size="10" value='<?php echo $wptm_connect['netease_username'];?>' /><br />(说明：有新的评论时将以 @微博帐号 的形式显示在您跟评论者相对应的微博上，<br />仅对方勾选了同步评论到微博时才有效！注：腾讯微博帐号不是QQ号码)</td>
          </tr>
          <tr>
            <td width="25%" valign="top">网易微博评论者头像</td>
            <td><label><input name="netease_avatar" type="checkbox" value="1" <?php if($wptm_connect['netease_avatar']) echo "checked "; ?>>已显示</label></td>
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
    </div>
    <div id="share">
      <form method="post" id="formdrag" action="options-general.php?page=wp-connect#share">
        <?php wp_nonce_field('share-options');?>
        <h3>分享设置</h3>
		<?php if (!function_exists('wp_connect_advanced')) {echo '<p><span style="color:#D54E21;"><strong>社会化分享按钮功能只针对捐赠用户！</strong></span></p>';} elseif (WP_CONNECT_ADVANCED != "true"){echo '<p><span style="color:#D54E21;"><strong>请先在高级设置项填写正确授权码！</strong></span></p>';}?>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">添加按钮</td>
            <td><label><input name="enable_share" type="radio" value="3" <?php if($wptm_share['enable_share'] == 3) echo "checked "; ?>> 文章前面</label> <label><input name="enable_share" type="radio" value="1" <?php if($wptm_share['enable_share'] == 1) echo "checked "; ?>> 文章末尾</label> <label><input name="enable_share" type="radio" value="2" <?php if($wptm_share['enable_share'] == 2) echo "checked "; ?>> 调用函数</label> ( <code>&lt;?php wp_social_share();?&gt;</code> ) [ <a href="http://loginsns.com/#share" target="_blank">详细说明</a> ]</td>
          </tr>
          <tr>
            <td width="25%" valign="top">样式选择</td>
            <td><label><input name="css" type="checkbox" value="1" <?php checked($wptm_share['css']); ?> />使用插件自带share.css文件 (建议复制样式到主题css文件中，以免升级时被覆盖！)</label>
            </td>
          </tr>
          <tr>
            <td width="25%" valign="top">显示设置</td>
            <td><label>分享按钮前面的文字: <input name="text" type="text" value='<?php echo $wptm_share['text'];?>' /></label><br /><label><input name="button" type="radio" value="1" <?php if($wptm_share['button'] == 1) echo "checked "; ?> />显示图标按钮</label> ( 选择尺寸 <select name="size"><option value="16"<?php if($wptm_share['size'] == 16) echo " selected";?>>小图标</option><option value="32"<?php if($wptm_share['size'] == 32) echo " selected";?> >大图标</option></select> ) <label><input name="button" type="radio" value="2" <?php if($wptm_share['button'] == 2) echo "checked "; ?> />显示图文按钮</label> <label><input name="button" type="radio" value="3" <?php if($wptm_share['button'] == 3) echo "checked "; ?> />显示文字按钮</label></td>
          </tr>
		  <tr>
			<td width="25%" valign="top">Google Analytics</td>
			<td><label><input type="checkbox" name="analytics" value="1" <?php if($wptm_share['analytics']) echo "checked "; ?>/>使用 Google Analytics 跟踪社会化分享按钮的使用效果</label> [ <a href="http://loginsns.com/#share_2" target="_blank">查看说明</a> ]<br /><label>配置文件ID: <input type="text" name="id" value="<?php echo $wptm_share['id'];?>" /></label></td>
		  </tr>
        </table>
        <h3>Google+1</h3>
        <table class="form-table">
          <tr>
            <td width="25%" valign="top">是否开启“Google+1”功能</td>
            <td><input name="enable_plusone" type="checkbox" value="1" <?php checked($wptm_share['enable_plusone']); ?>></td>
          </tr>
          <tr>
            <td width="25%" valign="top">添加按钮</td>
            <td><label><input name="plusone" type="radio" value="1" <?php checked($wptm_share['plusone'] == 1); ?>>文章前面</label> <label><input name="plusone" type="radio" value="2" <?php checked($wptm_share['plusone'] == 2); ?>>文章末尾</label> <label><input name="plusone" type="radio" value="3" <?php checked($wptm_share['plusone'] == 3); ?>> 调用函数</label> ( <code>&lt;?php wp_google_plusone();?&gt;</code> )</td>
          </tr>
          <tr>
            <td width="25%" valign="top">显示设置</td>
            <td><label>添加到 <select name="plusone_add"><option value="1"<?php selected($wptm_share['plusone_add'] == 1);?>>所有页面</option><option value="2"<?php selected($wptm_share['plusone_add'] == 2);?>>首页</option><option value="3"<?php selected($wptm_share['plusone_add'] == 3);?> >文章页和页面</option><option value="4"<?php selected($wptm_share['plusone_add'] == 4);?> >文章页</option><option value="5"<?php selected($wptm_share['plusone_add'] == 5);?> >页面</option></select></label> <label>选择尺寸 <select name="plusone_size"><option value="small"<?php selected($wptm_share['plusone_size'] == 'small');?>>小（15 像素）</option><option value="medium"<?php selected($wptm_share['plusone_size'] == 'medium');?> >中（20 像素）</option><option value="standard"<?php selected($wptm_share['plusone_size'] == 'standard');?> >标准（24 像素）</option><option value="tall"<?php selected($wptm_share['plusone_size'] == 'tall');?> >高（60 像素）</option></select><label> <input name="plusone_count" type="checkbox" value="1" <?php checked($wptm_share['plusone_count']); ?> />包含计数</label></td>
          </tr>
        </table>
        <h3>添加社会化分享按钮，可以上下左右拖拽排序(记得保存！) <span style="color:#440">[如果不能拖拽请刷新页面]</span>：</h3>
		  <ul id="dragbox">
		  <?php
		  if (WP_CONNECT_ADVANCED == "true") {
		  	wp_social_share_options();
		  } else {
		  	$social = wp_social_share_title();
		  	foreach($social as $key => $title) {
			  	echo "<li id=\"drag\"><input name=\"$key\" type=\"checkbox\" value=\"$key\" />$title</li>";
		  	}
		  }?>
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
         <li>1. 增加支持使用QQ、开心网、Google(谷歌)、Yahoo(雅虎)登录WordPress博客。<span style="color: red;">NEW!</span></li>
         <li>2. 登录提示文字包括简体中文、繁体中文、英文，根据浏览器的语言判断显示。<span style="color: red;">NEW!</span></li>
         <li>3. 去掉登录二次点击。<span style="color: red;">NEW!</span></li>
         <li>4、支持使用网页或者手机wap发布WordPress文章和一键发布到微博。<span style="color: red;">NEW!</span> [ <a href="http://loginsns.com/#web" target="_blank">查看</a> ]</li>
         <li>5、支持使用社会化分享按钮功能[52个]，同时在腾讯微博、新浪微博、网易微博、搜狐微博的分享中加入@微博帐号。(微博帐号在“连接设置”中填写)。<span style="color: red;">NEW!</span> [ <a href="http://loginsns.com/#share" target="_blank">查看</a> ]</li>
         <li>6. 支持使用Google+1按钮(在“分享设置”中开启)。</li>
         <li>7、支持让注册用户绑定多个微博和SNS，用户登录后可以在您创建的自定义页面，一键发布信息到他们的微博上。</li>
         <li>8、整合了新浪微博和腾讯微博的微博秀，侧边栏显示更方便！[ <a href="http://loginsns.com/#show" target="_blank">查看</a> ]</li>
         <li>9、支持使用Google talk指令 发布/修改文章(支持同步)，发布/回复评论，修改评论状态(获准、待审、垃圾评论、回收站、删除)，发布自定义信息到多个微博和SNS。[ <a href="http://loginsns.com/#gtalk" target="_blank">查看</a> ]</li>
         <li>10、支持在捐赠者间用Google talk指令 获得某个站点的最新文章，最新评论，支持发布/回复评论，如果你拥有某个站点特殊权限，还可以发布文章，发布自定义信息到多个微博和SNS等。[ <a href="http://loginsns.com/#gtalk_11" target="_blank">查看</a> ]</li>
         <li>11、<a href="http://loginsns.com/#more" target="_blank">查看更多功能</a></li>
		 <li>最低捐赠：10元人民币起，就当做是支持我继续开发插件的费用吧！<a href="http://loginsns.com/#donate" target="_blank">查看详细描述</a></li>
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
			    <td><input name="user_id" type="text" size="2" maxlength="4" value="<?php echo $wptm_advanced['user_id'];?>" onkeyup="value=value.replace(/[^\d]/g,'')" /> (这是为Google Talk发布文章设置的)</td>
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
<?php } ?>
      </form>
      <form method="post" action="">
	    <?php wp_nonce_field('wptm-delete');?>
		<p class="submit"><input type="submit" name="wptm_delete" value="卸载 WordPress连接微博" onclick="return confirm('您确定要卸载WordPress连接微博？')" /></p>
	  </form>
    </div>
    <div id="check">　
        <iframe width="100%" height="650" frameborder="0" scrolling="no" src="<?php echo $plugin_url . '/check.php'?>"></iframe>
    </div>
  </div>
</div>
<?php
}
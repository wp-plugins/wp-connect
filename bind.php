<?php
if (isset($_SERVER['SCRIPT_FILENAME']) && 'bind.php' == basename($_SERVER['SCRIPT_FILENAME']))
die ('Please do not load this page directly. Thanks!');
$wptm_options = get_option('wptm_options');
$wptm_advanced = get_option('wptm_advanced');
$action = IS_PROFILE_PAGE && $user_id ? $plugin_url.'/save.php?do=profile' : '';
?>
<link rel="stylesheet" type="text/css" href="<?php echo $plugin_url;?>/css/style.css" />
<script type="text/javascript" src="<?php echo $plugin_url;?>/js/jquery-1.2.6.pack.js"></script>
<script type="text/javascript" src="<?php echo $plugin_url;?>/js/floatdialog.js"></script>
<?php if (!$wptm_options['bind'] && $_SESSION['wp_url_bind'] == WP_CONNECT) {?>
<h3>开放平台</h3>
<a href="javascript:;" id="openqq"<?php echo ($account['openqq']['app_key']) ? ' class="bind"': '';?> title="腾讯微博开放平台">腾讯微博</a>
<a href="javascript:;" id="opensina"<?php echo ($account['opensina']['app_key']) ? ' class="bind"': '';?> title="新浪微博开放平台">新浪微博</a>
<a href="javascript:;" id="opensohu"<?php echo ($account['opensohu']['app_key']) ? ' class="bind"': '';?> title="搜狐微博开放平台">搜狐微博</a>
<span>[ <a href="http://loginsns.com/#faqs_15" target="_blank">如何获得APP Key？</a>]</span>
<p>(以上设置是为了显示微博的“来自XXX”，如果没有申请和审核通过千万不要填写) 注意：更换app key后，相应的帐号请重新绑定！</p>
<?php }?>
<div id="tlist">
<h3>帐号绑定</h3>
<a href="javascript:;" id="<?php echo ($account['twitter']['oauth_token']) ? 'bind_twitter' : 'twitter';?>" class="twitter" title="Twitter"><b></b></a>
<a href="javascript:;" id="<?php echo ($account['qq']['oauth_token']) ? 'bind_qq' : 'qq';?>" class="qq" title="腾讯微博"><b></b></a>
<a href="javascript:;" id="<?php echo ($account['sina']['oauth_token']) ? 'bind_sina' : 'sina';?>" class="sina" title="新浪微博"><b></b></a>
<a href="javascript:;" id="<?php echo ($account['netease']['oauth_token']) ? 'bind_netease' : 'netease';?>" class="netease" title="网易微博"><b></b></a>
<a href="javascript:;" id="<?php echo ($account['sohu']['oauth_token']) ? 'bind_sohu' : 'sohu';?>" class="sohu" title="搜狐微博"><b></b></a>
<a href="javascript:;" id="renren" class="renren<?php echo ($account['renren']['password']) ? ' bind': '';?>" title="人人网"><b></b></a>
<a href="javascript:;" id="kaixin001" class="kaixin001<?php echo ($account['kaixin001']['password']) ? ' bind': '';?>" title="开心网"><b></b></a>
<a href="javascript:;" id="digu" class="digu<?php echo ($account['digu']['password']) ? ' bind': '';?>" title="嘀咕"><b></b></a>
<a href="javascript:;" id="<?php echo ($account['douban']['oauth_token']) ? 'bind_douban' : 'douban';?>" class="douban" title="豆瓣"><b></b></a>
<a href="javascript:;" id="baidu" class="baidu<?php echo ($account['baidu']['password']) ? ' bind': '';?>" title="百度说吧"><b></b></a>
<a href="javascript:;" id="fanfou" class="fanfou<?php echo ($account['fanfou']['password']) ? ' bind': '';?>" title="饭否"><b></b></a>
<a href="javascript:;" id="renjian" class="renjian<?php echo ($account['renjian']['password']) ? ' bind': '';?>" title="人间网"><b></b></a>
<a href="javascript:;" id="zuosa" class="zuosa<?php echo ($account['zuosa']['password']) ? ' bind': '';?>" title="做啥"><b></b></a>
<a href="javascript:;" id="follow5" class="follow5<?php echo ($account['follow5']['password']) ? ' bind': '';?>" title="Follow5"><b></b></a>
<a href="javascript:;" id="leihou" class="leihou<?php echo ($account['leihou']['password']) ? ' bind': '';?>" title="雷猴"><b></b></a>
<a href="javascript:;" id="wbto" class="wbto<?php echo ($account['wbto']['password']) ? ' bind': '';?>" title="微博通wbto.cn"><b></b></a>
</div>
<?php
if ($wptm_options['multiple_authors'] || (function_exists('wp_connect_advanced') && $wptm_advanced['registered_users'])) {
	if ($_SESSION['wp_url_bind'] == WP_CONNECT) {
		if ($wptm_options['multiple_authors']) {
		    echo '<p>假如管理员只想同步自己发布的文章，请到 <a href="' . admin_url('profile.php') . '">我的资料</a> 里面绑定帐号。否则请在这里绑定 (即所有作者的文章都会同步到您绑定的微博上)。<br/>每位作者都可以自定义设置，互不干扰！</p>';
		}
		echo '<p>“我的资料”页面的设置或绑定优先级最大。当管理员在资料页有绑定任何一个帐号，则这里的帐号绑定将失效。</p>';
	} else {
		if ($wptm_options['multiple_authors']) {
			echo '<p>您可以在这里绑定帐号，当您发布文章时将同步该文章的信息到您的微博上。</p>';
		}
		if (function_exists('wp_connect_advanced') && $wptm_advanced['registered_users']) {
			echo '<p>绑定帐号后，您可以登录本站，在本站的微博自定义发布页面发布信息到您绑定的帐号上。</p>';
			echo '<p>您也可以捐助本人开发插件，以获得使用Gtalk指令进行更多便捷的操作。<a href="http://loginsns.com/#gtalk" target="_blank">查看详细</a></p>';
		}
		echo '<p><strong>请您再三确定您信任本站站长，否则导致微博等账户信息泄漏，插件开发者概不负责！</strong></p>';
	}
}
?>
<div class="dialog" id="dialog"> <a href="javascript:void(0);" class="close"></a>
<form method="post" action="<?php echo $action;?>">
<?php wp_nonce_field('options');?>
<p><img src="<?php echo $plugin_url;?>/images/qq.png" class="title_pic" /></p>
<table class="form-table">
<tr valign="top">
<th scope="row"><span class="appkey">APP Key</span><span class="token">Access token</span><span class="account">帐&nbsp;&nbsp;&nbsp;&nbsp;号</span> :</th>
<td><input type="text" class="username" id="username" name="username" /></td>
</tr>
<tr valign="top">
<th scope="row"><span class="appkey">Secret</span><span class="token">Token secret</span><span class="account">密&nbsp;&nbsp;&nbsp;&nbsp;码</span> :</th>
<td><input type="password" class="password" id="password" name="password" /></td>
</tr>
</table>
<p class="submit">
<input type="submit" name="update" id="update" class="button-primary" value="<?php _e('Save Changes') ?>" /> &nbsp;
<input type="submit" name="delete" id="delete" class="button-primary" value="解除绑定" onclick="return confirm('Are you sure? ')" />
</p>
</form>
</div>

<div class="dialog_add" id="dialog_add"> <a href="javascript:void(0);" class="close"></a>
<form method="post" action="<?php echo $action;?>">
<?php wp_nonce_field('add');?>
<p><img src="<?php echo $plugin_url;?>/images/qq.png" class="title_pic" /></p>
  <p>您还没有绑定同步授权，是否<b>绑定</b>？</p>
  <p>
    <input type="submit" class="button-primary add" name="add" value="是" /> &nbsp;
	<input type="button" class="button-primary close" value="否" /> 
  </p>
</form>
</div>

<div class="dialog_delete" id="dialog_delete"> <a href="javascript:void(0);" class="close"></a>
<form method="post" action="<?php echo $action;?>">
<?php wp_nonce_field('delete');?>
<p><img src="<?php echo $plugin_url;?>/images/qq.png" class="title_pic" /></p>
  <p>您已经绑定了同步授权，是否<b>解除</b>？</p>
  <p>
    <input type="submit" class="button-primary delete" name="delete" value="是" onclick="return confirm('Are you sure? ')" /> &nbsp;
	<input type="button" class="button-primary close" value="否" /> 
  </p>
</form>
</div>
<script type="text/javascript">
$(function () {
  var tabContainers = $('div.tabs > div');
  var hash = window.location.hash || '#sync';
  var css = hash.replace('#', '.');
  tabContainers.hide().filter(hash).show();
  $(css).addClass('selected');

  $('div.tabs ul.nav a').click(function () {
    tabContainers.hide();
    tabContainers.filter(this.hash).show();
    $('div.tabs ul.nav a').removeClass('selected');
    $(this).addClass('selected');
    return false;
  });
});
$(".close").show();
$("<?php if($wptm_options['bind']) echo '#twitter, #qq, #sina, #sohu, #netease, #douban, '?>#openqq, #opensina, #opensohu, #renren, #kaixin001, #digu, #baidu, #fanfou, #renjian, #zuosa, #ms9911, #follow5, #leihou, #wbto").click(function () {
  var id = $(this).attr("id").replace('_porxy', '');
  var pic = id.replace('open', '');
  $(".title_pic").attr("src", "<?php echo $plugin_url;?>/images/" + pic + ".png");
  $('input[name="username"]').attr("id", "username_" + id);
  $('input[name="password"]').attr("id", "password_" + id);
  $("#username_renren").attr("value", "<?php echo $account['renren']['username'];?>");
  $("#username_kaixin001").attr("value", "<?php echo $account['kaixin001']['username'];?>");
  $("#username_digu").attr("value", "<?php echo $account['digu']['username'];?>");
  $("#username_baidu").attr("value", "<?php echo $account['baidu']['username'];?>");
  $("#username_fanfou").attr("value", "<?php echo $account['fanfou']['username'];?>");
  $("#username_renjian").attr("value", "<?php echo $account['renjian']['username'];?>");
  $("#username_zuosa").attr("value", "<?php echo $account['zuosa']['username'];?>");
  $("#username_follow5").attr("value", "<?php echo $account['follow5']['username'];?>");
  $("#username_leihou").attr("value", "<?php echo $account['leihou']['username'];?>");
  $("#username_wbto").attr("value", "<?php echo $account['wbto']['username'];?>");
  $("#username_opensina").attr("value", "<?php echo $account['opensina']['app_key'];?>");
  $("#username_openqq").attr("value", "<?php echo $account['openqq']['app_key'];?>");
  $("#username_opensohu").attr("value", "<?php echo $account['opensohu']['app_key'];?>");
  $(".password").attr("value", "");
  if(id == "openqq" || id == "opensina" || id == "opensohu") {
	$(".account").hide();
    $(".token").hide();
    $(".appkey").show();
  } else if(id == "twitter" || id == "qq" || id == "sina" || id == "sohu" || id == "netease" || id == "douban") {
    $(".appkey").hide();
	$(".account").hide();
    $(".token").show();
  } else {
    $(".appkey").hide();
    $(".token").hide();
	$(".account").show();
  }
  $('#update').attr("name", 'update_' + id);
  $('#delete').attr("name", 'delete_' + id);
  $(".dialog").attr("id", "dialog_" + id);
  $("#delete").hide();
});
$(".bind").click(function () {
  $("#delete").show();
});
<?php if(!$wptm_options['bind']) {?>
$("#twitter, #qq, #sina, #sohu, #netease, #douban").click(function () {
  var id = $(this).attr("id");
  $(".title_pic").attr("src", "<?php echo $plugin_url;?>/images/" + id + ".png");
  $(".dialog_add").attr("id", "dialog_" + id);
  $(".add").attr("name", "add_" + id);
});
<?php }?>
$("#bind_twitter, #bind_qq, #bind_sina, #bind_sohu, #bind_netease, #bind_douban").click(function () {
  var id = $(this).attr("id").replace('bind_', '');
  $(".title_pic").attr("src", "<?php echo $plugin_url;?>/images/" + id + ".png");
  $(".dialog_delete").attr("id", "dialog_" + id);
  $(".delete").attr("name", "delete_" + id);
});
$("#demo").floatdialog("dialog");
$("#demo_add").floatdialog("dialog_add");
$("#demo_delete").floatdialog("dialog_delete");
$("#openqq").floatdialog("dialog_openqq");
$("#opensina").floatdialog("dialog_opensina");
$("#opensohu").floatdialog("dialog_opensohu");
$("#twitter, #bind_twitter").floatdialog("dialog_twitter");
$("#qq, #bind_qq").floatdialog("dialog_qq");
$("#sina, #bind_sina").floatdialog("dialog_sina");
$("#sohu, #bind_sohu").floatdialog("dialog_sohu");
$("#netease, #bind_netease").floatdialog("dialog_netease");
$("#douban, #bind_douban").floatdialog("dialog_douban");
$("#renren").floatdialog("dialog_renren");
$("#kaixin001").floatdialog("dialog_kaixin001");
$("#digu").floatdialog("dialog_digu");
$("#baidu").floatdialog("dialog_baidu");
$("#fanfou").floatdialog("dialog_fanfou");
$("#renjian").floatdialog("dialog_renjian");
$("#zuosa").floatdialog("dialog_zuosa");
$("#follow5").floatdialog("dialog_follow5");
$("#leihou").floatdialog("dialog_leihou");
$("#wbto").floatdialog("dialog_wbto");
$('#update').click(function () {
  if (($(".username").val() == '') || ($(".password").val() == '')) {
    alert("值不能为空!  ");
    return false;
  }
});
$('.wrap').click(function () {
   $('.updated').slideUp("normal");
});
$(function () {
   $('.show_botton').append( $('.hide_botton').html() );
   $('.hide_botton').hide();
});
</script>
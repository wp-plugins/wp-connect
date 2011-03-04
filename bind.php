<?php
if (is_user_logged_in()) {
// 绑定按钮
$wptm_options = get_option('wptm_options');
?>
<link rel="stylesheet" type="text/css" href="<?php echo $plugin_url;?>/style.css" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $plugin_url;?>/js/floatdialog.js"></script>
<div id="tlist">
<p><strong>帐号绑定</strong></p>
<?php if($wptm_options['enable_proxy']) { ?>
<a href="javascript:;" id="twitter_porxy" class="twitter<?php echo ($account['twitter']['password']) ? ' bind': '';?>" title="Twitter"><b></b></a>
<?php } else { ?>
<a href="javascript:;" id="<?php echo ($account['twitter_oauth']['oauth_token']) ? 'bind_twitter' : 'twitter';?>" class="twitter" title="Twitter"><b></b></a>
<?php } ?>
<a href="javascript:;" id="<?php echo ($account['qq']['oauth_token']) ? 'bind_qq' : 'qq';?>" class="qq" title="腾讯微博"><b></b></a>
<a href="javascript:;" id="<?php echo ($account['sina']['oauth_token']) ? 'bind_sina' : 'sina';?>" class="sina" title="新浪微博"><b></b></a>
<a href="javascript:;" id="<?php echo ($account['netease']['oauth_token']) ? 'bind_netease' : 'netease';?>" class="netease" title="网易微博"><b></b></a>
<a href="javascript:;" id="sohu" class="sohu<?php echo ($account['sohu']['password']) ? ' bind': '';?>" title="搜狐微博"><b></b></a>
<a href="javascript:;" id="renren" class="renren<?php echo ($account['renren']['password']) ? ' bind': '';?>" title="人人网"><b></b></a>
<a href="javascript:;" id="kaixin001" class="kaixin001<?php echo ($account['kaixin001']['password']) ? ' bind': '';?>" title="开心网"><b></b></a>
<a href="javascript:;" id="digu" class="digu<?php echo ($account['digu']['password']) ? ' bind': '';?>" title="嘀咕"><b></b></a>
<a href="javascript:;" id="<?php echo ($account['douban']['oauth_token']) ? 'bind_douban' : 'douban';?>" class="douban" title="豆瓣"><b></b></a>
<a href="javascript:;" id="fanfou" class="fanfou<?php echo ($account['fanfou']['password']) ? ' bind': '';?>" title="饭否"><b></b></a>
<a href="javascript:;" id="renjian" class="renjian<?php echo ($account['renjian']['password']) ? ' bind': '';?>" title="人间网"><b></b></a>
<a href="javascript:;" id="zuosa" class="zuosa<?php echo ($account['zuosa']['password']) ? ' bind': '';?>" title="做啥"><b></b></a>
<a href="javascript:;" id="follow5" class="follow5<?php echo ($account['follow5']['password']) ? ' bind': '';?>" title="Follow5"><b></b></a>
</div>
<?php
if ($wptm_options['multiple_authors']) {
	if ($_SESSION['wp_admin_go_url'] != admin_url('profile.php')) {
		echo '<p>假如管理员只想同步自己发布的文章，请到 <a href="' . admin_url('profile.php') . '">我的资料</a> 里面绑定帐号。否则请在这里绑定 (即所有作者的文章都会同步到您绑定的微博上)。<br/>说明：我的资料 页面 ‘同步设置’ 栏里设置的优先级最大。当管理员在资料页有绑定任何一个帐号，则这里的帐号绑定将失效。请二选一，谢谢！<br/>每位作者都可以自定义设置，互不干扰！</p>';
	} else {
		echo '<p>您可以在这里绑定帐号，当您发布日志时将同步该日志的信息到你的微博上。<br /><strong>请您再三确定您信任本站站长，否则导致微博等账户信息泄漏，插件开发者概不负责！</strong></p>';
	} 
} 

?>
<div class="dialog" id="dialog"> <a href="javascript:void(0);" class="close">X</a>
<form method="post" action="">
<?php wp_nonce_field('options');?>

<p align="center"><img src="<?php echo $plugin_url;?>/images/twitter.png" class="title_pic" /></p>
<table class="form-table">
<tr valign="top">
<th scope="row">帐&nbsp;&nbsp;&nbsp;&nbsp;号 :</th>
<td><input type="text" class="username" id="username" name="username" value="" /></td>
</tr>
<tr valign="top">
<th scope="row">密&nbsp;&nbsp;&nbsp;&nbsp;码 :</th>
<td><input type="password" class="password" id="password" name="password" value="" /></td>
</tr>
</table>
<p class="submit">
<input type="submit" name="update" id="update" class="button-primary" value="<?php _e('Save Changes') ?>" />
<input type="submit" name="delete" id="delete" class="button-primary" value="解除绑定" style="display: none;" />
</p>
</form>
</div>

<div class="dialog_add" id="dialog_add"> <a href="javascript:void(0);" class="close">X</a>
<form method="post" action="">
<?php wp_nonce_field('add');?>
  <h2>提示！</h2>
  <p> 您还没有绑定同步授权，是否<b>绑定</b>？ </p>
  <p>
    <input type="submit" class="button-primary add" name="add" value="是" /> 
	<input type="button" class="button-primary close" value="否" /> 
  </p>
</form>
</div>

<div class="dialog_delete" id="dialog_delete"> <a href="javascript:void(0);" class="close">X</a>
<form method="post" action="">
<?php wp_nonce_field('delete');?>
  <h2>提示！</h2>
  <p> 您已经绑定了同步授权，是否<b>解除</b>？ </p>
  <p>
    <input type="submit" class="button-primary delete" name="delete" value="是" onclick="if(confirm('Are you sure？')) return true;else return false; " /> 
	<input type="button" class="button-primary close" value="否" /> 
  </p>
</form>
</div>

</div>
<script type="text/javascript">
$(".close").show();
$("#twitter_porxy, #sohu, #renren, #kaixin001, #digu, #fanfou, #renjian, #zuosa, #ms9911, #follow5").click(function () {
  var id = $(this).attr("id").replace('_porxy', '');
  $(".title_pic").attr("src", "<?php echo $plugin_url;?>/images/" + id + ".png");
  $('input[name="username"]').attr("id", "username_" + id);
  $('input[name="password"]').attr("id", "password_" + id);
  $("#username_twitter").attr("value", "<?php echo $account['twitter']['username'];?>");
  $("#username_sohu").attr("value", "<?php echo $account['sohu']['username'];?>");
  $("#username_renren").attr("value", "<?php echo $account['renren']['username'];?>");
  $("#username_kaixin001").attr("value", "<?php echo $account['kaixin001']['username'];?>");
  $("#username_digu").attr("value", "<?php echo $account['digu']['username'];?>");
  $("#username_fanfou").attr("value", "<?php echo $account['fanfou']['username'];?>");
  $("#username_renjian").attr("value", "<?php echo $account['renjian']['username'];?>");
  $("#username_zuosa").attr("value", "<?php echo $account['zuosa']['username'];?>");
  $("#username_follow5").attr("value", "<?php echo $account['follow5']['username'];?>");
  $('#update').attr("name", 'update_' + id);
  $('#delete').attr("name", 'delete_' + id);
  $(".dialog").attr("id", "dialog_" + id);
  $("#delete").hide();
});
$(".bind").click(function () {
  $("#delete").show();
});
$("#twitter, #qq, #sina, #netease, #douban").click(function () {
  var id = $(this).attr("id");
  $(".dialog_add").attr("id", "dialog_" + id);
  $(".add").attr("name", "add_" + id);
});
$("#bind_twitter, #bind_qq, #bind_sina, #bind_netease, #bind_douban").click(function () {
  var id = $(this).attr("id").replace('bind_', '');
  $(".dialog_delete").attr("id", "dialog_" + id);
  $(".delete").attr("name", "delete_" + id + "_oauth");
});
$("#demo").floatdialog("dialog");
$("#demo_add").floatdialog("dialog_add");
$("#demo_delete").floatdialog("dialog_delete");
$("#twitter, #bind_twitter, #twitter_porxy").floatdialog("dialog_twitter");
$("#qq, #bind_qq").floatdialog("dialog_qq");
$("#sina, #bind_sina").floatdialog("dialog_sina");
$("#netease, #bind_netease").floatdialog("dialog_netease");
$("#douban, #bind_douban").floatdialog("dialog_douban");
$("#sohu").floatdialog("dialog_sohu");
$("#renren").floatdialog("dialog_renren");
$("#kaixin001").floatdialog("dialog_kaixin001");
$("#digu").floatdialog("dialog_digu");
$("#fanfou").floatdialog("dialog_fanfou");
$("#renjian").floatdialog("dialog_renjian");
$("#zuosa").floatdialog("dialog_zuosa");
$("#follow5").floatdialog("dialog_follow5");
$('#update').click(function () {
  if ($(".username").val() == '') {
    alert("请输入帐号!  ");
    return false;
  }
  if ($(".password").val() == '') {
    alert("请输入密码!  ");
    return false;
  }
});
$('.wrap').click(function () {
   $('.updated').slideUp("normal");
});
$(function () {
   $('.remove_botton').remove();
});
</script>
<?php
}
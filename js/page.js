$(document).ready(function(){$("#tform").click(function(){$("#v3 span, .error, .success").slideUp("normal")});function v(id){$(id).animate({left:"+=15"},100).animate({left:"-=30"},100).animate({left:"+=30"},100).animate({left:"-=30"},100).animate({left:"+=30"},100).animate({left:"-=15"},100)}$("#publish").click(function(){var message=$("#message").val();if(message==''){v("#v1");return false}var urls=/^http[s]?:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/;var url=$('#url').val();if((url!='')&&(!urls.test(url))){v("#v2");return false}var password=$("#password").val();if(password==''){v("#v3");return false}var twitter=$("#twitter:checked").val();var qq=$("#qq:checked").val();var sina=$("#sina:checked").val();var netease=$("#netease:checked").val();var sohu=$("#sohu:checked").val();var renren=$("#renren:checked").val();var kaixin001=$("#kaixin001:checked").val();var digu=$("#digu:checked").val();var douban=$("#douban:checked").val();var fanfou=$("#fanfou:checked").val();var renjian=$("#renjian:checked").val();var zuosa=$("#zuosa:checked").val();var tianya=$("#tianya:checked").val();var wbto=$("#wbto:checked").val();$(this).attr("disabled",true);$(".loading").slideDown("normal");$.post(wpurl+"/wp-content/plugins/wp-connect/save.php?do=page",{message:message,url:url,password:password,twitter:twitter,qq:qq,sina:sina,netease:netease,sohu:sohu,renren:renren,kaixin001:kaixin001,digu:digu,douban:douban,fanfou:fanfou,renjian:renjian,zuosa:zuosa,tianya:tianya,wbto:wbto},function(data){if(data=='你没有绑定微博帐号'){$(".loading").slideUp("normal",function(){$("#publish").attr("disabled",false);$(".error").slideDown("normal")});return false}if(data=='pwderror'){$(".loading").slideUp("normal",function(){$("#v3 span").show();$("#publish").attr("disabled",false);$("#password").val("")});return false}$(".loading").slideUp("normal",function(){$("#publish").attr("disabled",false);$("#message, #url").val("");$(".success").slideDown("normal")})});return false})});
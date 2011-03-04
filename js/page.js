$(document).ready(function () {
  $("#v3").click(function () {
    $("#v3 span").hide();
  });
  function v(id) {
    $(id).animate({left: "+=15"}, 100).animate({left: "-=30"}, 100).animate({left: "+=30"}, 100).animate({left: "-=30"}, 100).animate({left: "+=30"}, 100).animate({left: "-=15"}, 100);
  }
  $("#publish").click(function () {
    var message = $("#message").val();
    if (message == '') {
      v("#v1");
      return false;
    }
    var url = /^http[s]?:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/;
    var pic = $('#pic').val();
    if ((pic != '') && (!url.test(pic))) {
      v("#v2");
      return false;
    }
    var password = $("#password").val();
    if (password == '') {
      v("#v3");
      return false;
    }
    var twitter = $("#twitter:checked").val();
    var qq = $("#qq:checked").val();
    var sina = $("#sina:checked").val();
    var netease = $("#netease:checked").val();
    var sohu = $("#sohu:checked").val();
    var renren = $("#renren:checked").val();
    var kaixin001 = $("#kaixin001:checked").val();
    var digu = $("#digu:checked").val();
    var douban = $("#douban:checked").val();
    var fanfou = $("#fanfou:checked").val();
    var renjian = $("#renjian:checked").val();
    var zuosa = $("#zuosa:checked").val();
    var follow5 = $("#follow5:checked").val();

    $(this).attr("disabled", true);
    $(".loading").slideDown("normal");
    $.post(wpurl + "/wp-content/plugins/wp-connect/page.php?do=microblog", {
      message: message,
      pic: pic,
      password: password,
      twitter: twitter,
      qq: qq,
      sina: sina,
      netease: netease,
      sohu: sohu,
      renren: renren,
      kaixin001: kaixin001,
      digu: digu,
      douban: douban,
      fanfou: fanfou,
      renjian: renjian,
      zuosa: zuosa,
      follow5: follow5
    }, function (data) {
      if (data == 'pwderror') {
        $(".loading").slideUp("normal", function () {
          $("#v3 span").show();
          $("#publish").attr("disabled", false);
          $("#password").val("");
        });
        return false;
      }
      $(".loading").slideUp("normal", function () {
        $("#publish").attr("disabled", false);
        $("#message, #pic").val("");
        $(".success").slideDown("normal");
      });
      $("#tform").click(function () {
        $(".success").slideUp("normal");
      });

    });
    return false;
  });
});
<?php
date_default_timezone_set("PRC");
$wpurl = get_bloginfo('wpurl');
$wptm_options = get_option('wptm_options');
$qq = get_option('wptm_openqq');
$sina = get_option('wptm_opensina');
$key = get_option('wptm_key');
$time = ($wptm_options['char'] && $wptm_options['minutes']) ? time() + ($wptm_options['char'] * $wptm_options['minutes'] * 60) : time();
$qq_app_key = ($qq['app_key'] && !$wptm_options['bind']) ? $qq['app_key'] : 'd05d3c9c3d3748b09f231ef6d991d3ac';
$qq_app_secret = ($qq['secret'] && !$wptm_options['bind']) ? $qq['secret'] : 'e049e5a4c656a76206e55c91b96805e8';
$sina_app_key = ($sina['app_key'] && !$wptm_options['bind']) ? $sina['app_key'] : '1624795996';
$sina_app_secret = ($sina['secret'] && !$wptm_options['bind']) ? $sina['secret'] : '7ecad0335a50c49a88939149e74ccf81';
$sohu_app_key = ($key[5][0] && !$wptm_options['bind']) ? $key[5][0] : 'O9bieKU1lSKbUBI9O0Nf';
$sohu_app_secret = ($key[5][1] && !$wptm_options['bind']) ? $key[5][1] : 'k328Nm7cfUq0kY33solrWufDr(Tsordf1ek=bO5u';
$netease_app_key = ($key[6][0] && !$wptm_options['bind']) ? $key[6][0] : '9fPHd1CNVZAKGQJ3';
$netease_app_secret = ($key[6][1] && !$wptm_options['bind']) ? $key[6][1] : 'o98cf9oY07yHwJSjsPSYFyhosUyd43vO';
$douban_app_key = '0502b2569b45aed90f081703d1d10c8b';
$douban_app_secret = 'be87ce9c179080b0';
$tianya_app_key = 'ef9461411f2845a35ac06dc120710bf604e4f3f07';
$tianya_app_secret = 'ea401fa98aba3a75f6e65e9a201031c9';
$twitter_app_key = 'q5Hy9KIYnHX1fEfKZ8Vzog';
$twitter_app_secret = '9y8GHzzHM77KDJTe79k2vgkMctRrMvtnCNFcuetOUM';
define("WP_POST" , 'REQUEST');
define("WP_CONNECT" , $wpurl . '/wp-admin/options-general.php?page=wp-connect');
define("MY_PLUGIN_URL" , $wpurl . '/wp-content/plugins/wp-connect');
define("BJTIMESTAMP" , $time); //服务器时间校正
// 腾讯微博
define("QQ_APP_KEY" , $qq_app_key);
define("QQ_APP_SECRET" , $qq_app_secret);
// 新浪微博
define("SINA_APP_KEY" , $sina_app_key);
define("SINA_APP_SECRET" , $sina_app_secret);
// 搜狐微博
define("SOHU_APP_KEY" , $sohu_app_key);
define("SOHU_APP_SECRET" , $sohu_app_secret);
// 网易微博
define("APP_KEY" , $netease_app_key);
define("APP_SECRET" , $netease_app_secret);
// 豆瓣
define("DOUBAN_APP_KEY" , $douban_app_key);
define("DOUBAN_APP_SECRET" , $douban_app_secret);
// 天涯
define("TIANYA_APP_KEY" , $tianya_app_key);
define("TIANYA_APP_SECRET" , $tianya_app_secret);
// Twitter ,假如Twitter勾选代理，不能修改下面的值噢！
define("T_APP_KEY" , $twitter_app_key);
define("T_APP_SECRET" , $twitter_app_secret);

?>
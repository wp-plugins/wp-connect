<?php
if (isset($_SERVER['SCRIPT_FILENAME']) && 'config.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('Please do not load this page directly. Thanks!');
$wptm_options = get_option('wptm_options');
$qq = get_option('wptm_openqq');
$sina = get_option('wptm_opensina');
$sohu = get_option('wptm_opensohu');
/**
 * 假如您在使用插件时出现异常，并且在后台开启了“我不能同步”，那么请不要修改以下任何值！！！
 */
$qq_app_key = ($qq['app_key'] && !$wptm_options['api']) ? $qq['app_key'] : 'd05d3c9c3d3748b09f231ef6d991d3ac';
$qq_app_secret = ($qq['secret'] && !$wptm_options['api']) ? $qq['secret'] : 'e049e5a4c656a76206e55c91b96805e8';
$sina_app_key = ($sina['app_key'] && !$wptm_options['api']) ? $sina['app_key'] : '1624795996';
$sina_app_secret = ($sina['secret'] && !$wptm_options['api']) ? $sina['secret'] : '7ecad0335a50c49a88939149e74ccf81';
$sohu_app_key = ($sohu['app_key'] && !$wptm_options['api']) ? $sohu['app_key'] : 'O9bieKU1lSKbUBI9O0Nf';
$sohu_app_secret = ($sohu['secret'] && !$wptm_options['api']) ? $sohu['secret'] : 'k328Nm7cfUq0kY33solrWufDr(Tsordf1ek=bO5u';
define("WP_POST" , 'REQUEST');
define("WP_CONNECT" , get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=wp-connect');
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
define("APP_KEY" , '9fPHd1CNVZAKGQJ3');
define("APP_SECRET" , 'o98cf9oY07yHwJSjsPSYFyhosUyd43vO');
// 豆瓣
define("DOUBAN_APP_KEY" , '0502b2569b45aed90f081703d1d10c8b');
define("DOUBAN_APP_SECRET" , 'be87ce9c179080b0');
// Twitter
define("T_APP_KEY" , 'q5Hy9KIYnHX1fEfKZ8Vzog');
define("T_APP_SECRET" , '9y8GHzzHM77KDJTe79k2vgkMctRrMvtnCNFcuetOUM');

?>

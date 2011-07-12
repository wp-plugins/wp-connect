<?php
date_default_timezone_set("PRC");
$wptm_options = get_option('wptm_options');
$qq = get_option('wptm_openqq');
$sina = get_option('wptm_opensina');
$sohu = get_option('wptm_opensohu');
$netease = get_option('wptm_opennetease');
$qq_app_key = ($qq['app_key'] && !$wptm_options['bind']) ? $qq['app_key'] : 'd05d3c9c3d3748b09f231ef6d991d3ac';
$qq_app_secret = ($qq['secret'] && !$wptm_options['bind']) ? $qq['secret'] : 'e049e5a4c656a76206e55c91b96805e8';
$sina_app_key = ($sina['app_key'] && !$wptm_options['bind']) ? $sina['app_key'] : '1624795996';
$sina_app_secret = ($sina['secret'] && !$wptm_options['bind']) ? $sina['secret'] : '7ecad0335a50c49a88939149e74ccf81';
$sohu_app_key = ($sohu['app_key'] && !$wptm_options['bind']) ? $sohu['app_key'] : 'O9bieKU1lSKbUBI9O0Nf';
$sohu_app_secret = ($sohu['secret'] && !$wptm_options['bind']) ? $sohu['secret'] : 'k328Nm7cfUq0kY33solrWufDr(Tsordf1ek=bO5u';
$netease_app_key = ($netease['app_key'] && !$wptm_options['bind']) ? $netease['app_key'] : '9fPHd1CNVZAKGQJ3';
$netease_app_secret = ($netease['secret'] && !$wptm_options['bind']) ? $netease['secret'] : 'o98cf9oY07yHwJSjsPSYFyhosUyd43vO';
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
define("APP_KEY" , $netease_app_key);
define("APP_SECRET" , $netease_app_secret);
// 豆瓣
define("DOUBAN_APP_KEY" , '0502b2569b45aed90f081703d1d10c8b');
define("DOUBAN_APP_SECRET" , 'be87ce9c179080b0');
// Twitter ,假如Twitter勾选代理，不能修改下面的值噢！
define("T_APP_KEY" , 'q5Hy9KIYnHX1fEfKZ8Vzog');
define("T_APP_SECRET" , '9y8GHzzHM77KDJTe79k2vgkMctRrMvtnCNFcuetOUM');

?>

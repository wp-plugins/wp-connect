<?php
date_default_timezone_set("PRC");
define('ROOT_PATH', dirname(dirname(__FILE__)));
$funs_list = array('mysql_connect', 'curl_init', 'curl_setopt', 'curl_exec', 'file_get_contents', 'gzinflate', 'openssl_open');
$surrounding_list = array
('os' => array('p' => '操作系统 ', 'c' => 'PHP_OS', 'r' => '不限制', 'b' => 'unix'),
	'php' => array('p' => 'PHP版本', 'c' => 'PHP_VERSION', 'r' => '4.3', 'b' => '5.0'),
	'attachmentupload' => array('p' => '附件上传', 'r' => '不限制', 'b' => '2M'),
	'gdversion' => array('p' => 'GD 库', 'r' => '1.0', 'b' => '2.0'),
	'diskspace' => array('p' => '磁盘空间', 'r' => '10M', 'b' => '不限制')
	);
function surrounding_support(&$p) {
	foreach($p as $key => $item) {
		$p[$key]['status'] = 1;
		if ($key == 'php') {
			$p[$key]['current'] = PHP_VERSION;
			if ($p[$key]['current'] < 4.3) {
				$p[$key]['status'] = 0;
			} 
		} elseif ($key == 'attachmentupload') {
			$p[$key]['current'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '未知';
		} elseif ($key == 'gdversion') {
			$tmp = function_exists('gd_info') ? gd_info() : array();
			$p[$key]['current'] = empty($tmp['GD Version']) ? '不存在' : $tmp['GD Version'];
			unset($tmp);
			if ($p[$key]['current'] == "不存在") {
				$p[$key]['status'] = 0;
			} 
		} elseif ($key == 'diskspace') {
			if (function_exists('disk_free_space')) {
				$diskSize = disk_free_space(ROOT_PATH);
				if (floor($diskSize / (1024 * 1024)) >= 10) {
					if (floor($diskSize / (1024 * 1024)) >= 1024) {
						$p[$key]['current'] = floor($diskSize / (1024 * 1024 * 1024)) . 'G';
					} else {
						$p[$key]['current'] = floor($diskSize / (1024 * 1024)) . 'M';
					} 
					$p[$key]['status'] = 1;
				} else {
					if (floor($diskSize / 1024) == 0) {
						$p[$key]['current'] = "小于1K";
					} else {
						$p[$key]['current'] = floor($diskSize / 1024) . 'K';
					} 
					$p[$key]['status'] = 0;
				} 
			} else {
				$p[$key]['current'] = '未知';
				$p[$key]['status'] = 2;
			} 
		} elseif (isset($item['c'])) {
			$p[$key]['current'] = constant($item['c']);
		} 

		if ($item['r'] != '不限制' && $key != 'diskspace' && $key != 'gdversion' && strcmp($p[$key]['current'], $item['r']) < 0) {
			$p[$key]['status'] = 0;
		} 
	} 
	$env_str = "";
	foreach($p as $key => $item) {
		$wstr = "";
		if ($item['current'] == '未知') {
			$wstr = "<img src=\"images/alert.gif\" valign=\"middle\" title=\"参数无法检测，继续安装可能会有问题\"/>";
		} 
		$env_str .= "<tr>\n";
		$env_str .= "<td>" . $item['p'] . "</td>\n";
		$env_str .= "<td>" . $item['r'] . "</td>\n";
		$env_str .= "<td>" . $item['b'] . "</td>\n";
		$env_str .= "<td>" . $item['current'] . "</td>\n";
		if ($p[$key]['status'] == 0) {
			$env_str .= "<td><img src=\"images/0.gif\" class=\"no\" alt=\"" . $p[$key]['status'] . "\"/></td>";
		} else {
			$env_str .= "<td><img src=\"images/0.gif\" class=\"yes\" alt=\"" . $p[$key]['status'] . "\" " . ($wstr == ""?"":"style=\"display:none\"") . "/>" . $wstr . "</td>";
		} 
		$env_str .= "</tr>\n";
	} 
	return $env_str;
} 

function function_support(&$func_items) {
	$func_str = "";
	foreach($func_items as $item) {
		$status = function_exists($item);
		$func_str .= "<tr>\n";
		if (preg_match("/openssl/", $item)) {
			$func_str .= "<td>$item()";
			if (!$status) {
				$func_str .= " <a href=\"http://www.soso.com/q?w=%C8%E7%BA%CE%20%D4%DAPHP%C0%A9%D5%B9%C0%EF%20%B4%F2%BF%AA%20openssl%D6%A7%B3%D6\" target=\"_blank\" style=\"color:#f50\">在PHP扩展里打开openssl支持可以解决此问题</a>";
			} 
			$func_str .= "</td>\n";
		} else if ($item == "mb_strlen") {
			$func_str .= "<td>$item()";
			if (!$status) {
				$func_str .= " <a href=\"http://www.soso.com/q?pid=s.idx&w=PHP+%B4%F2%BF%AA%C0%A9%D5%B9extension%3Dphp_mbstring
\" target=\"_blank\" style=\"color:#f50\">需要在PHP扩展里打开扩展extension=php_mbstring</a>";
			} 
			$func_str .= "</td>\n";
		} else if ($item == "gzinflate") {
			$func_str .= "<td>$item()";
			if (!$status) {
				$func_str .= " <span style=\"color:green\">不支持该函数，意味着不能使用 “捐赠版”。</span>";
			} 
			$func_str .= "</td>\n";
		} else {
			$func_str .= "<td>$item()</td>\n";
		} 
		if ($status) {
			$func_str .= "<td>支持</td>\n";
			$func_str .= "<td><img src=\"images/0.gif\" class=\"yes\"/></td>\n";
		} else {
			$func_str .= "<td>不支持</td>\n";
			$func_str .= "<td><img src=\"images/0.gif\" class=\"no\"/></td>\n";
		} 
		$func_str .= "</tr>";
	} 
	return $func_str;
} 
if($_GET['i']) {include "../../../wp-config.php"; $getinfo = get_bloginfo('name').','.get_bloginfo('wpurl').'/';}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>环境检查-WordPress连接微博</title>
<meta name="robots" content="noarchive">
<style type="text/css">
body{margin-top:0px;font-family:Helvetica,Arial,Verdana,sans-serif; font-size:12px; background:#fff; color:#333; line-height:1.6em}
h3{margin:0px;font-size:1.17em;}
table{margin:10px 0; width:600px; text-align:left; border-collapse:collapse; border:1px solid #ebebeb}
table th{font-weight:bold; text-align:left; padding:10px 8px; background:#ebebeb}
table td{padding:8px}
table .odd{background:#f1f1f8}
img.yes, img.no{background:url(images/icon.gif) no-repeat; vertical-align:middle}
img.yes{width:15px; height:12px; background-position:0 -10px}
img.no{width:12px; height:12px; background-position:0 -22px}
</style>
</head>
<body>
<h3>环境检查</h3>
<p>当前服务器时间：<?php echo date("Y-m-d H:i:s",time());?> <a style="color:#f50" href="check.php">刷新</a> <a style="color:#f50" href="http://loginsns.com/#faqs_20" target="_blank">详细</a></p>
<table id="t1">
  <thead>
    <tr>
      <th>项目</th>
      <th>所需配置</th>
      <th>最佳配置</th>
      <th>当前服务器</th>
      <th>结果</th>
    </tr>
  </thead>
  <tbody>
    <?php echo(surrounding_support($surrounding_list));?>
  </tbody>
</table>
<h3>函数依赖性检查</h3>
<table id="t2">
  <thead>
    <tr>
      <th>函数名称</th>
      <th>状态</th>
      <th>结果</th>
    </tr>
  </thead>
  <tbody>
    <?php echo(function_support($funs_list));?>
  </tbody>
</table>
<?php echo ($getinfo) ? '<p>'.$getinfo.'</p>' : '';?>
<script type="text/javascript">
var a = document.getElementById("t1").getElementsByTagName("tr");
   for(i=0;i<a.length;i++)
   {
      a[i].className=(i%2>0)?"":"odd";
   }

var b = document.getElementById("t2").getElementsByTagName("tr");
   for(i=0;i<b.length;i++)
   {
      b[i].className=(i%2>0)?"":"odd";
   }
</script>
</body>
</html>
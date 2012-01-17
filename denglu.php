<?php
include "../../../wp-config.php";
$callback = (!empty($_SESSION['wp_url_bind'])) ? $_SESSION['wp_url_bind'] : $_SESSION['wp_url_back'];
if (!$callback) {
	$callback = get_bloginfo('url');
} 
header('Location:' . $callback);
?>
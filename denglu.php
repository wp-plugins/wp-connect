<?php
include "../../../wp-config.php";
$callback = (!empty($_SESSION['wp_url_bind'])) ? $_SESSION['wp_url_bind'] : $_SESSION['wp_url_back'];
if (!$callback) {
	if (isset($_GET['redirect_url'])) {
		$callback = utf8_uri_encode(urldecode($_GET['redirect_url']));
	} else {
		$callback = get_bloginfo('url');
	} 
} 
header('Location:' . $callback);

?>
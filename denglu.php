<?php
include "../../../wp-config.php";
if ($_SESSION['wp_url_back']) {
	$callback = $_SESSION['wp_url_back'];
} else {
	// $callback = admin_url('profile.php');
	$callback = get_bloginfo('url');
} 
header('Location:' . $callback);

?>
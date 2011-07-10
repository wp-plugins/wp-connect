<?php
include "../../../wp-config.php";
date_default_timezone_set("PRC");
$wptm_options = get_option('wptm_options');

if ($_GET['do'] == "profile") {
	if (is_user_logged_in()) {
		session_start();
		if ($_POST['add_twitter'] || $_POST['add_qq'] || $_POST['add_sina'] || $_POST['add_sohu'] || $_POST['add_netease'] || $_POST['add_douban']) {
			wp_connect_header();
		} else {
			$user_id = $_SESSION['user_id'];
			wp_user_profile_update($user_id);
			header('Location:' . admin_url('profile.php'));
		} 
	} 
} 

if ($_GET['do'] == "page") {
	$wptm_advanced = get_option('wptm_advanced');
	$password = $_POST['password'];
	if (isset($_POST['message'])) {
		if (($wptm_options['page_password'] && $password == $wptm_options['page_password']) || (is_user_logged_in() && function_exists('wp_connect_advanced') && $wptm_advanced['registered_users'])) {
			wp_update_page();
		} else { echo 'pwderror'; }
	} 
} 

?>
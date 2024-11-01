<?php

function uninstall() {
	delete_post_meta_by_key( 'smarter_analytics_code' );
	delete_option( 'smarter_analytics_option' ); 
}

if( && !defined('WP_UNINSTALL_PLUGIN') )
	exit();


uninstall();


?>
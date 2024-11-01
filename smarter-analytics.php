<?php
/*
Plugin Name: Smarter Analytics
Plugin URI: http://www.acumensoftwaredesign.com/wordpress-plugins/
Description: Add Google Analytics to your WordPress installation on a per-page/per-post basis or using global defaults. Set the defaults on the Settings -> Smarter Analytics options page, or set the page/post value on the page/post edit screen.
Version: 2.0
Author: Kerry Ritter
Author URI: http://www.kerryritter.com/
License: GPL
Copyright: Acumen Consulting
 */


function explode_codes($codes_string) {
    $delimiter = "|";
    return explode($delimiter, $codes_string);
}

require_once("smarter-analytics-posts-view-actions.php");

if( is_admin() ) {
    require_once("smarter-analytics-admin.php");
    require_once("smarter-analytics-add-metabox.php");
    
    $smarter_analytics = new SmarterAnalyticsAdmin();

	if ($_POST['reset'] == "reset") {
		delete_post_meta_by_key( 'smarter_analytics_code' );
		delete_option( 'smarter_analytics_option' ); 
	}    
}


register_uninstall_hook( "uninstall.php", 'uninstall' );

function add_plugin_links($links, $file) {  
    $plugin = plugin_basename(__FILE__);  
    if ($file == $plugin) {
        return array_merge( $links,   
            array( sprintf( '<a href="options-general.php?page=smarter-analytics-admin">Settings</a>', $plugin, __('Settings') ) )
        );  
    }
    return $links;  
}  
  
add_filter( 'plugin_row_meta', 'add_plugin_links', 10, 2 );  
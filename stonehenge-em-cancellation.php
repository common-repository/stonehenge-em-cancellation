<?php
/************************************************************
* Plugin Name:			Events Manager - Event Cancellation
* Description:			Adds the "Event Cancelled" status to your EM event and auto-emails a notification to your customers.
* Version:				2.0.2
* Author:  				Stonehenge Creations
* Author URI: 			https://www.stonehengecreations.nl/
* Plugin URI: 			https://www.stonehengecreations.nl/creations/stonehenge-em-cancellation/
* License URI: 			https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: 			stonehenge-em-cancellation
* Domain Path: 			/languages
* Requires at least: 	5.4
* Tested up to: 		6.0
* Requires PHP:			7.3
* Network:				false
************************************************************/

if( !defined('ABSPATH') ) exit;
include_once(ABSPATH.'wp-admin/includes/plugin.php');


#===============================================
function stonehenge_em_cancellation() {
	$wp 	= get_plugin_data(__FILE__);
	$plugin = array(
		'name' 		=> $wp['Name'],
		'short' 	=> 'EM - Cancel Events',
		'icon' 		=> '<span class="dashicons dashicons-dismiss"></span>',
		'slug' 		=> $wp['TextDomain'],
		'text' 		=> $wp['TextDomain'],
		'version' 	=> $wp['Version'],
		'class' 	=> 'Stonehenge_EM_Event_Cancellation',
		'base' 		=> plugin_basename(__DIR__),
		'network'	=> $wp['Network'],
		'prio' 		=> 9,
	);
	$plugin['url'] 		= admin_url().'admin.php?page='.$plugin['slug'];
	$plugin['options']	= get_option( $plugin['slug'] );
	return $plugin;
}

#===============================================
add_action('plugins_loaded', function() {
	if( !function_exists('stonehenge') ) { require_once('stonehenge/init.php'); }

	$plugin = stonehenge_em_cancellation();

	if( start_stonehenge($plugin) ) {
		include('classes/class-admin.php');
		include('classes/class-process.php');
		include('classes/class-init.php');
	}
	return;
}, 12);



#===============================================
# 	Set all cancelled events to pending upon deactivation, so they don't get lost.
#===============================================
function stonehenge_em_cancellation_deactivation() {
	global $wpdb;
	if( is_multisite() && is_plugin_active_for_network(__FILE__) ) {
		$sites = get_sites();
		foreach( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			$table = $wpdb->prefix.'posts';
			$wpdb->query("UPDATE `{$table}` SET `post_status` = 'pending' WHERE `post_status` = 'event-cancelled'");
			delete_option('em_cancelled_reactivated');
			restore_current_blog();
		}
	}
	else {
		$table = $wpdb->prefix.'posts';
		$wpdb->query("UPDATE `{$table}` SET `post_status` = 'pending' WHERE `post_status` = 'event-cancelled'");
		delete_option('em_cancelled_reactivated');
	}
	return;
}
register_deactivation_hook(__FILE__, 'stonehenge_em_cancellation_deactivation');
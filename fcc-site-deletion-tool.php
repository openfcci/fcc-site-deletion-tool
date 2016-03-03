<?php
/**
 * Plugin Name: FCC Site Deletion Tool
 * Plugin URI:  http://www.forumcomm.com/
 * Author:      Forum Communications Company
 * Author URI:  http://www.forumcomm.com/
 * Version:     0.16.03.02
 * Description: WP-CLI site deletion tool. Feed URL: example.com?feed=splogs
 * License:     GPL v2 or later
 * Text Domain: fcc-plugin-template
 */

# Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/*--------------------------------------------------------------
# PLUGIN ACTIVATION/DEACTIVATION HOOKS
--------------------------------------------------------------*/

 /**
  * Plugin Activation Hook
  */
 function fcc_site_deletion_tool_activation() {
   # Flush our rewrite rules on activation.
   flush_rewrite_rules();
 }
 register_activation_hook( __FILE__, 'fcc_site_deletion_tool_activation' );

 /**
  * Plugin Deactivation Hook
  */
 function fcc_site_deletion_tool_deactivation() {
   # Flush our rewrite rules on deactivation.
   flush_rewrite_rules();
 }
 register_deactivation_hook( __FILE__, 'fcc_site_deletion_tool_deactivation' );

 /*--------------------------------------------------------------
 # JSON FEED
 --------------------------------------------------------------*/

 /**
  * Add 'splogs' JSON Feed
  *
  * @since 0.16.03.02
  */
 function fcc_splogs_do_json_feed(){
   add_feed('splogs', 'add_splogs_feed');
 }
 add_action('init', 'fcc_splogs_do_json_feed');

 /**
  * Load JSON Feed Template
  *
  * @since 0.16.03.02
  */
 function add_splogs_feed(){
	load_template( plugin_dir_path( __FILE__ ) . 'template/feed-json.php' );
 }

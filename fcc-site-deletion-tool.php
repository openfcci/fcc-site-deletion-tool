<?php
/**
 * Plugin Name: FCC Site Deletion Tool
 * Plugin URI:  http://www.forumcomm.com/
 * Author:      Forum Communications Company
 * Author URI:  http://www.forumcomm.com/
 * Version:     1.16.07.18
 * Description: WP-CLI site deletion tool. Feed URL: example.com?feed=splogs
 * License:     GPL v2 or later
 * Text Domain: fcc-plugin-template
 * Network: True
 */

# Exit if accessed directly
defined( 'ABSPATH' ) || exit;

# Declare Constants
//define( 'FCCCLI__PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
//define( 'FCCCLI__PLUGIN_DIR',  plugin_dir_url( __FILE__ ) );

/*--------------------------------------------------------------
# PLUGIN ACTIVATION/DEACTIVATION HOOKS
--------------------------------------------------------------*/

/**
 * Plugin Activation Hook
 * Flush our rewrite rules on activation.
 * @since 0.16.03.02
 * @version 1.16.07.18
 */
function fcc_site_deletion_tool_activation() {
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'fcc_site_deletion_tool_activation' );

/**
 * Plugin Deactivation Hook
 * Flush our rewrite rules on deactivation.
 * @since 0.16.03.02
 * @version 1.16.07.18
 */
function fcc_site_deletion_tool_deactivation() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'fcc_site_deletion_tool_deactivation' );


/*--------------------------------------------------------------
 # LOAD INCLUDES FILES
 --------------------------------------------------------------*/

# Site/Blog Deletion Commands
require_once( plugin_dir_path( __FILE__ ) . '/includes/site-deletion.php' );

# Orphan Table Commands
require_once( plugin_dir_path( __FILE__ ) . '/includes/drop-orphan-tables.php' );

# Orphan Upload Directory Commands
require_once( plugin_dir_path( __FILE__ ) . '/includes/delete-orphan-directories.php' );


/*--------------------------------------------------------------
# Add's options page under tools
--------------------------------------------------------------*/

/**
 * Create settings menu under tools
 * @since 0.16.03.02
 * @version 1.16.07.18
 */
function fcc_create_settings_menu() {
	add_submenu_page(
		'settings.php',
		'Site Deletion Tool',
		'Site Deletion Tool',
		'manage_network',
		'fcc-site-deletion-tool',
		'fcc_site_options_page'
	);
};
add_action( 'network_admin_menu', 'fcc_create_settings_menu' );


/**
 * Options Page HTML
 * @since 0.16.03.02
 * @version 1.16.07.18
 */
function fcc_site_options_page() {
	if ( is_multisite() && current_user_can( 'manage_network' ) ) {
		if ( isset( $_POST['action'] ) && 'update_delete_settings' == $_POST['action'] ) {

			// Store option values in a variable
			$deletion_ids = preg_replace( '/\s+/', '', sanitize_text_field( $_POST['deletion-ids'] ) );
			$whitelist_ids = preg_replace( '/\s+/', '', sanitize_text_field( $_POST['whitelist-ids'] ) );

			// Save option values
			update_site_option( 'deletion_ids', $deletion_ids );
			update_site_option( 'whitelist_ids', $whitelist_ids );

			// Just assume it all went according to plan
			echo '<div id="message" class="updated fade"><p><strong>List Updated!</strong></p></div>';

		} // END if POST
		?>
		<div class="wrap">
		 <h2>Site Deletion Tool</h2>
		 <p><span class="description">Enter the IDs of the sites to be deleted or whitelisted against deletion. Separate IDs with commas (example: 4,5,6).</span></p>
		 <div class="card">
			 <div class="inside">
					<form method="post">
							<input type="hidden" name="action" value="update_delete_settings" />
							<h3>Sites to Delete</h3>
							<table class="form-table">
								<tr valign="top">
									<textarea type="text" name="deletion-ids" cols="70"><?php
									echo get_site_option( 'deletion_ids' );
									?></textarea>
								</tr>
							</table>
							<p><strong>Deletion Feed URL: </strong><a href="<?php
							echo network_site_url() . '?feed=splogs'; ?>" target="_blank"><?php echo network_site_url() . '?feed=splogs'; ?></a></p>
							<hr><br>
							<h3>Whitelist</h3>
							<table class="form-table">
								<tr valign="top">
									<textarea type="text" name="whitelist-ids" cols="70"><?php
									echo get_site_option( 'whitelist_ids' );
									?></textarea>
								</tr>
							</table>
							<p><strong>Whitelist Feed URL: </strong><a href="<?php echo network_site_url() . '?feed=whitelist'; ?>" target="_blank"><?php echo network_site_url() . '?feed=whitelist'; ?></a></p>
							<hr><br>
							<input type="submit" class="button-primary" name="update_delete_settings" value="Save Settings" />
					</form>
			</div>
			<br>
		</div>
		</div>

		<?php
	}
}

/*--------------------------------------------------------------
 # JSON FEED
 --------------------------------------------------------------*/

/**
 * Add 'splogs' JSON Feed
 *
 * @since 0.16.03.02
 * @version 1.16.07.18
 */
function fcc_splogs_do_json_feed() {
	add_feed( 'splogs', 'add_splogs_feed' );
	add_feed( 'whitelist', 'add_whitelist_feed' );
}
add_action( 'init', 'fcc_splogs_do_json_feed' );

/**
 * Load JSON Feed Template
 *
 * @since 0.16.03.02
 * @version 1.16.07.18
 */
function add_splogs_feed() {
	load_template( plugin_dir_path( __FILE__ ) . 'template/feed-json.php' );
}

function add_whitelist_feed() {
	load_template( plugin_dir_path( __FILE__ ) . 'template/whitelist-feed.php' );
}

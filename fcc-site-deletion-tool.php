<?php
/**
 * Plugin Name: FCC Site Deletion Tool
 * Plugin URI:  http://www.forumcomm.com/
 * Author:      Forum Communications Company
 * Author URI:  http://www.forumcomm.com/
 * Version:     0.16.03.28
 * Description: WP-CLI site deletion tool. Feed URL: example.com?feed=splogs
 * License:     GPL v2 or later
 * Text Domain: fcc-plugin-template
 * Network: True
 */

# Exit if accessed directly
defined( 'ABSPATH' ) || exit;


/*--------------------------------------------------------------
# Add's options page under tools
--------------------------------------------------------------*/

add_action('network_admin_menu', 'fcc_create_settings_menu');

#create settings menu under tools
function fcc_create_settings_menu(){
  add_submenu_page(
    'settings.php',
    'FCC Site Deletion Tool',
    'FCC Site Deletion Tool',
    'manage_network',
    'fcc-site-deletion-tool',
    'fcc_site_options_page'
  );
};

#Options page html
function fcc_site_options_page(){

   if (is_multisite() && current_user_can('manage_network'))  {

        if (isset($_POST['action']) && $_POST['action'] == 'update_delete_settings') {

            //store option values in a variable
            $network_settings = preg_replace('/\s+/', '', sanitize_text_field( $_POST['network-settings'] ) );

            //save option values
            update_site_option( 'deletion_ids', $network_settings );

            //just assume it all went according to plan
            echo '<div id="message" class="updated fade"><p><strong>List Updated!</strong></p></div>';

      }//if POST

      ?>

     <div class="wrap">
       <div class="card">
         <div class="inside">
            <form method="post">
                <input type="hidden" name="action" value="update_delete_settings" />
                <h3>Site Deletion Tool</h3>
                <span class="description">Enter the IDs of the sites to be deleted. Separate IDs with commas (example: 4,5,6).</span><br><br>
                <table class="form-table">
                  <tr valign="top">
                    <textarea type="text" name="network-settings" cols="70"><?php echo get_site_option('deletion_ids'); ?></textarea>
                  </tr>
                </table>

                <input type="submit" class="button-primary" name="update_delete_settings" value="Save Settings" />
            </form>
        </div>
        <br>
        <hr>
        <p><strong>Feed URL: </strong><a href="<?php echo network_site_url() . '?feed=splogs'; ?>" target="_blank"><?php echo network_site_url() . '?feed=splogs'; ?></a></p>
      </div>
    </div>

<?php
  }
}


/*--------------------------------------------------------------
# WP-CLI Deletion Command
--------------------------------------------------------------*/

if( defined( 'WP_CLI' ) && WP_CLI ) {

        class Delete_Sites {

                function start_delete(){
                    # Get the JSON
                    $response = file_get_contents(network_site_url() . "?feed=splogs");
                    # Decode to ARRAY
                    $array = json_decode( $response, TRUE );
                    # Convert array to OBJECT
                    $blogs = new stdClass();
                    foreach ($array as $key => $value) {
                      $blogs->$key = $value;
                    }
                    ### Start Blog Deletion ###
                    if (!empty($blogs)) {
                      #Blogs that are successfully deleted.
                      $blog_success_count = 0;
                      #blogs that will have an error.
                      $blog_error_count = 0;

                      #Start Deletion Loop
                      foreach ($blogs as $blog) {
                        #Get the blog details to test if it is a blog
                         $blog_detail = get_blog_details( $blog );

                         #If not a blog, output blog not found, else the blog is successfully deleted.
                         if ( !$blog_detail ) {
                           WP_CLI::line( "Error: " . $blog . " Blog Not Found." );
                           $blog_error_count++;
                         }else{
                           #delete blog id
                           wpmu_delete_blog( $blog, true  );
                           WP_CLI::success( " Blog Deleted: " . $blog );
                           $blog_success_count++;
                         }

                       }
                       #Output total number of blogs deleted and errors.
                      WP_CLI::success($blog_success_count . " Total Blogs Deleted. " . $blog_error_count . " Blogs Not Found or an Error on Deletion.");

                    }
                }
        }

        WP_CLI::add_command( 'delete_sites', 'Delete_Sites' );
}


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

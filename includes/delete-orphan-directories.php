<?php
/*--------------------------------------------------------------
# WP-CLI Deletion Command
--------------------------------------------------------------*/

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	function delete_directories( $args, $assoc_args ) {
		if ( ! is_multisite() ) {
			WP_CLI::error( 'This is not a multisite install.' );
		}

		// Get the Directory
		$dir = $assoc_args['dir'];
		if ( ! $dir ) {
			WP_CLI::error( 'Please provide a valid path to the WordPress uploads base directory.' );
		}

		if ( ! is_dir( $dir ) ) {
			WP_CLI::error( 'Provided directory is not valid.' );
		}
		WP_CLI::log( 'Provided path ' . WP_CLI::colorize( '%G' . $dir . '%n' ) . ' is a valid directory, proceeding with cleanup.' );

		/* Generate an Array of Orphan Directory IDs */
		WP_CLI::log( WP_CLI::colorize( '%G*%n' ) . ' Preparing directory IDs' );
		$blogs_dir = array(); // Array of (blog) IDs of the blogs.dir folder directories

		if ( is_dir( $dir ) ) { // Open a known directory, and proceed to read its contents
			if ( $dh = opendir( $dir ) ) {
				while ( ($file = readdir( $dh )) !== false ) {
					if ( '.' != $file && '..' != $file && '.DS_Store' != $file ) {
						$blogs_dir[] = $file;
					}
				}
				closedir( $dh );
			}
		}
		sort( $blogs_dir, SORT_NUMERIC );

		/* Generate an Array of Public/Live Blog IDs to Whitelist */
		WP_CLI::log( WP_CLI::colorize( '%G*%n' ) . ' Gathering public blog IDs' );
		global $wpdb;
		$prefix = $wpdb->base_prefix;
		$blogs_table = $prefix . 'blogs';
		$public_blogs = $wpdb->get_col( 'SELECT blog_id FROM ' . $blogs_table );

		/* Remove the public sites from the directory list */
		WP_CLI::log( WP_CLI::colorize( '%G*%n' ) . ' Whitelisting public blog IDs' );
		$blogs_dir_ids = array_values( array_diff( $blogs_dir, $public_blogs ) );

		WP_CLI::log( WP_CLI::colorize( '%G*%n' ) . ' Preparing orphan directory paths' );
		$target_dirs = array();
		foreach ( $blogs_dir_ids as $blogs_dir_id ) {
			$full_path = $dir . DIRECTORY_SEPARATOR . $blogs_dir_id;
			$target_dirs[] = $full_path;
		}

		# Count total for progress bar
		$total_to_delete = count( $target_dirs );
		$success_count = 0;
		$error_count = 0;
		WP_CLI::log( WP_CLI::colorize( '%G*%n' ). ' Preparing to delete ' . WP_CLI::colorize( '%B'.$total_to_delete.'%n' ) . ' orphaned WordPress upload directories' );
		$notify = \WP_CLI\Utils\make_progress_bar( 'Deleting Orphan Directories: ', $total_to_delete );

		foreach ( $target_dirs as $target_dir ) {
			if ( $target_dir ) {
				$it = new RecursiveDirectoryIterator( $target_dir, RecursiveDirectoryIterator::SKIP_DOTS );
				$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );
				foreach ( $files as $file ) {
					if ( $file->isDir() ) {
						rmdir( $file->getRealPath() );
					} else {
						unlink( $file->getRealPath() );
					}
				}
				rmdir( $target_dir ); // Delete the empty folder
				$success_count++;
				$notify->tick(); // tick after each directory
			} else {
				WP_CLI::warning( 'Target directory ' . $target_dir . 'cannot be found, skipping.' );
				$error_count++;
				$notify->tick(); // tick after each directory
			}
		}
		$notify->finish();
		WP_CLI::success( WP_CLI::colorize( '%B'.$success_count.'%n' ) . ' total database directories have been deleted. ' . WP_CLI::colorize( '%R'.$error_count.'%n' ) . ' errors occurred / directories not found.' );
		//WP_CLI::print_value( $target_dirs );
	}
	WP_CLI::add_command( 'delete_directories', 'delete_directories' );
}

/*--------------------------------------------------------------
# Generate an Array of Orphan Directory ID's
--------------------------------------------------------------*/

/*--------------------------------------------------------------
# Get the DIR (Misc Below)
--------------------------------------------------------------*/
/*if ( defined( 'WP_CLI' ) && WP_CLI ) {
	function debug_base_dir( $args, $assoc_args ) {
		WP_CLI::print_value( wp_get_upload_dir() );
	}
	WP_CLI::add_command( 'debug_base_dir', 'debug_base_dir' );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	function get_base_dir( $args, $assoc_args ) {
		$uploads = wp_get_upload_dir();
		$dir = $uploads['basedir'];

		/*$dir = rtrim( $dir, DIRECTORY_SEPARATOR );
		$basename = wp_basename( $uploads['basedir'] );
		$dir = rtrim( $dir, $basename );
		$dir = rtrim( $dir, DIRECTORY_SEPARATOR );
		$dir = rtrim( $dir, $blog_id );
		$dir = rtrim( $dir, DIRECTORY_SEPARATOR );*/

		/*WP_CLI::print_value( $dir );
	}
	WP_CLI::add_command( 'get_base_dir', 'get_base_dir' );
}*/

/*if ( defined( 'WP_CLI' ) && WP_CLI ) {
	function get_directories() {
		// Code Goes Here
		$subsite_url = get_subsite_url();
		$print = WP_CLI::launch_self( 'get_base_dir --url='.$subsite_url, array(), array(), false, true );
		$sites = json_decode( $print->stdout );
		WP_CLI::print_value( 'get_base_dir --url='.$subsite_url );
	}
	WP_CLI::add_command( 'get_directories', 'get_directories' );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	function get_site_ids() {
		# Get the Live Blog IDs
		global $wpdb;
		$prefix = $wpdb->base_prefix;
		$blogs_table = $prefix . 'blogs';
		$public_blogs = $wpdb->get_col( 'SELECT blog_id FROM ' . $blogs_table );
		WP_CLI::print_value( $public_blogs );
	}
	WP_CLI::add_command( 'get_site_ids', 'get_site_ids' );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	function get_subsite_url( $args, $assoc_args ) {
		$response = WP_CLI::launch_self( 'site list --public=1 --fields=blog_id,url,public', array(), array( 'format' => 'json' ), false, true ); //run_command
		$sites = json_decode( $response->stdout );
		$subsite = $sites['1']->url; // get first subsite
		//WP_CLI::print_value( $subsite );
		return $subsite;
	}
	WP_CLI::add_command( 'get_subsite_url', 'get_subsite_url' );
}*/

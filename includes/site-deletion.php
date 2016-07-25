<?php
/*--------------------------------------------------------------
# WP-CLI Deletion Command
--------------------------------------------------------------*/

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	//class Delete_Sites {
		function delete_sites() {

			# Set Up Colors
			$error = WP_CLI::colorize( '%RERROR%n' );
			$skipped = WP_CLI::colorize( '%BSKIPPED%n' );
			$deleted = WP_CLI::colorize( '%GDELETED%n' );

			# Whitelist the Main Site
			$main_site = array( '1' );

			# Get the JSON
			$whitelist_ids = file_get_contents( network_site_url() . '?feed=whitelist' );
			$deletion_ids = file_get_contents( network_site_url() . '?feed=splogs' );

			# Decode to ARRAY
			$whitelist_ids = json_decode( $whitelist_ids, true );
			$deletion_array = json_decode( $deletion_ids, true );

			# Count total for progress bar
			$total_to_delete = count( $deletion_array );

			# Merge main site ID into whitelist
			$whitelist_ids = array_merge( $main_site, $whitelist_ids );
			WP_CLI::warning( 'Whitelisted Sites: ' . join( ',', $whitelist_ids ) );
			WP_CLI::warning( 'Sites to be deleted: ' . join( ',', $deletion_array ) );

			$notify = \WP_CLI\Utils\make_progress_bar( 'Deleting Sites', $total_to_delete );

			# Convert array to OBJECT
			$blogs = new stdClass();
			foreach ( $deletion_array as $key => $value ) {
				$blogs->$key = $value;
			}

			### Start Blog Deletion ###
			if ( ! empty( $blogs ) ) {
				# Blogs that are successfully deleted.
				$blog_success_count = 0;
				# Blogs that will have an error.
				$blog_error_count = 0;

				#Start Deletion Loop
				foreach ( $blogs as $blog ) {
					if ( ! empty( $blog ) ) {
						#Get the blog details to test if it is a blog
						$blog_detail = get_blog_details( $blog );
						$siteurl = WP_CLI::colorize( '%M' . $blog_detail->siteurl . '%n' );

						# If not a blog, output blog not found, else the blog is successfully deleted.
						if ( ! $blog_detail ) {
							WP_CLI::line( $error . ':   Site ' . $blog . ' Not Found.' );
							$blog_error_count++;
						} else if ( in_array( $blog, $whitelist_ids ) ) {
							# Skip Whitelisted Sites
							WP_CLI::line( $skipped . ': Site ' . $blog . ' (' . $siteurl . ')' . ' is whitelisted against deletion.' );
						} else {
							# Delete the site
							wpmu_delete_blog( $blog, true );
							WP_CLI::line( $deleted . ': Site ' . $blog . ' (' . $siteurl . ')' . ' deleted.' );
							$blog_success_count++;
						}
					}
					$notify->tick();
				}
				$notify->finish();
			} // endif

			#Output total number of blogs deleted and errors.
			WP_CLI::success( $blog_success_count . ' total blogs would have been deleted. ' . $blog_error_count . 'errors/blogs not found.' );
		} // End function start_delete

		function test_delete() {
			# Set Up Colors
			$error = WP_CLI::colorize( '%RERROR%n' );
			$skipped = WP_CLI::colorize( '%BSKIPPED%n' );
			$deleted = WP_CLI::colorize( '%GDELETED%n' );

			# Whitelist the Main Site
			$main_site = array( '1' );
			# Get the JSON
			$whitelist_ids = file_get_contents( network_site_url().'?feed=whitelist' );
			$deletion_ids = file_get_contents( network_site_url() . '?feed=splogs' );
			# Decode to ARRAY
			$whitelist_ids = json_decode( $whitelist_ids, true );
			$deletion_array = json_decode( $deletion_ids, true );
			# Count total for progress bar
			$total_to_delete = count( $deletion_array );

			# Merge main site ID into whitelist
			$whitelist_ids = array_merge( $main_site, $whitelist_ids );
			WP_CLI::warning( 'Whitelisted Sites: ' . join( ',', $whitelist_ids ) );
			WP_CLI::warning( 'Sites to be deleted: ' . join( ',', $deletion_array ) );

			$notify = \WP_CLI\Utils\make_progress_bar( 'Deleting Sites', $total_to_delete );

			# Convert array to OBJECT
			$blogs = new stdClass();
			foreach ( $deletion_array as $key => $value ) {
				$blogs->$key = $value;
			}
			### Start Blog Deletion ###
			if ( ! empty( $blogs ) ) {

				#Blogs that are successfully deleted.
				$blog_success_count = 0;
				# Blogs that will have an error.
				$blog_error_count = 0;

				# Start Deletion Loop
				foreach ( $blogs as $blog ) {
					if ( ! empty( $blog ) ) {
						#Get the blog details to test if it is a blog
						$blog_detail = get_blog_details( $blog );
						$siteurl = WP_CLI::colorize( '%M' . $blog_detail->siteurl . '%n' );

						#If not a blog, output blog not found, else the blog is successfully deleted.
						if ( ! $blog_detail ) {
							WP_CLI::line( $error . ':   Site ' . $blog . ' Not Found.' );
							$blog_error_count++;
						} else if ( in_array( $blog, $whitelist_ids ) ) {
							# Skip Whitelisted Sites
							WP_CLI::line( $skipped . ': Site ' . $blog . ' (' . $siteurl . ')' . ' is whitelisted against deletion.' );
						} else {
							# Delete the site
							WP_CLI::line( $deleted . ': Site ' . $blog . ' (' . $siteurl . ')' . ' would have been deleted. (Test mode)' );
							$blog_success_count++;
						}
					}
					$notify->tick();
				}
				$notify->finish();
			}
			# Output total number of blogs deleted and errors.
			WP_CLI::success( $blog_success_count . ' total blogs would have been deleted. ' . $blog_error_count . ' errors/blogs not found.' );
		} // End function test_delete
	//} // end class
		//WP_CLI::add_command( 'delete_sites', 'Delete_Sites' );
		WP_CLI::add_command( 'delete_sites', 'delete_sites' );
}

<?php
/*--------------------------------------------------------------
# WP-CLI Deletion Command
--------------------------------------------------------------*/

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	function drop_tables() {

		global $wpdb;
		$db_tables = $wpdb->get_col( 'SHOW TABLES' );

		# Whitelist the Main Site
		$main_site = array( '1' );

		# Get the JSON
		$whitelist_ids = file_get_contents( network_site_url() . '?feed=whitelist' );
		$deletion_ids = file_get_contents( network_site_url() . '?feed=splogs' );

		# Decode to ARRAY
		$whitelist_ids = json_decode( $whitelist_ids, true );
		$deletion_array = json_decode( $deletion_ids, true );

		# Count total for progress bar
		$total_site_ids = count( $deletion_array );
		$total_to_delete = ( count( $deletion_array ) * 10 );

		# Merge main site ID into whitelist
		$whitelist_ids = array_merge( $main_site, $whitelist_ids );
		WP_CLI::warning( 'Whitelisted Site IDs: ' . join( ',', $whitelist_ids ) );
		//WP_CLI::warning( 'Sites IDs of tables to be dropped: ' . join( ',', $deletion_array ) );
		WP_CLI::warning( 'Preparing to drop up to: ' . WP_CLI::colorize( '%B'.$total_to_delete.'%n' ) . ' tables from ' . WP_CLI::colorize( '%G'.$total_site_ids.'%n' ) . ' site(s).' );

		$notify = \WP_CLI\Utils\make_progress_bar( 'Dropping Orphan Tables: ', $total_site_ids );

		# Convert array to OBJECT
		$tables = new stdClass();
		foreach ( $deletion_array as $key => $value ) {
			$tables->$key = $value;
		}

		### Start Table Deletion ###
		if ( ! empty( $tables ) ) {
			# Blogs that are successfully deleted.
			$table_success_count = 0;
			# Blogs that will have an error.
			$table_error_count = 0;

			$prefix = $wpdb->prefix;

			#Start Deletion Loop
			foreach ( $tables as $tableid ) {
				if ( ! empty( $tableid ) ) {

					$t_commentmeta = $prefix . $tableid . '_commentmeta';
					$t_comments = $prefix . $tableid . '_comments';
					$t_links = $prefix . $tableid . '_links';
					$t_options = $prefix . $tableid . '_options';
					$t_postmeta = $prefix . $tableid . '_postmeta';
					$t_posts = $prefix . $tableid . '_posts';
					$t_termmeta = $prefix . $tableid . '_termmeta';
					$t_terms = $prefix . $tableid . '_terms';
					$t_term_relationships = $prefix . $tableid . '_term_relationships';
					$t_term_taxonomy = $prefix . $tableid . '_term_taxonomy';

					$tablenames = array( $t_commentmeta, $t_comments, $t_links, $t_options, $t_postmeta, $t_posts, $t_termmeta, $t_terms, $t_term_relationships, $t_term_taxonomy );

					foreach ( $tablenames as $tablename ) {
						if ( in_array( $tablename, $db_tables ) ) {

							$result = $wpdb->query( 'DROP TABLE ' . $tablename );
							if ( false === $result ) {
								$table_error_count++;
								WP_CLI::error( 'Could not drop table ' . $tablename . ' - ' . $wpdb->last_error );
								continue;
							}
							WP_CLI::log( WP_CLI::colorize( '%B'.$tablename.'%n' ) . ' has been dropped.' );
							$table_success_count++;
							//$notify->tick(); // tick after each table
						}
					}
				}
				$notify->tick(); // tick after each site id
			} // for each id
		} // endif
		$notify->finish();
		#Output total number of blogs deleted and errors.
		WP_CLI::success( $table_success_count . ' total database tables have been dropped. ' . $table_error_count . ' errors occurred / tables not found.' );
	} // End function start_delete
	WP_CLI::add_command( 'drop_tables', 'drop_tables' );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	function drop_tables_by_name() {

		global $wpdb;
		$db_tables = $wpdb->get_col( 'SHOW TABLES' );

		# Get the JSON
		$deletion_ids = file_get_contents( network_site_url() . '?feed=splogs' );
		$deletion_array = json_decode( $deletion_ids, true );
		$tablenames = $deletion_array;

		# Count total for progress bar
		$total_to_drop = count( $deletion_array );

		WP_CLI::warning( 'Preparing to drop ' . WP_CLI::colorize( '%B'.$total_to_drop.'%n' ) . ' tables.' );

		$notify = \WP_CLI\Utils\make_progress_bar( 'Dropping Tables: ', $total_to_drop );

		# Convert array to OBJECT
		$tables = new stdClass();
		foreach ( $deletion_array as $key => $value ) {
			$tables->$key = $value;
		}

		### Start Table Deletion ###
		if ( ! empty( $tables ) ) {
			# Blogs that are successfully deleted.
			$table_success_count = 0;
			# Blogs that will have an error.
			$table_error_count = 0;

			#Start Deletion Loop
			foreach ( $tables as $tablename ) {

				if ( in_array( $tablename, $db_tables ) ) {

					$result = $wpdb->query( 'DROP TABLE ' . $tablename );
					if ( false === $result ) {
						$table_error_count++;
						WP_CLI::error( 'Could not drop table ' . $tablename . ' - ' . $wpdb->last_error );
						continue;
					}
					WP_CLI::line( WP_CLI::colorize( '%B'.$tablename.'%n' ) . ' has been dropped' );
					$table_success_count++;
					$notify->tick(); // tick after each table
				}
			}
		}
		$notify->finish();
		#Output total number of blogs deleted and errors.
		WP_CLI::success( $table_success_count . ' total database tables have been dropped. ' . $table_error_count . ' errors occurred / tables not found.' );
	} // End function start_delete
	WP_CLI::add_command( 'drop_tables_by_name', 'drop_tables_by_name' );
}

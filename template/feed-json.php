<?php
/**
 * JSON Feed Template
 *
 * @since 0.16.03.02
 */
$callback = trim(esc_html(get_query_var('callback')));
$charset  = get_option('charset');

$deletion_ids = get_site_option('deletion_ids');
$deletion_ids = explode( ',', $deletion_ids );

if ( $deletion_ids ) {

	$json = $deletion_ids;

	$json = json_encode($json);

	nocache_headers();
	if (!empty($callback)) {
		header("Content-Type: application/x-javascript; charset={$charset}");
		echo "{$callback}({$json});";
	} else {
		header("Content-Type: application/json; charset={$charset}");
		echo $json;
	}

} else {
	status_header('404');
	wp_die("404 Not Found");
}

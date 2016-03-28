<?php
/**
 * JSON Feed Template
 *
 * @since 0.16.03.28
 */
$callback = trim(esc_html(get_query_var('callback')));
$charset  = get_option('charset');

$whitelist_ids = get_site_option('whitelist_ids');
$whitelist_ids = explode( ',', $whitelist_ids );

if ( $whitelist_ids ) {

	$json = $whitelist_ids;

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

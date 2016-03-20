<?php
/*
    Plugin Name: URLength
    Plugin URI: http://intbizth.com/
    Description: Set url length on a wordpress site.
    Version: 0.1
    Author: Inwdragon
    Author URI: http://kanokpol.com/
    License: (c) 2016 urlength
*/

/**
 * activate hook
 */
register_activation_hook(__FILE__, 'activation_action');
function activation_action() {
	setupDatabase(true);
}


register_deactivation_hook(__FILE__, 'deactivation_deaction');
function deactivation_deaction() {
	setupDatabase(false);
}

/**
 * setup database
 */
function setupDatabase($isActivate){
	/**
	 * wordpress database
	 */
	global $wpdb;

	/**
	 * @var $min_mysql_version string
	 */
	$minMysqlVersion = '5.5.0';

	/**
	 * @var $charLength integer
	 */
	$charLength = 700;

	if(!$isActivate){
		$tablePost = $wpdb->posts;
		$sql = "ALTER TABLE ".$tablePost." CHANGE post_name post_name VARCHAR(200) $charset_charset $charset_collate NOT NULL DEFAULT ''";
		$results = $wpdb->query($sql);
		return;
	}

	$t = mysql_query("select version() as ve");
	echo mysql_error();
	$r = mysql_fetch_object($t);
	define('MYSQL_VERSION', $r->ve); 

	if(version_compare(MYSQL_VERSION, $minMysqlVersion) >= 0){
		$tablePost = $wpdb->posts;
		$sql = "ALTER TABLE ".$tablePost." CHANGE post_name post_name VARCHAR(".$charLength.") $charset_charset $charset_collate NOT NULL DEFAULT ''";
		$results = $wpdb->query($sql);
  	}
}


/**
 * activate plugin action
 */
remove_filter( 'sanitize_title', 'sanitize_title_with_dashes');
add_filter( 'sanitize_title', 'urlength_sanitize_title_with_dashes');

/**
 * urlength
 * 
 * Sanitizes a title, replacing whitespace and a few other characters with dashes.
 *
 * Limits the output to alphanumeric characters, underscore (_) and dash (-).
 * Whitespace becomes a dash.
 *
 * @since 1.2.0
 *
 * @param string $title     The title to be sanitized.
 * @return string The sanitized title.
 */
function urlength_sanitize_title_with_dashes($title) {
    $title = strip_tags($title);
    // Preserve escaped octets.
    $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
    // Remove percent signs that are not part of an octet.
    $title = str_replace('%', '', $title);
    // Restore octets.
    $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
 
    if(seems_utf8($title)) {
        if(function_exists('mb_strtolower')) {
            $title = mb_strtolower($title, 'UTF-8');
        }
    	$title = utf8_uri_encode($title, 700);
    }
 
    $title = strtolower($title);
    $title = preg_replace('/&.+?;/', '', $title); // kill entities
    $title = str_replace('.', '-', $title);
 
    $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
    $title = preg_replace('/\s+/', '-', $title);
    $title = preg_replace('|-+|', '-', $title);
    $title = trim($title, '-');
 
    return $title;
}

?>

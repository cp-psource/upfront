<?php

/**
 * This is the entity entry point, where we inform Upfront of our existence.
 */
function unewnavigation_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/unewnavigation.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_nav_presets_server.php');

	// Include the backend support stuff
	require_once( ABSPATH . 'wp-admin/includes/nav-menu.php' );

	// Add element defaults to data object
	add_action('upfront_data', array('Upfront_UnewnavigationView', 'add_js_defaults'));
	add_filter('upfront_l10n', array('Upfront_UnewnavigationView', 'add_l10n_strings'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('unewnavigation', upfront_relative_element_url('js/unewnavigation', __FILE__));

	add_action('wp_enqueue_scripts', array('Upfront_UnewnavigationView', 'add_styles_scripts'));
}
// Initialize the entity when Upfront is good and ready
add_action('upfront-core-initialized', 'unewnavigation_initialize');

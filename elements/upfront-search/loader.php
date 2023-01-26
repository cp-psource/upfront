<?php
/**
 * This is the entity entry point, where we inform Upfront of our existence.
 */
function usearch_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/upfront_search.php');

	// Add element defaults to data object
	add_action('upfront_data', array('Upfront_UsearchView', 'add_js_defaults'));
	add_filter('upfront_l10n', array('Upfront_UsearchView', 'add_l10n_strings'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('usearch', upfront_relative_element_url('js/usearch', __FILE__));

	// Add styles and dependencies
	add_action('wp_enqueue_scripts', array('Upfront_UsearchView', 'add_frontend_dependencies'));
}
// Initialize the entity when Upfront is good and ready
add_action('upfront-core-initialized', 'usearch_initialize');

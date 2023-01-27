<?php

/**
 * This is the entity entry point, where we inform Upfront of our existence.
 */
function this_post_initialize () {

	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/this_post.php');
	
	require_once (dirname(__FILE__) . '/lib/class_upfront_the_post_presets_server.php');

	// Add element defaults to data object
	add_action('upfront_data', array('Upfront_ThisPostView', 'add_js_defaults'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('this_post', upfront_relative_element_url('js/this_post', __FILE__));

	add_filter('upfront_l10n', array('Upfront_ThisPostView', 'add_l10n_strings'));

	// Define fallback selectors, if nothing else got in
	add_action('upfront_post_selectors', array('Upfront_ThisPostView', 'add_fallback_selectors'), 999); // <-- register late

}
// Initialize the entity when Upfront is good and ready
add_action('upfront-core-initialized', 'this_post_initialize');

<?php

/**
 * This is the entity entry point, where we inform Upfront of our existence.
 */
function this_page_initialize () {

	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/this_page.php');

	// Add element defaults to data object
	add_action('upfront_data', array('Upfront_ThisPageView', 'add_js_defaults'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('this_page', upfront_relative_element_url('js/this_page', __FILE__));

	add_filter('upfront_l10n', array('Upfront_ThisPageView', 'add_l10n_strings'));

}
// Initialize the entity when Upfront is good and ready
add_action('upfront-core-initialized', 'this_page_initialize');

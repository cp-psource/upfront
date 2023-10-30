<?php

/**
 * This is the entity entry point, where we inform Upfront of our existence.
 */
function ucomment_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/upfront_comment.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_ucomment_presets_server.php');

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('ucomment', upfront_relative_element_url('js/ucomment', __FILE__));

	// Add element defaults to data object
	add_action('upfront_data', array('Upfront_UcommentView', 'add_js_defaults'));

	add_filter('upfront_l10n', array('Upfront_UcommentView', 'add_l10n_strings'));

	add_action('wp_enqueue_scripts', array('Upfront_UcommentView', 'add_public_script'));
}
// Initialize the entity when Upfront is good and ready
add_action('upfront-core-initialized', 'ucomment_initialize');

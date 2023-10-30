<?php

/**
 * Registers the element in Upfront
 */
function ufcode_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/upfront_code.php');

	add_filter('upfront_l10n', array('Upfront_CodeView', 'add_l10n_strings'));
	add_filter('upfront_data', array('Upfront_CodeView', 'add_js_defaults'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('upfront_code', upfront_relative_element_url('js/upfront_code', __FILE__));
}

//Hook it when Upfront is ready
add_action('upfront-core-initialized', 'ufcode_initialize');

<?php

/**
 * Registers the element in Upfront
 */
function uaccordion_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/uaccordion.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_accordion_presets_server.php');

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('uaccordion', upfront_relative_element_url('js/uaccordion', __FILE__));

	add_filter('upfront_l10n', array('Upfront_UaccordionView', 'add_l10n_strings'));

	// Add element defaults to data object
	$uaccordion = new Upfront_UaccordionView(array());
	add_action('upfront_data', array($uaccordion, 'add_js_defaults'));

	// Add the public stylesheet
	add_action('wp_enqueue_scripts', array('Upfront_UaccordionView', 'add_styles_scripts'));
}

//Hook it when Upfront is ready
add_action('upfront-core-initialized', 'uaccordion_initialize');

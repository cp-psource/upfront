<?php

/**
 * Registers the element in Upfront
 */
function utabs_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/utabs.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_tab_presets_server.php');

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('utabs', upfront_relative_element_url('js/utabs', __FILE__));

	add_filter('upfront_l10n', array('Upfront_UtabsView', 'add_l10n_strings'));

	// Add element defaults to data object
	$utabs = new Upfront_UtabsView(array());
	add_action('upfront_data', array($utabs, 'add_js_defaults'));

	// Add the public stylesheet
	add_action('wp_enqueue_scripts', array('Upfront_UtabsView', 'add_styles_scripts'));
}

//Hook it when Upfront is ready
add_action('upfront-core-initialized', 'utabs_initialize');

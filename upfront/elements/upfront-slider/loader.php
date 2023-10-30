<?php

/**
 * This is the entity entry point, where we inform Upfront of our existence.
 */
function uslider_initialize () {

	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/upfront_slider.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_slider_presets_server.php');

	//Add js and css
	add_action('wp_enqueue_scripts', array('Upfront_UsliderView', 'add_styles_scripts'));

	//// Add element defaults to data object
	add_action('upfront_data', array('Upfront_UsliderView', 'add_js_defaults'));

	add_filter('upfront_l10n', array('Upfront_UsliderView', 'add_l10n_strings'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('upfront_slider', upfront_relative_element_url('js/uslider', __FILE__));
}
// Initialize the entity when Upfront is good and ready
add_action('upfront-core-initialized', 'uslider_initialize');

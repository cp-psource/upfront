<?php

/**
 * Registers the element in Upfront
 */
function uimage_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/uimage.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_image_presets_server.php');

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('uimage', upfront_relative_element_url('js/uimage', __FILE__));


	// Add element defaults to data object
	$uimage = new Upfront_UimageView(array());
	add_action('upfront_data', array($uimage, 'add_js_defaults'));

	add_filter('upfront_l10n', array('Upfront_UimageView', 'add_l10n_strings'));

	// Add the public stylesheet
	add_action('wp_enqueue_scripts', array('Upfront_UimageView', 'add_styles_scripts'));
}

//Hook it when Upfront is ready
add_action('upfront-core-initialized', 'uimage_initialize');

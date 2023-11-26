<?php

/**
 * Registers the element in Upfront
 */
function uyoutube_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/uyoutube.php');
	
	add_filter('upfront_l10n', array('Upfront_UyoutubeView', 'add_l10n_strings'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('uyoutube', upfront_relative_element_url('js/uyoutube', __FILE__));


	// Add element defaults to data object
	$uyoutube = new Upfront_UyoutubeView(array());
	add_action('upfront_data', array($uyoutube, 'add_js_defaults'));

	// Add the public stylesheet
	add_action('wp_enqueue_scripts', array('Upfront_UyoutubeView', 'add_styles_scripts'));
}

//Hook it when Upfront is ready
add_action('upfront-core-initialized', 'uyoutube_initialize');

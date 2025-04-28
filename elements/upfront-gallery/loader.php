<?php

/**
 * Registers the element in Upfront
 */
function ugallery_initialize () {
	// Include the backend support stuff
	$domain = 'ugallery';
	require_once (dirname(__FILE__) . '/lib/' . $domain . '.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_gallery_presets_server.php');

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('' . $domain . '', upfront_relative_element_url('js/' . $domain . '', __FILE__));


	// Add element defaults to data object
	$ugallery = new Upfront_UgalleryView(array());
	add_action('upfront_data', array($ugallery, 'add_js_defaults'));

	add_filter('upfront_l10n', array('Upfront_UgalleryView', 'add_l10n_strings'));
	add_filter('upfront-export-ugallery-object_content', array('Upfront_UgalleryView', 'export_content'), 10, 2);

	// Add the public stylesheet
	add_action('wp_enqueue_scripts', array('Upfront_' . ucwords($domain) . 'View', 'add_styles_scripts'));
}

//Hook it when Upfront is ready
add_action('upfront-core-initialized', 'ugallery_initialize');

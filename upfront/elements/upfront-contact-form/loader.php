<?php

/**
 * Registers the element in Upfront
 */
function ucontact_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/contact_form.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_contact_presets_server.php');

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('ucontact', upfront_relative_element_url('js/ucontact', __FILE__));

	// Add element defaults to data object
	$ucontact = new Upfront_UcontactView(array());
	add_action('upfront_data', array($ucontact, 'add_js_defaults'));

	// Add the public stylesheet
	add_action('wp_enqueue_scripts', array('Upfront_UcontactView', 'add_styles_scripts'));

	add_filter('upfront_l10n', array('Upfront_UcontactView', 'add_l10n_strings'));

	// Add the ajax handlers
	Ucontact_Server::serve();
}

//Hook it when Upfront is ready
add_action('upfront-core-initialized', 'ucontact_initialize');

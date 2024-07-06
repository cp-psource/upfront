<?php

/**
 * This is the entity entry point, where we inform Upfront of our existence.
 */
function upfront_login_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/upfront_login.php');

	add_filter('upfront_l10n', array('Upfront_LoginView', 'add_l10n_strings'));
	add_filter('upfront_data', array('Upfront_LoginView', 'add_data_defaults'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('upfront_login', upfront_relative_element_url('js/upfront_login', __FILE__));

	require_once (dirname(__FILE__) . '/lib/upfront_login_server.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_login_presets_server.php');
	Upfront_LoginAjax::serve();
}
// Initialize the entity when Upfront is good and ready
add_action('upfront-core-initialized', 'upfront_login_initialize');

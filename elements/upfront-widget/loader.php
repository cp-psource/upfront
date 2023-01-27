<?php

/**
 * This is the entity entry point, where we inform Upfront of our existence.
 */
function uwidget_initialize () {
	// Include the backend support stuff
	require_once (dirname(__FILE__) . '/lib/class_upfront_widget_view.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_widget.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_widget_server.php');
	require_once (dirname(__FILE__) . '/lib/class_upfront_widget_presets_server.php');

	// Add element defaults to data object
	add_action('upfront_data', array('Upfront_UwidgetView', 'add_js_defaults'));
	add_filter('upfront_l10n', array('Upfront_UwidgetView', 'add_l10n_strings'));
	add_filter('upfront_data', array('Upfront_UwidgetView', 'add_data'));
	add_filter('wp_enqueue_scripts', array('Upfront_UwidgetView', 'add_dependencies'));

	// Expose our JavaScript definitions to the Upfront API
	upfront_add_layout_editor_entity('uwidget', upfront_relative_element_url('js/uwidget', __FILE__));
}
// Initialize the entity when Upfront is good and ready
add_action('upfront-core-initialized', 'uwidget_initialize');

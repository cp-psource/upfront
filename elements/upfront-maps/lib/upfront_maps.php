<?php
class Upfront_UmapView extends Upfront_Object{

	public function get_markup(){
		$_id = $this->_get_property('element_id');
		$element_id = $_id ? "id='{$_id}'" : '';
		$raw_properties = !empty($this->_data['properties']) ? $this->_data['properties'] : array();
		$to_map = array('markers', 'map_center', 'zoom', 'style', 'controls', 'map_styles', 'draggable', 'scrollwheel', 'hide_markers', 'use_custom_map_code');

		$properties = array();
		foreach ($raw_properties as $prop) {
			if (in_array($prop['name'], $to_map)) $properties[$prop['name']] = $prop['value'];
		}
		if (!is_array($properties['controls'])) $properties['controls'] = array($properties['controls']);
		$map = 'data-map="' . esc_attr(json_encode($properties)) . '"';

		if (empty($properties)) return ''; // No info for this map, carry on.

		upfront_add_element_script('upfront_maps', array('js/upfront_maps-public.js', dirname(__FILE__)));
		upfront_add_element_style('upfront_maps', array('css/visitor.css', dirname(__FILE__)));
		
		if (Upfront_Permissions::current(Upfront_Permissions::BOOT)) {
			//wp_enqueue_style('ubutton_editor', upfront_element_url('css/upfront-button-editor.css', dirname(__FILE__)));
			upfront_add_element_style('upfront_maps_editor', array('css/upfront-map-editor.css', dirname(__FILE__)));
		}

		$msg = esc_html(self::_get_l10n('preloading_msg'));

		return "<div class='ufm-gmap-container' {$element_id} {$map}>{$msg}</div>";
	}

	public static function add_js_defaults($data){
		$data['umaps'] = array(
			'defaults' => self::default_properties(),
		 );
		return $data;
	}

	public static function default_properties(){
		return array(
			'type' => "MapModel",
			'view_class' => "UmapView",
			"class" => "c24 upfront-map_element-object",
			'has_settings' => 1,
			'id_slug' => 'upfront-map_element',

			'controls' => array(),
			'map_center' => array(-37.8180, 144.9760),
			'zoom' => 10,
			'style' => 'ROADMAP',
			'styles' => false,

			'draggable' => true,
			'scrollwheel' => false,

			'fallbacks' => array(
				'script' => self::_get_l10n('default_script'),
			)
		);
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['maps_element'])) return $strings;
		$strings['maps_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('G-Map', 'upfront'),
			'preloading_msg' => __('Hier kommt die Karte ins Spiel.', 'upfront'),
			'css' => array(
				'label' => __('Map-Container', 'upfront'),
				'info' => __('Die Ebene, die die Karte umschließt.', 'upfront'),
			),
			'menu' => array(
				'center_map' => __('Karte hier zentrieren', 'upfront'),
				'add_marker' => __('Markierung hinzufügen', 'upfront'),
				'remove_marker' => __('Markierung entfernen', 'upfront'),
				'change_icon' => __('Symbol ändern', 'upfront'),
			),
			'connectivity_warning' => __('Bitte überprüfe Deine Internetverbindung', 'upfront'),
			'instructions' => __('Bitte Adresse eingeben:', 'upfront'),
			'api_key_empty' => __('Bitte gib Deinen Google Maps API Key in das Feld ein', 'upfront'),
			'api_key_url' => admin_url('admin.php?page=upfront#api-key-gmaps'),
			'here' => __('hier', 'upfront'),
			'api_key_empty_region' => __('Bitte gib Deinen Google Maps-API-Schlüssel im ClassicPress-Adminbereich im Upfront-Menü im Untermenü Dashboard ein.', 'upfront'),
			'placeholder' => __('Straße, Stadt, Land', 'upfront'),
			'or' => __('oder', 'upfront'),
			'use_current_location' => __('Meinen aktuellen Standort verwenden', 'upfront'),
			'hold_on' => __('Bitte warte, Karte wird geladen', 'upfront'),
			'edit_this' => __('Bearbeite dies ...', 'upfront'),
			'image_url' => __('Bild URL (.png):', 'upfront'),
			'settings' => __('KMap-Einstellungen', 'upfront'),
			'general_settings' => __('Allgemeine Einstellungen', 'upfront'),
			'address' => __('Addresse:', 'upfront'),
			'label' => __('Google Map', 'upfront'),
			'location_label' => __('Map Location', 'upfront'),
			'style' => array(
				'roadmap' => __('Roadmap', 'upfront'),
				'satellite' => __('Satellite', 'upfront'),
				'hybrid' => __('Hybrid', 'upfront'),
				'terrain' => __('Terrain', 'upfront'),
			),
			'ctrl' => array(
				'pan' => __('Pan', 'upfront'),
				'zoom' => __('Zoom', 'upfront'),
				'type' => __('Map Type', 'upfront'),
				'scale' => __('Scale', 'upfront'),
				'street_view' => __('Street View', 'upfront'),
				'overview' => __('Overview Map', 'upfront'),
			),
			'zoom_level' => __('Map Zoom Level:', 'upfront'),
			'map_style' => __('Map-Stil', 'upfront'),
			'map_controls' => __('Map Kontrollen', 'upfront'),
			'draggable_map' => __('Ziehbare Karte', 'upfront'),
			'hide_markers' => __('Markierungen ausblenden', 'upfront'),
			'use_custom_map_code' => __('Benutzerdefinierten Kartencode verwenden', 'upfront'),
			'use_custom_map_code_info' => __('Von Apps wie Snazzy Maps generierter Code', 'upfront'),
			'open_map_code_panel' => __('Öffne das Kartencode-Bedienfeld', 'upfront'),
			'default_script' => __('/* Dein Code hier */', 'upfront'),
			'unable_to_geolocate' => __('Wir konnten Deinen aktuellen Standort nicht automatisch ermitteln.', 'upfront'),
			'create' => array(
				'change' => __('Klicken um zu ändern', 'upfront'),
				'js_error' => __('JS Fehler:', 'upfront'),
				'ok' => __('OK', 'upfront'),
			),
			'template' => array(
				'custom_map_code' => __('Benutzerdefinierter Kartencode', 'upfront'),
				'paste_below' => __('Füge Deinen generierten Code unten ein.', 'upfront'),
				'code_error' => __('Es gibt einen Fehler in Deinem JS-Code', 'upfront'),
				'close' => __('Schließen', 'upfront'),
				'save' => __('Speichern', 'upfront'),
			),
		);

		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}

function upfront_maps_add_context_menu ($paths) {
	$paths['maps_context_menu'] = upfront_relative_element_url('js/ContextMenu', dirname(__FILE__));
	return $paths;
}
add_filter('upfront-settings-requirement_paths', 'upfront_maps_add_context_menu');

function upfront_maps_add_maps_local_url ($data) {
	$data['upfront_maps'] = array(
		"root_url" => trailingslashit(upfront_element_url('/', dirname(__FILE__))),
		"markers" => trailingslashit(upfront_element_url('img/markers/', dirname(__FILE__))),
	);
	return $data;
}
add_filter('upfront_data', 'upfront_maps_add_maps_local_url');

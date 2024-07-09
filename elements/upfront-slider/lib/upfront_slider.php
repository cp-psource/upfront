<?php

/**
 * Object implementation for Search entity.
 * A fairly simple implementation, with applied settings.
 */
class Upfront_UsliderView extends Upfront_Object {

	public function get_markup () {
		$data = $this->properties_to_array();
		$slides = array();
		if ( isset($data['slides']) && $data['slides'] ) {
			foreach($data['slides'] as $slide){
				$slide = array_merge(self::slide_defaults(), $slide);
				$slide['breakpoint_map'] = !empty($slide['breakpoint']) ? json_encode($slide['breakpoint']) : "";
				$slides[] = $slide;
			}
		}

		if (isset($data['usingNewAppearance']) === false) {
			$data['usingNewAppearance'] = false;
		}

		if (!isset($data['preset'])) {
			$data['preset'] = 'default';
		}
		$data['properties'] = Upfront_Slider_Presets_Server::get_instance()->get_preset_properties($data['preset']);

		$data['slides'] = $slides;
		$data['rotate'] = $data['rotate'] ? true : false;
		$data['keyboardControls'] = $data['keyboardControls'] ? true : false;

		$data['dots'] = array_search($data['controls'], array('dots', 'both')) !== false;
		$data['arrows'] = array_search($data['controls'], array('arrows', 'both')) !== false;

		$data['slidesLength'] = sizeof($slides);

		$side_style = $data['properties']['primaryStyle'] === 'side';

		$data['imageWidth'] = $side_style ? floor($data['rightImageWidth'] / $data['rightWidth'] * 100) . '%': '100%';
		$data['textWidth'] =  $side_style ? floor(($data['rightWidth'] - $data['rightImageWidth']) / $data['rightWidth'] * 100) . '%' : '100%';

		$data['imageHeight'] = sizeof($slides) ? $slides[0]['cropSize']['height'] : 0;

		$data['production'] = true;
		$data['startingSlide'] = 0;

		// Overwrite properties with preset properties
		if ($data['usingNewAppearance'] !== false) {
			if (isset($data['properties']['primaryStyle'])) {
				$data['primaryStyle'] = $data['properties']['primaryStyle'];
			}
			if (isset($data['properties']['captionBackground'])) {
				$data['captionBackground'] = $data['properties']['captionBackground'];
			}
		}


		$markup = upfront_get_template('uslider', $data, dirname(dirname(__FILE__)) . '/tpl/uslider.html');

		return $markup;
	}

	public static function add_styles_scripts () {
		upfront_add_element_style('uslider_css', array('css/uslider.css', dirname(__FILE__)));
		upfront_add_element_style('uslider_settings_css', array('css/uslider_settings.css', dirname(__FILE__)));
		//wp_enqueue_style( 'uslider_css', upfront_element_url('css/uslider.css', dirname(__FILE__)), array(), "0.1" );
		//wp_enqueue_style( 'uslider_settings_css', upfront_element_url('css/uslider_settings.css', dirname(__FILE__)), array(), "0.1" );

		//wp_enqueue_script('uslider-front', upfront_element_url('js/uslider-front.js', dirname(__FILE__)), array('jquery'));
		upfront_add_element_script('uslider-front', array('js/uslider-front.js', dirname(__FILE__)));
	}

	public static function add_js_defaults($data){
		$data['uslider'] = array(
			'defaults' => self::default_properties(),
			'slideDefaults' => self::slide_defaults(),
			'template' => upfront_get_template_url('uslider', upfront_element_url('tpl/uslider.html', dirname(__FILE__)))
		);
		return $data;
	}

	private function properties_to_array(){
		$out = array();
		foreach($this->_data['properties'] as $prop){
			$out[$prop['name']] = $prop['value'];
		}
		return $out;
	}

	public static function default_properties(){
		return array(
			'id_slug' => 'uslider',
			'type' => "USliderModel",
			'view_class' => "USliderView",
			"class" => "c24 upfront-uslider",
			'has_settings' => 1,
			'preset' => 'default',

			'primaryStyle' => 'default', // notext, below, over, side, onlytext

			/* TO BE DEPRECATED, it is moved inside the slide */
			'style' => 'bottomOver', // nocaption, below, above, right, bottomOver, topOver, bottomCover, middleCover, topCover

			'controls' => 'both', // both, arrows, dots, none
			'controlsWhen' => 'always', // always, hover

			'rotate' => array('false'),
			'rotateTime' => 5,
			'keyboardControls' => array('true'),

			'transition' => 'crossfade', // crossfade, slide-left, slide-right, slide-bottom, slide-top
			'slides' => array(), // Convert to Uslider_Slides to use, and to Object to store

			'captionUseBackground' => '0',
			'captionBackground' => apply_filters('upfront_slider_caption_background', 'transparent'),

			/* TO BE DEPRECATED, it is moved inside the slide */
			'rightImageWidth' => 3,
			'rightWidth' => 6,
		);
	}


	public static function slide_defaults(){
		return array(
			'id' => 0,
			'src' => '',
			'srcFull' => '',
			'sizes' => array(),
			'size' => array('width' => 0, 'height' => 0),
			'cropSize' => array('width' => 0, 'height' => 0),
			'cropOffset' => array('top' => 0, 'left' => 0),
			'rotation' => 0,
			'url' => '',
			'urlType' => '',
			'text' => '',
			'margin' => array('left' => 0, 'top' => 0),
			'captionColor' => apply_filters('upfront_slider_caption_color', '#ffffff'),
			'captionBackground' => apply_filters('upfront_slider_caption_background', '#000000'),


			'style' => 'bottomOver', // nocaption, below, above, right, bottomOver, topOver, bottomCover, middleCover, topCover
			'rightImageWidth' => 3,
			'rightWidth' => 6,

			'breakpoint' => ''
		);
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['slider_element'])) return $strings;
		$strings['slider_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('Slider', 'upfront'),
			'css' => array(
				'images_label' => __('Bilder', 'upfront'),
				'images_info' => __('Slider-Bilder', 'upfront'),
				'captions_label' => __('Bildunterschriften', 'upfront'),
				'captions_info' => __('Slides-Bildunterschriften', 'upfront'),
				'caption_label' => __('Bildunterschriftenfeld', 'upfront'),
				'caption_info' => __('Beschriftungselement', 'upfront'),
				'img_containers_label' => __('Bildcontainer', 'upfront'),
				'img_containers_info' => __('Die Bild-Wrapper-Ebene', 'upfront'),
				'dots_wrapper_label' => __('Wrapper für Navigationspunkte', 'upfront'),
				'dots_wrapper_info' => __('Container der Navigationspunkte', 'upfront'),
				'dots_label' => __('Navigationspunkte', 'upfront'),
				'dots_info' => __('Markierungen des Navigationselements', 'upfront'),
				'dot_current_label' => __('Aktueller Navigationspunkt', 'upfront'),
				'dot_current_info' => __('Der Punkt, der die aktuelle Folie darstellt', 'upfront'),
				'prev_label' => __('Navigation Zurück', 'upfront'),
				'prev_info' => __('Vorherige Schaltfläche der Navigation', 'upfront'),
				'next_label' => __('Navigation Nächstes', 'upfront'),
				'next_info' => __('Schaltfläche Nächste der Navigation', 'upfront'),
			),
			'settings' => __('Einstellungen', 'upfront'),
			'general' => __('Allgemeine Einstellungen', 'upfront'),
			'above_img' => __('Bildunterschrift über Folie', 'upfront'),
			'below_img' => __('Bildunterschrift unter Folie', 'upfront'),
			'slider_behaviour' => __('Slider-Verhalten', 'upfront'),
			'image_caption_position' => __('Bild &amp; Beschriftungsposition:', 'upfront'),
			'slider_transition' => __('Slider-Übergang:', 'upfront'),
			'back_button' => __('Zurück', 'upfront'),
			'no_text' => __('Kein Titel', 'upfront'),
			'over_top' => __('Über Bild, oben', 'upfront'),
			'over_bottom' => __('Über Bild, unten', 'upfront'),
			'cover_top' => __('Deckt Bild, oben', 'upfront'),
			'cover_mid' => __('Deckt Bild, Mitte', 'upfront'),
			'cover_bottom' => __('Deckt Bild, unten', 'upfront'),
			'at_right' => __('Slider links, Beschriftung rechts', 'upfront'),
			'at_left' => __('Slider rechts, Beschriftung links', 'upfront'),
			'cap_position' => __('Layout der Folienbeschriftung', 'upfront'),
			'edit_img' => __('Aktuelle Folie zuschneiden', 'upfront'),
			'remove_slide' => __('Folie entfernen', 'upfront'),
			'img_link' => __('Aktuelle Folie verlinken', 'upfront'),
			'preparing_img' => __('Bilder vorbereiten', 'upfront'),
			'preparing_slides' => __('Folien vorbereiten', 'upfront'),
			'slider_styles' => __('Slider-Stile', 'upfront'),
			'notxt' => __('Keine Bildunterschrift', 'upfront'),
			'txtb' => __('Bildunterschrift unten', 'upfront'),
			'txto' => __('Bildunterschrift darüber', 'upfront'),
			'txts' => __('Beschriftung seitlich', 'upfront'),
			'caption_bg' => __('Beschriftungshintergrund', 'upfront'),
			'none' => __('Keinen', 'upfront'),
			'pick_color' => __('Farbe wählen', 'upfront'),
			'rotate_every' => __('Automatisch drehen alle', 'upfront'),
			'slide_down' => __('Slide Runter', 'upfront'),
			'slide_up' => __('Slide Hoch', 'upfront'),
			'slide_right' => __('Slide Rechts', 'upfront'),
			'slide_left' => __('Slide Links', 'upfront'),
			'crossfade' => __('Überblenden', 'upfront'),
			'slider_controls' => __('Slider Steuerelemente', 'upfront'),
			'slider_controls_style' => __('Slider Steuerelemente Stil', 'upfront'),
			'on_hover' => __('Beim Hover anzeigen', 'upfront'),
			'always' => __('Immer anzeigen', 'upfront'),
			'show_controls' => __('Steuerelemente anzeigen', 'upfront'),
			'dots' => __('Punkte', 'upfront'),
			'arrows' => __('Pfeile', 'upfront'),
			'both' => __('Beide', 'upfront'),
			'enable_arrows_slide' => __('Slide mit Pfeiltasten aktivieren?', 'upfront'),
			'ok' => __('Ok', 'upfront'),
			'slides_order' => __('Reihenfolge der Folien', 'upfront'),
			'accessibility' => __('Barrierefreiheit', 'upfront'),
			'slides' => __('Slides', 'upfront'),
			'add_slide' => __('Folie hinzufügen', 'upfront'),
			'choose_type' => __('Slider-Typ auswählen', 'upfront'),
			'can_change' => __('Dies kann später über das Einstellungsfeld geändert werden', 'upfront'),
			'img_only' => __('Nur Bild', 'upfront'),
			'default' => __('Standard', 'upfront'),
			'txt_over_img' => __('Überlagernd', 'upfront'),
			'txt_below_img' => __('Unterhalb', 'upfront'),
			'txt_on_side' => __('Seitlich', 'upfront'),
			'txt_only' => __('Nur Text/Widget', 'upfront'),
			'choose_img' => __('Wähle Bilder', 'upfront'),
			'slide_desc' => __('Folienbeschreibung', 'upfront'),
			'delete_slide_confirm' => __('Möchtest Du diese Folie wirklich löschen?', 'upfront'),
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}

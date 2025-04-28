<?php

abstract class Upfront_Presets_Server extends Upfront_Server {

    protected $isPostPartServer = false;
    protected $isThisPostServer = false;
    protected $isCommentServer = false;
    protected $isNavigationServer = false;
    public $db_key;

    protected function __construct() {
        parent::__construct();

        add_filter('upfront_l10n', array('Upfront_Presets_Server', 'add_l10n_strings'));

        $this->db_key = 'upfront_' . get_stylesheet() . '_' . $this->get_element_name() . '_presets';

        $registry = Upfront_PresetServer_Registry::get_instance();
        $registry->set($this->get_element_name(), $this);
    }

    public abstract function get_element_name();

    protected function _add_hooks () {
        if (Upfront_Permissions::current(Upfront_Permissions::BOOT)) {
            upfront_add_ajax('upfront_get_' . $this->get_element_name() . '_presets', array($this, 'get'));
        }
        if (Upfront_Permissions::current(Upfront_Permissions::SAVE)) {
            upfront_add_ajax('upfront_save_' . $this->get_element_name() . '_preset', array($this, 'save'));
            upfront_add_ajax('upfront_delete_' . $this->get_element_name() . '_preset', array($this, 'delete'));
            upfront_add_ajax('upfront_reset_' . $this->get_element_name() . '_preset', array($this, 'reset'));
        }
    }

    public function get() {
        $this->_out(new Upfront_JsonResponse_Success($this->get_presets()));
    }

	public function delete() {
		if (!isset($_POST['data'])) {
			return;
		}

		if (!Upfront_Permissions::current(Upfront_Permissions::DELETE_ELEMENT_PRESETS)) {
			$this->_reject();
		}

		$properties = stripslashes_deep($_POST['data']);
		do_action('upfront_delete_' . $this->get_element_name() . '_preset', $properties, $this->get_element_name());

		if (!has_action('upfront_delete_' . $this->get_element_name() . '_preset')) {
			$presets = $this->get_presets();

			$result = array();

			foreach ($presets as $preset) {
				if ($preset['id'] === $properties['id']) {
					continue;
				}
				$result[] = $preset;
			}

			$this->update_presets($result);
		}

		$this->_out(new Upfront_JsonResponse_Success('Deleted ' . $this->get_element_name() . ' preset.'));
	}

	public function reset() {
		if (!isset($_POST['data'])) {
			return;
		}

		if (!Upfront_Permissions::current(Upfront_Permissions::DELETE_ELEMENT_PRESETS)) {
			$this->_reject();
		}

		$properties = stripslashes_deep($_POST['data']);

		//If automatically generated default preset return false
		if(empty($properties['id'])) {
			return $this->_out(new Upfront_JsonResponse_Error("Invalid preset"));
		}

		do_action('upfront_reset_' . $this->get_element_name() . '_preset', $properties, $this->get_element_name());

		if (!has_action('upfront_reset_' . $this->get_element_name() . '_preset')) {
			$presets = $this->get_presets();
			$result = array();
			$resetpreset = array();

			foreach ($presets as $preset) {
				if ($preset['id'] === $properties['id']) {
					//Update preset properties
					$preset = $this->get_theme_preset_by_id($properties['id']);
					$resetpreset = $preset;
				}
				$result[] = $preset;
			}

			$this->update_presets($result);
		}


		$this->_out(new Upfront_JsonResponse_Success($resetpreset));
	}
	
	public function handle_post_parts($presets) {
		$new_presets = array();

		if(!empty($presets) && is_array($presets) ) {
			
			foreach($presets as $preset) {
				
				if ( ! is_array( $preset ) ) {
					continue;
				}
				
				foreach($preset as $key => $prop) {
					// Check if posts part
					if (0 === strpos($key, 'post-part-')) {
						// WARNING!!! This is added to prevent enourmos amount of slashes in preset_style
						$preset[$key] = str_replace("\\\\\\\\\\\\", "\\", $preset[$key]);
						// Do it twice just in case we have multiple slashes
						$preset[$key] = str_replace("\\\\\\\\\\\\", "\\", $preset[$key]);
						// Do it twice just in case we have multiple slashes
						$preset[$key] = str_replace("\\\\\\\\\\\\", "\\", $preset[$key]);
						// Finally clear slashes
						$preset[$key] = stripslashes_deep(stripslashes($preset[$key]));
					}
				}
				
				$new_presets[] = $preset;
			}
		}

		return $new_presets;
	}

	public function replace_new_lines($presets) {
		$new_presets = array();

		if(!empty($presets)) {
			foreach($presets as $preset) {
				if(isset($preset['preset_style']) && !empty($preset['preset_style'])) {
					
					// WARNING!!! This is added to prevent enourmos amount of slashes in preset_style
					$preset['preset_style'] = str_replace("\\\\\\\\\\", "\\", $preset['preset_style']);
					// Do it twice just in case we have multiple slashes
					$preset['preset_style'] = str_replace("\\\\\\\\\\", "\\", $preset['preset_style']);
					$preset['preset_style'] = str_replace("@n", "\n", $preset['preset_style']);
					// Replace @s with slash
					$preset['preset_style'] = str_replace("@s", "\\", $preset['preset_style']);
					$preset['preset_style'] = html_entity_decode($preset['preset_style'], ENT_NOQUOTES, "UTF-8");
				}

				$new_presets[] = $preset;
			}
		}

		return $new_presets;
	}

	/**
	 * Expand the URLs in preset style
	 *
	 * @param array $presets Presets to expand
	 *
	 * @return array Processed presets
	 */
	private function _expand_passive_relative_url ($presets) {
		if (empty($presets) || !is_array($presets)) return $presets;
		$contextless_uri = preg_replace('/^https?:/', '', get_stylesheet_directory_uri());
		foreach ($presets as $idx => $preset) {
			if (empty($preset['preset_style'])) continue;

			$preset['preset_style'] = preg_replace('/' . preg_quote(Upfront_ChildTheme::THEME_BASE_URL_MACRO, '/') . '/', $contextless_uri, $preset['preset_style']);
			$presets[$idx] = $preset;
		}

		return $presets;
	}
	
	/**
	 * @return array of saved preset ID`s
	 */
	 
	public function get_presets_ids () {
		$preset_ids = array();
		$presets = $this->get_presets();
		
		foreach ( $presets as $idx => $preset ) {
			if ( empty( $preset['id'] ) ) continue;
			$preset_ids[] = $preset['id'];
		}
		
		return $preset_ids;
	}

	/**
	 * @return array saved presets
	 */
	public function get_presets() {
		$presets = json_decode(Upfront_Cache_Utils::get_option($this->db_key, '[]'), true);

		$presets = apply_filters(
			'upfront_get_' . $this->get_element_name() . '_presets',
			$presets,
			array(
				'json' => false,
				'as_array' => true
			)
		);
        
		if ( ! is_array( $presets ) ) {
			$presets = array();
		}

		$presets = $this->replace_new_lines($presets);
		$presets = $this->handle_post_parts($presets);
		$presets = $this->_expand_passive_relative_url($presets);

		return $presets;
	}

	protected function update_presets($presets = array()) {
		// Do not need to update this in the db, if it is coming from exporter
		$isbuilder = isset($_POST['isbuilder']) ? stripslashes($_POST['isbuilder']) : false;

		if($isbuilder != 'true') {
			Upfront_Cache_Utils::update_option($this->db_key, json_encode($presets));
		}
	}

	public function save() {
		if (!isset($_POST['data'])) {
			return;
		}

		if (!Upfront_Permissions::current(Upfront_Permissions::MODIFY_ELEMENT_PRESETS)) {
			$this->_reject();
		}

		$properties = $_POST['data'];

		//Check if preset_style is defined
		if(isset($properties['preset_style'])) {
			$properties['preset_style'] = Upfront_UFC::utils()->replace_commented_style_with_variable( $properties['preset_style'] );
		}

		foreach($properties as $key => $prop) {
			if (0 === strpos($key, 'post-part-')) {
				// WARNING!!! This is added to prevent enourmos amount of slashes in preset_style
				$properties[$key] = str_replace("\\\\\\\\\\\\", "\\", $prop);
				// Do it twice just in case we have multiple slashes
				$properties[$key] = str_replace("\\\\\\\\\\\\", "\\", $prop);
				// Do it twice just in case we have multiple slashes
				$properties[$key] = str_replace("\\\\\\\\\\\\", "\\", $prop);
				// Finally clear slashes
				$properties[$key] = stripslashes_deep(stripslashes($prop));
			}
		}

		do_action('upfront_save_' . $this->get_element_name() . '_preset', $properties, $this->get_element_name());

		if (!has_action('upfront_save_' . $this->get_element_name() . '_preset')) {
			$presets = $this->get_presets();

			$result = array();

			foreach ($presets as $preset) {
				if ($preset['id'] === $properties['id']) {
					continue;
				}

				$result[] = $preset;
			}

			$result[] = $properties;

			$this->update_presets($result);
		}

		$this->_out(new Upfront_JsonResponse_Success('Saved ' . $this->get_element_name() . ' preset, yay.'));
	}

	public function get_presets_styles() {
		$presets = $this->get_presets();
		$presets = $this->_expand_passive_relative_url($presets);

		if (empty($presets)) {
			return '';
		}

		$styles = '';
		foreach ($presets as $preset) {
			if (!file_exists($this->get_style_template_path())) continue; // Don't bother if we don't have the styles

			if (isset($preset['breakpoint']) && isset($preset['breakpoint']['tablet'])) {
				$preset['tablet'] = array();
				foreach($preset['breakpoint']['tablet'] as $name=>$property) {
					$preset['tablet'][$name] = $property;
				};
			}
			if (isset($preset['breakpoint']) && isset($preset['breakpoint']['mobile'])) {
				$preset['mobile'] = array();
				foreach($preset['breakpoint']['mobile'] as $name=>$property) {
					$preset['mobile'][$name] = $property;
				};
			}

			// Handle specific case for button where button has both preset classes and element class
			if (isset($preset['id']) && isset($preset['preset_style']) && preg_match('#upfront\-button#', $preset['preset_style']) === 1) {
				$preset['preset_style'] = preg_replace('#' . $preset['id'] . ' \.upfront-button#', $preset['id'] . '.upfront-button', $preset['preset_style']);
			}
			if (isset($preset['preset_style'])) {
				$preset['preset_style'] = str_replace('\"', '"', $preset['preset_style']);
				$preset['preset_style'] = str_replace('\"', '"', $preset['preset_style']);
				$preset['preset_style'] = str_replace('\"', '"', $preset['preset_style']);
				$preset['preset_style'] = str_replace("\'", "'", $preset['preset_style']);
				$preset['preset_style'] = str_replace("\'", "'", $preset['preset_style']);
				$preset['preset_style'] = str_replace("\'", "'", $preset['preset_style']);

				if ($this->isPostPartServer) {
					$preset['preset_style'] = str_replace('#page', 'div#page .upfront-output-region-container', $preset['preset_style']);
				} else {
					$preset['preset_style'] = str_replace('#page', 'div#page .upfront-output-region-container .upfront-output-module', $preset['preset_style']);
				}

				if($this->isThisPostServer) {
					$preset['preset_style'] = str_replace('.default', '.default.upfront-this_post', $preset['preset_style']);
				}

				if($this->isCommentServer) {
					$preset['preset_style'] = str_replace('.default', '.default.upfront-comment', $preset['preset_style']);
				}
				
				if($this->isNavigationServer) {
					$preset['preset_style'] = str_replace('div.responsive_nav_toggler', '.responsive_nav_toggler', $preset['preset_style']);
				}
			}

			$args = array('properties' => $preset);
			extract($args);
			ob_start();
			include $this->get_style_template_path();
			$styles .= ob_get_clean();
		}

		$styles = stripslashes($styles);

		return $styles;
	}

	/**
	 * Get all theme presets presets data
	 *
	 * Theme presets are distributed with the theme
	 *
	 * @return mixed Array of preset hashes, or (bool)false on failure
	 */
	public function get_theme_presets() {
		$settings = Upfront_ChildTheme::get_settings();
		//Get presets distributed with the theme
		$theme_presets = is_object($settings) && $settings instanceof Upfront_Theme_Settings
			? json_decode($settings->get($this->get_element_name() . '_presets'), true)
			: false
		;

		return $theme_presets;
	}

	/**
	 * Gets a list of theme preset IDs
	 *
	 * @return mixed Array of preset IDs or (bool)false on failure
	 */
	public function get_theme_presets_names() {

		//Get presets distributed with the theme
		$theme_presets = $this->get_theme_presets();

		if(empty($theme_presets)) return false;

		$theme_preset_names = array();

		foreach($theme_presets as $preset) {
			$theme_preset_names[] = $preset['id'];
		}

		return $theme_preset_names;
	}

	/**
	 * Gets individual theme preset data by its ID
	 *
	 * @param string $preset Preset ID to use
	 *
	 * @return mixed A preset data map, or (bool)false on failure
	 */
	public function get_theme_preset_by_id($preset) {
		$theme_presets = $this->get_theme_presets();

		if(empty($theme_presets)) {
			return false;
		}

		foreach($theme_presets as $tpreset) {
			if($tpreset['id'] == $preset) {
				return $tpreset;
			}
		}

		return false;
	}

	/**
	 * Returns individual preset data by its ID
	 *
	 * @param string $preset Preset ID to use
	 *
	 * @return mixed A preset data map, or (bool)false on failure
	 */
	public function get_preset_by_id($preset_id) {
		$presets = $this->get_presets();

		foreach($presets as $preset) {
			if($preset['id'] == $preset_id) {
				return $preset;
			}
		}
	}

	/**
	 * Allow child classes to update presets if needed.
	 */
	protected function migrate_presets($presets) {
		return $presets;
	}

	public function properties_columns($array, $column) {
		$result = array();
		foreach ($array as $item) {
			if (!is_array($item)) continue; // Not an array, nothing to do here
			if (array_key_exists($column, $item)) {
				$result[] = $item[$column];
			}
		}
		return $result;
	}

	public function get_presets_javascript_server() {
		$presets = Upfront_Cache_Utils::get_option('upfront_' . get_stylesheet() . '_' . $this->get_element_name() . '_presets');
		$presets = apply_filters(
			'upfront_get_' . $this->get_element_name() . '_presets',
			$presets,
			array(
				'json' => false,
				'as_array' => true
			)
		);

		if(!is_array($presets)) {
			$presets = json_decode($presets, true);
		}

		$theme_presets = array();
		$updatedPresets = array();

		//Get presets distributed with the theme
		$theme_presets = $this->get_theme_presets_names();

		if(empty($theme_presets)) {
			return json_encode($this->migrate_presets($presets));
		}

		//Check if preset is distributed with the theme
		if (is_array($presets)) foreach($presets as $preset) {
			if(in_array($preset['id'], $theme_presets)) {
				$preset['theme_preset'] = true;
			} else {
				$preset['theme_preset'] = false;
			}
			$updatedPresets[] = $preset;
		}

		$updatedPresets = $this->replace_new_lines(
			$this->migrate_presets($updatedPresets)
		);
		$updatedPresets = $this->_expand_passive_relative_url($updatedPresets);
		$updatedPresets = $this->handle_post_parts($updatedPresets);

		$updatedPresets = json_encode($updatedPresets);

		if(empty($updatedPresets)) $updatedPresets = json_encode(array());

		return $updatedPresets;
	}

	public function get_typography_values_by_tag($tag) {
		$tag_typography = array();

		//Get breakpoints typography
		$grid = Upfront_Grid::get_grid();
		$breakpoint = $grid->get_default_breakpoint();
		$typography = $breakpoint->get_typography();

		$theme_typography_array = array();

		if(!is_null(Upfront_ChildTheme::get_instance())) {
			//We load this in case typography is empty or specific tag is empty
			$layout_properties = Upfront_ChildTheme::get_instance()->getLayoutProperties();
			$theme_typography = upfront_get_property_value('typography', array('properties'=>$layout_properties));

			//Make sure we use array not an object recursively
			foreach($theme_typography as $key => $object) {
				$theme_typography_array[$key] = get_object_vars($object);
			}
		}

		//Set child theme typography if breakpoint typography is empty
		if(empty($typography)) {
			$typography = $theme_typography_array;
		}

		if(isset($typography[$tag]) && !empty($typography[$tag])) {
			//Breakpoint typography exist
			$tag_typography = $typography[$tag];

			//If tag is A we should inherit size and line-height from P
			if($tag == "a") {
				if(isset($typography['p']['size'])) {
					$tag_typography['size'] = $typography['p']['size'];
				}
				if(isset($typography['p']['line_height'])) {
					$tag_typography['line_height'] = $typography['p']['line_height'];
				}
			}
		} else {
			//Child theme typography
			if(isset($theme_typography_array[$tag]) && !empty($theme_typography_array[$tag])) {
				$tag_typography = $theme_typography_array[$tag];
			} else {
				$tag_typography = !empty($tag_typography['p']) ? $tag_typography['p'] : false;
			}
		}

		return $tag_typography;
	}

	public function get_typography_defaults_array($defaults, $part) {
		//Make sure we use array
		if (is_object($defaults)) {
			$defaults = get_object_vars($defaults);
		}

		if (!is_array($defaults)) $defaults = array();
		$defaults = wp_parse_args($defaults, array(
			'font_face' => '',
			'weight' => '',
			'style' => '',
			'size' => '',
			'line_height' => '',
			'color' => '',
		));

		$typography = array(
			'static-'.$part.'-use-typography' => '',
			'static-'.$part.'-font-family' => $defaults['font_face'],
			'static-'.$part.'-weight' => $defaults['weight'],
			'static-'.$part.'-fontstyle' => $defaults['weight'].' '.$defaults['style'],
			'static-'.$part.'-style' => $defaults['style'],
			'static-'.$part.'-font-size' => $defaults['size'],
			'static-'.$part.'-line-height' => $defaults['line_height'],
			'static-'.$part.'-font-color' => $defaults['color'],
		);

		return $typography;
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['preset_manager'])) return $strings;
		$strings['preset_manager'] = self::_get_l10n();
		return $strings;
	}

	public static function get_preset_defaults () {
		return array();
	}

	public static function get_l10n ($key) {
		return self::_get_l10n($key);
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'select_preset' => __('Voreinstellung auswählen', 'upfront'),
			'preset' => __('Voreinstellung', 'upfront'),
			'select_preset_label' => __('Voreinstellung auswählen oder erstellen:', 'upfront'),
			'delete_label' => __('Löschen', 'upfront'),
			'add_label' => __('Hinzufügen', 'upfront'),
			'ok_label' => __('OK', 'upfront'),
			'cancel_label' => __('Abbrechen', 'upfront'),
			'apply_label' => __('Anwenden', 'upfront'),
			'not_empty_label' => __('Der Name der Voreinstellung darf nicht leer sein.', 'upfront'),
			'special_character_label' => __('Der voreingestellte Name darf nur Zahlen, Buchstaben und Leerzeichen enthalten.', 'upfront'),
			'invalid_preset_label' => __('Ungültiger voreingestellter Name. Der Name der Voreinstellung sollte mit einem Buchstaben beginnen.', 'upfront'),
			'default_preset' => __('Standard', 'upfront'),
			'add_preset_label' => __('Voreinstellung hinzufügen', 'upfront'),
			'border' => __('Border', 'upfront'),
			'none' => __('Keinen', 'upfront'),
			'solid' => __('Solid', 'upfront'),
			'dashed' => __('Dashed', 'upfront'),
			'dotted' => __('Dotted', 'upfront'),
			'width' => __('Breite', 'upfront'),
			'color' => __('Farbe', 'upfront'),
			'bg_color' => __('Hintergrundfarbe', 'upfront'),
			'edit_text' => __('Text bearbeiten', 'upfront'),
			'default_preset' => __('Standard', 'upfront'),
			'border' => __('Border', 'upfront'),
			'px' => __('px', 'upfront'),
			'type_element' => __('Typelement:', 'upfront'),
			'typeface' => __('Schrift:', 'upfront'),
			'weight_style' => __('Weight/Style:', 'upfront'),
			'size' => __('Größe:', 'upfront'),
			'line_height' => __('Zeilenhöhe:', 'upfront'),
			'text_alignment' => __('Textausrichtung', 'upfront'),
			'rounded_corners' => __('Runde Ecken', 'upfront'),
			'typography' => __('Typografie', 'upfront'),
			'animate_hover_changes' => __('Zustandsänderungen animieren', 'upfront'),
			'sec' => __('sec', 'upfront'),
			'ease' => __('ease', 'upfront'),
			'linear' => __('linear', 'upfront'),
			'ease_in' => __('ease-in', 'upfront'),
			'ease_out' => __('ease-out', 'upfront'),
			'ease_in_out' => __('ease-in-out', 'upfront'),
			'accordion' => __('Akkordeon', 'upfront'),
			'comments' => __('Kommentare', 'upfront'),
			'contact_form' => __('Kontakt Formular', 'upfront'),
			'gallery' => __('Galerie', 'upfront'),
			'image' => __('Bild', 'upfront'),
			'login' => __('Login', 'upfront'),
			'like_box' => __('Like Box', 'upfront'),
			'map' => __('Map', 'upfront'),
			'navigation' => __('Navigation', 'upfront'),
			'button' => __('Button', 'upfront'),
			'posts' => __('Posts', 'upfront'),
			'search' => __('Suche', 'upfront'),
			'slider' => __('Slider', 'upfront'),
			'social' => __('Social', 'upfront'),
			'tabs' => __('Tabs', 'upfront'),
			'page' => __('Seite', 'upfront'),
			'post' => __('Post', 'upfront'),
			'widget' => __('Widget', 'upfront'),
			'youtube' => __('YouTube', 'upfront'),
			'margin' => __('Margin', 'upfront'),
			'text' => __('Text', 'upfront'),
			'code' => __('Code', 'upfront'),
			'posts_label' => __('Post', 'upfront'),
			'reset_posts' => __('Alle zurücksetzen', 'upfront'),
			'default_label' => __('Standard', 'upfront'),
			'edit_preset_css' => __('CSS EDITOR', 'upfront'),
			'edit_preset_label' => __('Stylesheet', 'upfront'),
			'convert_style_to_preset' => __('Als Voreinstellung speichern', 'upfront'),
			'convert_preset_info' => __('Upfront 1.0 führt Voreinstellungen ein, die es Dir ermöglichen, Stile für jedes Element auf Deiner Webseite zu speichern und wiederzuverwenden. Bevor Du dieses Element bearbeiten kannst, wähle eine der folgenden Optionen:', 'upfront'),
			'select_preset_info' => __('Vorhandene Voreinstellung auswählen (<strong>empfohlen</strong>):', 'upfront'),
			'save_as_preset_button_info' => __('Oder speichere den aktuellen Stil als neue Voreinstellung:', 'upfront'),
			'preset_changed' => __('Voreinstellung geändert zu %s', 'upfront'),
			'preset_already_exist' => __('Preset %s existiert bereits, verwende einen anderen Namen!', 'upfront'),
			'preset_created' => __('Voreinstellung %s erfolgreich erstellt!', 'upfront'),
			'preset_reset' => __('Voreinstellung %s wurde zurückgesetzt!', 'upfront'),
			'default_overlay_title' => __('Bearbeiten der Standardvoreinstellung', 'upfront'),
			'default_overlay_text' => __('<p>Bitte beachte, dass dieses Element <strong>Standardvoreinstellung</strong> verwendet. Das Ändern von Voreinstellungen wirkt sich auf jedes Layout aus, in dem diese Voreinstellung verwendet wird.</p><p>Um nur diese Instanz von Element zu ändern, erstelle bitte eine neue Voreinstellung.</p>', 'upfront'),
			'default_overlay_button' => __('Standardvoreinstellung bearbeiten', 'upfront')
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}

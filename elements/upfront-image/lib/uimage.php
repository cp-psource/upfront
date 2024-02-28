<?php
/**
 * Bild-Element für Upfront
 */
class Upfront_UimageView extends Upfront_Object {

	public function get_markup () {
		$data = $this->properties_to_array();

		if (isset($data['usingNewAppearance']) === false) {
			$data['usingNewAppearance'] = false;
		}

		$data['in_editor'] = false;
		if (!isset($data['link']) || $data['link'] === false) {
			$link = array(
				'type' => $data['when_clicked'],
				'target' => isset($data['link_target']) ? $data['link_target'] : '_self',
				'url' => $data['image_link']
			);
		} else {
			$link = $data['link'];
		}

		if (!isset($data['link_target'])) $data['link_target'] = '';

		if($link['type'] == 'image'){
			upfront_add_element_style('magnific', array('/scripts/magnific-popup/magnific-popup.css', false));
			upfront_add_element_script('magnific', array('/scripts/magnific-popup/magnific-popup.min.js', false));
		}

		$data['url'] = $link['type'] == 'unlink' ? false : $link['url'];
		$data['wrapper_id'] = str_replace('image-object-', 'wrapper-', $data['element_id']);
		$data['wrapper_id'] = 'hello_up';

		if($data['stretch']) {
			$data['imgWidth'] = '100%';
			$data['stretchClass'] = ' uimage-stretch';
		}
		else {
			$data['imgWidth'] = '';
			$data['stretchClass'] = '';
		}
		
		// Alternativen Text des Bildes abrufen
		$data['alternative_text'] = get_post_meta($data['image_id'], '_wp_attachment_image_alt', true);
		$data['containerWidth'] = min($data['size']['width'], $data['element_size']['width']);

		if($data['vstretch'])
			$data['marginTop'] = 0;

		$data['gifImage'] = isset($data['gifImage']) && $data['gifImage'] ? ' uimage-gif' : '';
		$data['gifLeft'] = $data['gifImage'] && $data['position']['left'] > 0 ? (-$data['position']['left']) . 'px' : 0;
		$data['gifTop'] = (-$data['position']['top']) . 'px';

		// Die Bildunterschrift darf nicht größer als das Bild sein
		$data['captionData'] = array(
			'top' => $data['vstretch'] ? 0 : (-$data['position']['top']) . 'px',
			'left'=> $data['stretch'] ? 0 : (-$data['position']['left']) . 'px',
			'width'=> $data['stretch'] ? '100%' : $data['size']['width'] . 'px',
			'height'=> $data['vstretch'] ? '100%' : $data['size']['height'] . 'px',
			'bottom' => $data['vstretch'] ? '100%' : (round((int)$data['element_size']['height'] + (int)$data['position']['top'] - (int)$data['size']['height'])) . 'px'
		);

		if(!isset($data['preset'])) {
			$data['preset'] = 'default';
		}

		if ($data['usingNewAppearance'] === true) {
			// Bereinigen der festcodierten Bildunterschriftfarbe
			$data['image_caption'] = preg_replace('#^<span style=".+?"#', '<span ', $data['image_caption']);
		}

		$data['properties'] = Upfront_Image_Presets_Server::get_instance()->get_preset_properties($data['preset']);
		$data['cover_caption'] = $data['caption_position'] != 'below_image'; // array_search($data['caption_alignment'], array('fill', 'fill_bottom', 'fill_middle')) !== FALSE;
		$data['placeholder_class'] = !empty($data['src']) ? '' : 'uimage-placeholder';
		$data['link_target'] = $link['target'];

		if (!empty($data['src'])) $data['src'] = preg_replace('/^https?:/', '', trim($data['src']));

		// print_r($data);die;
		$markup = '<div>' . upfront_get_template('uimage', $data, dirname(dirname(__FILE__)) . '/tpl/image.html') . '</div>';

		if($link['type'] == 'image'){
			//Lightbox
			upfront_add_element_style('magnific', array('/scripts/magnific-popup/magnific-popup.css', false));
			upfront_add_element_script('magnific', array('/scripts/magnific-popup/magnific-popup.min.js', false));
			upfront_add_element_script('uimage', array('js/uimage-front.js', dirname(__FILE__)));

			$magnific_options = array(
				'type' => 'image',
				'delegate' => 'a'
			);
			$markup .= '
				<script type="text/javascript">
					if(typeof uimages == "undefined")
						uimages = {};
					uimages["' . $data['element_id'] . '"] = ' . json_encode($magnific_options) . ';
				</script>
			';
		}

		return $markup;
	}

	public function add_js_defaults($data){
		$data['uimage'] = array(
			'defaults' => self::default_properties(),
			'template' => upfront_get_template_url('uimage', upfront_element_url('tpl/image.html', dirname(__FILE__)))
		);
		return $data;
	}

	public static function default_properties(){
		return array(
			'src' => false,
			'srcFull' => false,
			'srcOriginal' => false,
			'image_title' => '',
			'alternative_text' => '',
			'include_image_caption' => false,
			'image_caption' => self::_get_l10n('image_caption'),
			'caption_position' => false,
			'caption_alignment' => false,
			'caption_trigger' => 'always_show',
			'image_status' => 'starting',
			'size' =>  array('width' => '100%', 'height' => 'auto'),
			'fullSize' => array('width' => 0, 'height' => 0),
			'position' => array('top' => 0, 'left' => 0),
			'marginTop' => 0,
			'element_size' => array('width' => '100%', 'height' => 250),
			'rotation' => 0,
			'color' => apply_filters('upfront_image_caption_color', '#ffffff'),
			'background' => apply_filters('upfront_image_caption_background', '#000000'),
			'captionBackground' => '0',
			'image_id' => 0,
			'align' => 'center',
			'stretch' => false,
			'vstretch' => false,
			'quick_swap' => false,
			'is_locked' => true,
			'gifImage' => 0,
			'placeholder_class' => '',
			'preset' => 'default',
			'display_caption' => 'showCaption',

			'type' => 'UimageModel',
			'view_class' => 'UimageView',
			'has_settings' => 1,
			'class' =>  'upfront-image',
			'id_slug' => 'image',

			'when_clicked' => false, // false | external | entry | anchor | image | lightbox
			'image_link' => '',
			'link' => false
		);
	}

	private function properties_to_array(){
		$out = array();
		foreach($this->_data['properties'] as $prop)
			$out[$prop['name']] = $prop['value'];
		return $out;
	}

	public static function add_styles_scripts () {
		//wp_enqueue_style( 'wp-color-picker' ); // Why do we need this? Especially for all users!
		upfront_add_element_style('upfront_image', array('css/uimage.css', dirname(__FILE__)));
		//wp_enqueue_style('uimage-style', upfront_element_url('css/uimage.css', dirname(__FILE__)));
		//wp_enqueue_script('wp-color-picker'); // Why do we need this? We surely don't need it at least for visitors
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['image_element'])) return $strings;
		$strings['image_element'] = self::_get_l10n();
		return $strings;
	}

	public static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' 		=> __('Bild', 'upfront'),
			'no_images' 		=> __("Keine Bilder gesendet", 'upfront'),
			'not_allowed' 		=> __("Nicht erlaubt", 'upfront'),
			'invalid_id' 		=> __('Ungültige Bild-ID', 'upfront'),
			'no_id' 			=> __('Keine Bild-ID angegeben', 'upfront'),
			'not_modifications' => __('Nicht Modifikationen', 'upfront'), // wtf?
			'edit_error' 		=> __('Beim Bearbeiten des Bildes ist ein Fehler aufgetreten', 'upfront'),
			'save_error' 		=> __('Beim Speichern des bearbeiteten Bildes ist ein Fehler aufgetreten', 'upfront'),
			'process_error' 	=> __('Bild konnte nicht verarbeitet werden.', 'upfront'),
			'image_caption' 	=> __('Meine tolle Bildbeschriftung', 'upfront'),
			'css' => array(
				'image_label' 	=> __('Bildelement', 'upfront'),
				'image_info' 	=> __('Das gesamte Bildelement', 'upfront'),
				'caption_label' => __('Beschriftungsfeld', 'upfront'),
				'caption_info' 	=> __('Beschriftungsebene', 'upfront'),
				'wrapper_label' => __('Bild-Wrapper', 'upfront'),
				'wrapper_info' 	=> __('Bildcontainer', 'upfront'),
			),
			'ctrl' => array(
				'caption_position' => __('Beschriftungsort', 'upfront'),
				'caption_display' => __('Sichtbarkeit der Beschriftung', 'upfront'),
				'caption_position_disabled' => __('Die Bildbeschriftung ist für Bilder deaktiviert, die kleiner oder schmaler als 100 Pixel sind', 'upfront'),
				'dont_show_caption' => __('Bildbeschriftung ausblenden', 'upfront'),
				'show_caption' => __('Bildbeschriftung anzeigen', 'upfront'),
				'over_top' => __('Über dem Bild, oben', 'upfront'),
				'over_bottom' => __('Über dem Bild, unten', 'upfront'),
				'cover_top' => __('Deckt Bild, oben', 'upfront'),
				'cover_middle' => __('Deckt Bild, Mitte', 'upfront'),
				'cover_bottom' => __('Deckt Bild, Unten', 'upfront'),
				'below' => __('Unter dem Bild', 'upfront'),
				'no_caption' => __('Keine Bildbeschriftung', 'upfront'),
				'edit_image' => __('Bild bearbeiten', 'upfront'),
				'image_link' => __('Bild verlinken', 'upfront'),
				'add_image' => __('Bild hinzufügen', 'upfront'),
				'more_tools' => __('Mehr Werkzeuge', 'upfront'),
				'edit_caption' => __('Bildbeschriftung bearbeiten', 'upfront'),
				'add_caption' => __('Bildbeschriftung hinzufügen', 'upfront'),
				'replace_for_edit' => __('Bild ersetzen', 'upfront'),
				'lock_image' => __('Entsperre Größenänderung von Bildern', 'upfront'),
				'unlock_image' => __('Sperre Größenänderung von Bildern', 'upfront'),
			),
			'drop_image' => __('Lege das Bild hier ab', 'upfront'),
			'external_nag' => __('Die Bildbearbeitung ist nur für Bilder geeignet, die in ClassicPress hochgeladen wurden', 'upfront'),
			'desktop_nag' => __('Die Bildausgabe ist nur im Desktop-Modus verfügbar.', 'upfront'),
			'settings' => array(
				'label' => __('Bildeinstellungen', 'upfront'),
				'alt' => __('Alternativer Text', 'upfront'),
				'caption' => __('Bildbeschriftung Einstellungen:', 'upfront'),
				'show_caption' => __('Bildbeschriftung anzeigen', 'upfront'),
				'always' => __('Immer', 'upfront'),
				'hover' => __('Bei Hover', 'upfront'),
				'caption_bg' => __('Bildbeschriftung BG', 'upfront'),
				'none' => __('Keinen', 'upfront'),
				'pick' => __('Farbe wählen', 'upfront'),
				'ok' => __('Ok', 'upfront'),
				'padding' => __('Padding Einstellungen:', 'upfront'),
				'no_padding' => __('Verwende kein Theme-Padding', 'upfront'),
				'image_style_label' => __('Bildstil', 'upfront'),
				'image_style_info' => __('Form des Bildelements:', 'upfront'),
				'content_area_colors_label' => __('Farben', 'upfront'),
				'caption_text_label' => __('Bildbeschriftung Text', 'upfront'),
				'caption_bg_label' => __('Bildbeschriftung BG', 'upfront'),
			),
			'btn' => array(
				'fit_label' => __('An Element anpassen', 'upfront'),
				'fit_info' => __('An die Maske anpassen', 'upfront'),
				'exp_label' => __('IMG 100%', 'upfront'),
				'exp_info' => __('Bild erweitern', 'upfront'),
				'save_label' => __('Ok', 'upfront'),
				'save_info' => __('Bild speichern', 'upfront'),
				'fit_element' => __('An Element anpassen', 'upfront'),
				'restore_label' => __('Bildgröße wiederherstellen', 'upfront'),
				'restore_info' => __('Bildgröße zurücksetzen', 'upfront'),
				'swap_image' => __('Bild wechseln', 'upfront'),
				'natural_size' => __('Natürliche Größe', 'upfront'),
				'fit' => __('Anpassen', 'upfront'),
				'fill' => __('Füllen', 'upfront'),
				'image_tooltip' => __('Bildsteuerung', 'upfront'),
			),
			'image_expanded' => __('Das Bild wird vollständig erweitert', 'upfront'),
			'cant_expand' => __('Das Bild kann nicht erweitert werden', 'upfront'),
			'saving' => __('Bild wird gespeichert...', 'upfront'),
			'saving_done' => __('Hier sind wir', 'upfront'),
			'sel' => array(
				'preparing' => __('Bild vorbereiten', 'upfront'),
				'upload_error' => __('Beim Hochladen der Datei ist ein Fehler aufgetreten. Bitte versuche es erneut.', 'upfront'),
			),
			'template' => array(
				'drop_files' => __('Ziehe die Dateien hier hin, um sie hochzuladen', 'upfront'),
				'select_files' => __('Datei hochladen', 'upfront'),
				'max_file_size' => sprintf(__('Maximale Upload-Dateigröße: %s', 'upfront'), upfront_max_upload_size_human()),
				'or_browse' => __('oder durchsuche Deine', 'upfront'),
				'media_gallery' => __('Medien durchsuchen', 'upfront'),
				'uploading' => __('Hochladen...', 'upfront'),
				'links_to' => __('Links zu:', 'upfront'),
				'no_link' => __('Kein Link', 'upfront'),
				'external_link' => __('Externer Link', 'upfront'),
				'post_link' => __('Link zu einem Beitrag oder einer Seite', 'upfront'),
				'larger_link' => __('Größeres Bild anzeigen', 'upfront'),
				'ok' => __('Ok', 'upfront'),
				'move_image_nag' => __('Um ein Bild in voller Breite zu erhalten, verschiebe es bitte zuerst so, dass keine anderen Elemente im Weg sind.', 'upfront'),
				'dont_show_again' => __('Diese Nachricht nicht mehr anzeigen', 'upfront'),
				'supported_video_formats' => __('Unterstützte Formate: mp4/webm', 'upfront'),
			),
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}

class Upfront_Uimage_Server extends Upfront_Server {
	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}
	private function _add_hooks() {
		if (Upfront_Permissions::current(Upfront_Permissions::BOOT)) {
			upfront_add_ajax('upfront-media-image_sizes', array($this, "get_image_sizes"));
			upfront_add_ajax('upfront-media-video_info', array($this, "get_video_info"));
			upfront_add_ajax('upfront-media-image-create-size', array($this, "create_image_size"));
		}
		if (Upfront_Permissions::current(Upfront_Permissions::SAVE) && Upfront_Permissions::current(Upfront_Permissions::LAYOUT_MODE)) {
			upfront_add_ajax('upfront-media-save-images', array($this, "save_resizing"));
		}
	}

	function create_image_size(){
		$data = stripslashes_deep($_POST);

		if(! $data['images'])
			return $this->_out(new Upfront_JsonResponse_Error(Upfront_UimageView::_get_l10n('no_images')));

		@ini_set( 'memory_limit', apply_filters( 'upfront_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		$images = array();

		foreach($data['images'] as $imageData){
			if(!$imageData['id'])
				continue;

			$image = get_post($imageData['id']);
			if( $image instanceof WP_Post && $image->post_mime_type == 'image/gif'){ //Gif are not really resized/croped to preserve animations
				$imageAttrs = wp_get_attachment_image_src( $imageData['id'], 'full' );
				$images[$imageData['id']] = $this->get_resized_gif_data($imageData, $imageAttrs);
			}
			else {
				$rotate = isset($imageData['rotate']) && is_numeric($imageData['rotate']) ? $imageData['rotate'] : false;
				$resize = isset($imageData['resize']) ? $imageData['resize'] : false;
				$crop = isset($imageData['crop']) ? $imageData['crop'] : false;

				$images[$imageData['id']] = self::resize_image($imageData);
			}
		}
		return $this->_out(new Upfront_JsonResponse_Success(array('images' => $images)));
	}

	function get_resized_gif_data($resizeData, $imageAttrs){
		return array(
			'error' => 0,
			'url' => $imageAttrs[0],
			'urlOriginal' => $imageAttrs[0],
			'full' => $resizeData['resize'],
			'crop' => array('width' => $resizeData['crop']['width'], 'height' => $resizeData['crop']['height']),
			'gif' => 1
		);
	}

	function get_image_id_by_filename($filename) {
		global $wpdb;

		// Query post meta because it contains literal filename
		$query = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value like '%%%s';", $filename);
		$image = $wpdb->get_col($query);
		if (is_array($image) && isset($image[0])) {
			return $image[0];
		}
		return null;
	}

	function get_image_sizes() {
		$data = stripslashes_deep($_POST);

		$item_id = !empty($data['item_id']) ? $data['item_id'] : false;
		if (!$item_id) $this->_out(new Upfront_JsonResponse_Error(Upfront_UimageView::_get_l10n('invalid_id')));

		$ids = json_decode($item_id);

		if (is_null($ids) || !is_array($ids)) $this->_out(new Upfront_JsonResponse_Error(Upfront_UimageView::_get_l10n('invalid_id')));

		$custom_size = isset($data['customSize']) && is_array($data['customSize']);

		// Try to find images from slider in database.
		if (function_exists('upfront_exporter_is_running') && upfront_exporter_is_running()) {
			// Convert image theme paths to image ids
			$image_ids = array();
			foreach ($ids as $id) {
				// Leave integers alone!
				if (is_numeric($id)) {
					$image_ids[] = $id;
					continue;
				}
				// Check if it really is image path
				if (!is_string($id) || strpos($id, 'images/') === false) {
					continue;
				}

				$slash = preg_quote('/', '/');
				$image_filename = preg_replace("/{$slash}?images{$slash}/", '', $id);
				$image_id = $this->get_image_id_by_filename($image_filename);
				if (!is_null($image_id)) {
					$image_ids[] = $image_id;
				}
				else {
					$full_img_path = get_stylesheet_directory() . DIRECTORY_SEPARATOR . ltrim($id, '/');
					if (file_exists($full_img_path)) {
						$image_ids[] = Upfront_ChildTheme::import_slider_image($id);
					}
				}
			}
			$ids = $image_ids;
			if (empty($ids)) {
				$this->_out(new Upfront_JsonResponse_Error(Upfront_UimageView::_get_l10n('In der lokalen ClassicPress wurden keine Bilder gefunden.')));
			}
		}

		$images = array();
		$intermediate_sizes = get_intermediate_image_sizes();
		$intermediate_sizes[] = 'full';
		foreach ($ids as $id) {
			$sizes = array();
			foreach ( $intermediate_sizes as $size ) {
				$image = wp_get_attachment_image_src( $id, $size);
				if ($image) $sizes[$size] = $image;
			}

		if($custom_size){
			$image_custom_size = self::calculate_image_resize_data($data['customSize'], array('width' => $sizes['full'][1], 'height' => $sizes['full'][2]));
			$image_custom_size['id'] = $id;
			if (!empty($data['element_id'])) $image_custom_size['element_id'] = $data['element_id'];
			$sizes['custom'] = $this->resize_image($image_custom_size);
			$sizes['custom']['editdata'] =$image_custom_size;
		}
		else
			$sizes['custom'] = $custom_size ? $data['customSize'] : array();
			if (sizeof($sizes) != 0) $images[$id] = $sizes;
		}

		if (0 === sizeof($images)) $this->_out(new Upfront_JsonResponse_Error(Upfront_UimageView::_get_l10n('no_id')));

		$result = array(
			'given' => sizeof($ids),
			'returned' => sizeof($ids),
			'images' => $images
		);

		return $this->_out(new Upfront_JsonResponse_Success($result));
	}

	public static function resize_image($imageData) {
		$rotate = isset($imageData['rotate']) && is_numeric($imageData['rotate']) ? $imageData['rotate'] : false;
		$resize = isset($imageData['resize']) ? $imageData['resize'] : false;
		$crop = isset($imageData['crop']) ? $imageData['crop'] : false;

		if (!$rotate && !$resize && !$crop) {
			return array(
				'error' => true,
				'msg' => Upfront_UimageView::_get_l10n('not_modifications')
			);
		}

		$image_path = isset($imageData['image_path']) ? $imageData['image_path'] : _load_image_to_edit_path( $imageData['id'] );
		$image_editor = wp_get_image_editor( $image_path );

		if (is_wp_error($image_editor)) {
			return array(
				'error' => true,
				'msg' 	=> Upfront_UimageView::_get_l10n('invalid_id')
			);
		}

		if ($rotate && !$image_editor->rotate(-$rotate)) return array(
			'error' => true,
			'msg' 	=> Upfront_UimageView::_get_l10n('edit_error')
		);

		$full_size = $image_editor->get_size();
		//Cropping for resizing allows to make the image bigger
		if ($resize && !$image_editor->crop(0, 0, $full_size['width'], $full_size['height'], $resize['width'], $resize['height'], false)) {
			return array(
				'error' => true,
				'msg' 	=> Upfront_UimageView::_get_l10n('edit_error')
			);
		}

		// Crop the image and ensure the crop area is within the image bounds
		$size = $image_editor->get_size();
		$crop = array(
			'top' 		=> round($crop['top']),
			'left' 		=> round($crop['left']),
			'width' 	=> round($crop['width']),
			'height' 	=> round($crop['height'])
		);

		$crop['top'] 	= max(0, $crop['top']);
		$crop['left'] 	= max(0, $crop['left']);
		$crop['width'] 	= min($crop['width'], $size['width']);
		$crop['height'] = min($crop['height'], $size['height']);

		if ($crop && !$image_editor->crop($crop['left'], $crop['top'], $crop['width'], $crop['height'])) {
			return array(
				'error' => true,
				'msg' => Upfront_UimageView::_get_l10n('edit_error')
			);
		}

		// Generate a new filename for the cropped image
		$path = $image_path;
		$path_parts = pathinfo($path);

		$filename = $path_parts['filename'] . '-' . $image_editor->get_suffix();
		if (!isset($imageData['skip_random_filename'])) {
			$filename .=  '-' . rand(1000, 9999);
		}

		$imagepath = $path_parts['dirname'] . '/' . $filename . '.' . $path_parts['extension'];

		// Set the quality of the new image
		$image_editor->set_quality(90);

		// Save the cropped image
		$saved = $image_editor->save($imagepath);

		if (is_wp_error($saved)) {
			return array(
				'error' => true,
				'msg' => 'Fehler beim Speichern des Bildes: ' . implode('; ', $saved->get_error_messages())
			);
		}

		if (is_wp_error($image_editor) || empty($imageData['id'])) {
			return array(
				'error' => true,
				'msg' => 'Fehler beim Bearbeiten des Bildes.'
			);
		}

		// Get the URLs of the original and cropped images
		$urlOriginal = wp_get_attachment_image_src($imageData['id'], 'full');
		$urlOriginal = $urlOriginal[0];
		$url  = str_replace($path_parts['basename'], $saved['file'], $urlOriginal);

		// Handle rotated versions of the full-size image if needed
		if ($rotate) {
			$fullsizename = $path_parts['filename'] . '-r' . $rotate;
			$fullsizepath = $path_parts['dirname'] . '/' . $fullsizename . '.' . $path_parts['extension'];
			if (!file_exists($fullsizepath)) {
				$full = wp_get_image_editor(_load_image_to_edit_path($imageData['id']));
				$full->rotate(-$rotate);
				$full->set_quality(90);
				$savedfull = $full->save($fullsizepath);
			}
			$urlOriginal = str_replace($path_parts['basename'], $fullsizename . '.' . $path_parts['extension'], $urlOriginal);
		}

		// Update the used image sizes metadata
		$used = get_post_meta($imageData['id'], 'upfront_used_image_sizes', true);
		$element_id = !empty($imageData['element_id']) ? $imageData['element_id'] : 0;

		// Check if the element_id exists in the metadata array before accessing it
		if (!empty($used) && is_array($used) && array_key_exists($element_id, $used)) {
			// Keep track of used elements per element ID
			$used[$element_id] = $saved;
			update_post_meta($imageData['id'], 'upfront_used_image_sizes', $used);
		}

		// Trigger post-processing hook for the image
		if (!empty($imagepath) && !empty($url)) {
			/**
			 * Image has been successfully changed. Trigger any post-processing hook.
			 *
			 * @param string $imagepath Path to the newly created image
			 * @param string $url Newly changed image URL
			 * @param array $saved Processing data
			 * @param array $used Image Metadata
			 * @param array $imageData
			 */
			do_action('upfront-media-images-image_changed', $imagepath, $url, $saved, $used, $imageData);
		}

		return array(
			'error' => false,
			'url' => $url,
			'urlOriginal' => $urlOriginal,
			'full' => $full_size,
			'crop' => $image_editor->get_size()
		);
	}

	public static function calculate_image_resize_data($custom, $full) {
		$image_factor = $full['width'] / $full['height'];
		$custom_factor =  $custom['width'] / $custom['height'];

		$pivot = $image_factor > $custom_factor ? 'height' : 'width';
		$factor = $custom[$pivot] / $full[$pivot];

		$transformations = array(
			'rotate' => 0
		);

		$resize = array(
			'width' => round($full['width'] * $factor),
			'height' => round($full['height'] * $factor)
		);
		$crop = $custom;

		$crop['left'] = $resize['width'] > $crop['width'] ? floor(($resize['width'] - $crop['width']) / 2) : 0;
		$crop['top'] = $resize['height'] > $crop['height'] ? floor(($resize['height'] - $crop['height']) / 2) : 0;

		$transformations['crop'] = $crop;
		$transformations['resize'] = $resize;

		return $transformations;
	}

	function save_resizing() {
		$data = stripslashes_deep($_POST);
		$layout = Upfront_Layout::from_entity_ids($data['layout']);
		return $this->_out(new Upfront_JsonResponse_Success($layout->get_element_data('uslider-object-1388746230599-1180')));
	}

	function get_video_info() {
		$data = stripslashes_deep($_POST);

		$video_id = !empty($data['video_id']) ? intval($data['video_id']) : false;
		if (!$video_id) $this->_out(new Upfront_JsonResponse_Error(Upfront_UimageView::_get_l10n('invalid_id')));

		$result = array(
			'url' => self::get_video_html($video_id),
		);

		return $this->_out(new Upfront_JsonResponse_Success($result));
	}

	public static function get_video_html ($video_id) {
		if (!is_numeric($video_id)) return false;
		$video_id = (int)$video_id;
		if (empty($video_id)) return false;

		$video_url = wp_get_attachment_url($video_id);
		$video_html = wp_video_shortcode( array('src' => $video_url) );
		$video_html = preg_replace('#width="\d+"#', 'width="1920"', $video_html);
		$video_html = preg_replace('#height="\d+"#', 'height="1080"', $video_html);
		$video_html = str_replace('preload="metadata"', 'preload="auto"', $video_html);
		$video_html = str_replace('controls="controls"', '', $video_html);

		return $video_html;
	}
}
Upfront_Uimage_Server::serve();

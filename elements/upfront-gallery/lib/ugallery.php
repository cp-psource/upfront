<?php

/*
	Replace the indentifiers with yours

	class: Upfront_UgalleryView
	domaing: ugallery
*/

class Upfront_UgalleryView extends Upfront_Object {

	var $image_labels = array();
	var $all_labels = array();

	public function get_markup () {
		$data = $this->properties_to_array();
		$images = array();

		if (isset($data['usingNewAppearance']) === false) {
			$data['usingNewAppearance'] = false;
		}

		// Flag for excluding stuff that is only for editor
		$data['in_editor'] = false;
		$data['even_padding'] = isset($data['even_padding']) ? $data['even_padding'] : array('false');
		$data['thumbPadding'] = isset($data['thumbPadding']) ? $data['thumbPadding'] : 15;

		if (!empty($data['images'])) {
			foreach($data['images'] as $im){
			if (!empty($im['src'])) $im['src'] = preg_replace('/^https?:/', '', trim($im['src']));
			$images[] = array_merge(self::image_defaults(), $im);
		}
	}


		// Ensure template backward compatibility
		foreach($images as $index=>$image) {
			if (isset($images[$index]['imageLink']) && $images[$index]['imageLink'] !== false) {
				$images[$index]['imageLinkType'] = $image['imageLink']['type'];
				$images[$index]['imageLinkUrl'] = $image['imageLink']['url'];
				$images[$index]['imageLinkTarget'] = $image['imageLink']['target'];
			} else {
				$images[$index]['imageLinkType'] = $image['urlType'];
				$images[$index]['imageLinkUrl'] = $image['url'];
				$images[$index]['imageLinkTarget'] = $image['linkTarget'];
			}
			
			// Retrieve image ALT
			if(isset($image['id'])) {
				$images[$index]['alt'] = get_post_meta($image['id'], '_wp_attachment_image_alt', true);
			}
		}

		$data['images'] = $images;

		$data['imagesLength'] = sizeof($images);
		$data['editing'] = false;

		$this->get_labels($data['images']);
		$data['labels'] = $this->all_labels;

		/**
		 * Remove All if we already have one
		 */
		if(($key = array_search(array('id' => 'Alle', 'text' => 'Alle'), $data['labels'],  true)) !== false) {
			unset($data['labels'][$key]);
		}

		array_unshift($data['labels'], array('id' => '0', 'text' => 'Alle'));
		$data['labels_length'] = sizeof($data['labels']);
		$data['image_labels'] = $this->image_labels;

		$data['l10n'] = self::_get_l10n('template');

		if (!isset($data['preset'])) {
			$data['preset'] = 'default';
		}

		$data['properties'] = Upfront_Gallery_Presets_Server::get_instance()->get_preset_properties($data['preset']);

		if (is_array($data['labelFilters']) && $data['labelFilters'][0] === 'true') {
			$data['labelFilters'] = 'true';
		}

		$lbTpl = upfront_get_template('ugallery', $data, dirname(dirname(__FILE__)) . '/tpl/lightbox.html');
		$markup = upfront_get_template('ugallery', $data, dirname(dirname(__FILE__)) . '/tpl/ugallery.html');

		$markup .= '
			<script type="text/javascript">
				if(typeof ugalleries == "undefined")
					ugalleries = {};

				ugalleries["' . $data['element_id'] . '"] = {
					labels: ' . json_encode($data['labels']) . ',
					labels_length: ' . json_encode($data['labels_length']) . ',
					image_labels: ' . json_encode($data['image_labels']) . ',
					grid: ' . ($data['labelFilters'] === 'true' ? 1 : 0) . ',
					useLightbox: '. ($data['linkTo'] == 'image' ? '1' : '0') . '
				};
			</script>
		';

		if( $data['linkTo'] == 'image' ){
			$magnific_options = array(
				'type' => 'image',
				'delegate' => 'a',
				'gallery' => array(
					'enabled' => 'true',
					'tCounter' => '<span class="mfp-counter">%curr% / %total%</span>'
				),
				'image' => array(
					'markup' => upfront_get_template('ugallery', $data, dirname(dirname(__FILE__)) . '/tpl/lightbox.html'),
					'titleSrc' => 'title',
					'verticalFit' => true
				)
			);
			$markup .= '
				<script type="text/javascript">
					ugalleries["' . $data['element_id'] . '"].magnific = ' . json_encode($magnific_options) . ';
				</script>
			';
		}
		else {
			$tplObject = array('markup' => $lbTpl);
			$markup .= '
				<script type="text/javascript">
					ugalleries["' . $data['element_id'] . '"].template = ' . json_encode($tplObject) . ';
				</script>
			';
		}

		return $markup;
	}

	private function get_labels($images){
		$label_keys = array_keys($this->all_labels);
		$all_labels = array();
		foreach($images as $image){
			$image_labels = '"label_0"';
			$terms = wp_get_object_terms(array($image['id']), array('media_label'));
			// Add tags from uploaded images
			if(is_array($terms)){
				foreach($terms as $label){
					$image_labels .= ', "label_' . $label->term_id . '"';
					if(array_search($label->term_id, $label_keys) === FALSE){
						$label_keys[] = $label->term_id;
						$all_labels[] = array('id' => $label->term_id, 'text' => $label->name);
					}
				}
			}
			// Add tags from layouts
			$image_tags = $image['tags'];
			if (!empty($image_tags)) {
				foreach($image['tags'] as $tag) {
					$image_labels .= ', "label_' . $tag . '"';
					if (!in_array($tag, $label_keys)) {
						$label_keys[] = $tag;
						$all_labels[] = array('id' => $tag, 'text' => $tag);
					}
				}
			}
			$this->image_labels[$image['id']] = $image_labels;
		}
		usort($all_labels, array($this, 'sort_labels'));
		$this->all_labels = $all_labels;
	}

	public function sort_labels($a, $b){
		$texta = strtolower($a['text']);
		$textb = strtolower($b['text']);
		if($textb == $texta)
			return 0;
		return ($textb < $texta) ? 1 : -1;
	}

	private function get_template_content($data){
		$data['l10n'] = self::_get_l10n('template');
		extract($data);
		ob_start();
		include dirname(dirname(__FILE__)) . '/tpl/ugallery.html';
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	private function properties_to_array(){
		$out = array();
		foreach($this->_data['properties'] as $prop){
			$out[$prop['name']] = $prop['value'];
			if(is_array($prop['value']) && $prop['name'] != 'images')
				$out[$prop['name']]['length'] = sizeof($prop['value']);
		}
		return $out;
	}

	public function add_js_defaults($data){
		$post_types = get_post_types(array('public' => true), 'objects');
		$labels = get_terms('media_label', array('hide_empty' => false));
		$labels_names = array();
		$labels_ids = array();
		foreach($labels as $label){
			if (!is_object($label)) continue;
			$labels_ids[$label->term_id] = array('id' => $label->term_id, 'text' => $label->name);
			$labels_names[$label->name] = array('id' => $label->term_id, 'text' => $label->name);
		}

		// Sanitize post type objects array
		foreach ($post_types as $ptidx => $ptype) {
			if (empty($ptype->register_meta_box_cb)) continue;
			$ptype->register_meta_box_cb = false;
			$post_types[$ptidx] = $ptype;
		}
		// Whatever we need in the post types array, I am fairly sure metabox callback is *NOT* one of those things...

		$data['ugallery'] = array(
			'defaults' => self::default_properties(),
			'imageDefaults' => self::image_defaults(),
			'template' => upfront_get_template_url('ugallery', upfront_element_url('tpl/ugallery.html', dirname(__FILE__))),
			'lightboxTpl' => upfront_get_template('lightbox', array(), dirname(dirname(__FILE__)) . '/tpl/lightbox.html'),
			'postTypes' => $post_types,
			'grids' => array(),
			'label_names' => $labels_names,
			'label_ids' => $labels_ids,
			'themeDefaults' => apply_filters('upfront_gallery_defaults', array())
		);
		return $data;
	}

	public static function image_defaults(){
		$l10n = self::_get_l10n('template');

		return array(
			'id' => 0,
			'src' => 'http//imgsrc.hubblesite.org/hu/db/images/hs-2013-12-a-small_web.jpg',
			'srcFull' => 'http//imgsrc.hubblesite.org/hu/db/images/hs-2013-12-a-small_web.jpg',
			'sizes' => array(),
			'size' => array('width' => 0, 'height' => 0),
			'cropSize' => array('width' => 0, 'height' => 0),
			'cropOffset' => array('top' => 0, 'left' => 0),
			'rotation' => 0,
			'link' => 'original',
			'url' => '',
			'title' => $l10n['image_caption'],
			'caption' => $l10n['image_description'],
			'alt' => '',
			'tags' => array(),
			'margin' => array('left' => 0, 'top' => 0),

			'imageLink' => false,

			// Deprecated properties, leave for safety
			'linkTarget' => '',
			'link' => 'original',
			'url' => '',
		);
	}

	//Defaults for properties
	public static function default_properties(){
		return array(
			'type' => 'UgalleryModel',
			'view_class' => 'UgalleryView',
			'has_settings' => 1,
			'class' => 'c24 upfront-gallery',
			'id_slug' => 'ugallery',
			'preset' => 'default',
			'status' => 'starting',
			'images' => array(), // Convert to new UgalleryImages() for using
			'elementSize' => array( 'width' => 0, 'height' => 0),
			'labelFilters' => array(), //Since checkboxes fields return an array
			'thumbProportions' => '1', // 'theme' | '1' | '0.66' | '1.33'
			'thumbWidth' => 140,
			'thumbHeight' => 140,
			'thumbWidthNumber' => 140,
			'captionType' => 'none', // 'above' | 'over' | 'none'
			'captionColor' => apply_filters('upfront_gallery_caption_color', '#ffffff'),
			'captionUseBackground' => 0,
			'captionBackground' => apply_filters('upfront_gallery_caption_background', '#000000'),
			'showCaptionOnHover' => array( 'true' ),
			'fitThumbCaptions' => false,
			'thumbCaptionsHeight' => 20,
			'linkTo' => false, // 'url' | 'image', false is special case meaning type is not selected yet
			'even_padding' => array('false'),
			'thumbPadding' => 15,
			'sidePadding' => 15,
			'showCaptionOnHover' => 0,
			'bottomPadding' => 15,
			'thumbPaddingNumber' => 15,
			'thumbSidePaddingNumber' => 15,
			'thumbBottomPaddingNumber' => 15,
			'lockPadding' => 'yes',
			'lightbox_show_close' => array('true'),
			'lightbox_show_image_count' => array('true'),
			'lightbox_click_out_close' => array('true'),
			'lightbox_active_area_bg' => 'rgba(255,255,255,1)',
			'lightbox_overlay_bg' => 'rgba(0,0,0,0.2)',
			'styles' => ''
		);
	}

	public static function add_styles_scripts () {
		//wp_enqueue_style('ugallery-style', upfront_element_url('css/ugallery.css', dirname(__FILE__)));


		//Lightbox
		//wp_enqueue_style('magnific');
		upfront_add_element_style('magnific', array('/scripts/magnific-popup/magnific-popup.css', false));

		// Place them under the magnific styles so that UF can override magnific
		upfront_add_element_style('upfront_gallery', array('css/ugallery.css', dirname(__FILE__)));
		if (is_user_logged_in()) {
			upfront_add_element_style('ugallery-style-editor', array('css/ugallery-editor.css', dirname(__FILE__)));
		}

		//wp_enqueue_script('magnific');
		upfront_add_element_script('magnific', array('/scripts/magnific-popup/magnific-popup.min.js', false));


		upfront_add_element_script('jquery-shuffle', array('js/jquery.shuffle.js', dirname(__FILE__)));

		//Front script
		upfront_add_element_script('ugallery', array('js/ugallery-front.js', dirname(__FILE__)));

	}

		public static function add_l10n_strings ($strings) {
		if (!empty($strings['gallery_element'])) return $strings;
		$strings['gallery_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('Galerie', 'upfront'),
			'css' => array(
				'container_label' => __('Galerie-Container', 'upfront'),
				'container_info' => __('Die ganze Galerie', 'upfront'),
				'elements_label' => __('Galerieelemente', 'upfront'),
				'elements_info' => __('Der Container jedes Galerieelements.', 'upfront'),
				'images_label' => __('Galeriebilder', 'upfront'),
				'images_info' => __('Jedes Bild in der Galerie.', 'upfront'),
				'captions_label' => __('Bildunterschriften', 'upfront'),
				'captions_info' => __('Jede Bildunterschrift der Galerie. Untertitel sind möglicherweise nicht verfügbar, wenn sie über die Optionen deaktiviert wurden.', 'upfront'),
				'lblcnt_label' => __('Etikettenbehälter', 'upfront'),
				'lblcnt_info' => __('Der Wrapper der Bildlabels.', 'upfront'),
				'labels_label' => __('Labels', 'upfront'),
				'labels_info' => __('Labels zum Filtern von Galerieelementen.', 'upfront'),
				'lightbox_close' => __('Schließen-Schaltfläche', 'upfront'),
				'lightbox_content_wrapper' => __('Inhaltswrapper', 'upfront'),
				'lightbox_content_wrapper_info' => __('Container, der Bild und Beschriftung umschließt.', 'upfront'),
				'lightbox_image_wrapper' => __('Bild-Wrapper', 'upfront'),
				'lightbox_caption_wrapper' => __('Untertitel-Wrapper', 'upfront'),
				'lightbox_caption' => __('Bildbeschriftung', 'upfront'),
				'lightbox_arrow_left' => __('Pfeil links', 'upfront'),
				'lightbox_arrow_right' => __('Pfeil rechts', 'upfront'),
				'lightbox_image_count' => __('Bildzähler', 'upfront'),
			),
			'ctrl' => array(
				'show_image' => __('Ansehen lightbox', 'upfront'),
				'edit_image' => __('Vorschaubild zuschneiden', 'upfront'),
				'rm_image' => __('Miniaturbild löschen', 'upfront'),
				'image_link' => __('Link-Miniaturansicht', 'upfront'),
				'edit_labels' => __('Etiketten bearbeiten', 'upfront'),
				'thumbnail_options' => __('Thumbnail-Optionen', 'upfront')
			),
			'desc_update_success' => __('Die Bildbeschreibung wurde erfolgreich aktualisiert.', 'upfront'),
			'loading' => __('Wird geladen...', 'upfront'),
			'personalize' => __('Klicke hier um diese Galerie zu personalisieren', 'upfront'),
			'no_labels' => __('Dieses Bild hat keine Labels', 'upfront'),
			'preparing' => __('Bilder vorbereiten', 'upfront'),
			'not_all_added' => __('Es konnten nicht alle Bilder hinzugefügt werden.', 'upfront'),
			'thumbnail_clicked' => __('Wenn auf ein Galerie-Thumbnail geklickt wird', 'upfront'),
			'show_larger' => __('größeres Bild anzeigen', 'upfront'),
			'go_to_linked' => __('zur verlinkten Seite gehen', 'upfront'),
			'regenerating' => __('Bilder regenerieren...', 'upfront'),
			'regenerating_done' => __('Wow, die sind cool!', 'upfront'),
			'settings' => __('Einstellungen', 'upfront'),
			'toggle_dnd' => __('Schalte die Drag and Drop-Sortierung von Bildern um', 'upfront'),
			'panel' => array(
				'sort' => __('Etikettensortierung aktivieren', 'upfront'),
				'even_padding' => __('Gleichmäßige Polsterung'),
				'show_caption' => __('Untertitel anzeigen', 'upfront'),
				'never' => __('niemals', 'upfront'),
				'hover' => __('Bei hover', 'upfront'),
				'always' => __('Immer', 'upfront'),
				'caption_location' => __('Beschriftungsort', 'upfront'),
				'caption_style' => __('Beschriftungsstil', 'upfront'),
				'caption_height' => __('Beschriftungshöhe', 'upfront'),
				'none' => __('Keine', 'upfront'),
				'over' => __('Über Bild', 'upfront'),
				'under' => __('Unter Bild', 'upfront'),
				'showCaptionOnHover' => __('Bildunterschrift bei Hover', 'upfront'),
				'caption_bg' => __('Beschriftungshintergrund:', 'upfront'),
				'ok' => __('Ok', 'upfront'),
				'auto' => __('Auto', 'upfront'),
				'fixed' => __('Fixiert', 'upfront'),
				'adds_sortable' => __('Fügt eine sortierbare Schnittstelle basierend auf den den Bildern gegebenen Beschriftungen hinzu.', 'upfront'),
				'fit_thumb_captions' => __('Thumbnail-Beschriftungen anpassen.', 'upfront'),
				'thumb_captions_height' => __('Höhe der Beschriftungen (in px).', 'upfront'),
				'content_area_label' => __('Farben des Inhaltsbereichs', 'upfront'),
				'caption_text_label' => __('Beschriftungstext', 'upfront'),
				'caption_bg_label' => __('Bildunterschrift BG', 'upfront'),
				'caption_show' => __('Untertitel anzeigen', 'upfront'),
			),
			'thumb' => array(
				'ratio' => __('Miniaturbild-Formverhältnis:', 'upfront'),
				'theme' => __('Theme', 'upfront'),
				'size' => __('Größe der Miniaturansichten', 'upfront'),
				'thumb_settings' => __('Miniaturbild Einstellungen', 'upfront'),
				'padding' => __('Miniaturbild-Padding', 'upfront'),
				'spacing' => __('Abstand der Miniaturansichten', 'upfront'),
				'side_spacing' => __('Seitenabstand:', 'upfront'),
				'bottom_spacing' => __('Unterer Abstand:', 'upfront')
			),
			'template' => array(
				'add_more' => __('Füge mehr hinzu', 'upfront'),
				'add_new' => __('Füge der Galerie neue Bilder hinzu', 'upfront'),
				'no_images' => __('Keine Bilder in dieser Galerie', 'upfront'),
				'add_img' => __('Füge Bilder hinzu', 'upfront'),
				'add_images' => __('Bilder zur Galerie hinzufügen', 'upfront'),
				'drop_images' => __('Lege hier Bilder ab', 'upfront'),
				'select_images' => __('Bilder auswählen', 'upfront'),
				'max_upload_size' => sprintf(__('Maximale Upload-Dateigröße: %s', 'upfront'), upfront_max_upload_size_human()),
				'or_browse' => __('oder durchsuche Deine', 'upfront'),
				'media_gallery' => __('Medien Galerie', 'upfront'),
				'uploading' => __('Hochladen...', 'upfront'),
				'like' => __('Ich mag es', 'upfront'),
				'upload_different' => __('Lade ein anderes Bild hoch', 'upfront'),
				'upload' => __('Upload', 'upfront'),
				'edit_details' => __('Bilddetails bearbeiten', 'upfront'),
				'title' => __('Titel', 'upfront'),
				'caption' => __('Bildbeschriftung', 'upfront'),
				'image_caption' => __('<p>Bildbeschreibung</p>', 'upfront'),
				'image_description' => __('Bildbeschreibung', 'upfront'),
				'alt' => __('Alternativer Text', 'upfront'),
				'ok' => __('Ok', 'upfront'),
				'labels' => __('Labels', 'upfront'),
				'wtf' => __('Ein Etikett', 'upfront'),
				'add_new_label' => __('Füge ein neues Etikett hinzu', 'upfront'),
				'label_sorting_nag' => __('Aktiviere Label-Sortierung in den Einstellungen, um Galerie-Labels anzuzeigen.', 'upfront'),
				'add_label' => __('Hinzufügen', 'upfront'),
				'image_labels' => __('Bildetiketten', 'upfront'),
				'create_label' => __('Neues Etikett erstellen', 'upfront'),
				'type_label' => __('Eingabe um ein Etikett zu erstellen', 'upfront'),
				'pick_label' => __('Eingabe um das Etikett auszuwählen', 'upfront'),
			),
			'lightbox' => array(
				'title' => __('Galerie-Lightbox', 'upfront'),
				'edit_css' => __('Lightbox-CSS bearbeiten', 'upfront'),
				'show_image_count' => __('Bildanzahl anzeigen', 'upfront'),
				'active_area_bg' => __('Aktiver Bereich BG', 'upfront'),
				'overlay_bg' => __('Overlay BG', 'upfront'),
			),
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}

	public static function export_content ($export, $object) {
		$images = upfront_get_property_value('images', $object);
		if (!empty($images)) foreach ($images as $img) {
			if (empty($img['src']) || (empty($img['title']) || empty($img['caption']))) continue;
			$text = array();
			if (!empty($img['title'])) $text[] = $img['title'];
			if (!empty($img['caption'])) $text[] = $img['caption'];
			$export .= $img['src'] . ': ' . join(', ', $text) . "\n";
		}
		return $export;
	}
}

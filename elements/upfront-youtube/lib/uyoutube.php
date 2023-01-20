<?php
/**
 * YouTube element for Upfront
 */
class Upfront_UyoutubeView extends Upfront_Object {
	public static function default_properties(){
		return array(
			'type' => 'UyoutubeModel',
			'view_class' => 'UyoutubeView',
			'has_settings' => 1,
			'class' =>  'upfront-youtube',
			'id_slug' => 'uyoutube',
			'videoType' => false,
			'display_style' => 'gallery',
			'multiple_source' => 'user_channel',
			'multiple_source_id' => '',
			'multiple_videos' => false,
			'multiple_count' => 6,
			'multiple_description_length' => 100,
			'multiple_show_description' => array('multiple_show_description'),
			'multiple_title_length' => 100,
			'multiple_show_title' => array('multiple_show_title'),
			'title' => '',
			'full_title' => '',
			'show_title' => array('show_title'),
			'title_length' => 100,
			'description' => '',
			'full_description' => '',
			'show_description' => array('show_description'),
			'description_length' => 100,
			'thumbWidth' => 200,
			'thumbHeight' => 111,
			'thumbOffset' => 8,
			'single_video_url' => '',
			'single_video_id' => '',
			'player_width' => 0,
			'player_height' => 0,
			'youtube_status' => 'starting',
			'loop' => false,
			'autoplay' => false
		);
	}

	function __construct($data) {
			$data['properties'] = $this->merge_default_properties($data);
			parent::__construct($data);
	}

	protected function merge_default_properties($data){
			$flat = array();
			if(!isset($data['properties']))
					return $flat;

			foreach($data['properties'] as $prop) {
				if (isset($prop['value']) === false) continue;
				$flat[$prop['name']] = $prop['value'];
			}

			$flat = array_merge(self::default_properties(), $flat);

			$properties = array();
			foreach($flat as $name => $value)
					$properties[] = array('name' => $name, 'value' => $value);

			return $properties;
	}

	public function get_markup () {
	// This data is passed on to the template to precompile template
			$data = $this->properties_to_array();

			$data['wrapper_id'] = str_replace('youtube-object-', 'wrapper-', $data['element_id']);
			$video_id = $data['multiple_videos'][0]['id'];
			$data['loop_string'] = $data['loop'] ? "&loop=1&playlist=$video_id" : '';
			$data['autoplay_string'] = $data['autoplay'] ? "&autoplay=1" : '';

			$markup = upfront_get_template('uyoutube', $data, dirname(dirname(__FILE__)) . '/tpl/youtube.html');

			// upfront_add_element_style('upfront_youtube', array('css/uyoutube.css', dirname(__FILE__)));
			upfront_add_element_script('upfront_youtube', array('js/uyoutube-front.js', dirname(__FILE__)));

			return $markup;
	}

	public function add_js_defaults($data){
			$data['uyoutube'] = array(
					'defaults' => self::default_properties(),
					'template' => upfront_get_template_url('uyoutube', upfront_element_url('tpl/youtube.html', dirname(__FILE__)))
			);
			return $data;
	}

	private function properties_to_array(){
			$out = array();
			foreach($this->_data['properties'] as $prop)
					$out[$prop['name']] = $prop['value'];
			return $out;
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['youtube_element'])) return $strings;
		$strings['youtube_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('YouTube', 'upfront'),
			'enter_url' => __('Gib die YouTube-Video-URL ein', 'upfront'),
			'url_placeholder' => __('YouTube URL', 'upfront'),
			'submit_button' => __('Go', 'upfront'),
			'element_settings' => __('YouTube Einstellungen', 'upfront'),
			'apperance_title' => __('DARSTELLUNG', 'upfront'),
			'title_limit' => __('Videotitel beschränken auf', 'upfront'),
			'characters_label' => __('Zeichen.', 'upfront'),
			'display_style' => __('Anzeigestil:', 'upfront'),
			'gallery_label' => __('Galerie', 'upfront'),
			'list_label' => __('Liste', 'upfront'),
			'first_to_thumbnails' => __('Erstes Video zu Miniaturansichten hinzufügen', 'upfront'),
			'autoplay' => __('Video beim Laden der Seite abspielen', 'upfront'),
			'loop' => __('Loop', 'upfront'),
			'playback' => __('Playback', 'upfront'),
			'thumbnail_size' => __('Thumbnail Größe', 'upfront'),
			'thumbnail_size_info' => __('Schieben um die Größe der Miniaturansichten zu ändern.', 'upfront'),
			'videos_title' => __('VIDEO', 'upfront'),
			'default_video' => __('Video 1 URL', 'upfront'),
			'video_placeholder' => __('YouTube Video URL', 'upfront'),
			'add_video' => __('Füge ein weiteres Video hinzu', 'upfront'),
			'settings' => __('Einstellungen', 'upfront'),
			'general_settings' => __('Allgemeine Einstellungen', 'upfront'),
			'validMessage' => __('Bitte gib eine gültige YouTube-Video-URL ein', 'upfront'),
			'template' => array(
				'video_label' => __('Videos', 'upfront'),
				'url_label' => __('URL', 'upfront'),
				'url_placeholder' => __('YouTube Video URL', 'upfront'),
			),
			'css' => array(
				'global_wrapper_label' => __('Globaler Container', 'upfront'),
				'global_wrapper_info' => __('Die Ebene, die den gesamten Inhalt des Elements enthält.', 'upfront'),
				'thumbnails_wrapper_label' => __('Thumbnails-Container', 'upfront'),
				'thumbnails_wrapper_info' => __('Die Ebene, die Miniaturansichten enthält.', 'upfront'),
				'thumbnail_label' => __('Thumbnail', 'upfront'),
				'thumbnail_info' => __('Die Ebene, die Miniaturbild und Titel enthält.', 'upfront'),
			),

			'edit_text' => __('Edit Text', 'upfront'),
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}


	public static function add_styles_scripts() {
		upfront_add_element_style('uyoutube-style', array('css/uyoutube.css', dirname(__FILE__)));
		//wp_enqueue_style('uyoutube-style', upfront_element_url('css/uyoutube.css', dirname(__FILE__)));
	}
}

class Upfront_Uyoutube_Server extends Upfront_Server {

	public static function serve () {
			$me = new self;
			$me->_add_hooks();
	}

	private function _add_hooks() {
			add_action('wp_ajax_upfront_youtube_single', array($this, "get_single_video_data"));
			add_action('wp_ajax_upfront_youtube_channel', array($this, "get_channel_data"));
			add_action('wp_ajax_upfront_youtube_playlist', array($this, "get_playlist_data"));
	}

	function get_single_video_data() {
		$data = stripslashes_deep($_POST);

		if(! $data['data']['video_id'])
			return $this->_out(new Upfront_JsonResponse_Error("No video id sent"));

		$gdata_video_url = sprintf(
			'http://www.youtube.com/oembed?url=%s&format=json',
			rawurlencode($data['data']['video_id'])
		);
		try {
			$response = wp_remote_get($gdata_video_url);

			if (is_wp_error($response) && $this->yt_requests !== 3) {
				// Try again
				if (isset($this->yt_requests) === false) {
					$this->yt_requests = 1;
				} else {
					$this->yt_requests++;
				}
				$this->get_single_video_data();
				return;
			} else if (is_wp_error($response)) {
				return $this->_out(new Upfront_JsonResponse_Error("something fucked up " . json_encode($response)));
			}
			$response_json = json_decode($response['body'], true);
			$data = array(
				'title' => $response_json['title'],
				'thumbnail_url' => $response_json['thumbnail_url']
			);
			return $this->_out(new Upfront_JsonResponse_Success(array('video' => $data)));
		} catch (Exception $e) {
			return $this->_out(new Upfront_JsonResponse_Error($e->getMessage()));
		}
	}

	function get_channel_data() {
		$data = stripslashes_deep($_POST);

		if(! $data['data']['channel'])
			return $this->_out(new Upfront_JsonResponse_Error("No video id sent"));
		$gdata_channel_url = sprintf(
			'https://gdata.youtube.com/feeds/users/%s/uploads?alt=json',
			$data['data']['channel']
		);
		try {
			$response = wp_remote_get($gdata_channel_url);
			if ($response instanceof WP_Error) {
				var_dump($response);die;
			}
			//TODO check errors
			$response_json = json_decode($response['body'], true);
			$data = $this->getVideosFromChannelData($response_json['feed']['entry']);
			return $this->_out(new Upfront_JsonResponse_Success(array('videos' => $data)));
		} catch (Exception $e) {
			return $this->_out(new Upfront_JsonResponse_Error($e->getMessage()));
		}
	}

	function get_playlist_data() {
		$data = stripslashes_deep($_POST);

		if(! $data['data']['playlist'])
			return $this->_out(new Upfront_JsonResponse_Error("No playlist id sent"));
		$gdata_playlist_url = sprintf(
			'https://gdata.youtube.com/feeds/api/playlists/%s?alt=json',
			$data['data']['playlist']
		);
		try {
			$response = wp_remote_get($gdata_playlist_url);
			if ($response instanceof WP_Error) {
				var_dump($response);die;
			}
			if ($response['response']['code'] === 404) {
				echo 'playlist not found'; die;
			}
			//TODO check errors
			$response_json = json_decode($response['body'], true);
			$data = $this->getVideosFromChannelData($response_json['feed']['entry']);
			return $this->_out(new Upfront_JsonResponse_Success(array('videos' => $data)));
		} catch (Exception $e) {
			return $this->_out(new Upfront_JsonResponse_Error($e->getMessage()));
		}
	}

	private function getVideosFromChannelData($data) {
		$videos = array();
		if (!empty($data) && is_array($data)) foreach ($data as $video) {
			$query = array();
			parse_str(parse_url($video['link'][0]['href'], PHP_URL_QUERY), $query);
			$description = substr($video['media$group']['media$description']['$t'], 0, 100);
			$description = empty($description) ? 'This video has no description.' : $description;
			$videos[] = array(
				'original_description' => $description,
				'description' => $description,
				'title' => $video['title']['$t'],
				'thumbnail' => $video['media$group']['media$thumbnail'][0]['url'],
				'id' => $query['v']
			);
		}

		return $videos;
	}
}
Upfront_Uyoutube_Server::serve();

function upfront_youtube_add_youtube_local_url ($data) {
	$data['upfront_youtube'] = array(
		"root_url" => trailingslashit(upfront_element_url('/', dirname(__FILE__)))
	);
	return $data;
}
add_filter('upfront_data', 'upfront_youtube_add_youtube_local_url');

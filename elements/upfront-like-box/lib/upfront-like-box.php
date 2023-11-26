<?php

class Upfront_LikeBoxView extends Upfront_Object {

	public function get_markup () {
		$element_size = $this->_get_property('element_size');
		$url = $this->_get_property('facebook_url');

		$hide_cover = ($this->_get_property('hide_cover') == "yes" ||
							is_array($this->_get_property('hide_cover'))) ? 'true' : 'false';
		$show_friends = ($this->_get_property('show_friends') == "yes" ||
							is_array($this->_get_property('show_friends'))) ? 'true' : 'false';
		$small_header = ($this->_get_property('small_header') == "yes" ||
							is_array($this->_get_property('small_header'))) ? 'true' : 'false';
		$show_posts = ($this->_get_property('show_posts') == "yes" ||
							is_array($this->_get_property('show_posts'))) ? 'true' : 'false';

		$global_settings = Upfront_SocialMedia_Setting::get_globals();

		if($url=='' && $global_settings){
			$services = $global_settings['services'];
			$url = false;

			foreach($services as $s){
				if($s->id == 'facebook')
					$url = $s->url;
			}

			if(!$url)
				return $this->wrap(self::_get_l10n('url_nag'));
		}
		if($url) {
			$parts = parse_url($url);
			$path = explode('/', trim($parts['path'], '/'));
			$fbname = end($path);

			$wide = intval($element_size['width'])-30;

			if($wide > 500)
				$wide=500;


			/*if($wide%53 > 0)
				$wide = intval($wide/53)*53+22;
			else
				$wide = $element_size['width'];
			*/

			return "<iframe src='//www.facebook.com/v2.5/plugins/page.php?adapt_container_width=true&amp;container_width={$wide}&amp;width={$wide}&amp;height=".($element_size['height']-30)."&amp;hide_cover={$hide_cover}&amp;href=https%3A%2F%2Fwww.facebook.com%2F{$fbname}&amp;show_facepile={$show_friends}&amp;show_posts={$show_posts}&amp;small_header={$small_header}' scrolling='no' frameborder='0' style='border:none; display:block; overflow:hidden; margin: auto; width:{$wide}px; height:".($element_size['height']-30)."px;' allowTransparency='true'></iframe>";
			/*return $this->wrap(
				"<iframe src='//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2F{$fbname}&amp;width={$wide}&amp;height={$element_size['height']}&amp;show_faces=true&amp;colorscheme=light&amp;stream=false&amp;show_border=true&amp;header=false' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:{$wide}px; height:{$element_size['height']}px;' allowTransparency='true'></iframe>"
			);*/
		}
		else{
			return $this->wrap(self::_get_l10n('url_nag'));
		}
	}

	protected function wrap($content){
		$element_id = $this->_get_property('element_id');
		$element_id = $element_id ? "id='{$element_id}'" : '';
		return "<div class=' upfront-like-box ' {$element_id}>" . $content . "</div>";

	}

	// Inject style dependencies
	public static function add_public_style () {
		upfront_add_element_style('upfront-like-box', array('css/upfront-like-box-style.css', dirname(__FILE__)));
	}
	public static function add_js_defaults($data){
		$data['ulikebox'] = array(
			'defaults' => self::default_properties(),
		);
		return $data;
	}

	public static function default_properties(){
		return array(
			'id_slug' => 'Like-box-object',
			'type' => "LikeBox",
			'view_class' => "LikeBoxView",
			"class" => "c24 upfront-like-box",
			'has_settings' => 1,
			'hide_cover' =>array('no'),
			'show_friends' =>array('yes'),
			'small_header' =>array('no'),
			'show_posts' =>array('yes'),
			'element_size' => array(
				'width' => 278,
				'height' => 270
			)
		);
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['like_box_element'])) return $strings;
		$strings['like_box_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('Like Box', 'upfront'),
			'url_nag' => __('Du musst eine Facebook-URL in Deinen globalen sozialen Einstellungen festlegen.', 'upfront'),
			'container_label' => __('Container', 'upfront'),
			'facebook_account' => __('Facebook Account', 'upfront'),
			'container_info' => __('Facebook-Box-Wrapper-Schicht.', 'upfront'),
			'placeholder_guide' => __('Gib die URL Deiner Facebook-Seite ein:', 'upfront'),
			'placeholder' => __('facebook.com/yourPageName', 'upfront'),
			'ok' => __('Ok', 'upfront'),
			'you_need_to_set_url' => __('Du musst eine Facebook-URL festlegen in Deinen', 'upfront'),
			'global_social_settings' => __('globalen sozialen Einstellungen', 'upfront'),
			'opts' => array(
				'style_label' => __('Layout-Stil', 'upfront'),
				'style_title' => __('Layoutstil-Einstellungen', 'upfront'),
				'page_url' => __('Deine Facebook-Seiten-URL', 'upfront'),
				'url_sample' => __('https://www.facebook.com/YourPage', 'upfront'),
				'back_to' => __('Zurück zu Deinem', 'upfront'),
				'global_settings' => __('globalen Einstellungen', 'upfront'),
				'show_friends' => __('Zeige Gesichter von Freunden', 'upfront'),
				'small_header' => __('Verwende kleine Kopfzeile', 'upfront'),
				'hide_cover' => __('Titelbild ausblenden', 'upfront'),
				'show_posts' => __('Seitenbeiträge anzeigen', 'upfront'),
			),
			'general_settings' => __('Allgemeine Einstellungen', 'upfront'),
			'settings' => __('LikeBox-Einstellungen', 'upfront'),
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}


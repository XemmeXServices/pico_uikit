<?php
/**
 * Plugin to make proper use of UI KIT by YooTheme
 *
 * @author Daniel James
 * @link https://github.com/khanduras/pico_uikit
 * @license http://opensource.org/licenses/MIT
 */
class _puik {	
	##
	# VARS
	##
	private $pages;
	private $pages_urls;
	private $current_url;
	private $base_url;
	private $hide_list;
	private $settings = array();

	
	##
	# HOOKS
	##	

	public function config_loaded(&$settings)
	{
		$this->settings = $settings;
		$this->base_url = $settings['base_url'];
				
		if (!isset($this->settings['puik']['hide_pages'])) { $this->settings['puik']['hide_pages'] = ''; }
		$this->hide_list = array_map('trim', explode(',', $this->settings['puik']['hide_pages']));
		// default id
		if (!isset($this->settings['puik']['id'])) { $this->settings['puik']['id'] = 'uk-navbar-nav'; }
		
		// default classes
		if (!isset($this->settings['puik']['class'])) { $this->settings['puik']['class'] = 'uk-navbar-nav'; }
		if (!isset($this->settings['puik']['class_li'])) { $this->settings['puik']['class_li'] = 'li-item'; }
		if (!isset($this->settings['puik']['class_a'])) { $this->settings['puik']['class_a'] = 'a-item'; }
		
		//default paramiters
		if (!isset($this->settings['puik']['width'])) { $this->settings['puik']['width'] = 'fluid'; }
		if (!isset($this->settings['puik']['style'])) { $this->settings['puik']['style'] = 'gradient'; }
		if (!isset($this->settings['puik']['global_navbar_sticky'])) { $this->settings['puik']['global_navbar_sticky'] = '{top:0}'; }
		if (!isset($this->settings['puik']['global_sidebar'])) { $this->settings['puik']['global_sidebar'] = 'Left'; }
		if (!isset($this->settings['puik']['global_sidebar_source'])) { $this->settings['puik']['global_sidebar_source'] = 'layout/main_sidebar.html'; }
		if (!isset($this->settings['puik']['global_navbar_source'])) { $this->settings['puik']['global_navbar_source'] = 'layout/main_navbar.html'; }
		if (!isset($this->settings['puik']['global_footer_source'])) { $this->settings['puik']['global_footer_source'] = 'layout/main_footer.html'; }
		if (!isset($this->settings['puik']['global_content_source'])) { $this->settings['puik']['global_content_source'] = 'layout/main_content.html'; }
		
	}
	
	public function before_render(&$twig_vars, &$twig)
	{
		$twig_vars['puik']['navbar'] = $this->output($this->pages);
		$twig_vars['puik']['width'] = $this->settings['puik']['width'];
		$twig_vars['puik']['style'] = $this->settings['puik']['style'];
		$twig_vars['puik']['global_navbar_sticky'] = $this->settings['puik']['global_navbar_sticky'];
		$twig_vars['puik']['global_sidebar'] = $this->settings['puik']['global_sidebar'];
		$twig_vars['puik']['global_sidebar_source'] = $this->settings['puik']['global_sidebar_source'];
		$twig_vars['puik']['global_navbar_source'] = $this->settings['puik']['global_navbar_source'];
		$twig_vars['puik']['global_footer_source'] = $this->settings['puik']['global_footer_source'];
		$twig_vars['puik']['global_content_source'] = $this->settings['puik']['global_content_source'];
	}
	
	public function before_read_file_meta(&$headers) {
		$headers["icon"] = "Icon";
		$headers["subtext"] = "Subtext";
		$headers["navbar_sticky"] = "Navbar_Sticky";
		$headers["sidebar"] = "Sidebar";
		$headers["sidebar_source"] = "Sidebar_Source";
		$headers["navbar_source"] = "Navbar_Source";
		$headers["footer_source"] = "Footer_Source";
		$headers["content_source"] = "Content_Source";
		$headers["navbar_visible"] = "Navbar_Visible";
		
	}
	public function request_url(&$url)
	{
		if($url == 'query') $this->do_search();
	}
	
	public function get_page_data(&$data, $page_meta) {
		//Loads all the meta values, including meta values from headers into page values
        foreach ($page_meta as $key => $value) {
			if (($key == 'navbar_visible') && ($value != "Hide")) {
				$data[$key] = "Show";
			} elseif (($key == 'icon') && ($value == "")) {
				$data[$key] = "None";
			} elseif (($key == 'subtext') && ($value == "")) {
				$data[$key] = "None";
			} else {
				$data[$key] = $value ;
			}
        }
    }
	
	public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page)
	{
		$this->pages_urls = array();
		foreach ($pages as $page) {
			$this->pages_urls[] = $page['url'];
		}

		$this->pages = array();
		$this->current_url = $current_page['url'];
		$this->construct_pages($pages);
	}

	##
	# HELPER
	##
	
	/**
	 * Create a nested array of the pages, according to their paths.
	 * Merge all individual pages *nested_path*.
	 *
	 * @see    nested_path
	 * @param  array $pages Pico pages flat array
	 */
	private function construct_pages($pages)
	{
		foreach ($pages as $page)
		{
			$page['path'] = rtrim(str_replace($this->base_url.'/','',$page['url']), '/');
			$nested_path = $this->nested_path($page);
			$this->pages = array_merge_recursive($this->pages, $nested_path);
		}
	}

	/**
	 * Create a nested path of a given path, with page infos at the end.
	 * Each path fragment is a "_child" of its parent fragment.
	 *
	 * @param  array  $page the corresponding page data, with 'path' key.
	 * @return array        the nested path
	 */
	private function nested_path($page)
	{
		$parts = explode('/', $page['path']);
		$count = count($parts);

		$arr = array();
		$parent = &$arr;
		foreach($parts as $id => $part) {
			$value = array();
			if(!$part || $id == $count-1) {
				$value = array(
					'url'=>$page['url'],
					'path'=>$page['path'],
					'title'=>$page['title'],
					'navbar_visible'=>$page['navbar_visible'],
					'icon'=>$page['icon'],
					'subtext'=>$page['subtext']
				);
			}
			if(!$part) {
				$parent = $value;
				break;
			}
			$parent['_childs'][$part] = $value;
			$parent = &$parent['_childs'][$part];
		}
		return $arr;
	}

	/**
	 * Create an html list based on the nested pages array.
	 *
	 * @param  array  $pages a nested pages array
	 * @return string        the html list
	 */
	private function output($pages, $is_parent = false)
	{
		if(!isset($pages['_childs'])) return '';
		
		if ($is_parent) {
			$html = ' <div class="uk-dropdown uk-dropdown-navbar"><ul class="uk-nav uk-nav-navbar">';
		} else {
			$html = '<ul class="uk-navbar-nav">';	
		}
		foreach ($pages['_childs'] as $page)
		{
			if($this->is_hidden($page['path'])) continue;

			$url = $page['url'];
			$filename = basename($url);
			$childs = $this->output($page, true);
			$icon = $page['icon'];
			$subtext = $page['subtext'];
			
			if(empty($childs)) { $has_children = false; } else { $has_children = true; }
			
			// use title if the page have one, and make a link if the page exists.
			$item = !empty($page['title']) ? $page['title'] : $filename;
			if(in_array($url, $this->pages_urls)) {
				if((isset($subtext)) && ($subtext != "None")){
					$item = '<a href="'.$url.'" class = "uk-navbar-nav-subtitle"><i class="'.$icon.'"></i> '.$item.'<div>'.$subtext.'</div></a>';
					
				} else {
					$item = '<a href="'.$url.'"><i class="'.$icon.'"></i> '.$item.'</a>';
				}
			}
			$class = "";
			// add the filename in class, and indicates if is current or parent
			
			$dropdown = "";
			if($this->current_url == $url) $class .= ' uk-active';
			elseif(strpos($this->current_url, $url) === 0) $class .= ' uk-active';
			
			if(isset($page['subtext'])) {
			
			}
			if($has_children) {
				$class .= ' uk-parent';
				$dropdown .= 'data-uk-dropdown';
				$html .= '<li class="'.$class.'" '.$dropdown.'>' . $item . $childs . '</li>';
			} else {
				$html .= '<li class="'.$class.'">' . $item . '</li>';
			}
		}
		$html .= '</ul>';
		if ($is_parent) { $html .= '</div>'; }
		return $html;
	}

	/**
	 * Return if the given path had to be hidden or not.
	 *
	 * @param  string  $path the page short path
	 * @return boolean
	 */
	private function is_hidden($path)
	{
		foreach($this->hide_list as $p)
		{
			if( !$p ) continue;
			if( $path == $p ) return true;
			if( strpos($path, $p) === 0 ) {
				if( substr($p,-1) == '/' ) return true;
				elseif( $path[strlen($p)] == '/' ) return true;
			}
		}
		return false;
	}
	
	public function get_search_results($search_field)
	{
		$json = array(
			"results" => array(
				array(
					"title" => "Google",
					"url" => "http://google.com",
					"text" => "A large search engine"),
				array(
					"title" => "Microsoft",
					"url" => "http://microsoft.com",
					"text" => "Devices and Services company"),
				array(
					"title" => "Apple",
					"url" => "http://apple.com",
					"text" => "iPad, iPhone, Mac, iOS"),
				array(
					"title" => "IBM",
					"url" => "http://ibm.com",
					"text" => "Innovators of hardware and software")
			)
		);		
		return json_encode($json);
		//return file_get_contents('uikit_search.json');
	}
	
	private function do_search()
	{	
		if ((isset($_REQUEST['search'])) && (!empty($_REQUEST['search'])))
		{
			$search_field = $_REQUEST['search'];
			$json = $this->get_search_results($search_field);
			die($json);
		}
	}

}
?>
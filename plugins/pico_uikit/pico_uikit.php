<?php
	global $search_field;
	if ((isset($_REQUEST['search'])) && (!empty($_REQUEST['search'])))
	{
		$search_field = $_REQUEST['search'];
		$class_pico_uikit = new pico_uikit();
		$search_list = $class_pico_uikit->get_search_results($search_field);
		echo $search_list;
	}
/**
 * Plugin to make proper use of UI KIT by YooTheme
 *
 * @author Daniel James
 * @link https://github.com/khanduras/pico_uikit
 * @license http://opensource.org/licenses/MIT
 */
class pico_uikit {	
	##
	# VARS
	##
	private $plugin_path;
	private $settings = array();
	private $navigation = array();
	
	##
	# HOOKS
	##	

	public function config_loaded(&$settings)
	{
		$this->settings = $settings;
		
		// default id
		if (!isset($this->settings['uikit']['id'])) { $this->settings['uikit']['id'] = 'uk-navbar-nav'; }
		
		// default classes
		if (!isset($this->settings['uikit']['class'])) { $this->settings['uikit']['class'] = 'uk-navbar-nav'; }
		if (!isset($this->settings['uikit']['class_li'])) { $this->settings['uikit']['class_li'] = 'li-item'; }
		if (!isset($this->settings['uikit']['class_a'])) { $this->settings['uikit']['class_a'] = 'a-item'; }
		
		//default paramiters
		if (!isset($this->settings['uikit']['width'])) { $this->settings['uikit']['width'] = 'fluid'; }
		if (!isset($this->settings['uikit']['style'])) { $this->settings['uikit']['style'] = 'gradient'; }
		if (!isset($this->settings['uikit']['global_navbar_sticky'])) { $this->settings['uikit']['global_navbar_sticky'] = '{top:0}'; }
		if (!isset($this->settings['uikit']['global_sidebar'])) { $this->settings['uikit']['global_sidebar'] = 'Left'; }
		if (!isset($this->settings['uikit']['global_sidebar_source'])) { $this->settings['uikit']['global_sidebar_source'] = 'layout/main_sidebar.html'; }
		if (!isset($this->settings['uikit']['global_navbar_source'])) { $this->settings['uikit']['global_navbar_source'] = 'layout/main_navbar.html'; }
		if (!isset($this->settings['uikit']['global_footer_source'])) { $this->settings['uikit']['global_footer_source'] = 'layout/main_footer.html'; }
		if (!isset($this->settings['uikit']['global_content_source'])) { $this->settings['uikit']['global_content_source'] = 'layout/main_content.html'; }
		
		// default excludes
		$this->settings['uikit']['exclude'] = array_merge_recursive(
			array('single' => array(), 'folder' => array()),
			isset($this->settings['uikit']['exclude']) ? $this->settings['uikit']['exclude'] : array()
		);
	}
	
	public function before_render(&$twig_vars, &$twig)
	{
		$twig_vars['uikit']['navigation'] = $this->build_navbar($this->navigation, true);
		$twig_vars['uikit']['width'] = $this->settings['uikit']['width'];
		$twig_vars['uikit']['style'] = $this->settings['uikit']['style'];
		$twig_vars['uikit']['global_navbar_sticky'] = $this->settings['uikit']['global_navbar_sticky'];
		$twig_vars['uikit']['global_sidebar'] = $this->settings['uikit']['global_sidebar'];
		$twig_vars['uikit']['global_sidebar_source'] = $this->settings['uikit']['global_sidebar_source'];
		$twig_vars['uikit']['global_navbar_source'] = $this->settings['uikit']['global_navbar_source'];
		$twig_vars['uikit']['global_footer_source'] = $this->settings['uikit']['global_footer_source'];
		$twig_vars['uikit']['global_content_source'] = $this->settings['uikit']['global_content_source'];
		$twig_vars['uikit']['search_results'] = $this->return_uikit();
	}
	
	public function before_read_file_meta(&$headers) {
		$headers["icon"] = "Icon";
		$headers["subtext"] = "Subtext";
		$headers["navbar_sticky"] = "Navbar_Sticky";
		$headers["sidebar"] = "Sidebar";
		$headers["sidebar_source"] = "Sidebar_Source";
		$headers["navbar_source"] = "Navbar_Source";
		$headers["footer_source"] = "Footer_Source";
		
	}
	
	public function get_page_data(&$data, $page_meta) {
		//Loads all the meta values, including meta values from headers into page values
        foreach ($page_meta as $key => $value) {
            $data[$key] = $value ;
        }
    }
	
	public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page)
	{
		$navigation = array();
		
		foreach ($pages as $page)
		{
			if (!$this->nav_exclude($page))
			{
				$_split = explode('/', substr($page['url'], strlen($this->settings['base_url'])+1));
				$navigation = array_merge_recursive($navigation, $this->nav_recursive($_split, $page, $current_page));
			}
		}
				
		array_multisort($navigation);

		$this->navigation = $navigation;
	}

	##
	# HELPER
	##
	
	private function build_navbar($navigation = array(), $start = false)
	{
		
		$id = $start ? $this->settings['uikit']['id'] : '';
		$class = $start ? $this->settings['uikit']['class'] : '';
		$class_li = $this->settings['uikit']['class_li'];
		$class_a = $this->settings['uikit']['class_a'];
		$child = '';

		$ul = $start ? '<ul id="%s" class="%s">%s</ul>' : '<ul>%s</ul>';
		
		if (isset($navigation['_child']))
		{
			$_child = $navigation['_child'];
			array_multisort($_child);
			
			foreach ($_child as $c)
			{
				$child .= $this->build_navbar($c);
			}
			
			$child = $start ? sprintf($ul, $id, $class, $child) : sprintf($ul, $child);
		}

		if (isset($navigation['subtext']) && $navigation['subtext'] != "")
		{
			$li = isset($navigation['title'])
			? sprintf(
				'<li class="%1$s %5$s"><a href="%2$s" class="%1$s %6$s %9$s" title="%3$s"><i class="%7$s"></i> %3$s <div>%8$s</div></a>%4$s</li>',
				$navigation['class'],
				$navigation['url'],
				$navigation['title'],
				$child,
				$class_li,
				$class_a,
				$navigation['icon'],
				$navigation['subtext'],
				"uk-navbar-nav-subtitle"
			)
			: $child;
		} else {
			$li = isset($navigation['title'])
			? sprintf(
				'<li class="%1$s %5$s"><a href="%2$s" class="%1$s %6$s" title="%3$s"><i class="%7$s"></i>%3$s</a>%4$s</li>',
				$navigation['class'],
				$navigation['url'],
				$navigation['title'],
				$child,
				$class_li,
				$class_a,
				$navigation['icon']
			)
			: $child;
		
		}

		
		return $li;
	}
	
	private function nav_exclude($page)
	{
		$exclude = $this->settings['uikit']['exclude'];
		$url = substr($page['url'], strlen($this->settings['base_url'])+1);
		$url = (substr($url, -1) == '/') ? $url : $url.'/';
		
		foreach ($exclude['single'] as $s)
		{	
			$s = (substr($s, -1*strlen('index')) == 'index') ? substr($s, 0, -1*strlen('index')) : $s;
			$s = (substr($s, -1) == '/') ? $s : $s.'/';
			
			if ($url == $s)
			{
				return true;
			}
		}
		
		foreach ($exclude['folder'] as $f)
		{
			$f = (substr($f, -1) == '/') ? $f : $f.'/';
			$is_index = ($f == '' || $f == '/') ? true : false;
			
			if (substr($url, 0, strlen($f)) == $f || $is_index)
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function nav_recursive($split = array(), $page = array(), $current_page = array())
	{
		$activeClass = (isset($this->settings['uikit']['activeClass'])) ? $this->settings['uikit']['activeClass'] : 'uk-active';

		if (count($split) == 1)
		{			
			$is_index = ($split[0] == '') ? true : false;
			$ret = array(
				'title'			=> $page['title'],
				'url'			=> $page['url'],
				'class'			=> ($page['url'] == $current_page['url']) ? $activeClass : '',
				'icon'			=> $page["icon"],
				'subtext'		=> $page['subtext']
			);
			
			$split0 = ($split[0] == '') ? '_index' : $split[0];
			return array('_child' => array($split0 => $ret));
			return $is_index ? $ret : array('_child' => array($split[0] => $ret));
		}
		else
		{
			if ($split[1] == '')
			{
				array_pop($split);
				return $this->nav_recursive($split, $page, $current_page);
			}
			
			$first = array_shift($split);
			return array('_child' => array($first => $this->nav_recursive($split, $page, $current_page)));
		}
	}
	//Has to return a url, either json or php
	private function return_uikit() 
	{
		//$search_list= file_get_contents("uikit_search.json");
		
		// ob_start(); // turn on output buffering
			// echo include("uikit_search.json");
			// //echo ob_get_contents(); // get the contents of the output buffer
		// ob_get_clean(); //  clean (erase) the output buffer and turn off output buffering
		
		// $jsonIterator = new RecursiveIteratorIterator(
			// new RecursiveArrayIterator(json_decode($search_list, TRUE)),
			// RecursiveIteratorIterator::SELF_FIRST);
			
		// foreach ($jsonIterator as $key => $val) {
			// if(is_array($val)) {
				// echo "$key:\n";
			// } else {
				// echo "$key => $val\n";
			// }
		// }
		
		//($this->plugin_path .'

		
		return '/pico_uikit/plugins/pico_uikit/pico_uikit.php';
			
		// $json = json_encode(array(
			// "results" => array(
				// array(
					// "title" => "Google",
					// "url" => "http://google.com",
					// "text" => "A large search engine"),
				// array(
					// "title" => "Microsoft",
					// "url" => "http://microsoft.com",
					// "text" => "Devices and Services company"),
				// array(
					// "title" => "Apple",
					// "url" => "http://apple.com",
					// "text" => "iPad, iPhone, Mac, iOS"),
				// array(
					// "title" => "IBM",
					// "url" => "http://ibm.com",
					// "text" => "Innovators of hardware and software")
			// )
		// ));
		// return $json;
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

}
?>
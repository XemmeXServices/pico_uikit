<?php

/**
 * Plugin to make proper use of UI KIT by YooTheme  
 * with Pico CMS.
 *
 * @author Daniel James
 * @link https://github.com/khanduras/pico_uikit
 * @license http://opensource.org/licenses/MIT
 */
class pico_uikit {	
	##
	# VARS
	##
	private $settings = array();
	private $navigation = array();
	##
	# HOOKS
	##
	

	public function config_loaded(&$settings)
	{
		$this->settings = $settings;
		
		// default id
		if (!isset($this->settings['uikit_plugin']['id'])) { $this->settings['uikit_plugin']['id'] = 'uk-navbar-nav'; }
		
		// default classes
		if (!isset($this->settings['uikit_plugin']['class'])) { $this->settings['uikit_plugin']['class'] = 'uk-navbar-nav'; }
		if (!isset($this->settings['uikit_plugin']['class_li'])) { $this->settings['uikit_plugin']['class_li'] = 'li-item'; }
		if (!isset($this->settings['uikit_plugin']['class_a'])) { $this->settings['uikit_plugin']['class_a'] = 'a-item'; }
		
		//default styles
		if (!isset($this->settings['uikit_plugin']['width'])) { $this->settings['uikit_plugin']['width'] = 'fluid'; }
		if (!isset($this->settings['uikit_plugin']['style'])) { $this->settings['uikit_plugin']['style'] = ''; }
		if (!isset($this->settings['uikit_plugin']['global_stickynavbar'])) { $this->settings['uikit_plugin']['global_stickynavbar'] = ''; }
		if (!isset($this->settings['uikit_plugin']['global_sidebar'])) { $this->settings['uikit_plugin']['global_sidebar'] = 'layout/main_sidebar.html'; }
		
		// default excludes
		$this->settings['uikit_plugin']['exclude'] = array_merge_recursive(
			array('single' => array(), 'folder' => array()),
			isset($this->settings['uikit_plugin']['exclude']) ? $this->settings['uikit_plugin']['exclude'] : array()
		);
	}
	
	public function before_render(&$twig_vars, &$twig)
	{
		$twig_vars['uikit_plugin']['navigation'] = $this->build_navbar($this->navigation, true);
		$twig_vars['uikit_plugin']['width'] = $this->settings['uikit_plugin']['width'];
		$twig_vars['uikit_plugin']['style'] = $this->settings['uikit_plugin']['style'];
		$twig_vars['uikit_plugin']['global_stickynavbar'] = $this->settings['uikit_plugin']['global_stickynavbar'];
		$twig_vars['uikit_plugin']['global_sidebar'] = $this->settings['uikit_plugin']['global_sidebar'];
	}
	
	public function before_read_file_meta(&$headers) {
		$headers["icon"] = "Icon";
		$headers["subtext"] = "Subtext";
		$headers["stickynavbar"] = "StickyNavBar";
		$headers["sidebar"] = "Sidebar";
		
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
		
		$id = $start ? $this->settings['uikit_plugin']['id'] : '';
		$class = $start ? $this->settings['uikit_plugin']['class'] : '';
		$class_li = $this->settings['uikit_plugin']['class_li'];
		$class_a = $this->settings['uikit_plugin']['class_a'];
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
		$exclude = $this->settings['uikit_plugin']['exclude'];
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
		$activeClass = (isset($this->settings['uikit_plugin']['activeClass'])) ? $this->settings['uikit_plugin']['activeClass'] : 'uk-active';

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
}
?>
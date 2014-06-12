#Pico UI Kit (PUIK) Framework

UI Kit is a "A lightweight and modular front-end framework
for developing fast and powerful web interfaces." [UI Kit](http://getuikit.com/)

Pico CMS is "A stupidly simple & blazing fast, flat file CMS." [Pico CMS](http://picocms.org/)

## Features

 * UI Kit theme, fluid or fixed width, based primarily on [Fluid UI Kit Theme](https://github.com/nands/uikit-fluid-layout).
 * Navigation bar that updates with active page, icons and subtext, editable with a layout file, based primarily on [Pico Navigation](https://github.com/ahmet2106/pico-navigation).
 * Sidebar that is customizable per page as it can be on the left or right or disabled and is editable with a layout file.
 * Search functionality - basic search

## Theme & Plugin

The uikit theme and uikit_plugin work together for navigation, customization and different layouts for different styles. 

### Configuration

Copy the pico_uikit.php file into the plugin folder and uikit folder into the themes folder.

There's plenty of configuration options that can be set in the config.php file.

<pre><code>$config['puik']['width'] = 'fixed';
$config['puik']['style'] = 'almost-flat';
$config['puik']['global_navbar_sticky'] = 'Yes'; 
$config['puik']['global_sidebar'] = 'Right';
$config['puik']['global_sidebar_source'] = 'layout/main_sidebar.html';</code></pre>
 * <strong>width</strong> - Can be fixed or fluid. 
 * <strong>style</strong> - Can be 'almost-flat' or 'gradient'
 * <strong>global_stickybar</strong> - Sets whether the top navbar is sticky or not, with offset, for example "{top:100}".
 * <strong>global_sidebar</strong> - The path to the sidebar layout file, can be over-ridden per page
 
<pre>/*
Title: Welcome
Author: 
Date: 2014/05/28
Subtext: to Pico
Icon: uk-icon-github-square
Sidebar_Source: layout/welcome_sidebar.html
Sidebar: Right
*/</pre>

Some meta data can be set per page which will change how the page will display on the navbar.
 

 * <strong>Subtext</strong> - Set the subtext for the page.
 * <strong>Icon</strong> - Change the icon according to the [Icon Mapping](http://www.getuikit.com/docs/icon.html) section
 * <strong>Sidebar_Source</strong> - The path to the sidebar layout file to over ride the global sidebar.
 * <strong>Sidebar</strong> - Left, Right or None to disable
 * <strong>Navbar_Sticky</strong> - Set if the Navbar is sticky or not

## Ambitions

 * Complete the navbar to include the dropdown menu from UI Kit, pulling from subpages. Works for one submenu.
 * Complete the sidebar navigation to pull from pages/subpages.
 * Sticky Footer
 * Create a front-end editor using off-canvas and [Markdown Editor](http://getuikit.com/docs/addons_markdownarea.html), incorporating user roles and rights from [Pico Users](https://github.com/nliautaud/pico-users)
 * Content Types [Page, Post, Menu, Plugin, Widget] in meta data to give content different functions
 * Pagelets that handle different types of content

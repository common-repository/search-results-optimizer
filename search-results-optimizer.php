<?php

/**
 * Plugin Name: Search Results Optimizer
 * Plugin URI: http://wordpress.org/plugins/search-results-optimizer/
 * Description: Search Results Optimizer learns which results users find useful and automatically prioritizes them in future searches.
 * Version: 1.0.3
 * Author: Chris Gorvan
 * Author URI: http://www.gorvan.com
*/

namespace SearchResultsOptimizer;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SearchResultsOptimizer')):

/**
 * The main Search Results Optimizer class.
 *
 * @package SearchResultsOptimizer
 * @class SearchResultsOptimizer
 * @author Chris Gorvan (@chrisgorvan)
 * @since 1.0
 */
final class SearchResultsOptimizer {
  /**
   * This version of SearchResultsOptimizer.
   * 
   * @since 1.0
   * @access public
   */
  const VERSION = '1.0.3';
  
  /**
   * The singleton instance.
   * 
   * @since 1.0
   * @access private
   * @var \SearchResultsOptimizer\SearchResultsOptimizer
   */
  private static $_instance = null;
  
  /**
   * A collection of feedback messages to display.
   * 
   * @since 1.0
   * @access public
   * @var array 
   */
  public static $messages = array();
  
  /**
   * The locatino of the templates.
   * 
   * @since 1.0
   * @access public
   * @var string 
   */
  public $templateUrl = '';
  
  /**
   * The SRO search form.
   * 
   * @since 1.0
   * @access public
   * @var \SearchResultsOptimizer\includes\SROSearchForm 
   */
  public $sroSearchform = null;
  
  /**
   * The SRO result set, used to process clicks.
   * 
   * @since 1.0
   * @access public
   * @var \SearchResultsOptimizer\includes\SROSearchResults
   */
  public $sroResults = null;
  
  /**
   * The SRO query builder.
   * 
   * @since 1.0
   * @access public
   * @var \SearchResultsOptimizer\includes\SROSearchBuilder
   */
  public $sroBuilder = null;
  
  /**
   * Singleton handler, returns the instance.
   * 
   * @since 1.0
   * @access public
   * @return \SearchResultsOptimizer\SearchResultsOptimizer
   */
  public static function instance() {
    if (\is_null(self::$_instance)) {
      $GLOBALS['sroPlugin'] = self::$_instance = new self();
    }
    return self::$_instance;
  }
  
  /**
   * Constructor.
   * 
   * @since 1.0
   * @access private
   */
  private function __construct() {
    $this->init();
    $this->includes();
  }
  
  /**
   * Includes required classes.
   * 
   * @since 1.0
   * @access protected
   */
  protected function includes() {
    new \SearchResultsOptimizer\includes\SROInstall();
    if (\is_admin()) {
      new \SearchResultsOptimizer\includes\admin\SROAdminActions();
      new \SearchResultsOptimizer\includes\admin\SROAdminMenus();
      new \SearchResultsOptimizer\includes\admin\SROAdminAjax();
    }
    $this->sroSearchform = new \SearchResultsOptimizer\includes\SROSearchForm();
    $this->sroBuilder = new \SearchResultsOptimizer\includes\SROSearchBuilder();
    $this->sroResults = new \SearchResultsOptimizer\includes\SROSearchResults();
  }
  
  /**
   * Initialises SRO.
   * 
   * @since 1.0
   * @access public
   */
  public function init() {
    \spl_autoload_register(array($this, 'autoload'));
    $this->loadTextdomain();
    $this->templateUrl = \apply_filters('searchresultsoptimizer_template_url', 'sro' . DIRECTORY_SEPARATOR);
    \add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
    \add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'));
    \add_action('admin_notices', array($this,'displayAdminNotice'),100);
  }
  
  /**
   * The SRO autoloader, maps the namespace to directories.
   * 
   * @since 1.0
   * @access public
   * @param string $className
   */
  public static function autoload($className) {
    if (false !== \strpos($className, 'SearchResultsOptimizer')) {
      $path = __DIR__ . DIRECTORY_SEPARATOR . \strtolower(\preg_replace('/([A-Z])([a-z]+)/', "-$1$2", \strtr($className, array('SearchResultsOptimizer\\' => '', '\\' => DIRECTORY_SEPARATOR)))) . '.php';
      if (\is_readable($path)) {
        include $path;
      }
    }
  }

  /**
   * Localise SRO.
   * 
   * @since 1.0
   * @access protected
   */
  protected function loadTextdomain() {
    $locale = \apply_filters('plugin_locale', \get_locale(), 'searchresultsoptimizer');
    if (\is_admin()) {
      \load_textdomain('searchresultsoptimizer', WP_LANG_DIR . "/searchresultsoptimizer/sro-admin-{$locale}.mo");
      \load_textdomain('searchresultsoptimizer', __DIR__ . "/languages/sro-admin-{$locale}.mo");
    }
    \load_textdomain('searchresultsoptimizer', WP_LANG_DIR . "/searchresultsoptimizer/sro-{$locale}.mo");
    \load_textdomain('searchresultsoptimizer', __DIR__ . "/languages/sro-{$locale}.mo");
  }
  
  /**
   * Enqueue SRO assets.
   * 
   * @since 1.0
   * @access public
   */
  public function enqueueAssets() {
    \wp_enqueue_style('sro-style', \plugins_url(\implode(DIRECTORY_SEPARATOR, array('search-results-optimizer','assets','css','searchresultsoptimizer.css'))), array(), self::VERSION);
    \wp_enqueue_script('sro-script', \plugins_url(DIRECTORY_SEPARATOR . \implode(DIRECTORY_SEPARATOR, array('search-results-optimizer','assets','js','searchresultsoptimizer.min.js'))), array('jquery'), self::VERSION);
    if (\is_admin()) {
      \wp_enqueue_script('google-charts', 'https://www.google.com/jsapi', array(), '', true);
      \wp_enqueue_script('sro-admin-script', \plugins_url(DIRECTORY_SEPARATOR . \implode(DIRECTORY_SEPARATOR, array('search-results-optimizer','assets','js','searchresultsoptimizer-admin.min.js'))), array('wp-color-picker', 'jquery-ui-sortable', 'jquery', 'google-charts'), self::VERSION, true);
      \wp_enqueue_style('wp-color-picker');
    }
  }
  
  /**
   * Returns the plugin directory
   * 
   * @since 1.0
   * @access public
   * @return string
   */
  public function pluginPath() {
    return __DIR__;
  }
  
  /**
   * Returns the template directory.
   * 
   * @since 1.0
   * @access public
   * @return string
   */
  public function templatePath() {
    return __DIR__ . DIRECTORY_SEPARATOR . 'templates';
  }
  
  /**
   * Returns the location to the template part, falling back to defaults.
   * 
   * @since 1.0
   * @access public
   * @param string $slug
   * @param string $name
   */
  public function getTemplatePart($slug, $name = '') {
    $template = '';
    // Check the theme's override directory
    if (!empty($name)) {
      $template = \locate_template($this->templateUrl . "{$slug}-{$name}.php");
    }
    // Check the SRO default
    if (empty($template) && !empty($name) && \file_exists($this->templatePath() . DIRECTORY_SEPARATOR . "{$slug}-{$name}.php")) {
        $template = $this->templatePath() . DIRECTORY_SEPARATOR . "{$slug}-{$name}.php";
    }
    // Check the theme's default
    if (empty($template)) {
      $template = \locate_template(array("{$slug}-{$name}.php", "{$slug}.php"));
    }
    if (!empty($template)) {
      \load_template($template, false);
    }
  }
  
  /**
   * Output html formatted admin messages.
   * 
   * @since 1.0
   * @access public
   */
  public function displayAdminNotice() {
    foreach (self::$messages as $messageClass => $messages) {
      foreach ($messages as $message) {
        echo "<div class='{$messageClass}'><p>{$message}</p></div>";
      }
    }
  }

}

endif;

\SearchResultsOptimizer\SearchResultsOptimizer::instance();
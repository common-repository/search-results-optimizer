<?php

/**
 * Copyright (C) 2014 Chris Gorvan (@chrisgorvan)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @package SearchResultsOptimizer
 * @since 1.0
 */

namespace SearchResultsOptimizer\includes\admin;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROAdminMenus')):

/**
 * Adds the admin menu tree.
 *
 * @package SearchResultsOptimizer
 * @class SROAdminMenus
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminMenus {
    
  /**
   * Constructor. Adds WordPress hooks for admin menu items.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    add_action('admin_menu', array($this, 'adminMenu'), 9);
    add_action('admin_menu', array($this, 'reportsMenu'), 20);
    add_action('admin_menu', array($this, 'themesMenu'), 30);
    add_action('admin_menu', array($this, 'searchesMenu'), 40);
    add_action('admin_menu', array($this, 'settingsMenu'), 50);
  }
  
  /**
   * Adds the root menu item.
   * 
   * @since 1.0
   * @access public
   * @global array $menu
   */
  public function adminMenu() {
    global $menu;
    if (current_user_can('manage_options')) {
      $menu[] = array( '', 'read', 'separator-searchresultsoptimizer', '', 'wp-menu-separator searchresultsoptimizer');
    }
    add_menu_page(__('Search Results Optimizer', 'searchresultsoptimizer'), __( 'Search Results Optimizer', 'searchresultsoptimizer'), 'manage_options', 'searchresultsoptimizer' , array($this, 'dashboardPage'), 'dashicons-list-view', '55.2');
  }
  
  /**
   * Outputs the dashboard page.
   * 
   * @since 1.0
   * @access public
   */
  public function dashboardPage() {
    $page = new \SearchResultsOptimizer\includes\admin\SROAdminDashboard();
    $page->output();
  }
  
  /**
   * Adds the settings menu
   * 
   * @since 1.0
   * @access public
   */
  public function settingsMenu() {
    \add_submenu_page('searchresultsoptimizer', __( 'Settings', 'searchresultsoptimizer' ),  __( 'Settings', 'searchresultsoptimizer') , 'manage_options', 'searchresultsoptimizer_settings', array($this, 'settingsPage'));
  }
  
  /**
   * Outputs the settings page.
   * 
   * @since 1.0
   * @access public
   */
  public function settingsPage() {
    $page = new \SearchResultsOptimizer\includes\admin\SROAdminSettings();
    $page->output();
  }
  
  /**
   * Adds the report menu
   * 
   * @since 1.0
   * @access public
   */
  public function reportsMenu() {
    \add_submenu_page('searchresultsoptimizer', __('Reports', 'searchresultsoptimizer'),  __('Reports', 'searchresultsoptimizer') , 'manage_options', 'searchresultsoptimizer_reports', array($this, 'reportsPage'));
  }
  
  /**
   * Outputs the reports page.
   * 
   * @since 1.0
   * @access public
   */
  public function reportsPage() {
    $page = new \SearchResultsOptimizer\includes\admin\SROAdminReports();
    $page->output();
  }
  
  /**
   * Adds the themes menu
   * 
   * @since 1.0
   * @access public
   */
  public function themesMenu() {
    \add_submenu_page('searchresultsoptimizer', __('Themes', 'searchresultsoptimizer'),  __('Themes', 'searchresultsoptimizer') , 'manage_options', 'searchresultsoptimizer_themes', array($this, 'themesPage'));
  }
  
  /**
   * Outputs the themes page.
   * 
   * @since 1.0
   * @access public
   */
  public function themesPage() {
    $page = new \SearchResultsOptimizer\includes\admin\SROAdminThemes();
    $page->output();
  }
  
  /**
   * Adds the searches menu
   * 
   * @since 1.0
   * @access public
   */
  public function searchesMenu() {
    \add_submenu_page('searchresultsoptimizer', __('Searches', 'searchresultsoptimizer'),  __('Searches', 'searchresultsoptimizer') , 'manage_options', 'searchresultsoptimizer_searches', array($this, 'searchesPage'));
  }
  
  /**
   * Outputs the searches page.
   * 
   * @since 1.0
   * @access public
   */
  public function searchesPage() {
    $page = new \SearchResultsOptimizer\includes\admin\SROAdminSearches();
    $page->output();
  }
}

endif;
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

if (!class_exists(__NAMESPACE__ . '\SROAdminThemes')):

/**
 * Description of class-sro-admin-menus
 *
 * @package SearchResultsOptimizer
 * @class SROAdminThemes
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminThemes extends \SearchResultsOptimizer\includes\abstracts\SROAdminPage {
  
  /**
   * The theme.
   * 
   * @since 1.0
   * @access protected
   * @var \SearchResultsOptimizer\includes\classes\SROTheme
   */
  protected $theme;
  
  /**
   * The update nonce.
   * 
   * @since 1.0
   * @access protected
   * @var string
   */
  protected $nonce = '';
  
  /**
   * The result excerpt length.
   * 
   * @since 1.0
   * @access public
   */
  const EXCERPT_LENGTH = 200;

  /**
   * Constructor. Adds hooks.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    $this->id = $id = \filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    if (!empty($id)) {
      $this->theme = new \SearchResultsOptimizer\includes\classes\SROTheme($id, null, true);
    }
    $this->action = \filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
    $this->addHooks();
  }
  
  /**
   * Adds WordPress hook for processing bulk actions.
   * 
   * @since 1.0
   * @access protected
   */
  protected function addHooks() {
    add_action('wp_loaded', array($this, 'processAction'));
  }
  
  /**
   * Returns the theme.
   * 
   * @since 1.0
   * @access public
   * @return \SearchResultsOptimizer\includes\classes\SROTheme
   */
  public function getTheme() {
    return $this->theme;
  }
  
  /**
   * Outputs a table of themes.
   * 
   * @since 1.0
   * @access protected
   */
  protected function displayTable() {
    $table = new \SearchResultsOptimizer\includes\admin\SROAdminThemesTable();
    $table->prepare_items();
    echo "<input type='hidden' name='page' value='{$table->screen->id}' />";
    $table->search_box(__('search', 'searchresultsoptimizer' ), 'normalization_search');
    $table->display();
  }
  
  /**
   * Calls the theme delete action.
   * 
   * @since 1.0
   * @access protected
   */
  protected function deleteAction() {
    $nonce = filter_input(INPUT_GET, 'nonce');
    $this->theme->delete($nonce);
  }

  /**
   * Calls the theme save action.
   * 
   * @since 1.0
   * @access protected
   */
  protected function saveAction() {
    $nonce = \filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);
    $name = \filter_input(INPUT_POST, 'theme_name', FILTER_SANITIZE_STRING);
    $this->theme->name = $name;
    $this->theme->save($nonce);
  }
  
  /**
   * Outputs a list of searchs for the theme.
   * 
   * @since 1.0
   * @access protected
   */
  protected function displaySearches() {
    $posts = $this->theme->getPopularPosts('normalized');
    if (!empty($posts)) {
      $ids = array();
      $pinnedId = '';
      foreach ($posts as $k => $post) {
        if (\is_object($post)) {
          include __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sro-admin_search.php';
          $ids[] = $post->ID;
        }
      }
      echo "<input type='hidden' name='pin' value='" . $pinnedId . "'>";
      echo "<input type='hidden' name='order' value='" . \implode(',', $ids) . "'>";
    } else {
      $noun = __('theme', 'searchresultsoptimizer');
      include __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sro-admin_nosearches.php';
    }
  }
}

endif;
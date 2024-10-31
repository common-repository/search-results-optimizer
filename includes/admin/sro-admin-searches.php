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

if (!class_exists(__NAMESPACE__ . '\SROAdminSearches')):

/**
 * Description of class-sro-admin-menus
 *
 * @package SearchResultsOptimizer
 * @class SROAdminSearches
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminSearches extends \SearchResultsOptimizer\includes\abstracts\SROAdminPage {
  
  /**
   * An instance of the search being edited.
   * 
   * @since 1.0
   * @access protected
   * @var \SearchResultsOptimizer\includes\classes\SROSearch 
   */
  protected $search;
  
  /**
   * The lenth of the result excerpt.
   * 
   * @since 1.0
   * @access public
   */
  const EXCERPT_LENGTH = 200;
  
  /**
   * Constructor.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    $this->id = $id = \filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    if (!empty($id)) {
      $this->search = new \SearchResultsOptimizer\includes\classes\SROSearch($id);
    }
    $this->action = \filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
    $this->addHooks();
  }
  
  /**
   * Adds WordPress hooks.
   * 
   * @since 1.0
   * @access protected
   */
  protected function addHooks() {
    \add_action('wp_loaded', array($this, 'processAction'));
  }
  
  /**
   * Calls search delete on a row.
   * 
   * @since 1.0
   * @access protected
   * @param integer $id
   */
  protected function deleteAction($id) {
    $nonce = \filter_input(INPUT_GET, 'nonce', FILTER_SANITIZE_STRING);
    $search = new \SearchResultsOptimizer\includes\classes\SROSearch($id);
    $search->delete($nonce);
  }
  
  /**
   * Calls the search save.
   * 
   * @since 1.0
   * @access protected
   */
  protected function saveAction() {
    $nonce = \filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);
    $pinnedPostId = \filter_input(INPUT_POST, 'pin', FILTER_VALIDATE_INT, array('options' => array('default' => NULL)));
    $this->search->pinnedPostId = $pinnedPostId;
    $this->search->save($nonce);
  }
  
  /**
   * Returns an html formatted page introduction.
   * 
   * @since 1.0
   * @access protected
   * @return string
   */
  protected function getIntro() {
    $action = \filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
    switch ($action) {
      case 'similar':
        $page = \filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
        return '<p>' . __('Showing similar searches, to view all searches click ', 'searchresultsoptimizer') . "<a href='?page={$page}'>" . __('here', 'searchresultsoptimizer') . '</a>.</p>';
      case 'delete':
      default:
        return '<p>' . __('Similar searches are grouped by default and when adding a theme they will also be highlighted.', 'searchresultsoptimizer');
    }
  }
  
  /**
   * Returns the search.
   * 
   * @since 1.0
   * @access public
   * @return \SearchResultsOptimizer\includes\classes\SROSearch
   */
  public function getSearch() {
    return $this->search;
  }
  
  /**
   * Outputs the list of results.
   * 
   * @since 1.0
   * @access protected
   */
  protected function displaySearches() {
    $posts = $this->search->getPopularPosts('id');
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
      $noun = __('search', 'searchresultsoptimizer');
      include __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sro-admin_nosearches.php';
    }
  }
}

endif;
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

if (!class_exists(__NAMESPACE__ . '\SROAdminAjax')):

/**
 * Ajax handler for admin actions.
 *
 * @package SearchResultsOptimizer
 * @class SROAdminAjax
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminAjax {
  
  /**
   * Constructor. Adds hooks.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    $this->addHooks();
  }
  
  /**
   * Add WordPress hooks for the endpoints.
   * 
   * @since 1.0
   * @access protected
   */
  protected function addHooks() {
    add_action('wp_ajax_linkSearch', array($this, 'linkSearch'));
    add_action('wp_ajax_unlinkSearch', array($this, 'unlinkSearch'));
  }
  
  /**
   * Link a search to the theme, creating the theme if it doesn't exist.
   * 
   * @since 1.0
   * @access public
   */
  public function linkSearch() {
    $output = array();
    $name = \filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $theme = new \SearchResultsOptimizer\includes\classes\SROTheme(null, $name);
    $nonce = \filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);
    if ($theme->verifyAddNonce($nonce)) {
      $hash = \filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_STRING);
      $theme->name = $name;
      $theme->save($theme->getUpdateNonce());
      if ((\in_array($hash, $theme->getHashes())) || ($theme->linkSearch($hash, $theme->getUpdateNonce()))) {
        $output['error'] = 0;
        $output['id'] = $theme->id;
        $output['nonce'] = $theme->getUpdateNonce();
      } else {
        $output['error'] = 1;
        $output['message'] = __("Unable to link new theme to current search", 'searchresultsoptimizer');
      }
    } else {
      $output['error'] = 1;
      $output['message'] = __("Unable to save theme, try refreshing the page and adding it again.", 'searchresultsoptimizer');
    }
    echo \json_encode($output);
    die();
  }
  
  /**
   * Unlinks a search from a theme.
   * 
   * @since 1.0
   * @access public
   */
  public function unlinkSearch() {
    $output = array();
    $nonce = \filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);
    $hash = \filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_STRING);
    $id = \filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $theme = new \SearchResultsOptimizer\includes\classes\SROTheme($id);
    if (($theme instanceof \SearchResultsOptimizer\includes\classes\SROTheme) && $theme->unlinkSearch($hash, $nonce)) {
      $output['error'] = 0;
    } else {
      $output['error'] = 1;
      $output['message'] = __('Unable to unlink search', 'searchresultsoptimizer');
    }
    echo json_encode($output);
    die();
  }
}

endif;
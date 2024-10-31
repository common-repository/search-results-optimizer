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

if (!class_exists(__NAMESPACE__ . '\SROAdminActions')):

/**
 * Factory for the admin pages. Instantiates non-static. 
 *
 * @package SearchResultsOptimizer
 * @class SROAdminActions
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminActions extends \SearchResultsOptimizer\includes\abstracts\SROAdminPage {

  /**
   * Constructor.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    $action = \filter_input(INPUT_GET, 'action');
    $page = \filter_input(INPUT_GET, 'page');
    if (!empty($action) && !empty($page)) {
      $class = "\SearchResultsOptimizer\includes\admin\SROAdmin" . ucfirst(substr(strstr($page, "_"),1));
      if (class_exists($class)) {
        new $class();
      }
    }
  }
}

endif;
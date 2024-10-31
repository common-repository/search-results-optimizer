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

if (!class_exists(__NAMESPACE__ . '\SROAdminSettings')):

/**
 * Description of class-sro-admin-menus
 *
 * @package SearchResultsOptimizer
 * @class SROAdminSettings
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminSettings extends \SearchResultsOptimizer\includes\abstracts\SROAdminPage {
  /**
   * The settings sections.
   * 
   * @since 1.0
   * @access public
   * @var array
   */
  public $settings;
  
  /**
   * Constructor. Instantiates the settings sections.
   */
  public function __construct() {
    $this->settings['general'] = new \SearchResultsOptimizer\includes\admin\settings\SROAdminSettingsGeneral();
    $this->settings['results'] = new \SearchResultsOptimizer\includes\admin\settings\SROAdminSettingsResults();
  }
  
  /**
   * Input sanitizer.
   * 
   * @since 1.0
   * @access public
   * @param type $input
   */
  public function sanitize($input) {
    return trim($input);
  }
}

endif;
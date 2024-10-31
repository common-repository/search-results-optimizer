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

namespace SearchResultsOptimizer\includes\admin\settings;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROAdminSettingsGeneral')):

/**
 * Settings for the general section.
 *
 * @package SearchResultsOptimizer
 * @class SROAdminSettingsGeneral
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminSettingsGeneral extends \SearchResultsOptimizer\includes\abstracts\SROAdminSettings {
  
  /**
   * Constructor. Sets id and label for section.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    $this->id = 'searchresultsoptimizer_general';
    $this->label = __('General', 'searchresultsoptimizer');
  }

  /**
   * Returns an array of formatted form elements.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function getSettings() {
    return apply_filters('searchresultsoptimizer_general_settings', array(
      array(
        'title' => __('Advanced Search Form', 'searchresultsoptimizer'),
        'desc' => __('Enable the advanced search form', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_advanced_search_enabled',
        'type' => 'checkbox',
        'default' => 1,
      ),
      array(
        'title' => __('Metadata Filters', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_metadata_filters',
        'type' => 'fieldset',
        'values' => $this->getMetaFilters(),
      ),
      array(
        'title' => __('Searched Post Types', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_post_types_searched',
        'type' => 'fieldset',
        'values' => $this->getPostTypes()
      ),
    ));
  }
}

endif;
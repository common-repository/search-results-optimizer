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

if (!class_exists(__NAMESPACE__ . '\SROAdminSettingsResults')):

/**
 * Settings for the results section.
 *
 * @package SearchResultsOptimizer
 * @class SROAdminSettingsResults
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminSettingsResults extends \SearchResultsOptimizer\includes\abstracts\SROAdminSettings {
  
  /**
   * Constructor. Sets id and label for section.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    $this->id = 'searchresultsoptimizer_results';
    $this->label = __('Results', 'searchresultsoptimizer');
  }

  /**
   * Returns an array of formatted form elements.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function getSettings() {
    return apply_filters('searchresultsoptimizer_results_settings', array(
      array(
        'title' => __('Highlighting', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_highlighting',
        'type' => 'fieldset',
        'values' => array(
          array(
            'title' => __('Highlight search terms', 'searchresultsoptimizer'),
            'desc' => __('Highlight search terms', 'searchresultsoptimizer'),
            'id' => 'searchresultsoptimizer_result_highlighting_enabled',
            'type' => 'checkbox',
            'default' => 1,
          ),
          array(
            'title' => __('Highlight colour', 'searchresultsoptimizer'),
            'desc' => '',
            'id' => 'searchresultsoptimizer_result_highlighting_colour',
            'type' => 'textfield',
            'default' => '#efdd95',
          ),
        )
      ),
      array(
        'title' => __('Sorting', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_sorting',
        'type' => 'fieldset',
        'values' => array(
          array(
            'title' => __('Sort first five results by popularity', 'searchresultsoptimizer'),
            'desc' => __('Sort first five results by popularity', 'searchresultsoptimizer'),
            'id' => 'searchresultsoptimizer_sorting_popularity',
            'type' => 'checkbox',
            'default' => 1,
          ),
          array(
            'title' => __('Secondary sorting', 'searchresultsoptimizer'),
            'desc' => '',
            'id' => 'searchresultsoptimizer_sorting_secondary',
            'type' => 'select',
            'default' => 'datenew',
            'options' => array(
              'datenew' => __('then by date created (newest to oldest)', 'searchresultsoptimizer'),
              'dateold' => __('then by date created (oldest to newest)', 'searchresultsoptimizer'),
              'modifiednew' => __('then by date last modified (newest to oldest)', 'searchresultsoptimizer'),
              'modifiedold' => __('then by date last modified (oldest to newest)', 'searchresultsoptimizer'),  
              'title' => __('then by title', 'searchresultsoptimizer'),
            ),
            'requires' => 'searchresultsoptimizer_sorting_popularity'
          ),
          array(
            'title' => __('Result Pinning', 'searchresultsoptimizer'),
            'desc' => __('Enable result pinning', 'searchresultsoptimizer'),
            'id' => 'searchresultsoptimizer_result_pinning_enabled',
            'type' => 'checkbox',
            'default' => 1,
            'requires' => 'searchresultsoptimizer_sorting_popularity'
          ),
        )
      ),
      array(
        'title' => __('Suggest Similar Searches', 'searchresultsoptimizer'),
        'desc' => __('Display when current search returns no results', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_include_other_searches',
        'type' => 'checkbox',
        'default' => 1,
      )
    ));
  }
}

endif;
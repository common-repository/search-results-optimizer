<?php

/*
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

namespace SearchResultsOptimizer\includes\admin\reports;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROAdminReportsLatestSearchesTable')):

/**
 * Generates a table of Search Themes, used to group searches
 * 
 * @package SearchResultsOptimizer
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminReportsLatestSearchesTable extends \SearchResultsOptimizer\includes\abstracts\SROListTable {
  
  /**
   * Constructor.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    parent::__construct(array(
      'singular' => 'sro-latest-search',
      'plural' => 'sro-latest-searches',
      'ajax' => false
    ));
  }
  
  /**
   * Gets column names
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function get_columns() {
    return array(
      'query' => __('Search', 'searchresultsoptimizer'),
      'time' => __('Time', 'searchresultsoptimizer'),
    );
  }
  
  /**
   * Gets hidden columns.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function get_hidden_columns() {
    return array();
  }
  
  /**
   * Returns the formatted date time for the table
   * 
   * @since 1.0
   * @access public
   * @param \stdClass $item
   * @return string
   */
  public function column_time(\stdClass $item) {
    return \date_i18n(\get_option('date_format') . ' @ ' . \get_option('time_format'), $item->time);
  }
  
  /**
   * Returns the data query.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @param integer $weeks
   * @return string
   */
  protected function getQuery($weeks = 1) {
    global $wpdb;
    return <<<EOT
SELECT 
  `{$wpdb->prefix}sro_queries`.`query`, 
  UNIX_TIMESTAMP(`{$wpdb->prefix}sro_queries`.`timestamp`) AS 'time'
FROM `{$wpdb->prefix}sro_queries` 
ORDER BY `time` DESC
EOT;
  }

  /**
   * Implemented for abstract.
   * 
   * @since 1.0
   * @access public
   * @param array $records
   */
  protected function bulkDeleteAction(array $records) {

  }

  /**
   * Implemented for abstract
   * 
   * @since 1.0
   * @access public
   * @param integer $id
   */
  protected function getDeleteNonce($id) {

  }

}

endif;
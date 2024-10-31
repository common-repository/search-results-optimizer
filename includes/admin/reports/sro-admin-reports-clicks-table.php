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

namespace SearchResultsOptimizer\includes\admin\reports;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROAdminReportsClicksTable')):

/**
 * Generates a summary table of popular searches.
 * 
 * @package SearchResultsOptimizer
 * @class SROAdminReportsClicksTable
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminReportsClicksTable extends \SearchResultsOptimizer\includes\abstracts\SROListTable {
  
  /**
   * Constructor.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    parent::__construct(array(
      'singular' => 'sro-click',
      'plural' => 'sro-clicks',
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
      'post_title' => __('Post', 'searchresultsoptimizer'),
      'clicks' => __('Clicks', 'searchresultsoptimizer'),
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
   * Returns the data query.
   * 
   * @since 1.0
   * @access public
   * @global $wpdb
   * @param integer $weeks
   * @return string
   */
  protected function getQuery($weeks = 1) {
    global $wpdb;
    return <<<EOT
SELECT 
  `{$wpdb->prefix}sro_results`.`postId`, 
  `{$wpdb->prefix}posts`.`post_title`, 
  COUNT(`{$wpdb->prefix}sro_results`.`postId`) AS 'clicks' 
FROM `{$wpdb->prefix}sro_clicks` 
JOIN `{$wpdb->prefix}sro_results` ON `{$wpdb->prefix}sro_clicks`.`resultId` = `{$wpdb->prefix}sro_results`.`id` 
JOIN `{$wpdb->prefix}posts` ON `{$wpdb->prefix}posts`.`ID` = `{$wpdb->prefix}sro_results`.`postId` 
WHERE `{$wpdb->prefix}sro_clicks`.`timestamp` > DATE_SUB(NOW(), INTERVAL {$weeks} WEEK)
GROUP BY `{$wpdb->prefix}sro_results`.`postId`
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
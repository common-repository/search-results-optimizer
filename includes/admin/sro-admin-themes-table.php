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

if (!class_exists(__NAMESPACE__ . '\SROAdminThemesTable')):

/**
 * Generates a table of Search Themes, used to group searches
 * 
 * @package SearchResultsOptimizer
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminThemesTable extends \SearchResultsOptimizer\includes\abstracts\SROListTable {
  
  /**
   * Constructor.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    parent::__construct(array(
      'singular' => 'sro-theme',
      'plural' => 'sro-themes',
      'ajax' => false
    ));
  }
  
  /**
   * Returns columns names.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function get_columns() {
    return array(
      'cb' => true,
      'id' => __('ID', 'searchresultsoptimizer'),
      'name' => __('Search Theme', 'searchresultsoptimizer'),
      'searches' => __('Linked Searches', 'searchresultsoptimizer'),
    );
  }
  
  /**
   * Returns hidden columns.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function get_hidden_columns() {
    return array(
        'id'
    );
  }
  
  /**
   * Returns sortable column config.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function get_sortable_columns() {
    return $sortable = array(
      'name' => array('name', true),
      'searches' => array('name', false)
    );
  }
  
  /**
   * Helper function to get delete nonce.
   * 
   * @since 1.0
   * @access protected
   * @param integer $id
   * @return string
   */
  protected function getDeleteNonce($id) {
    $theme = new \SearchResultsOptimizer\includes\classes\SROTheme($id);
    return $theme->getDeleteNonce();
  }
  
  /**
   * Returns the SQL for the table data.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return string
   */
  protected function getQuery() {
    global $wpdb;
    $query = "SELECT `{$wpdb->prefix}sro_themes`.*, COUNT(`{$wpdb->prefix}sro_themehashes`.`normalized`) AS 'searches' FROM `{$wpdb->prefix}sro_themes` ";
    $query .= "LEFT JOIN `{$wpdb->prefix}sro_themehashes` ON `{$wpdb->prefix}sro_themehashes`.`themeId` = `{$wpdb->prefix}sro_themes`.`id` ";
    $query .= "LEFT JOIN `{$wpdb->prefix}sro_queries` ON `{$wpdb->prefix}sro_queries`.`normalized` = `{$wpdb->prefix}sro_themehashes`.`normalized` ";
    $query .= $this->getWhere();
    $query .= "GROUP BY `{$wpdb->prefix}sro_themes`.`id`";
    $query .= $this->getQueryOrder();
    return $query;
  }
  
  /**
   * Returns the where condition for the table query.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return string
   */
  protected function getWhere() {
    global $wpdb;
    $where = ' WHERE 1=1 ';
    $searchString = filter_input(INPUT_POST, 's');
    if (!empty($searchString)) {
      $where .= "AND `{$wpdb->prefix}sro_themes`.`name` LIKE '%{$searchString}%' ";
    }
    return $where;
  }
  
  /**
   * Returns the bulk actions for the table.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function get_bulk_actions() {
    return array(
      'delete' => __('Delete' , 'searchresultsoptimizer')
    );
  }
  
  /**
   * Calls the delete for each requested theme.
   * 
   * @since 1.0
   * @access protected
   * @param array $records
   */
  protected function bulkDeleteAction(array $records) {
    foreach ($records as $id) {
      $theme = new \SearchResultsOptimizer\includes\classes\SROTheme($id);
      if ($theme instanceof \SearchResultsOptimizer\includes\abstracts\SROBase) {
        $theme->delete($theme->getDeleteNonce());
      }
    }
  }

}

endif;
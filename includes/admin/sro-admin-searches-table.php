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

if (!class_exists(__NAMESPACE__ . '\SROAdminSearchesTable')):

/**
 * Generates a table of Searches
 *
 * @package SearchResultsOptimizer
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminSearchesTable extends \SearchResultsOptimizer\includes\abstracts\SROListTable {
  
  /**
   * A cache of theme data to save on database calls.
   * 
   * @since 1.0
   * @access protected
   * @var array 
   */
  protected $themes;
  
  /**
   * Constructor.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    parent::__construct(array(
      'singular' => 'sro-search',
      'plural' => 'sro-searches',
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
      'query' => __('Search Phrase', 'searchresultsoptimizer'),
      'themes' => __('Search Themes', 'searchresultsoptimizer'),
      'pinned' => __('Pinned Post', 'searchresultsoptimizer'),
      'normalized' => __('Normalized', 'searchresultsoptimizer'),
      'count' => __('Times Searched', 'searchresultsoptimizer')
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
        'id',
        'normalized'
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
      'themes' => array('themes', true),
      'query' => array('query', false),
      'count' => array('count', false),
      'pinned' => array('pinned', false),
      'normalized' => array('normalized', false)
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
    $search = new \SearchResultsOptimizer\includes\classes\SROSearch($id);
    return $search->getDeleteNonce();
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
   * Returns the SQL for the table data.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return string
   */
  protected function getQuery() {
    global $wpdb;
    $query = "SELECT `{$wpdb->prefix}sro_queries`.*, COUNT(`{$wpdb->prefix}sro_themehashes`.`themeId`) AS 'themes', `{$wpdb->prefix}posts`.`post_title` AS 'pinned' FROM `{$wpdb->prefix}sro_queries` ";
    $query .= "LEFT JOIN `{$wpdb->prefix}sro_themehashes` ON `{$wpdb->prefix}sro_themehashes`.`normalized` = `{$wpdb->prefix}sro_queries`.`normalized`";
    $query .= "LEFT JOIN `{$wpdb->prefix}sro_themes` ON `{$wpdb->prefix}sro_themes`.`id` = `{$wpdb->prefix}sro_themehashes`.`themeId`";
    $query .= "LEFT JOIN `{$wpdb->prefix}posts` ON `{$wpdb->prefix}sro_queries`.`pinnedPostId` = `{$wpdb->prefix}posts`.`ID`";
    $query .= $this->getWhere();
    $query .= "GROUP BY `{$wpdb->prefix}sro_queries`.`id`";
    $query .= $this->getQueryOrder("`{$wpdb->prefix}sro_queries`.`normalized`, `{$wpdb->prefix}sro_themes`.`name` ASC");
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
    $hash = filter_input(INPUT_GET, 'hash');
    $searchString = filter_input(INPUT_POST, 's');
    if (!empty($hash)) {
      $where .= "AND `{$wpdb->prefix}sro_queries`.`normalized` = '{$hash}' ";
    }
    if (!empty($searchString)) {
      $where .= "AND `{$wpdb->prefix}sro_queries`.`query` LIKE '%{$searchString}%' ";
    }
    return $where;
  }
  
  /**
   * Returns formatted value for the themes column.
   * 
   * @since 1.0
   * @access public
   * @param stdClass $item
   * @return string
   */
  public function column_themes($item) {
    $ouput = "<div class='tagchecklist sro'>";
    $addText = __('Add a theme', 'searchresultsoptimizer');
    $addAnotherText = __('Add another theme', 'searchresultsoptimizer');
    $themes = $this->searches[$item->id]->getThemes();
    if (!empty($themes)) {
      foreach ($themes as $theme) {
        $ouput .= "<span><a data-id='{$theme->id}' data-nonce='{$theme->getUpdateNonce()}' data-hash='{$item->normalized}' class='sro-unlink-search ntdelbutton'>X</a>&nbsp;{$theme->name}</span>";
      }
      $addText = $addAnotherText;
    }
    $theme = new \SearchResultsOptimizer\includes\classes\SROTheme();
    $ouput .= "</div>";
    $ouput .= "<div class='row-actions'><span class='add'><a class='sro add-theme' data-nonce='{$theme->getAddNonce()}' data-hash='{$item->normalized}' data-alternate='{$addAnotherText}'>{$addText}</a></span></div>";
    return $ouput;
  }
  
  /**
   * Returns formatted value for the query column.
   * 
   * @since 1.0
   * @access public
   * @param stdClass $item
   * @return string
   */
  public function column_query($item) {
    if ($item instanceof \stdClass) {
      if (empty($this->searches[$item->id])) {
        $this->searches[$item->id] = new \SearchResultsOptimizer\includes\classes\SROSearch($item->id);
      }
      $actions = $this->getRowActions($item);
      return sprintf('%1$s %2$s', $item->query, $this->row_actions($actions));
    } else {
      throw new \Exception('Cannot create action links from ' . gettype($item));
    }
  }
  
  /**
   * Returns an array of row actions, overrides parent.
   * 
   * @since 1.0
   * @access protected
   * @param \stdClass $item
   * @return array
   */
  protected function getRowActions(\stdClass $item) {
    $page = \filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
    return array(
      'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Edit','searchresultsoptimizer') . '</a>',$page,'edit',$item->id),
      'similar' => sprintf('<a href="?page=%s&action=%s&hash=%s">' . __('Similar Searches','searchresultsoptimizer') . '</a>',$page,'similar',$item->normalized),
      'delete' => sprintf('<a href="?page=%s&action=%s&nonce=%s&id=%s">' . __('Delete','searchresultsoptimizer') . '</a>',$page,'delete',$this->searches[$item->id]->getDeleteNonce(),$item->id),
    );
  }
  
  /**
   * Returns the themes associated with the $normalizedHash param.
   * @global $wpdb
   * @param string $normalizedHash
   * @return array
   */
  protected function getThemes($normalizedHash) {
    global $wpdb;
    if (!isset($this->themes[$normalizedHash])) {
      $this->themes[$normalizedHash] = array();
      $sql = $wpdb->prepare("SELECT `{$wpdb->prefix}sro_themes`.`id` FROM `{$wpdb->prefix}sro_themes` JOIN `{$wpdb->prefix}sro_themehashes` ON `{$wpdb->prefix}sro_themes`.`id` = `{$wpdb->prefix}sro_themehashes`.`themeId` WHERE `{$wpdb->prefix}sro_themehashes`.`normalized` = '%s'", $normalizedHash);
      $themes = $wpdb->get_results($sql);
      foreach ($themes as $theme) {
        $this->themes[$normalizedHash][] = new \SearchResultsOptimizer\includes\classes\SROTheme($theme->id);
      }
    }
    return $this->themes[$normalizedHash];
  }
  
  /**
   * Calls the delete for each requested search.
   * 
   * @since 1.0
   * @access protected
   * @param array $records
   */
  protected function bulkDeleteAction(array $records) {
    foreach ($records as $id) {
      $search = new \SearchResultsOptimizer\includes\classes\SROSearch($id);
      if ($search instanceof \SearchResultsOptimizer\includes\abstracts\SROBase) {
        $search->delete($search->getDeleteNonce());
      }
    }
  }
}

endif;
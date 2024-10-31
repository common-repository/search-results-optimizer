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

namespace SearchResultsOptimizer\includes\abstracts;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROListTable')):

/**
 * Extends the WP_List_Table from Core, providing a way to generate admin tables.
 * 
 * @package SearchResultsOptimizer
 * @class SROListTable
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
abstract class SROListTable extends \SearchResultsOptimizer\includes\abstracts\WPListTable {
  /**
   * The data for the table
   * 
   * @since 1.0
   * @access public
   * @var array 
   */
  public $items;
  
  /**
   * The default number of rows to show per page
   * 
   * @since 1.0
   * @access public
   * @var integer 
   */
  public $perPage = 10;

  /**
   * Should provide SQL query to return table data.
   * 
   * @since 1.0
   * @access protected
   */
  abstract protected function getQuery();
  
  /**
   * Should return a delete nonce for the child
   * 
   * @since 1.0
   * @access protected
   */
  abstract protected function getDeleteNonce($id);
  
  /**
   * Should delete child instances with IDs in $records
   * 
   * @since 1.0
   * @access protected
   * @param array $records an array of integers
   */
  abstract protected function bulkDeleteAction(array $records);
  
  /**
   * Constructor. The child class should call this constructor from its own constructor
   * 
   * @since 1.0
   * @access public
   */
  public function __construct($args) {
    parent::__construct($args);
  }
  
  /**
   * Adds "sro" class to tables constructed with this.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function get_table_classes() {
    return array_merge(parent::get_table_classes(), array('sro'));
  }
  
  /**
   * Returns action links for rows, such as 'edit' and 'delete'.
   * 
   * @since 1.0
   * @access public
   * @param stdClass $item
   * @return string
   */
  public function column_name(\stdClass $item) {
    $page = \filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
    $actions = array(
        'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Edit','searchresultsoptimizer') . '</a>',$page,'edit',$item->id),
        'delete' => sprintf('<a href="?page=%s&action=%s&nonce=%s&id=%s">' . __('Delete','searchresultsoptimizer') . '</a>',$page,'delete',$this->getDeleteNonce($item->id),$item->id),
    );
    return sprintf('%1$s %2$s', $item->name, $this->row_actions($actions));
  }
  
  /**
   * The default formatter for column value output.
   * 
   * @param stdClass $item
   * @param string $column_name
   * @return string
   */
  public function column_default(\stdClass $item, $column_name) {
    return $item->$column_name;
  }
  
  /**
   * Returns a checkbox used to select a row.
   * 
   * @since 1.0
   * @access public
   * @param stdClass $item
   * @return string
   */
  public function column_cb(\stdClass $item) {
    return sprintf('<input type="checkbox" name="record[]" value="%s" />', $item->id);
  }
  
  /**
   * Fetch data, set table configuration.
   * 
   * @since 1.0
   * @access public
   * @global type $wpdb
   */
  public function prepare_items() {
    global $wpdb;
    $this->processBulkActions();
    $query = $this->getQuery();
    $totalitems = $wpdb->query($query);
    $query .= $this->configurePagination($totalitems);
    $this->configureColumns();
    $this->items = $wpdb->get_results($query);
  }
  
  /**
   * Process any bulk actions passed by the table.
   * 
   * @since 1.0
   * @access protected
   */
  protected function processBulkActions() {
    $action = \filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    $records = \filter_input(INPUT_POST, 'record', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
    $nonce = \filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
    if ($action && $records && \wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
      switch ($action) {
        case 'delete':
          $this->bulkDeleteAction($records);
          break;
      }
    }
  }
  
  /**
   * Sets table pagination and query limit.
   * 
   * @since 1.0
   * @access protected
   * @param integer $totalitems
   * @throws Exception
   * @return string
   */
  protected function configurePagination($totalitems) {
    if (is_numeric($totalitems)) {
      $paged = \filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT) ?: 1;
      $totalpages = ceil($totalitems/$this->perPage);
      $this->set_pagination_args(array(
        "total_items" => $totalitems,
        "total_pages" => $totalpages,
        "per_page" => $this->perPage,
      ));
      return $this->setQueryLimit($paged);
    } else {
      throw new \Exception('Cannot paginate with non-numeric value. Received ' . gettype($totalitems));
    }
  }
  
  /**
   * Returns query limit.
   * 
   * @since 1.0
   * @access protected
   * @param integer $paged
   * @return string queryLimit
   */
  protected function setQueryLimit($paged) {
    if (is_numeric($paged)) {
      $offset = ($paged - 1) * $this->perPage;
      return ' LIMIT ' . (int) $offset . ',' . (int) $this->perPage;
    }
    return '';
  }
  
  /**
   * Configures table columns.
   * 
   * @since 1.0
   * @access protected
   */
  protected function configureColumns() {
    $columns = $this->get_columns();
    $hidden = $this->get_hidden_columns();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
  }
  
  /**
   * Returns the query ordering for table data.
   * 
   * @since 1.0
   * @access protected
   * @param string $default
   * @return string
   */
  protected function getQueryOrder($default = null) {
    $queryOrder = '';
    $orderby = \filter_input(INPUT_GET, 'orderby', FILTER_SANITIZE_STRING) ?: 'ASC';
    $order = \filter_input(INPUT_GET, 'order') ?: '';
    if (!empty($orderby) & !empty($order)) {
      $queryOrder .= " ORDER BY {$orderby} {$order}";
    } elseif (isset($default)) {
      return " ORDER BY {$default}";
    }
    return $queryOrder;
  }
}

endif;
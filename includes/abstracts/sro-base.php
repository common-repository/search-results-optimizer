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

namespace SearchResultsOptimizer\includes\abstracts;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROBase')):

/**
 * The SRO object base class.
 *
 * @package SearchResultsOptimizer
 * @class SROBase
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
abstract class SROBase {
  protected $id;
  protected $dbTable;
  
  /**
   * Popular posts associated with the theme.
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $posts = array();
  
  /**
   * Should map the value provided by $record to class properties.
   * 
   * @since 1.0
   * @access protected
   * @param stdClass $record
   */
  abstract protected function assignValues(\stdClass $record);
  
  /**
   * Should return an array mapping columns to properties.
   * 
   * @since 1.0
   * @access protected
   */
  abstract protected function getColumnMap();
  
  /**
   * Should return an array of properties that __get can return.
   * 
   * @since 1.0
   * @access protected
   */
  abstract protected function getAccessibleProperties();
  
  /**
   * Constructor. Calls for initialisation from DB if $id provided.
   * 
   * @since 1.0
   * @access public
   * @global type $wpdb
   * @param type $id
   * @param type $dbTable the primary for the inheriting child class
   * @return type
   */
  public function __construct($id = null, $dbTable = null) {
    global $wpdb;
    $this->dbTable = $dbTable ? "`{$wpdb->prefix}{$dbTable}`" : '';
    if (!empty($id) && is_numeric($id)) {
      return $this->initFromId($id);
    }
  }
  
  /**
   * Magic method only returns explicitly stated properties.
   * 
   * @since 1.0
   * @access public
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    if (in_array($name, $this->getAccessibleProperties(), true)) {
      return $this->$name;
    }
  }
  
  /**
   * Magic method only permits explictly stated properties to be set.
   * 
   * @since 1.0
   * @access public
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value) {
    if (in_array($name, $this->getMutatablebleProperties(), true)) {
      $this->$name = $value;
    }
  }
  
  /**
   * Retrieve a record from the database and populate the object.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @param integer $id
   * @return boolean|this
   */
  protected function initFromId($id) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM {$this->dbTable} WHERE `id` = '%d'", $id);
    $record = $wpdb->get_row($sql);
    if (!empty($record)) {
      $this->assignValues($record);
      return $this;
    }
    return false;
  }
  
  /**
   * Returns an array of properties that can be set by __set.
   * 
   * @since 1.0
   * @access protected
   * @return array
   */
  protected function getMutatablebleProperties() {
    return \array_keys($this->getColumnMap());
  }

  /**
   * Returns a nonce for adding a child type.
   * 
   * @since 1.0
   * @access public
   * @return string
   */
  public function getAddNonce() {
    return \wp_create_nonce("sro_" . \get_class($this) . "_add");
  }
  
  /**
   * Verifies a nonce for adding a child type.
   * 
   * @since 1.0
   * @access public
   * @param string $nonce
   * @return bool
   */
  public function verifyAddNonce($nonce) {
    return (bool) \wp_verify_nonce($nonce, "sro_" . \get_class($this) . "_add");
  }
  
  /**
   * Returns a nonce for updating a child type.
   * 
   * @since 1.0
   * @access public
   * @return string
   */
  public function getUpdateNonce() {
    return \wp_create_nonce("sro_" . \get_class($this) . "_update-" . $this->id);
  }
  
  /**
   * Verifies a nonce for updating a child type.
   * 
   * @since 1.0
   * @access public
   * @param string $nonce
   * @return bool
   */
  public function verifyUpdateNonce($nonce) {
    return \wp_verify_nonce($nonce, "sro_" . \get_class($this) . "_update-" . $this->id);
  }
  
  /**
   * Returns a nonce for deleting a child type.
   * 
   * @since 1.0
   * @access public
   * @return string
   */
  public function getDeleteNonce() {
    return \wp_create_nonce("sro_" . \get_class($this) . "_delete-" . $this->id);
  }
  
  /**
   * Verifies a nonce for deleting a child type.
   * 
   * @since 1.0
   * @access public
   * @param string $nonce
   * @return bool
   */
  public function verifyDeleteNonce($nonce) {
    return \wp_verify_nonce($nonce, "sro_" . \get_class($this) . "_delete-" . $this->id);
  }
  
  /**
   * Adds or updates the child type to the database.
   * 
   * @since 1.0
   * @access public
   * @global $wpdb
   * @param string $nonce
   */
  public function save($nonce) {
    if ($this->verifyUpdateNonce($nonce)) {
      global $wpdb;
      if ($this->id) {
        $args = array_merge(array_diff_key($this->getColumnMap(), array_filter($this->getColumnMap(), 'is_null')), array($this->id));
        $setters = $this->getSetters();
        $sql = $wpdb->prepare("UPDATE {$this->dbTable} SET {$setters} WHERE `id` = %d;", $args);
      } else {
        $sql = $wpdb->prepare("INSERT INTO {$this->dbTable} (`" . \implode('`,`', array_keys($this->getColumnMap())) . "`) VALUES (" . \implode(', ', \array_fill(0, count($this->getColumnMap()), '%s')) . ");", $this->getColumnMap());
      }
      $result = $wpdb->query($sql);
      if (!$this->id && $wpdb->insert_id) {
        $this->id = $wpdb->insert_id;
      }
      $messageType = false === $result ? 'error' : 'updated';
      \SearchResultsOptimizer\SearchResultsOptimizer::$messages[$messageType][] = substr(strstr(get_class($this),'SRO'),3) . ' ' . ('updated' === $messageType ? '' : __('not','searchresultsoptimizer') . ' ') . __('updated','searchresultsoptimizer');
    } else {
      $messageType = 'error';
      \SearchResultsOptimizer\SearchResultsOptimizer::$messages[$messageType][] = substr(strstr(get_class($this),'SRO'),3) . ' ' . ('updated' === $messageType ? '' : __('not','searchresultsoptimizer') . ' ') . __('updated','searchresultsoptimizer');
    }
  }
  
  /**
   * Returns a formatted set statement for updates.
   * 
   * @since 1.0
   * @access protected
   * @return string
   */
  protected function getSetters() {
    $setters = array();
    foreach ($this->getColumnMap() as $name => $arg) {
      switch (\gettype($arg)) {
        case 'integer':
          $placeholder = '%d';
          break;
        case 'NULL':
          $placeholder = 'NULL';
          break;
        default:
          $placeholder = '%s';
      }
      $setters[] = "`{$name}` = {$placeholder}";
    }
    return \implode(',', $setters);
  }
  
  /**
   * Deletes a child type from the database.
   * 
   * @global $wpdb
   * @param string $nonce
   * @return boolean
   */
  public function delete($nonce) {
    if ($this->verifyDeleteNonce($nonce)) {
      global $wpdb;
      $sql = $wpdb->prepare("DELETE FROM {$this->dbTable} WHERE `id` = '%d' LIMIT 1;", $this->id);
      $result = $wpdb->query($sql);
      $messageType = false === $result ? 'error' : 'updated';
      \SearchResultsOptimizer\SearchResultsOptimizer::$messages[$messageType][] = substr(strstr(get_class($this),'SRO'),3) . ' ' . ('updated' === $messageType ? '' : __('not','searchresultsoptimizer') . ' ') . __('deleted','searchresultsoptimizer');
      return 'success' === $result ? true : false;
    }
    return false;
  }
  
  /**
   * Returns an array of default WP_Query args for retrieving posts.
   * 
   * @since 1.0
   * @access protected
   * @param array $postIds
   * @return array
   */
  protected function getPostSelectLogic(array $postIds = null, $postsPerPage = 10) {
    return array(
      'post__in' => $postIds,
      'orderby' => 'post__in',
      'post_status' => 'publish',
      'post_type' => 'any',
      'ignore_sticky_posts' => true,
      'posts_per_page' => $postsPerPage
    );
  }

  /**
   * Returns an array of WP_Post, ordered by popularity.
   * 
   * @since 1.0
   * @access public
   * @param string $selector The column to select on
   * @return array
   */
  public function getPopularPosts($selector = 'normalized') {
    $posts = $this->getPriorityPosts($selector);
    if (!empty($this->posts)) {
      $postObjects = get_posts($this->getPostSelectLogic(array_keys($posts)));
      foreach ($postObjects as $post) {
        $post->sroViews = \number_format((int)$posts[$post->ID]['views']);
        $post->sroClicks = \number_format((int)$posts[$post->ID]['clicks']);
        $post->sroCtr = ($posts[$post->ID]['ctr'] ? \number_format($posts[$post->ID]['ctr'], 2) : 0) . '%';
        $post->pinned = $posts[$post->ID]['pinned'];
        $this->posts[$post->ID] = $post;
      }
    }
    return $this->posts;
  }
  
  /**
   * Implementation should retrieves an array of prioritised posts with stats.
   * 
   * @since 1.0
   * @access protected
   * @return array
   */
  protected abstract function fetchPriorityPosts();
  
  /**
   * Returns an array of prioritised posts.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function getPriorityPosts($selector = 'normalized') {
    if (empty($this->posts)) {
      $this->fetchPriorityPosts($selector);
    }
    return $this->posts;
  }

}

endif;
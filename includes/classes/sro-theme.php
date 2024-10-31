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

namespace SearchResultsOptimizer\includes\classes;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROTheme')):

/**
 * Search theme, used to link hashes
 *
 * @package SearchResultsOptimizer
 * @class SROTheme
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROTheme extends \SearchResultsOptimizer\includes\abstracts\SROBase {
  /**
   * Id of the theme.
   * 
   * @since 1.0
   * @access protected
   * @var integer
   */
  protected $id;
  
  /**
   * Name of the theme.
   * 
   * @since 1.0
   * @access protected
   * @var string
   */
  protected $name = '';
  
  /**
   * Normalized hashes associated with the theme.
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $linkedHashes = array();
  
  /**
   * Searches associated with the theme.
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $linkedSearches = array();

  /**
   * Constructor. Calls the parent constructor for initialisation.
   * 
   * @since 1.0
   * @access public
   * @param integer $id
   * @param boolean $searches
   */
  public function __construct($id = null, $name = null, $searches = false) {
    $loaded = parent::__construct($id, 'sro_themes');
    if ((true === $searches) && $loaded) {
      $this->fetchSearches();
    }
    if (!$loaded && !empty($name)) {
      $this->initFromName($name);
    }
  }
  
  /**
   * Retrieve a record from the database and populate the object.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @param string $name
   * @return boolean|this
   */
  protected function initFromName($name) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM {$this->dbTable} WHERE `name` = '%s'", $name);
    $record = $wpdb->get_row($sql);
    if (!empty($record)) {
      $this->assignValues($record);
      return $this;
    }
    return false;
  }
  
  /**
   * Returns an array of properties that __get can provide.
   * 
   * @since 1.0
   * @access protected
   * @return array
   */
  protected function getAccessibleProperties() {
    return array('id', 'name', 'linkedHashes');
  }
  
  /**
   * Returns an arrat of properties tat __set can modify.
   * 
   * @since 1.0
   * @access protected
   * @return array
   */
  protected function getMutatablebleProperties() {
    return array_keys($this->getColumnMap());
  }
  
  /**
   * Maps values provided by $record to class properties.
   * 
   * @since 1.0
   * @access protected
   * @param stdClass $record
   */
  protected function assignValues(\stdClass $record) {
    $this->id = $record->id;
    $this->name = $record->name;
  }
  
  /**
   * Returns a map of database column names to class properties.
   * 
   * @since 1.0
   * @access protected
   * @return type
   */
  protected function getColumnMap() {
    return array(
      'name' => $this->name
    );
  }
  
  /**
   * Deletes the theme from the database.
   * 
   * @global $wpdb
   * @param string $nonce
   * @return boolean
   */
  public function delete($nonce) {
    if (parent::delete($nonce)) {
      global $wpdb;
      $sql = $wpdb->prepare("DELETE FROM `{$wpdb->prefix}sro_themehashes` WHERE `themeId` = '%d'", $this->id);
      return $wpdb->query($sql);
    }
    return false;
  }
  
  /**
   * Retrieves associated normalization hashes from the database.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   */
  protected function fetchHashes() {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT `{$wpdb->prefix}sro_themehashes`.`normalized` FROM `{$wpdb->prefix}sro_themehashes` WHERE `themeId` = '%d'", $this->id);
    $hashes = $wpdb->get_results($sql);
    foreach ($hashes as $hash) {
      $this->linkedHashes[] = $hash->normalized;
    }
  }
  
  /**
   * Returns an array of associated hashes.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function getHashes() {
    return $this->linkedHashes;
  }
  
  /**
   * Retrieves associated searches from the database.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return type
   */
  protected function fetchSearches() {
    global $wpdb;
    if (empty($this->linkedHashes)) {
      $this->fetchHashes();
    }
    if (!empty($this->linkedHashes)) {
      $sql = $wpdb->prepare("SELECT `{$wpdb->prefix}sro_queries`.`id` FROM `{$wpdb->prefix}sro_queries` WHERE `normalized` IN (" . implode(', ', array_fill(0, count($this->linkedHashes), '%s')) . ")", $this->linkedHashes);
      $searchIds = $wpdb->get_results($sql);
      foreach ($searchIds as $searchId) {
        $this->linkedSearches[] = new \SearchResultsOptimizer\includes\classes\SROSearch($searchId->id);
      }
      return $this->linkedSearches;
    }
    return array();
  }
  
  /**
   * Stores a link between the theme and provided hash in the database.
   * 
   * @since 1.0
   * @access public
   * @global $wpdb
   * @param string $hash
   * @param string $nonce
   * @return boolean
   */
  public function linkSearch($hash, $nonce) {
    global $wpdb;
    if ($this->verifyUpdateNonce($nonce)) {
      $sql = $wpdb->prepare("INSERT INTO `{$wpdb->prefix}sro_themehashes` (`themeId`, `normalized`) VALUES('%d', '%s');", $this->id, $hash);
      if ($wpdb->query($sql)) {
        $this->linkedHashes[] = $hash;
        return true;
      }
    }
    return false;
  }
  
  /**
   * Removes the link between the theme and provided hash in the database.
   * 
   * @since 1.0
   * @access public
   * @global $wpdb
   * @param string $hash
   * @param string $nonce
   * @return boolean
   */
  public function unlinkSearch($hash, $nonce) {
    global $wpdb;
    if ($this->verifyUpdateNonce($nonce)) {
      $sql = $wpdb->prepare("DELETE FROM `{$wpdb->prefix}sro_themehashes` WHERE `themeId` = '%d' AND `normalized` = '%s' LIMIT 1;", $this->id, $hash);
      if ($wpdb->query($sql)) {
        $this->linkedHashes = array_diff($this->linkedHashes, array($hash));
        return true;
      }
    }
    return false;
  }
  
  /**
   * Returns associated searches.
   * 
   * @since 1.0
   * @access public
   * @return type
   */
  public function getSearches() {
    if (empty($this->linkedSearches)) {
      $this->fetchSearches();
    }
    return $this->linkedSearches;
  }
  
  /**
   * Retrieves an array of prioritised posts with stats.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return array
   */
  protected function fetchPriorityPosts() {
    global $wpdb;
    if (!empty($this->linkedHashes)) {
      $sql = $wpdb->prepare($this->getPostPrioritySQL(), $this->linkedHashes);
      $prioritisedPosts = $wpdb->get_results($sql);
      foreach ($prioritisedPosts as $post) {
        $this->posts[$post->postId] = array('views' => $post->views, 'clicks' => $post->clicks, 'ctr' => $post->CTR, 'pinned' => (bool) $post->pinned);
      }
    }
    return $this->posts;
  }
  
  /**
   * Returns the SQL for retreiving prioritised posts.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @param integer $limit
   * @return string
   */
  protected function getPostPrioritySQL($limit = 10) {
    global $wpdb;
    $placeholders = implode(', ', array_fill(0, count($this->linkedHashes), '%s'));
    return <<<EOT
SELECT 
  DISTINCT `{$wpdb->prefix}sro_results`.`postId`, 
  SUM(`{$wpdb->prefix}sro_results`.`count`) AS 'views',
  `clicks`.`count` AS 'clicks', 
  ROUND(((`clicks`.`count`/SUM(`{$wpdb->prefix}sro_results`.`count`))*100),2) AS 'CTR', 
  false AS 'pinned' 
FROM `{$wpdb->prefix}sro_queries` 
LEFT JOIN `{$wpdb->prefix}sro_themehashes` ON `{$wpdb->prefix}sro_themehashes`.`normalized` = `{$wpdb->prefix}sro_queries`.`normalized` 
LEFT JOIN `{$wpdb->prefix}sro_themes` ON `{$wpdb->prefix}sro_themes`.`id` = `{$wpdb->prefix}sro_themehashes`.`themeId` 
JOIN `{$wpdb->prefix}sro_results` 
  ON `{$wpdb->prefix}sro_results`.`queryId` = `{$wpdb->prefix}sro_queries`.`id` 
LEFT JOIN (
  SELECT `{$wpdb->prefix}sro_clicks`.`resultId`, COUNT(`{$wpdb->prefix}sro_clicks`.`id`) AS 'count' 
  FROM `{$wpdb->prefix}sro_clicks` 
  JOIN `{$wpdb->prefix}sro_results`
    ON `{$wpdb->prefix}sro_clicks`.`resultId` = `{$wpdb->prefix}sro_results`.`id`
  GROUP BY `{$wpdb->prefix}sro_clicks`.`resultId`
) as clicks
ON `clicks`.`resultId` = `{$wpdb->prefix}sro_results`.`id` 
WHERE `{$wpdb->prefix}sro_queries`.`normalized` IN ({$placeholders}) 
GROUP BY `{$wpdb->prefix}sro_results`.`postId` 
ORDER BY 
  `CTR` DESC
LIMIT 0,{$limit};
EOT;
  }
}

endif;
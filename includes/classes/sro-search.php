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

if (!class_exists(__NAMESPACE__ . '\SROSearch')):

/**
 * Search, a logged query from the search form.
 *
 * @package SearchResultsOptimizer
 * @class SROSearch
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROSearch extends \SearchResultsOptimizer\includes\abstracts\SROBase {
  /**
   * Id of the search.
   * 
   * @since 1.0
   * @access protected
   * @var integer
   */
  protected $id = null;
  
  /**
   * Search terms of the search.
   * 
   * @since 1.0
   * @access protected
   * @var string
   */
  protected $query = '';
  
  /**
   * Normalized hash of the search terms, md5 of query - stopwords.
   * 
   * @since 1.0
   * @access protected
   * @var string
   */
  protected $normalized = '';
  
  /**
   * The number of times the search has been run.
   * 
   * @since 1.0
   * @access protected
   * @var integer
   */
  protected $count = 0;
  
  /**
   * The last time the search was performed.
   * 
   * @since 1.0
   * @access protected
   * @var string
   */
  protected $timestamp = null;
  
  /**
   * An array of associated themes.
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $linkedThemes = array();
  
  /**
   * An array of associated search results.
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $linkedResults = array();
  
  /**
   * An array of prioritised post ids.
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $prioritisedPosts = array();
  
  /**
   * A manually set post id that answers this search.
   * 
   * @since 1.0
   * @access protected
   * @var integer
   */
  protected $pinnedPostId = null;
  
  /**
   * An array of searches with the same normalized search hash
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $relatedSearches = array();

  /**
   * Constructor. Calls the parent constructor, setting dbTable.
   * 
   * @since 1.0
   * @access public
   * @param integer $id
   */
  public function __construct($id = null, $hash = null) {
    parent::__construct($id, 'sro_queries');
    if (!empty($hash)) {
      $this->initFromHash($hash);
    }
  }
  
  /**
   * Retrieve a record from the database and populate the object.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @param string $hash
   * @return \SearchResultsOptimizer\includes\classes\SROSearch|boolean
   */
  protected function initFromHash($hash) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM {$this->dbTable} WHERE `normalized` = '%s'", $hash);
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
    return array('id', 'query', 'normalized', 'pinnedPostId', 'count', 'timestamp');
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
    $this->query = $record->query;
    $this->normalized = $record->normalized;
    $this->pinnedPostId = $record->pinnedPostId;
    $this->count = $record->count;
    $this->timestamp = $record->timestamp;
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
      'query' => $this->query,
      'normalized' => $this->normalized,
      'pinnedPostId' => $this->pinnedPostId
    );
  }
  
  /**
   * Retrieves an array of themes associated with the search.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return array
   */
  protected function fetchThemes() {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT `{$wpdb->prefix}sro_themehashes`.`themeId` FROM `{$wpdb->prefix}sro_themehashes` JOIN `{$wpdb->prefix}sro_themes` ON `{$wpdb->prefix}sro_themes`.`id` = `{$wpdb->prefix}sro_themehashes`.`themeId` WHERE `{$wpdb->prefix}sro_themehashes`.`normalized` = '%s'", $this->normalized);
    $themeIds = $wpdb->get_results($sql);
    foreach ($themeIds as $themeId) {
      $this->linkedThemes[] = new \SearchResultsOptimizer\includes\classes\SROTheme($themeId->themeId);
    }
    return $this->linkedThemes;
  }
  
  /**
   * Returns an array of associated themes.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function getThemes() {
    if (empty($this->linkedThemes)) {
      $this->fetchThemes();
    }
    return $this->linkedThemes;
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
  protected function getPostPrioritySQL($selector, $limit = 5) {
    global $wpdb;
    if ('1' === \get_option('searchresultsoptimizer_result_pinning_enabled')) {
      $pinned = '`pinned` DESC, ';
    } else {
      $pinned = '';
    }
    switch (\gettype($this->$selector)) {
      case 'integer':
        $condition = "WHERE `{$wpdb->prefix}sro_queries`.`{$selector}` = '%d'";
        break;
      case 'string':
        $condition = "WHERE `{$wpdb->prefix}sro_queries`.`{$selector}` = '%s'";
        break;
    }
    return <<<EOT
SELECT 
  DISTINCT `{$wpdb->prefix}sro_results`.`postId`, 
  SUM(`{$wpdb->prefix}sro_results`.`count`) AS 'views',
  `clicks`.`count` AS 'clicks', 
  ROUND(((`clicks`.`count`/SUM(`{$wpdb->prefix}sro_results`.`count`))*100),2) AS 'CTR', 
  IF (`{$wpdb->prefix}sro_results`.`postId` = `{$wpdb->prefix}sro_queries`.`pinnedPostId`, true, false) AS 'pinned' 
FROM `{$wpdb->prefix}sro_queries` 
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
{$condition} 
GROUP BY `{$wpdb->prefix}sro_results`.`postId` 
ORDER BY 
  {$pinned}`CTR` DESC
LIMIT 0,{$limit};
EOT;
  }
  
  /**
   * Retrieve the ids of prioritised posts.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return array
   */
  protected function fetchPriorityPosts($columnName = 'normalized') {
    global $wpdb;
    $sql = $wpdb->prepare($this->getPostPrioritySQL($columnName), $this->$columnName);
    $prioritisedPosts = $wpdb->get_results($sql);
    foreach ($prioritisedPosts as $post) {
      $this->posts[$post->postId] = array('views' => $post->views, 'clicks' => $post->clicks, 'ctr' => $post->CTR, 'pinned' => (bool) $post->pinned);
    }
    return $this->posts;
  }
  
  /**
   * Calls the parent method to delete the search and then deletes any theme association.
   * 
   * @since 1.0
   * @access public
   * @global $wpdb
   * @param string $nonce
   */
  public function delete($nonce) {
    if (parent::delete($nonce)) {
      global $wpdb;
      $sql = $wpdb->prepare("DELETE FROM `{$wpdb->prefix}sro_themehashes` WHERE `normalized` = '%s'", $this->normalized);
      $wpdb->query($sql);
    }
  }
  
  /**
   * Fetch related searches from the database.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @param integer $limit
   * @return array
   */
  protected function fetchRelatedSearches($limit) {
    global $wpdb;
    $sql = $wpdb->prepare("SELECT `{$wpdb->prefix}sro_queries`.`id` FROM `{$wpdb->prefix}sro_queries` JOIN `{$wpdb->prefix}sro_results` ON `{$wpdb->prefix}sro_queries`.`id` = `{$wpdb->prefix}sro_results`.`queryId` WHERE `{$wpdb->prefix}sro_queries`.`normalized` = %s AND `{$wpdb->prefix}sro_queries`.`id` <> %d ORDER BY `{$wpdb->prefix}sro_queries`.`count` DESC LIMIT %d", $this->normalized, $this->id, $limit);
    $searchIds = $wpdb->get_results($sql);
    foreach ($searchIds as $searchId) {
      $this->relatedSearches[] = new \SearchResultsOptimizer\includes\classes\SROSearch($searchId->id);
    }
    return $this->relatedSearches;
  }

  /**
   * Gets searches related to the current search.
   * 
   * @since 1.0
   * @access public
   * @param integer $limit
   * @return array
   */
  public function getRelatedSearches($limit = null) {
    if (empty($this->relatedSearches)) {
      $this->fetchRelatedSearches($limit);
    }
    return $this->relatedSearches;
  }
}

endif;
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

namespace SearchResultsOptimizer\includes;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROSearchBuilder')):

/**
 * Search builder modifies the WordPress search query to create the SRO result set.
 *
 * @package SearchResultsOptimizer
 * @class SROSearchBuilder
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROSearchBuilder {
  /**
   * The database identifier for the query.
   * 
   * @since 1.0
   * @access public
   * @var integer
   */
  public $queryId;
  
  /**
   * Whether or not the advanced form is enabled.
   * 
   * @since 1.0
   * @access public
   * @var boolean
   */
  public $advanced = false;
  
  /**
   * An array of words filters out during normalisation, populated from file.
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $filteredWords = array();

  /*
   * Constructor.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    $this->addHooks();
    $this->loadStopwords();
  }
  
  /**
   * Ensure enabled types can be searched and set advanced.
   * 
   * @since 1.0
   * @access public
   */
  public function init() {
    if (\is_search()) {
      $this->makeTypesQueryable();
      if ('1' === \get_option("searchresultsoptimizer_advanced_search_enabled")) {
        $this->advanced = true;
      }
    }
  }
  
  /**
   * Loads the filtered word array from file, based on site locale, defaults to en_GB.
   * 
   * @since 1.0
   * @access protected
   */
  protected function loadStopwords() {
    if (\is_search()) {
      $dir = __DIR__ . DIRECTORY_SEPARATOR . "stopwords" . DIRECTORY_SEPARATOR;
      $locale = \apply_filters('plugin_locale', \get_locale(), 'searchresultsoptimizer');
      $localisedStopwords = "{$dir}stopwords.{$locale}.php";
      if (\file_exists($localisedStopwords)) {
        include_once $localisedStopwords;
      } else {
        include_once "{$dir}stopwords.en_GB.php";
      }
      $this->filteredWords = $stopwords;
    }
  }
  
  /**
   * Makes enabled post types searchable.
   * 
   * @since 1.0
   * @access protected
   * @global array $wp_post_types
   */
  protected function makeTypesQueryable() {
    global $wp_post_types;
    foreach ($this->getEnabledTypes() as $name) {
      $wp_post_types[$name]->publicly_queryable = true;
    }
  }
  
  /**
   * Add WordPress hooks.
   * 
   * @since 1.0
   * @access protected
   */
  protected function addHooks() {
    \add_action('init', array($this, 'init'), 1);
    \add_action('pre_get_posts', array($this, 'buildQuery'));
    \add_action('loop_start', array($this, 'logResults'));
    \add_action('loop_start', array($this, 'hookPermalink'));
    \add_action('loop_end', array($this, 'unhookPermalink'));
    \add_filter('query_vars', array($this, 'registerQueryParam'));
    if ('1' === \get_option('searchresultsoptimizer_sorting_popularity')) {
      \add_filter('posts_orderby', array($this, 'addOrder'));
    }
  }
  
  /**
   * Checks settings and triggers query modification.
   * 
   * @since 1.0
   * @access public
   * @param WP_Query $query
   */
  public function buildQuery($query) {
    if (\is_search()) {
      if ($this->advanced) {
        $this->addTypes($query);
        if ('1' === \get_option('searchresultsoptimizer_metadata_filters_tags')) {
          $this->addTags($query);
        }
        if ('1' === \get_option('searchresultsoptimizer_metadata_filters_categories')) {
          $this->addCategories($query);
        }
      } elseif (empty($query->query_vars['post_types'])) {
        // The simple search form was used, set post types for the user
        $this->addTypes($query);
      }
      if ('/' === strtok($_SERVER["REQUEST_URI"],'?')) {
        $this->logQuery($query);
      }
    }
  }
  
  /**
   * Logs the query to the database.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @param WP_Query $query
   */
  protected function logQuery($query) {
    global $wpdb;
    if (\is_search() && !empty($query->query['s']) && !\is_paged()) {
      $filteredQuery = \preg_replace('/[^\w ]/ui', '', \strtolower($query->query['s']));
      if (!empty($filteredQuery)) {
        $normalizedSearch = $this->normalizeSearch($filteredQuery);
        $query->normalizedSearch = $normalizedSearch;
        $sql = $wpdb->prepare("INSERT INTO `{$wpdb->prefix}sro_queries` (`query`, `normalized`) VALUES('%s', '%s') ON DUPLICATE KEY UPDATE `count` = `count` + 1;", $filteredQuery, $normalizedSearch);
        $wpdb->query($sql);
        $this->queryId = $wpdb->insert_id;
      }
    } elseif (\is_search() && \is_paged() && !empty($query->query['s'])) {
      $filteredQuery = \preg_replace('/[^a-z0-9 ]/i', '', \strtolower($query->query['s']));
      $normalizedSearch = $this->normalizeSearch($filteredQuery);
      $query->normalizedSearch = $normalizedSearch;
      $sql = $wpdb->prepare("SELECT `id` FROM `{$wpdb->prefix}sro_queries` WHERE `query` = '%s' AND `normalized` = '%s';", $filteredQuery, $normalizedSearch);
      $row = $wpdb->get_row($sql);
      $this->queryId = $row->id;
    }
  }
  
  /**
   * Logs the results displayed for the query.
   * 
   * @since 1.0
   * @access public
   * @global $wpdb
   * @param WP_Query $query
   */
  public function logResults($query) {
    global $wpdb, $wp_the_query;
    if (\is_search() && !empty($wp_the_query->posts) && !empty($this->queryId)) {
      $ids = $placeholders  = array();
      foreach ($wp_the_query->posts as $post) {
        \array_push($ids, $this->queryId, $post->ID);
        $placeholders[] = "('%d', '%d')";
      }
      $placeholderString = \implode(',', $placeholders);
      $sql = $wpdb->prepare("INSERT INTO `{$wpdb->prefix}sro_results` (`queryId`, `postId`) VALUES {$placeholderString} ON DUPLICATE KEY UPDATE `count` = `count` + 1;", $ids);
      $wpdb->query($sql);
    }
  }
  
  /**
   * Adds a filter for the loop permalinks.
   * 
   * @since 1.0
   * @access public
   * @global $wp_the_query
   * @param WP_Query $query
   */
  public function hookPermalink($query) {
    if (\is_search() && \in_the_loop()) {
      \add_filter('post_link', array($this, 'addQueryParam'));
      \add_filter('page_link', array($this, 'addQueryParam'));
    }
  }
  
  /**
   * Removes the filter for the loop permalinks to stop them affecting outside the loop.
   * 
   * @since 1.0
   * @access public
   * @param WP_Query $query
   */
  public function unhookPermalink($query) {
    \remove_filter('post_link', array($this, 'addQueryParam'));
    \remove_filter('page_link', array($this, 'addQueryParam'));
  }
  
  /**
   * Adds SRO query vars to WordPress permitted.
   * 
   * @since 1.0
   * @access public
   * @param array $vars
   * @return array
   */
  public function registerQueryParam($vars) {
    return \array_merge($vars, array('sro_p', 'sro_q', 'typelist', 'taglist', 'tags_combined', 'catlist', 'cats_combined'));
  }
  
  /**
   * Modifies the permalinks, adding tracking params.
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_query
   * @param string $url
   * @return string
   */
  public function addQueryParam($url) {
    global $wp_query;
    if ($wp_query->is_search) {
      return \add_query_arg(array('sro_p' => (((($wp_query->query_vars['paged'] ?: 1) - 1) * $wp_query->query_vars['posts_per_page']) + $wp_query->current_post), 'sro_q' => $this->queryId), $url);
    } else {
      return $url;
    }
  }
  
  /**
   * Normalises the search by removing stopwords, sorting and hashing.
   * 
   * @since 1.0
   * @access protected
   * @param string $filteredQuery
   * @return string
   */
  protected function normalizeSearch($filteredQuery) {
    $queryWords = \explode(' ', $filteredQuery);
    $filteredQueryWords = \array_diff($queryWords, $this->filteredWords);
    if (!empty($filteredQueryWords)) {
      \sort($filteredQueryWords, SORT_STRING);
      $normalizedHash = \hash('md5', \implode('', $filteredQueryWords));
    } else {
      $normalizedHash = \hash('md5', $filteredQuery);
    }
    return $normalizedHash;
  }
  
  /**
   * Returns searchable post types.
   * 
   * @since 1.0
   * @access public
   * @global array $wp_post_types
   * @return array
   */
  public function getEnabledTypes() {
    global $wp_post_types;
    $typesToSearch = array();
    foreach (\array_keys($wp_post_types) as $name) {
      if ('1' === \get_option("searchresultsoptimizer_post_type_{$name}")) {
        $typesToSearch[] = $name;
      }
    }
    return $typesToSearch;
  }
  
  /**
   * Return all of the tags.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function getEnabledTags() {
    $tags = \get_tags();
    $tagsToSearch = array();
    foreach ($tags as $tag) {
      $tagsToSearch[$tag->term_id] = $tag->name;
    }
    return $tagsToSearch;
  }
  
  /**
   * Return all of the categories.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  public function getEnabledCategories() {
    $categories = \get_categories();
    $categoriesToSearch = array();
    foreach ($categories as $category) {
      $categoriesToSearch[$category->term_id] = $category->name;
    }
    return $categoriesToSearch;
  }
  
  /**
   * Adds post type condition to query.
   * 
   * @since 1.0
   * @access protected
   * @param WP_Query $query
   */
  protected function addTypes($query) {
    if ($query->is_search) {
      if ('1' === \get_option('searchresultsoptimizer_metadata_filters_types')) {
        $typelist = \get_query_var('typelist');
        if (!empty($typelist)) {
          $types = array();
          foreach (\explode('|',$typelist) as $type) {
            $types[] = $type;
          }
          $query->set('post_type', \array_intersect($types,$this->getEnabledTypes()));
        }
      } else {
        $query->set('post_type', $this->getEnabledTypes());
      }
    }
  }
  
  /**
   * Returns whether the param was checked.
   * 
   * @global WP_Query $wp_the_query
   * @param string $name
   * @return boolean
   */
  public function typeWasChecked($name) {
    global $wp_the_query;
    if ($wp_the_query instanceof \WP_Query) {
      return \in_array($name, (array) $wp_the_query->query_vars['post_type']);
    }
  }
  
  /**
   * Adds tag condition to the query.
   * 
   * @since 1.0
   * @access protected
   * @param WP_Query $query
   */
  protected function addTags($query) {
    if ($query->is_search) {
      $taglist = \get_query_var('taglist');
      if (!empty($taglist)) {
        $tagIds = array();
        foreach (\explode('|',$taglist) as $tagId) {
          if (is_numeric($tagId)) {
            $tagIds[] = $tagId;
          }
        }
        if (\get_query_var('tags_combined')) {
          $query->set('tag__and', $tagIds);
        } else {
          $query->set('tag__in', $tagIds);
        }
      }
    }
  }
  
  /**
   * Returns whether the tag was checked.
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_the_query
   * @param integer $id
   * @return boolean
   */
  public function tagWasChecked($id) {
    global $wp_the_query;
    if ($wp_the_query instanceof \WP_Query) {
      return \in_array($id, (array) $wp_the_query->query_vars['tag__in']) || \in_array($id, (array) $wp_the_query->query_vars['tag__and']);
    }
  }
  
  /**
   * Returns whether or not the tag condition was AND or OR
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_the_query
   * @return boolean
   */
  public function tagWasRestricted() {
    global $wp_the_query;
    if ($wp_the_query instanceof \WP_Query) {
      return !empty($wp_the_query->query_vars['tag__and']);
    }
  }
  
  /**
   * Adds category condition to the query.
   * 
   * @since 1.0
   * @access protected
   * @param WP_Query $query
   */
  protected function addCategories($query) {
    if ($query->is_search) {
      $categoryList = \get_query_var('catlist');
      if (!empty($categoryList)) {
        $categoryIds = array();
        foreach (explode('|',$categoryList) as $categoryId) {
          if (is_numeric($categoryId)) {
            $categoryIds[] = $categoryId;
          }
        }
        if (\get_query_var('cats_combined')) {
          $query->set('category__and', $categoryIds);
        } else {
          $query->set('category__in', $categoryIds);
        }
      }
    }
  }
  
  /**
   * Returns whether the category was checked.
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_the_query
   * @param integer $id
   * @return boolean
   */
  public function categoryWasChecked($id) {
    global $wp_the_query;
    if ($wp_the_query instanceof \WP_Query) {
      return \in_array($id, (array) $wp_the_query->query_vars['category__in']) || \in_array($id, (array) $wp_the_query->query_vars['category__and']);
    }
  }
  
  /**
   * Returns whether or not the category condition was AND or OR
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_the_query
   * @return type
   */
  public function categoryWasRestricted() {
    global $wp_the_query;
    if ($wp_the_query instanceof \WP_Query) {
      return !empty($wp_the_query->query_vars['category__and']);
    }
  }
  
  /**
   * Gets the result priority.
   * 
   * @since 1.0
   * @access protected
   * @param string $normalizedHash
   * @return string|null
   */
  protected function getPriorityOrder($normalizedHash) {
    $search = new \SearchResultsOptimizer\includes\classes\SROSearch(null, $normalizedHash);
    $posts = $search->getPriorityPosts();
    if (!empty($posts)) {
      $order = "(CASE ";
      $priority = 1;
      foreach (\array_keys($posts) as $postId) {
        $order .= " WHEN `ID` = '{$postId}' THEN {$priority}";
        $priority++;
      }
      $order .= " ELSE 999999 END) ASC";
      return $order;
    }
    return null;
  }

  /**
   * Joins the SRO tables to the search query.
   * 
   * @since 1.0
   * @access public
   * @global $wpdb
   * @global WP_Query $wp_query
   * @return string
   */
  public function addJoin() {
    global $wpdb, $wp_query;
    if (($wp_query->is_search) && !empty($wp_query->query['s']) && !empty($wp_query->normalizedSearch)) {
      $join = " LEFT JOIN `{$wpdb->prefix}sro_queries` ON `{$wpdb->prefix}sro_queries`.`normalized` = '{$wp_query->normalizedSearch}' ";
      $join .= " LEFT JOIN `{$wpdb->prefix}sro_results` ON `{$wpdb->prefix}sro_results`.`queryId` = `{$wpdb->prefix}sro_queries`.`id`";
      $join .= " LEFT JOIN `{$wpdb->prefix}sro_clicks` ON `{$wpdb->prefix}sro_clicks`.`resultId` = `{$wpdb->prefix}sro_results`.`id`";
      return $join;
    }
    return '';
  }

  /**
   * Adds the ordering rules to the query.
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_query
   * @return string
   */
  public function addOrder($orderby_statement) {
    global $wp_query;
    if (($wp_query->is_search) && !empty($wp_query->query['s']) && !empty($wp_query->normalizedSearch)) {
      $order = array();
      $order[] = $this->getPriorityOrder($wp_query->normalizedSearch);
      $order[] = $this->getSecondaryOrder();
      $filteredOrder = \array_filter($order);
      return implode(',', $filteredOrder);
    }
    return $orderby_statement;
  }
  
  /**
   * Gets the secondary ordering, if set. Else WordPress' default.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return string|null
   */
  protected function getSecondaryOrder() {
    global $wpdb;
    switch (\get_option('searchresultsoptimizer_sorting_secondary')) {
      case 'datenew':
        return "`{$wpdb->prefix}posts`.`post_date` DESC";
      case 'dateold':
        return "`{$wpdb->prefix}posts`.`post_date` ASC";
      case 'modifiednew':
        return "`{$wpdb->prefix}posts`.`post_modified` DESC";
      case 'modifiedold':
        return "`{$wpdb->prefix}posts`.`post_modified` ASC";
      case 'title':
        return "`{$wpdb->prefix}posts`.`post_title` ASC";
      default:
        return null;
    }
  }
}

endif;
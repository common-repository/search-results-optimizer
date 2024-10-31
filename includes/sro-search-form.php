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

if (!class_exists(__NAMESPACE__ . '\SROSearchForm')):

/**
 * Provides the advanced search form and related searches.
 *
 * @package SearchResultsOptimizer
 * @class SROSearchForm
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROSearchForm {
  /**
   * The filename for the searchform
   * 
   * @since 1.0
   * @access protected
   * @var string
   */
  protected $template = 'searchform.php';

  /**
   * Constructor. Adds hooks.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    $this->addHooks();
  }
  
  /**
   * Add WordPress hooks for overriding search form.
   * 
   * @since 1.0
   * @access protected
   */
  protected function addHooks() {
    \add_action('loop_start', array($this, 'useTemplate'));
    \add_action('get_template_part_content', array($this, 'helpEmpty'));
    if ('1' === \get_option('searchresultsoptimizer_advanced_search_enabled')) {
      \add_filter('body_class', array($this, 'addClasses'));
    }
  }
  
  /**
   * Adds a body class to the seach results page.
   * 
   * @since 1.0
   * @access public
   * @param array $classes
   * @return array
   */
  public function addClasses($classes) {
    return \array_merge($classes, array('sro-advanced-search'));
  }
  
  /**
   * Hook for empty content template.
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_query
   * @param string $slug
   */
  public function helpEmpty($slug) {
    global $wp_query;
    if (\is_search() && $wp_query->found_posts === 0) {
      if ('content' === $slug) {
        \add_filter('get_search_form', array($this, 'useTemplate'));
      }
    }
  }
  
  /**
   * Overrides the searchform template and related searches on zero results.
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_the_query
   * @global \SearchResultsOptimizer\SearchResultsOptimizer $sroPlugin
   * @param WP_Query $query
   * @return string
   */
  public function useTemplate($query) {
    global $wp_the_query, $sroPlugin;
    $overridePath = TEMPLATEPATH . DIRECTORY_SEPARATOR . $sroPlugin->templateUrl . $this->template;
    $path = is_readable($overridePath) ? $overridePath : $sroPlugin->templatePath() . DIRECTORY_SEPARATOR . $this->template;
    if (\is_search() && \in_the_loop()) {
      include $path;
    } elseif (\is_search() && 0 === $wp_the_query->found_posts && 'string' === gettype($query)) {
      \remove_filter('get_search_form', array($this, 'useTemplate'));
      if (('1' === \get_option('searchresultsoptimizer_include_other_searches')) && isset($wp_the_query->normalizedSearch)) {
        $assitiveSearch = $this->getRelatedSearches($wp_the_query->normalizedSearch);
      } else {
        $assitiveSearch = false;
      }
      include $path;
      if ('1' === \get_option('searchresultsoptimizer_advanced_search_enabled')) {
        return '';
      }
    }
  }
  
  /**
   * Returns an array of searches related to the provided hash.
   * 
   * @since 1.0
   * @access protected
   * @param string $normalized
   * @param integer $limit
   * @return array
   */
  protected function getRelatedSearches($normalized, $limit = 5) {
    $searches = array();
    $search = new \SearchResultsOptimizer\includes\classes\SROSearch(null, $normalized);
    if ($search instanceof \SearchResultsOptimizer\includes\classes\SROSearch) {
      $searches = $search->getRelatedSearches($limit);
      $searches[] = $search;
    }
    return \array_filter($searches, array($this, 'excludeCurrentSearch'));
  }
  
  /**
   * Remove the current search from the related searches.
   * 
   * @since 1.0
   * @access protected
   * @global WP_Query $wp_the_query
   * @param \SearchResultsOptimizer\includes\classes\SROSearch $search
   * @return boolean
   */
  protected function excludeCurrentSearch(\SearchResultsOptimizer\includes\classes\SROSearch $search) {
    global $wp_the_query;
    if ($search->query === $wp_the_query->query_vars['s']) {
      return false;
    } else {
      return true;
    }
  }
  
}

endif;
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

if (!class_exists(__NAMESPACE__ . '\SROSearchResults')):

/**
 * Modifies the search results page.
 *
 * @package SearchResultsOptimizer
 * @class SROSearchResults
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROSearchResults {
  
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
   * Add WordPress hooks for click logging and highlighting.
   * 
   * @since 1.0
   * @access protected
   */
  protected function addHooks() {
    \add_action('the_post', array($this, 'logClick'));
    if ('1' === \get_option('searchresultsoptimizer_result_highlighting_enabled')) {
      \add_filter('the_title', array($this, 'highlightSearchTermsInTitle'));
      \add_filter('get_the_excerpt', array($this, 'highlightSearchTermsInExcerpt'));
    }
  }
  
  /**
   * Logs result clicks in the database.
   * 
   * @since 1.0
   * @access public
   * @global $wpdb
   * @global WP_Query $wp_the_query
   * @param $post
   */
  public function logClick($post) {
    global $wpdb, $wp_the_query;
    $resultPosition = \get_query_var('sro_p');
    $queryId = \get_query_var('sro_q');
    if (isset($wp_the_query->queried_object) && ($post === $wp_the_query->queried_object) && !empty($queryId)) {
      $sql = $wpdb->prepare("INSERT INTO `{$wpdb->prefix}sro_clicks` SET `position` = %d, `resultId` = (SELECT `id` FROM `{$wpdb->prefix}sro_results` WHERE `queryId` = '%d' AND `postId` = '%d');", $resultPosition, $queryId, $post->ID);
      $wpdb->query($sql);
      \remove_action('the_post', array($this, 'logClick'));
    }
  }
  
  /**
   * Wraps the search terms the $excerpt with span.
   * 
   * @since 1.0
   * @access protected
   * @global WP_Query $wp_the_query
   * @param string $excerpt
   * @return string|boolean
   */
  protected function highlightSearchTerms($excerpt) {
    global $wp_the_query;
    $highlightColour = \get_option('searchresultsoptimizer_result_highlighting_colour');
    $searchTerms = "/(" . \strtr($wp_the_query->query_vars['s'], ' ', '|') . ")/iu";
    $replacement = "<span style='background-color:{$highlightColour};' class='sro-highlight'>$1</span>";
    $count = 0;
    $highLightedExcerpt = \preg_replace($searchTerms, $replacement, $excerpt, -1, $count);
    return $count > 0 ? $highLightedExcerpt : false;
  }
  
  /**
   * Highlights search terms in the WordPress or SRO excerpt.
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_the_query
   * @param string $excerpt
   * @return string
   */
  public function highlightSearchTermsInExcerpt($excerpt) {
    global $wp_the_query;
    if ($wp_the_query->is_search && \in_the_loop()) {
      if (!empty($wp_the_query->query_vars['s'])) {
        return $this->highlightSearchTerms($excerpt) ?: $this->getBetterExcerpt($excerpt);
      }
    }
    return $excerpt;
  }
  
  /**
   * Highlights search terms in the title.
   * 
   * @since 1.0
   * @access public
   * @global WP_Query $wp_the_query
   * @param string $title
   * @return string
   */
  public function highlightSearchTermsInTitle($title) {
    global $wp_the_query;
    if ($wp_the_query->is_search && \in_the_loop()) {
      if (!empty($wp_the_query->query_vars['s'])) {
        $highlightColour = \get_option('searchresultsoptimizer_result_highlighting_colour') ?: '#efdd95';
        $searchTerms = "/(" . \strtr($wp_the_query->query_vars['s'], ' ', '|') . ")/iu";
        $replacement = "<span style='background-color:{$highlightColour};' class='sro-highlight'>$1</span>";
        return \preg_replace($searchTerms, $replacement, $title);
      }
    }
    return $title;
  }
  
  /**
   * Provides an excerpt with search terms visible, for highlighting.
   * 
   * @since 1.0
   * @access protected
   * @global WP_Query $wp_the_query
   * @param string $originalExcerpt
   * @return string
   */
  protected function getBetterExcerpt($originalExcerpt) {
    global $wp_the_query;
    $excerptLength = \strlen($originalExcerpt);
    $searchTerms = \explode(' ', $wp_the_query->query_vars['s']);
    $content = \strip_tags(\preg_replace("/\r|\n/", "", $wp_the_query->posts[$wp_the_query->current_post]->post_content));
    $start = \stripos($content, $searchTerms[0]);
    $realStart = \strrpos(\substr($content, 0, $start), '.') + 1;
    if (($start - $realStart) < $excerptLength) {
      $excerpt = \substr($content, $realStart, $excerptLength);
    } else {
      $excerpt = \substr($content, $start, $excerptLength);
    }
    return $this->highlightSearchTerms($excerpt) ?: $originalExcerpt;
  }
  
}

endif;
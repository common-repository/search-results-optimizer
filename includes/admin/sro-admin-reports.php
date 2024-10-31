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

if (!class_exists(__NAMESPACE__ . '\SROAdminReports')):

/**
 * Description of class-sro-admin-menus
 *
 * @package SearchResultsOptimizer
 * @class SROAdminReports
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
class SROAdminReports extends \SearchResultsOptimizer\includes\abstracts\SROAdminPage {
  
  /**
   * Returns an array of posts ordered by popularity.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return array
   */
  protected function getLastTenResults() {
    global $wpdb;
    $posts = $clicks = array();
    $sql = $wpdb->prepare("SELECT `{$wpdb->prefix}sro_queries`.`id`, `{$wpdb->prefix}sro_queries`.`query` FROM `{$wpdb->prefix}sro_queries` ORDER BY `{$wpdb->prefix}sro_queries`.`timestamp` DESC LIMIT %d", 1);
    $row = $wpdb->get_row($sql);
    if ($row instanceof \stdClass) {
      $sql = $wpdb->prepare($this->getLastTenSearchesLogic(), $row->id);
      $results = $wpdb->get_results($sql);
      if (!empty($results)) {
        $clickCounter = 0;
        foreach ($results as $click) {
          $clicks[$click->postId] = $click->position;
          $clickCounter += (int) isset($click->position);
        }
        echo "<h3>" . __('The last search was for','searchresultsoptimizer') . " &quot;{$row->query}&quot; " . __('and resulted in','searchresultsoptimizer') . " {$clickCounter} " . __('clicks','searchresultsoptimizer') . "</h3>";
        $posts = \get_posts(array('post_type' => 'any', 'orderby' => 'post__in', 'posts_per_page' => -1, 'post__in' => \array_keys($clicks)));
        foreach ($posts as $k => $post) {
          if (\is_object($post)) {
            $post->clicked_position = $clicks[$post->ID];
            $post->read_only = true;
            include __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sro-admin_search.php';
          }
        }
      }
    }
    return $posts;
  }
  
  /**
   * Returns the SQL for the last ten searches.
   * 
   * @since 1.0
   * @access protected
   * @global $wpdb
   * @return string
   */
  protected function getLastTenSearchesLogic() {
    global $wpdb;
    return <<<EOT
SELECT 
  DISTINCT `{$wpdb->prefix}sro_results`.`postId`,
  `{$wpdb->prefix}sro_clicks`.`position`
FROM `{$wpdb->prefix}sro_results`
JOIN `{$wpdb->prefix}sro_queries` ON `{$wpdb->prefix}sro_queries`.`id` = `{$wpdb->prefix}sro_results`.`queryId` 
LEFT JOIN `{$wpdb->prefix}sro_clicks` ON `{$wpdb->prefix}sro_clicks`.`resultId` = `{$wpdb->prefix}sro_results`.`id` 
WHERE `{$wpdb->prefix}sro_queries`.`id` = %s
  AND `{$wpdb->prefix}sro_results`.`timestamp` >= `{$wpdb->prefix}sro_queries`.`timestamp`
  AND (
    `{$wpdb->prefix}sro_clicks`.`timestamp` IS NULL 
    OR `{$wpdb->prefix}sro_clicks`.`timestamp` >= `{$wpdb->prefix}sro_results`.`timestamp`
  )
ORDER BY `{$wpdb->prefix}sro_results`.`timestamp` ASC, `{$wpdb->prefix}sro_clicks`.`timestamp` ASC 
LIMIT 10
EOT;
  }
}

endif;
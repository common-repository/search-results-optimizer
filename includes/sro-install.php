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

namespace SearchResultsOptimizer\includes;

if (!defined('ABSPATH')) { exit; }

if (!class_exists(__NAMESPACE__ . '\SROInstall')):

/**
 * Installs the Search Results Optimizer 
 *
 * @package SearchResultsOptimizer
 * @class SROInstall
 * @author Chris Gorvan (@chrisgorvan)
 * @since 1.0
 */
class SROInstall {
  
  /**
   * Multidimensional array of settings for each section
   * 
   * @since 1.0
   * @access protected
   * @var array
   */
  protected $settings;
  
  /**
   * Constructor. Registers the installer and sets configuration.
   * 
   * @since 1.0
   * @access public
   */
  public function __construct() {
    \register_activation_hook(
      dirname(__DIR__) . DIRECTORY_SEPARATOR . basename(dirname(__DIR__)) . '.php',
      array($this, 'install')
    );
    \register_deactivation_hook(
      dirname(__DIR__) . DIRECTORY_SEPARATOR . basename(dirname(__DIR__)) . '.php',
      array($this, 'deactivate')
    );
    $configuration = new \SearchResultsOptimizer\includes\admin\SROAdminSettings();
    $this->settings = $configuration->settings;
    \add_action('admin_init', array($this, 'init'));
  }
  
  /**
   * Registers plugin settings, initialises each section.
   * 
   * @since 1.0
   * @access public
   */
  public function init() {
    \register_setting('searchresultsoptimizer_fields', 'searchresultsoptimizer_options', array($this, 'validateSettings'));
    foreach ($this->settings as $section) {
      if ($section instanceof \SearchResultsOptimizer\includes\abstracts\SROAdminSettings) {
        $section->init();
      }
    }
  }
  
  /**
   * Trims submitted config values.
   * 
   * @since 1.0
   * @access public
   * @param mixed $input
   * @return mixed
   */
  public function validateSettings($input) {
    return trim($input);
  }
  
  /**
   * The install process creates tables and options.
   * 
   * @since 1.0
   * @access public
   */
  public function install() {
    $this->createOptions();
    $this->createTables();
    $this->upgrade();
  }
  
  /**
   * Uninstallation triggers truncate of SRO data
   * 
   * @since 1.0
   * @access public
   */
  public function uninstall() {
    if (!\current_user_can('activate_plugins')) {
      return;
    }
    $this->truncateTables();
  }
  
  /**
   * Ensures deactivation by permitted user only.
   * 
   * @since 1.0
   * @access public
   */
  public function deactivate() {
    if (!\current_user_can('activate_plugins')) {
      return;
    }
  }
  
  /**
   * The upgrade process parses versioned upgrade scripts
   * 
   * @since 1.0
   * @access public
   */
  public function upgrade() {
    $currentVersion = \get_option('searchresultsoptimizer_version');

    if (version_compare($currentVersion, '1.0', '<')) {
      include('updates/searchresultsoptimizer-update-1.0.php');
      update_option('searchresultsoptimizer_version', '1.0');
    }

    \update_option('searchresultsoptimizer_version', \SearchResultsOptimizer\SearchResultsOptimizer::VERSION);
  }
  
  /**
   * Creates the necessary database tables.
   * 
   * @since 1.0
   * @access private
   * @global wpdb $wpdb
   */
  private function createTables() {
    global $wpdb;
    $wpdb->hide_errors();
    $collate = '';
    if ($wpdb->has_cap('collation')) {
      if(!empty($wpdb->charset)) {
        $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
      }
      if(!empty($wpdb->collate)) {
        $collate .= " COLLATE $wpdb->collate";
      }
    }
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sroTables = <<<EOT
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sro_queries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `query` VARCHAR(200) NOT NULL,
  `normalized` VARCHAR(32) NOT NULL,
  `pinnedPostId` INT UNSIGNED NULL,
  `count` INT UNSIGNED NOT NULL DEFAULT 1,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `query_UNIQUE` (`query` ASC),
  INDEX `normalized_INDEX` (`normalized` ASC),
  FULLTEXT INDEX `query_FULLTEXT` (`query` ASC)
) ENGINE = MyISAM
{$collate};

EOT;
    dbDelta($sroTables);
    $sroTables = <<<EOT
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sro_themes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC)
) ENGINE = MyISAM
{$collate};

EOT;
    dbDelta($sroTables);
    $sroTables = <<<EOT
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sro_themehashes` (
  `themeId` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `normalized` VARCHAR(44) NOT NULL,
  PRIMARY KEY (`themeId` ASC, `normalized` ASC), 
  INDEX `themeId_INDEX` (`themeId` ASC)
) ENGINE = MyISAM
{$collate};

EOT;
    dbDelta($sroTables);
    $sroTables = <<<EOT
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sro_results` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `queryId` int(10) unsigned NOT NULL,
  `postId` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `queryPostId` (`queryId`,`postId`),
  KEY `queryId` (`queryId`)
) ENGINE=MyISAM
{$collate};

EOT;
    dbDelta($sroTables);
    $sroTables = <<<EOT
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sro_clicks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `resultId` INT UNSIGNED NOT NULL,
  `position` INT UNSIGNED NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `resultId` (`resultId` ASC)
) ENGINE = MyISAM
{$collate};
      
EOT;
    dbDelta($sroTables);
  }
  
  /**
   * Truncate the SRO tables, as data is no longer needed.
   * 
   * @since 1.0
   * @access private
   * @global $wpdb
   */
  private function truncateTables() {
    global $wpdb;
    $wpdb->hide_errors();
    foreach (array("`{$wpdb->prefix}sro_clicks`", "`{$wpdb->prefix}sro_results`", "`{$wpdb->prefix}sro_themehashes`", "`{$wpdb->prefix}sro_queries`", "`{$wpdb->prefix}sro_themes`") as $table) {
      $wpdb->query("TRUNCATE TABLE {$table};");
    }
  }
  
  /**
   * Add the default config values.
   * 
   * @since 1.0
   * @access public
   */
  public function createOptions() {
    \add_option('searchresultsoptimizer_version', \SearchResultsOptimizer\SearchResultsOptimizer::VERSION, '', 'no');
    
    foreach ($this->settings as $section) {
      if ($section instanceof \SearchResultsOptimizer\includes\abstracts\SROAdminSettings) {
        foreach ($section->getSettings() as $value) {
          if ('fieldset' === $value['type']) {
            foreach ($value['values'] as $subvalue) {
              \register_setting($section->id, $subvalue['id'], array($section, 'validate'));
              $autoload = isset($subvalue['autoload']) ? (bool) $subvalue['autoload'] : true;
              \add_option($subvalue['id'], $subvalue['default'], '', ($autoload ? 'yes' : 'no'));
            }
          } elseif (isset($value['default']) && isset($value['id'])) {
            \register_setting($section->id, $value['id'], array($section, 'validate'));
            $autoload = isset($value['autoload']) ? (bool) $value['autoload'] : true;
            \add_option($value['id'], $value['default'], '', ($autoload ? 'yes' : 'no'));
          }
        }
      }
    }
  }
}

endif;
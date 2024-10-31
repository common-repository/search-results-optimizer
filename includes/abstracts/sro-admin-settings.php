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

if (!class_exists(__NAMESPACE__ . '\SROAdminSettings')):

/**
 * A settings section.
 *
 * @package SearchResultsOptimizer
 * @class SROAdminSettings
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
abstract class SROAdminSettings {
  /**
   * The id for the settings section
   * 
   * @since 1.0
   * @access public
   * @var string
   */
  public $id;
  
  /**
   * The title for the settings section
   * 
   * @since 1.0
   * @access public
   * @var string 
   */
  public $label;
  
  /**
   * Child classes should return a multidimensional array of form elements.
   * Supported keys are title, desc, id, type, and default.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  abstract public function getSettings();
  
  /**
   * Initialises the settings, creating and iterating over each section.
   * 
   * @since 1.0
   * @access public
   */
  public function init() {
    add_settings_section($this->id, $this->label, array($this, 'outputText'), 'searchresultsoptimizer_settings');
    try {
      foreach ($this->getSettings() as $value) {
        $value = $this->registerField($value);
      }
    } catch(\Exception $exception) {
      \SearchResultsOptimizer\SearchResultsOptimizer::$messages[] = $exception->getMessage();
    }
  }
  
  /**
   * Registers a settings field.
   * 
   * @since 1.0
   * @access protected
   * @param array $value An individual element
   * @param boolean $deferRender Whether or not to render just in time
   */
  protected function registerField($value, $deferRender = false) {
    if ('fieldset' != $value['type']) {
      if (!$deferRender) {
        add_settings_field($value['id'], $value['title'], array($this, "render{$value['type']}"), 'searchresultsoptimizer_settings', $this->id, $value);
      }
      register_setting('searchresultsoptimizer_fields', $value['id'], array($this, 'validate'));
    } else {
      add_settings_field($value['id'], $value['title'], array($this, "render{$value['type']}"), 'searchresultsoptimizer_settings', $this->id, $value);
      foreach ($value['values'] as $nestedValue) {
        $this->registerField($nestedValue, true);
      }
    }
  }
  
  /**
   * Returns the section label.
   * 
   * @since 1.0
   * @access public
   * @return string The html formatted label
   */
  public function outputText() {
    return "<p>{$this->label}</p>";
  }
  
  /**
   * Default setting validation.
   * 
   * @since 1.0
   * @access public
   * @param mixed $input
   * @return mixed
   */
  public function validate($input) {
    return $input;
  }
  
  /**
   * Outputs an html input of type checkbox.
   * 
   * @since 1.0
   * @access public
   * @param array $field
   */
  public function renderCheckbox(array $field) {
    $value = ('1' === \get_option($field['id'])) ? "checked='checked'" : '';
    $description = $field['desc'] ?: '';
    $disabled = !empty($field['requires']) ? ('1' !== \get_option($field['requires']) ? ' disabled="disabled"' : '') : '';
    echo "<label for='{$field['id']}'><input type='checkbox' name='{$field['id']}' value='1' id='{$field['id']}' {$value} {$disabled}>{$description}</label>";
  }
  
  /**
   * Outputs an html input of type text.
   * 
   * @since 1.0
   * @access public
   * @param array $field
   */
  public function renderTextfield(array $field) {
    $value = get_option($field['id']);
    $description = $field['desc'] ?: '';
    echo "<label for='{$field['id']}'>{$description}<input type='text' name='{$field['id']}' id='{$field['id']}' value='{$value}' /></label>";
  }
  
  /**
   * Outputs an html select.
   * 
   * @since 1.0
   * @access public
   * @param array $field
   */
  public function renderSelect(array $field) {
    $disabled = !empty($field['requires']) ? ('1' !== \get_option($field['requires']) ? ' disabled="disabled"' : '') : '';
    echo "<label for='{$field['id']}'><select id='{$field['id']}' name='{$field['id']}'{$disabled}>";
    $set = get_option($field['id']);
    foreach ($field['options'] as $value => $option) {
      $selected = $value === $set ? ' selected="selected"' : '';
      echo "<option value='{$value}'{$selected}>{$option}</option>";
    }
    $description = $field['desc'] ?: '';
    echo "</select>{$description}</label>";
  }
  
  /**
   * Outputs an html input of type fieldset, iterating over each contained element.
   * 
   * @since 1.0
   * @access public
   * @param array $fieldset
   */
  public function renderFieldset(array $fieldset) {
    echo "<fieldset>";
    echo "<legend class='screen-reader-text'><span>{$fieldset['title']}</span></legend>";
    foreach ($fieldset['values'] as $field) {
      $method = "render" . $field['type'];
      if (\method_exists($this, $method)) {
        $this->$method($field);
        echo "<br>";
      }
    }
    echo "</fieldset>";
  }
  
  /**
   * Returns a formatted array of searchable post types
   * 
   * @since 1.0
   * @access protected
   * @return array
   */
  protected function getPostTypes() {
    $postTypes = get_post_types(array('public' => true));
    foreach ($postTypes as &$postType) {
      $postType = array(
        'title' => __($postType, 'searchresultsoptimizer'),
        'desc' => __($postType, 'searchresultsoptimizer'),
        'id' => "searchresultsoptimizer_post_type_{$postType}",
        'type' => 'checkbox',
        'default' => 1
      );
    }
    unset($postType);
    return $postTypes;
  }
  
  /**
   * Returns a formatted array of metadata to filter on.
   * 
   * @since 1.0
   * @access public
   * @return array
   */
  protected function getMetaFilters() {
    return array(
      'tags' => array(
        'title' => __('Tags', 'searchresultsoptimizer'),
        'desc' => __('Allow filtering by tags', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_metadata_filters_tags',
        'type' => 'checkbox',
        'default' => 0,
        'requires' => 'searchresultsoptimizer_advanced_search_enabled'
      ),
      'categories' => array(
        'title' => __('Categories', 'searchresultsoptimizer'),
        'desc' => __('Allow filtering by categories', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_metadata_filters_categories',
        'type' => 'checkbox',
        'default' => 0,
        'requires' => 'searchresultsoptimizer_advanced_search_enabled'
      ),
      'types' => array(
        'title' => __('Types', 'searchresultsoptimizer'),
        'desc' => __('Allow filtering by post types', 'searchresultsoptimizer'),
        'id' => 'searchresultsoptimizer_metadata_filters_types',
        'type' => 'checkbox',
        'default' => 0,
        'requires' => 'searchresultsoptimizer_advanced_search_enabled'
      )
    );
  } 
}

endif;
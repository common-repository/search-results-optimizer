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

if (!class_exists(__NAMESPACE__ . '\SROAdminPage')):

/**
 * An admin page.
 *
 * @package SearchResultsOptimizer
 * @class SROAdminPage
 * @since 1.0
 * @author Chris Gorvan (@chrisgorvan)
 */
abstract class SROAdminPage {
  /**
   * The action being carried out
   * 
   * @since 1.0
   * @access protected
   * @var string
   */
  protected $action = '';
  
  /**
   * Includes the view that produces ouput, the name is based on the child class.
   * 
   * @since 1.0
   * @access public
   */
  public function output() {
    include $this->getDir() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . strtolower(preg_replace('/([A-Z])([a-z]+)/', "-$1$2", trim(strrchr(get_class($this), '\\'),'\\'))) . $this->getSubview() . '.php';
  }
  
  /**
   * Returns the view type used in the view filename.
   * 
   * @since 1.0
   * @access protected
   * @return string
   */
  protected function getSubview() {
    switch ($this->action) {
      case 'edit':
        return '-edit';
      default:
        return '-view';
    }
  }
  
  /**
   * Returns the directory location of the child class.
   * 
   * @since 1.0
   * @access protected
   * @return string
   */
  protected function getDir() {
    $reflector = new \ReflectionClass(get_class($this));
    return \dirname($reflector->getFileName());
  }
  
  /**
   * Process individual row actions.
   * 
   * @since 1.0
   * @access public
   */
  public function processAction() {
    $action = \filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
    $id = \filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    if (!empty($action) && $id) {
      switch ($action) {
        case 'delete':
          $this->deleteAction($id);
          break;
        case 'edit':
          $save = \filter_input(INPUT_POST, 'save');
          if (!empty($save)) {
            $this->saveAction($id);
          }
          break;
      }
    }
  }
}

endif;
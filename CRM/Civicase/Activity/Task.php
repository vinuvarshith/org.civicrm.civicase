<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 */

/**
 * Class to represent the actions that can be performed on a group of contacts used by the search forms.
 */
class CRM_Civicase_Activity_Task extends CRM_Activity_Task {

  static $objectType = 'activity';

  const TASK_CASE_COPY = 100,
    TASK_CASE_MOVE  = 101;

  /**
   * These tasks are the core set of tasks that the user can perform
   * on a contact / group of contacts.
   *
   * @return array
   *   the set of tasks for a group of contacts
   */
  public static function tasks() {
    parent::tasks();

    self::$_tasks += array(
      self::TASK_CASE_COPY => array(
        'title' => ts('Copy to case'),
        'class' => 'CRM_Civicase_Activity_Form_Task_CopyToCase',
        'result' => FALSE,
      ),
      self::TASK_CASE_MOVE => array(
        'title' => ts('Move to case'),
        'class' => 'CRM_Civicase_Activity_Form_Task_MoveToCase',
        'result' => FALSE,
      ),
    );

    return self::$_tasks;
  }

  public static function bulkOps($activityIds, $action = self::TASK_PRINT) {
    $tasks = self::tasks();
    $currPage = NULL;
    if(is_array($tasks[$action]['class'])) {
      $currPage = reset($tasks[$action]['class']);
    }
    else {
      $currPage = $tasks[$action]['class'];
    }
    $actionName = CRM_Utils_String::getClassName($currPage);

    $_POST['task'] = $action;
    $_REQUEST['_qf_'.$actionName.'_display'] = true;

    $controller = new CRM_Activity_Controller_Search(
      ts('Activities')
    );
    $controller->set('task', $action);
    $controller->_modal = FALSE;

    $stateMachine = $controller->getStateMachine();
    $formName = $stateMachine->getTaskFormName();

    $controller->resetPage($formName);
    $controller->_actionName = $actionName;

    $config = & CRM_Core_Config::singleton();
    $baseUrl = $config->userFrameworkBaseURL;
    $values = array(
      'qfKey' => $controller->_key,
      'entryURL' => $baseUrl . 'civicrm/activity/search?reset=1',
      'task' => $action,
      'radio_ts' => 'ts_sel',
      'activity_test' => 0,
      '_qf_Search_next_action' => 'Go',
      '_qf_default' => 'Search:refresh'
    );
    foreach ($activityIds as $activityId) {
      $values[CRM_Core_Form::CB_PREFIX . $activityId] = 1;
    }

    $taskName = CRM_Utils_String::getClassName($currPage);
    $page = new $currPage(NULL, CRM_Core_Action::NONE, 'post', $taskName);
    $page->setAttribute('id', $taskName);

    $controller->set('searchFormName', $actionName);
    $page->controller = $controller;
    $controller->addPage($page);

    // set values
    $name = '_' . $controller->_name . '_container';
    $_SESSION[$name]['values']['Search'] = $values;
    $_SESSION[$name]['values'][$actionName] = $values;
    $controller->set('searchFormName', $actionName);
    $controller->process();

    $form = new CRM_Activity_Form_Search();
    $form->controller = $controller;
    $form->preProcess();
    $form->_formValues = $values;
    $form->postProcess();
    $form->controller->getPage('Search')->loadValues($values);
    $form->controller->getPage($actionName)->controller->run();
  }

}


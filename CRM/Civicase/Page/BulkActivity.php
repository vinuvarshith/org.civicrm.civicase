<?php

/**
 * Class CRM_Civicase_Page_ContactActivityTab
 *
 * Implement the Angular version of the tab "View Contact => Cases".
 */
class CRM_Civicase_Page_BulkActivity extends CRM_Core_Page {

  public function run() {
    $store = $this;
    $activityIds = CRM_Utils_Request::retrieve('activityIds', 'CommaSeparatedIntegers', $store, FALSE, NULL, 'GET');
    $task = CRM_Utils_Request::retrieve('task', 'Int', $store, FALSE, NULL, 'GET');
    if((isset($activityIds) && isset($task))) {
      CRM_Civicase_Activity_Task::bulkOps(explode(',', $activityIds), $task);
    }
    else {
      parent::run();
    }
  }

}

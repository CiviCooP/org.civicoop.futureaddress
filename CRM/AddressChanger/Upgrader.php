<?php

/**
 * Collection of upgrade steps
 */
class CRM_AddressChanger_Upgrader extends CRM_AddressChanger_Upgrader_Base {
  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  protected $activity_type;
  
  public function __construct($extensionName, $extensionDir) {
    parent::__construct($extensionName, $extensionDir);
    
    $this->activity_type = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'activity_type'));
  }
  
  /**
   * 
   */
  public function install() {
    $this->executeCustomDataFile('xml/date_fields.xml');
    
    $this->addActivityType('address_change', ts('Address change'));
    
    $this->executeCustomDataFile('xml/address_history.xml');
  }
  
  public function upgrade_1001() {
    $this->addActivityType('address_change', ts('Address change'));
    return true;
  }
  
  public function upgrade_1002() {
    $this->executeCustomDataFile('xml/address_history.xml');
    return true;
  }
  
  public function upgrade_1003() {
    $this->executeCustomDataFile('xml/upgrade_1003.xml');
    return true;
  }
  
  public function upgrade_1004() {
    $this->executeCustomDataFile('xml/upgrade_1004.xml');
    return true;
  }

  /**
   * Remove the custom fields from this extension
   */
  public function uninstall() {
    //remove the custom fields and groups
    $config = CRM_Futureaddress_Config::singleton();
    $cgroup = $config->getCustomGroup();
    $this->deleteCustomGroup($cgroup['id']);
    
    $cgroup = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Address_change'));
    $this->deleteCustomGroup($cgroup['id']);
    
    $this->removeActivityType('address_change');
  }
  
  protected function deleteCustomGroup($gid) {
    $fields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $gid));
    foreach($fields['values'] as $field) {
      civicrm_api3('CustomField', 'delete', array('id' => $field['id']));
    }
    civicrm_api3('CustomGroup', 'delete', array('id' => $gid));
  }  
  
  protected function removeActivityType($name) {
    $params['option_group_id'] = $this->activity_type['id'];
    $params['name'] = $name;
    try {
      $result = civicrm_api3('OptionValue', 'getsingle', $params);
      civicrm_api3('OptionValue', 'delete', array('id' => $result['id']));
    } catch (Exception $e) {
      //do nothing
    }
  }
  
  protected function addActivityType($name, $label, $is_active=true, $is_reserved=true) {
    $params['option_group_id'] = $this->activity_type['id'];
    $params['name'] = $name;
    try {
      $result = civicrm_api3('OptionValue', 'getsingle', $params);
      return $result['id'];
    } catch (Exception $e) {
      //do nothing
    }
    $params['label'] = $label;
    $params['is_reserved'] = $is_reserved ? '1' : '0';
    $params['is_active'] = $is_active ? '1' : '0';
    
    $result = civicrm_api3('OptionValue', 'create', $params);
    return $result['id'];
  }

  /**
   * Example: Run a simple query when a module is enabled
   *
    public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
    }

    /**
   * Example: Run a simple query when a module is disabled
   *
    public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
    }

    /**
   * Example: Run a couple simple queries
   *
   * @return TRUE on success
   * @throws Exception
   *
    public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
    } // */
  /**
   * Example: Run an external SQL script
   *
   * @return TRUE on success
   * @throws Exception
    public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
    } // */
  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk
   *
   * @return TRUE on success
   * @throws Exception
    public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
    }
    public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
    public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
    public function processPart3($arg5) { sleep(10); return TRUE; }
    // */

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
    public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
    $endId = $startId + self::BATCH_SIZE - 1;
    $title = ts('Upgrade Batch (%1 => %2)', array(
    1 => $startId,
    2 => $endId,
    ));
    $sql = '
    UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
    WHERE id BETWEEN %1 and %2
    ';
    $params = array(
    1 => array($startId, 'Integer'),
    2 => array($endId, 'Integer'),
    );
    $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
    } // */

}

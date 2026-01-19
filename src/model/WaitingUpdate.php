<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
require_once('_securityCheck.php'); 
class WaitingUpdate extends SqlElement {
  
  public $id;
  public $idUser;
  public $scope;
  public $itemId;
  public $parameter;
  public $storeDateTime;
      
  public static $updateNow=false;
  private static $_databaseCriteria = array();
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }
  
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
  public static function storeWaiting($scope, $itemId, $parameter) {
    $wu=new WaitingUpdate();
    $wu->idUser=getCurrentUserId();
    $wu->scope=$scope;
    $wu->itemId=$itemId;
    if (is_array($parameter)) {
      $wu->parameter='array|'.implode(',', $parameter);
    } else if (is_object($parameter)) {
      $wu->parameter='object|'.get_class(parameter).'#'.$parameter->id;
    } else {
      $wu->parameter=$parameter;
    }
    $wu->storeDateTime=date('Y-m-d H:i:s');
    $wu->save();
  }
  public static function executeWaiting ($allUsers=false) {
    self::$updateNow=true;
    $now=date('Y-m-d H:i:s');
    $wu=new WaitingUpdate();
    $crit=($allUsers)?null:array('idUser'=>getCurrentUserId());
    $wuList=$wu->getSqlElementsFromCriteria($crit,null,null,'id asc');
    $done=array();
    foreach ($wuList as $wu) {
      $key="$wu->scope|$wu->itemId|$wu->parameter";
      if (isset($done[$key])) continue;
      if (pq_substr($wu->parameter,0,6)=='array|') {
        $param=explode(',',pq_substr($wu->parameter,6));
      } if (pq_substr($wu->parameter,0,7)=='object|') {
        $ref=explode('#',pq_substr($wu->parameter,7));
        $objectClass=$ref[0];
        $objectId=(count($ref)>1)?$ref[1]:null;
        $param=new $objectClass($objectId);
      } else {
        $param=$wu->parameter;
      }
      if ($wu->scope=='Organization::updateSynthesis') {
        $o=new Organization($wu->itemId);
        $o->updateSynthesis($param);
      } else {
        traceLog("WaitingUpdate::executeWaiting - scope not planned '$wu->scope'");
        continue;
      }
      $done[$key]=$wu;
      $purgeClause="scope='$wu->scope'";
      $purgeClause.=($wu->itemId!==null)?" and itemId=$wu->itemId":" and itemId is null ";
      $purgeClause.=($wu->parameter!==null)?" and parameter='$wu->parameter'":" and parameter is null";
      $purgeClause.=" and storeDateTime<='$now'";
      $res=$wu->purge($purgeClause);
    }
    
  }
}
?>
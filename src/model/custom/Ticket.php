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

/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
require_once('_securityCheck.php');
class Ticket extends TicketMain {
  
  public $_sec_description;
  public $id;
  public $reference;
  public $name;
  public $idActivity;
  public $idProject;
  public $tags;
  public $idTicketType;
  public $idUrgency;
  public $idCriticality;
  public $idPriority;
  public $creationDateTime;
  public $lastUpdateDateTime;
  public $idUser;
  public $idContact;
  public $idResource;
  public $Origin;
  public $idTicket;
  public $externalReference;
  public $idContext1;
  public $idContext2;
  public $idContext3;
  public $description;
  public $_sec_treatment;
  public $idStatus;
  public $idResolution;
  public $isRegression;
  public $idAccountable;
  public $idMilestone;
  public $_tab_2_1=array('initial','actual','dueDate');
  public $initialDueDateTime;
  public $actualDueDateTime;
  public $delayReadOnly;
  public $WorkElement;
  public $handled;
  public $handledDateTime;
  public $paused;
  public $pausedDateTime;
  public $_button_showStatusPeriod;
  public $done;
  public $doneDateTime;
  public $solved;
  public $_lib_colSolved;
  public $idle;
  public $idleDateTime;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  public $_sec_productComponent;
  public $idProduct;
  public $idComponent;
  public $idOriginalProductVersion;
  public $idTargetComponentVersion;
  public $_OtherOriginalProductVersion=array();
  public $idOriginalComponentVersion;
  public $_OtherOriginalComponentVersion=array();
  public $idTargetProductVersion;
  public $_OtherTargetProductVersion=array();
  public $_OtherTargetComponentVersion=array();
  public $_sec_ToDoList;
  public $_SubTask;
  public $_sec_vote;
  public $VotingItem;
  public $_sec_Link;
  public $_OtherClient=array();
  public $_nbColMax=3;

  private static $_fieldsAttributes=array(
    "id"=>"nobr",
    "reference"=>"readonly",
    "name"=>"required",
    "idProject"=>"required",
    "idTicketType"=>"required",
    "idStatus"=>"required",
    "creationDateTime"=>"required",
    "handled"=>"nobr",
    "done"=>"nobr",
    "idle"=>"nobr",
    "idleDateTime"=>"nobr",
    "cancelled"=>"hidden,nobr",
    "idContext1"=>"nobr",
    "idContext2"=>"hidden,nobr",
    "idContext3"=>"",
    "doneDateTime"=>"nobr",
    "solved"=>"hidden,nobr",
    "idActivity"=>"",
    "delayReadOnly"=>"hidden",
    "paused"=>"nobr",
    "pausedDateTime"=>"nobr",
    "description"=>"",
    "idTicket"=>"hidden",
    "Origin"=>"hidden",
    "idResource"=>"required",
    "idCriticality"=>"required",
    "idPriority"=>"required",
    "idUrgency"=>"required",
    "externalReference"=>"",
    "idResolution"=>"hidden",
    "isRegression"=>"hidden",
    "_lib_colSolved"=>"hidden",
    "idComponent"=>"hidden",
    "idTargetProductVersion"=>"hidden",
    "idTargetComponentVersion"=>"hidden",
    "idOriginalComponentVersion"=>"hidden"
  );
  
  

	
  private static $_staticDisplayStyling=array(
        'name'=>array('caption'=>'color:#ff0000 !important;text-shadow:none;','field'=>'')
); 
  
  
  private static $_colCaptionTransposition=array(
); 
  
  
  public static $_defaultValues=array(
); 
  
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
	
	

  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
 
	/** ==========================================================================
	 * Return the specific styling for fields
	 * @return the fields styling
	 */	
	public function getStaticDisplayStyling() {
	  return self::$_staticDisplayStyling;
	}
 
  /** ============================================================================
   * Set attribut from parent : merge current attributes with those of Main class
   * @return void
   */
  public function setAttributes() {
	  $parentClass=get_class($this)."Main";
	  if (!SqlElement::class_exists($parentClass)) return;
	  $parent=new $parentClass($this->id);
	  if (! method_exists($parent, "setAttributes")) return; 
	  $parent->setAttributes();
	  if (method_exists("SqlElement","mergeAttributesArrays")) {
	    self::$_fieldsAttributes=SqlElement::mergeAttributesArrays(self::$_fieldsAttributes,$parent->getStaticFieldsAttributes());
	  } else {
	    self::$_fieldsAttributes=array_merge_preserve_keys(self::$_fieldsAttributes,$parent->getStaticFieldsAttributes());
	  }
	} 
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    if (isset(self::$_colCaptionTransposition)) {
      return array_merge(parent::getStaticColCaptionTransposition($fld),self::$_colCaptionTransposition);
    } else {
      return parent::getStaticColCaptionTransposition($fld);
    }
  }
 
  /** ==========================================================================
	 * Return the generic defaultValues
	 * @return the layout
	 */
	protected function getStaticDefaultValues() {
	  return self::$_defaultValues;
	}
  }
	?>
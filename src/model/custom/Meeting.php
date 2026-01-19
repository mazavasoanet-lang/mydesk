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
 * Meeting.
 */ 
require_once('_securityCheck.php'); 
class Meeting extends MeetingMain {
  
  public $_sec_description;
  public $id;
  public $reference;
  public $name;
  public $idActivity;
  public $idProject;
  public $idMeetingType;
  public $idPeriodicMeeting;
  public $isPeriodic;
  public $periodicOccurence;
  public $meetingDate;
  public $_lib_from;
  public $meetingStartTime;
  public $_lib_to;
  public $meetingEndTime;
  public $location;
  public $idResource;
  public $_spe_buttonSendMail;
  public $_spe_startMeeting;
  public $idUser;
  public $description;
  public $_sec_treatment;
  public $idStatus;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  public $_sec_Attendees;
  public $_spe_showClosedAssignment;
  public $_Assignment=array();
  public $attendees;
  public $_spe_buttonAssignTeam;
  public $_sec_progress_left;
  public $MeetingPlanningElement;
  public $_sec_predecessor;
  public $_Dependency_Predecessor=array();
  public $_sec_successor;
  public $_Dependency_Successor=array();
  public $meetingStartDateTime;
  public $meetingEndDateTime;
  public $_sec_Link;
  public $_nbColMax=3;

  private static $_fieldsAttributes=array(
    "id"=>"nobr",
    "reference"=>"readonly",
    "idProject"=>"required",
    "idMeetingType"=>"required",
    "meetingDate"=>"required,nobr",
    "_lib_from"=>"nobr",
    "_lib_to"=>"nobr",
    "meetingStartTime"=>"nobr",
    "idUser"=>"hidden",
    "idStatus"=>"required",
    "handled"=>"nobr",
    "done"=>"nobr",
    "idle"=>"nobr",																"idPeriodicMeeting"=>"hidden",																"isPeriodic"=>"readonly",																"periodicOccurence"=>"hidden",
    "idleDate"=>"nobr",
    "cancelled"=>"nobr",
    "meetingStartDateTime"=>"hidden",
    "meetingEndDateTime"=>"hidden",
    "idActivity"=>"",
    "idResource"=>"",
    "name"=>"required"
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
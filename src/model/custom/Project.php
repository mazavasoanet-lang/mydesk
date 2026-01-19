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
class Project extends ProjectMain {
  
  public $_sec_Description;
  public $id;
  public $_spe_rf;
  public $name;
  public $idProject;
  public $tags;
  public $idProjectType;
  public $idOrganization;
  public $idCategory;
  public $organizationInherited;
  public $organizationElementary;
  public $codeType;
  public $idClient;
  public $idContact;
  public $idCatalogUO;
  public $idCalendarDefinition;
  public $projectCode;
  public $contractCode;
  public $clientCode;
  public $idSponsor;
  public $idResource;
  public $idUser;
  public $creationDate;
  public $lastUpdateDateTime;
  public $color;
  public $longitude;
  public $latitude;
  public $description;
  public $objectives;
  public $_sec_Progress;
  public $ProjectPlanningElement;
  public $_sec_Affectations;
  public $_spe_affectations;
  public $_sec_Proposal;
  public $strength;
  public $weakness;
  public $opportunity;
  public $threats;
  public $_sec_treatment;
  public $idStatus;
  public $strategicValue;
  public $benefitValue;
  public $idRiskLevel;
  public $idHealth;
  public $idQuality;
  public $idTrend;
  public $idOverallProgress;
  public $fixPlanning;
  public $_lib_helpFixPlanning;
  public $paused;
  public $_lib_helpPaused;
  public $fixPerimeter;
  public $_lib_helpFixPerimeter;
  public $allowReduction;
  public $_lib_helpAllowReduction;
  public $isUnderConstruction;
  public $_lib_helpUnderConstruction;
  public $excludeFromGlobalPlanning;
  public $_lib_helpExcludeFromGlobalPlanning;
  public $commandOnValidWork;
  public $_lib_helpCommandOnValidWork;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $_sec_Synchronisation;
  public $_spe_isSynchronised;
  public $_sec_ProjectDailyHours;
  public $_tab_2_2=array('start','end','morning','afternoon');
  public $startAM;
  public $endAM;
  public $startPM;
  public $endPM;
  public $_sec_ProductprojectProducts;
  public $_ProductProject=array();
  public $_sec_VersionprojectVersions;
  public $_VersionProject=array();
  public $_sec_Subprojects;
  public $_spe_subprojects;
  public $_sec_restrictTypes;
  public $_spe_restrictTypes;
  public $_sec_predecessor;
  public $_Dependency_Predecessor=array();
  public $_sec_successor;
  public $_Dependency_Successor=array();
  public $sortOrder;
  public $isLeaveMngProject;
  public $_sec_Link;
  public $_nbColMax=3;

  private static $_fieldsAttributes=array(
    "name"=>"required",
    "done"=>"nobr",
    "idle"=>"nobr",
    "handled"=>"nobr",
    "sortOrder"=>"hidden",
    "codeType"=>"hidden",
    "idProjectType"=>"required",
    "longitude"=>"hidden",
    "latitude"=>"hidden",
    "idStatus"=>"required",
    "idleDate"=>"nobr",
    "cancelled"=>"nobr",
    "organizationInherited"=>"hidden",
    "organizationElementary"=>"hidden",
    "fixPlanning"=>"nobr",
    "allowReduction"=>"nobr",
    "paused"=>"",
    "idCatalogUO"=>"hidden",
    "fixPerimeter"=>"nobr",
    "isUnderConstruction"=>"nobr",
    "excludeFromGlobalPlanning"=>"nobr",
    "commandOnValidWork"=>"nobr",		"isLeaveMngProject"=>"hidden",		"locked"=>"hidden",		"paused"=>"nobr",
    "idProject"=>"",
    "idContact"=>"hidden",
    "idCategory"=>"hidden",
    "contractCode"=>"hidden",
    "clientCode"=>"hidden",
    "idCalendarDefinition"=>"hidden",
    "idClient"=>"hidden"
  );
  
  

	
  private static $_staticDisplayStyling=array(
        'name'=>array('caption'=>'color:#ff4500 !important;text-shadow:none;background:#ffffff !important;','field'=>'')
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
// MTY - LEAVE SYSTEM    
	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct($id,$withoutDependentObjects);
// MTY - LEAVE SYSTEM    	
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
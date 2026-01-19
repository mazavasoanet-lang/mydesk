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
 * Contact
 */  
require_once('_securityCheck.php');
class Contact extends ContactMain {
  
  public $_sec_Description;
  public $id;
  public $_spe_image;
  public $name;
  public $userName;
  public $initials;
  public $email;
  public $idProfile;
  public $idClient;
  public $idProvider;
  public $contactFunction;
  public $phone;
  public $mobile;
  public $fax;
  public $isUser;
  public $_spe_isUserGoTo;
  public $isResource;
  public $_spe_isResourceGoTo;
  public $startDate;
  public $_lib_colAsResource;
  public $idRole;
  public $idle;
  public $designation;
  public $street;
  public $complement;
  public $zip;
  public $city;
  public $state;
  public $country;
  public $description;
  public $_sec_Affectations;
  public $_spe_affectations;
  public $_sec_SubscriptionContact;
  public $_sec_Address;
  public $_spe_subscriptions;
  public $_sec_Miscellaneous;
  public $dontReceiveTeamMails;
  public $_sec_TicketsContact;
  public $_spe_tickets;
  public $password;
  public $crypto;
  public $idTeam;
  public $idOrganization;
  public $_nbColMax=3;

  private static $_fieldsAttributes=array(
    "name"=>"required,truncatedWidth100",
    "userName"=>"truncatedWidth100",
    "email"=>"truncatedWidth100",
    "idProfile"=>"",
    "isUser"=>"",
    "isResource"=>"",
    "password"=>"hidden",
    "crypto"=>"hidden",
    "startDate"=>"nobr",
    "idTeam"=>"hidden",
    "idRole"=>"hidden",
    "idOrganization"=>"hidden",
    "idle"=>"hidden",
    "designation"=>"",
    "street"=>"",
    "complement"=>"",
    "zip"=>"",
    "city"=>"",
    "state"=>"",
    "country"=>"",
    "_sec_Address"=>"hidden"
  );
  
  

   
  private static $_staticDisplayStyling=array(
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
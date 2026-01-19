<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 ******************************************************************************
 *** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
 ******************************************************************************
 *
 * Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * 
 * This file is an add-on to ProjeQtOr, packaged as a plug-in module.
 * It is NOT distributed under an open source license. 
 * It is distributed in a proprietary mode, only to the customer who bought
 * corresponding licence. 
 * The company ProjeQtOr remains owner of all add-ons it delivers.
 * Any change to an add-ons without the explicit agreement of the company 
 * ProjeQtOr is prohibited.
 * The diffusion (or any kind if distribution) of an add-on is prohibited.
 * Violators will be prosecuted.
 *    
 *** DO NOT REMOVE THIS NOTICE ************************************************/
require_once "../tool/projeqtor.php";
require_once "../plugin/screenCustomization/screenCustomizationFunctions.php";
require_once "../db/maintenanceFunctions.php";
function screenCustomizationFixDefinition($fixclass) {
  global $availableAttributes, $lastFixedField; // for calling SaveField script
  $_automaticFixing=true;
  $included=true;
  $fixclassMain=$fixclass.'Main';
  $fixresult="KO";
  //$checkIncorrectPosition=true;
  $moved=array();
  $fixobj=new $fixclass();
  $fixobjMain=new $fixclassMain();
  $fixlast=false;
  $checkIncorrectPosition=false;
  foreach ($fixobj as $fixfld=>$fixval) {
    if ($fixfld=='_Note' or $fixfld=='_Link' or $fixfld=='_Attachment' or $fixfld=='_sec_Link' or $fixfld=='_OtherClient' or $fixfld=='_nbColMax') {
      $fixlast=true;
    } else if ($fixlast and !isset($moved[$fixfld])) { // not a specific field, after $last (notes, attachment, link)
      $fixprec=null;
      foreach ($fixobjMain as $fixmainFld=>$fixmainVal) {
        if ($fixmainFld==$fixfld) {
          // found field in Main before fix fields (note, link, ...) => Must move
          break; 
        }
        if ($fixmainFld=='_Note' or $fixmainFld=='_Link' or $fixmainFld=='_Attachment' or $fixmainFld=='_sec_Link' or $fixmainFld=='_nbColMax') {
          // Field is after fix fields (note, link, ...) in Main => No need to move
          $fixprec=null;
          break;
        }
        $fixprec=$fixmainFld;
      }
      if ($fixprec) {
        $_REQUEST['screenCustomizationEditFieldObjectClass']=$fixclass;
        $_REQUEST['screenCustomizationEditFieldField']=$fixfld;
        $_REQUEST['screenCustomizationEditFieldNew']='false';
        $_REQUEST['screenCustomizationEditFieldDataType']='';
        $_REQUEST['screenCustomizationEditFieldDataLength']='';
        $_REQUEST['screenCustomizationEditFieldPosition']=$fixprec;
        $_REQUEST['screenCustomizationEditFieldName']='';
        $_REQUEST['screenCustomizationEditFieldDefaultValue']='';
        ob_start();
        include "screenCustomizationSaveField.php";
        ob_clean();
        $fixresult='OK';
        $lastFixedField=$fixfld;
        $checkIncorrectPosition=true; // Look for another field that is not on correct place
        $moved[$fixfld]=$fixfld;
        if (function_exists("opcache_reset")) opcache_reset();
        break; // Mandatory - Can manage fixing for only 1 field at a time, will be called several times to fix allscreenCustomizationFixDefinition.php
      } else {
        $fixresult='KO';
      }
    }
  }
  return $fixresult;
}
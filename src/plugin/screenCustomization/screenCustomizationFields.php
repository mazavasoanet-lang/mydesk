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

/* ============================================================================
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
  chdir('../');
  require_once "../tool/projeqtor.php";
  require_once "../tool/formatter.php";
  require_once "../plugin/screenCustomization/screenCustomizationFunctions.php";
  scriptLog('   ->/plugin/screenCustomization/screenCustomizationFields.php');  
  if (file_exists('../plugin/screenCustomization/screenCustomizationFixDefinition.php')) {
    include_once('../plugin/screenCustomization/screenCustomizationFixDefinition.php');
  }
  $user=getSessionUser();
  if (!isset($objectClass)) {
    $objectClass=null;
    if (isset($_REQUEST["objectClass"])) {
      $objectClass=$_REQUEST["objectClass"];
    }
  }
  $hideScope='Type';
  if (isset($_REQUEST["hideScope"])) {
    $hideScope=$_REQUEST["hideScope"];
    if ($hideScope!='Type' and $hideScope!='Status' and $hideScope!='Profile') {
      traceHack("Invalid hideScope parameter '$hideScope'");
      exit;
    }
    Parameter::storeUserParameter('plgScreenCustomizationHideScope', $hideScope);
  } else {
    $scope=Parameter::getUserParameter('plgScreenCustomizationHideScope');
    if ($scope and $scope!='') $hideScope=$scope;
  }
  Security::checkValidClass($objectClass);  
  
  //ob_start();
  if (file_exists("../model/custom/$objectClass.php")) {
    $check=Plugin::checkCustomDefinition($objectClass);
  }

  //ob_clean();
  
  echo '<form xdojoType="dijit.form.Form" id="screenCustomizationFieldsForm" jsId="screenCustomizationFieldsForm" name="screenCustomizationFieldsForm" encType="multipart/form-data" action="" method="" >';
  echo '<input type="hidden" name="screenCustomizationFieldsObjetClass" value="'.$objectClass.'" />';
  echo '<table style="width:100%">';
  drawObject($objectClass,$hideScope);
  echo '</table>';
  echo '</form>';
  
function drawObject($objectClass,$hideScope) {
  global $notDisplayedSections,$availableAttributes;
  $extraHiddenFields=array();
  $extraReadonlyFields=array();
  $extraRequiredFields=array();
  $customFields=array();
  if (property_exists($objectClass, '_customFields')) $customFields=$objectClass::$_customFields;
  $obj=new $objectClass();
  $obj->id=1;
  $listType=array();
  $parentClass=str_replace('PlanningElement','',$objectClass);
  if ($objectClass=='WorkElement') $parentClass='Ticket';
  $typeClass=$parentClass.'Type';
  if ($typeClass=='TicketSimpleType') $typeClass=('TicketType');
  if ($hideScope=='Type' and SqlElement::class_exists($typeClass) and property_exists($parentClass, 'id'.$typeClass) ) {
      $listType=SqlList::getList($typeClass);
  } else if ($hideScope=='Status' and property_exists($parentClass, 'idStatus')) {
    $listType=SqlList::getStatusList($parentClass);
  } else if ($hideScope=='Profile') {
    $listType=SqlList::getList('Profile');
  }
  
  echo '<tr>';
  echo "<td colspan='4' style='width:20%'>";
  
  echo "<table width='100%'><tr>";
  if (file_exists("../model/custom/$objectClass.php") ) {
    echo '<td style="width:10%;text-align:right;padding-right:5px;">';      
    echo ' <a onClick="plg_screenCustomization_reset(\''.$objectClass.'\');" title="' . i18n('resetCustomScreen') . '" class="roundedButtonSmall" /> '.formatSmallButton('Remove').'</a>';
	echo '</td>';
    echo '<td style="width:40%;">'.i18n('resetCustomScreen').'</td>';
  }
  if (!SqlElement::is_a($obj,'PlanningElement')) {
	echo '<td style="width:10%;text-align:right;padding-right:5px;">';
      echo ' <a onClick="plg_screenCustomization_add(\''.$objectClass.'\');" title="' . i18n('addNewCustomField') . '" class="roundedButtonSmall" /> '.formatSmallButton('Add').'</a>';
	echo '</td>';
      echo '<td style="width:40%;">'.i18n('addNewCustomField').'</td>';
  }
  echo "</tr></table>";

  echo "</td>";
  echo "<td colspan='".(count($listType)+1)."'>";
  $arrayScope=array('Type', 'Status', 'Profile');
  echo "<table><tr><td class='screenCustomisationHideMessage' style='min-width:".(count($arrayScope)*25)."px'>";
  foreach ($arrayScope as $scope) {
    $clickFunction="plg_screenCustomization_changeScope('$objectClass','$scope');";
    $title=i18n('hideFieldsFor'.$scope);
    echo "<div onclick=\"$clickFunction\" class='roundedButtonSmall' style='float:left;position:relative;top:-1px;padding:1px;".(($hideScope==$scope)?'top:-2px;border:2px solid #e97b2c;border-radius:10px !important;':'')."'>".formatIcon($scope,16,$title)."</div>";
  }
  echo "</td><td class='screenCustomisationHideMessage'>";
  //echo "<div xclass='screenCustomisationHideMessage' style='height:20px;vertical-align:middle;'>";
  if (count($listType)) {echo i18n('hideFieldsFor'.$hideScope);}
  //echo "</div>";
  echo "</td></tr></table>";

  echo "</td>";
  
  echo '</tr>';
  // Types
  echo '<tr style="height:80px"><td colspan="4"><table>';
  foreach ($availableAttributes as $attr) {
    echo '<tr><td>&nbsp;&nbsp;</td><td><img style="width:16px" src="../plugin/screenCustomization/icon'.ucfirst($attr).'.png" /></td><td style="padding-bottom:5px;vertical-align:middle;font-size:80%">&nbsp;'.i18n('attribute'.ucfirst($attr)).'</td></tr>';
  }
  echo '<tr><td>&nbsp;&nbsp;</td><td><img style="width:16px" src="../plugin/screenCustomization/iconDefault.png" /></td><td style="padding-bottom:5px;vertical-align:middle;font-size:80%">&nbsp;'.i18n('attributeDefault').'</td></tr>';
  echo '</table></td>';
  if (count($listType)) {
    foreach ($listType as $id=>$val) {
      echo '<td style="width:40px;max-width:40px"><p class="screenCustomisationType">'.$val.'</p></td>';
      $idType=($hideScope=='Type')?$id:'*';
      $idStatus=($hideScope=='Status')?$id:'*';
      $idProfile=($hideScope=='Profile')?$id:'*';
      $extraHiddenFields[$id]=$obj->getExtraHiddenFields($idType,$idStatus,$idProfile);
      $extraReadonlyFields[$id]=$obj->getExtraReadonlyFields($idType,$idStatus,$idProfile);
      $extraRequiredFields[$id]=$obj->getExtraRequiredFields( $idType,$idStatus,'*', $idProfile);
    }
  }
  echo '<td></td></tr>';
  if (SqlElement::is_a($obj, 'PlanningElement')) {
    echo '<tr><td class="listTitle section" colspan="4" style="width:20%">'.i18n('sectionProgress').'</td>';
    echo '</tr>';
  } else if (SqlElement::is_a($obj, 'WorkElement')) {
    echo '<tr><td class="listTitle section" colspan="4" style="width:20%">'.i18n('Work').'</td>';
    echo '</tr>';
  } else if (SqlElement::is_a($obj, 'BudgetElement')) {
    echo '<tr><td class="listTitle section" colspan="4" style="width:20%">'.i18n('sectionCurrentProjects').'</td>';
    echo '</tr>';
  }
  $inTable=0;
  $previousIsSection=false;
  //foreach ($obj as $col=>$val) {
  foreach (screenCustomizationGetFieldsList($obj) as $col) {
    if (! property_exists($obj, $col)) continue;
    $val=(property_exists($obj, $col) and isset($obj->$col))?$obj->$col:null;
    $edit=true;
    echo '<tr>';
    $dataType=screenCustomizationGetDataType($obj,$col);
    $hide=screenCustomizationIsAlwaysHidden($obj,$col);
    $isSection=screenCustomizationIsSection($obj,$col);
    $isSpecific=screenCustomizationIsSpecific($obj,$col);
    $isMessage=screenCustomizationIsMessage($obj, $col);
    $isArray=screenCustomizationIsArray($obj,$col);
    if ($col=='_productLanguage' or $col=='_productContext' or $col=='_productBusinessFeatures') {
      $dataType='table';
      $isArray=true;
    }
    if ($col=='id') {
      //$edit=false;
    }
    if ($isArray and $col!='_Note' and $col!='_Attachment') {
      $edit=false;
    }
    if ($isSpecific and $previousIsSection) {
      $edit=false;
    }
    if ($hide) {
      // field is hidden in main class : must not be displayed
    } else {
      if ($col=='_Note' or $col=='_Attachment') {
        echo '<td class="listTitle section" colspan="2" style="width:20%">'.i18n('section'.substr($col,1)).'</td>';
        echo '<td class="section" style="text-align:left;width:50px;padding:3px 10px;vertical-align:top">'.screenCustomizationFormatAllAttributes($obj,$col).'</td>';
      } else if ($isSection) {
	      $section=ucfirst(substr($col,5));
	      $nameSection=getColCaption($obj,$col);
	      //$nameSection=getColCaption($obj,str_replace('_', '',$col));
	      //$edit=false;
	      echo '<td class="listTitle section" colspan="2" style="width:20%">'.$nameSection.'</td>';
	      echo '<td class="section" style="text-align:left;width:50px;padding:3px 10px;vertical-align:top">'.screenCustomizationFormatAllAttributes($obj,$col).'</td>';
	    } else  {
	      $label=getColCaption($obj,$col);
	      if ($dataType=='message') $label=i18n(substr($col,5));
	      //if ($dataType) 
	      echo '<td class="label">'.$label.'&nbsp;:&nbsp;</td>';     
	      echo '<td style="width:100px;padding:3px 10px;vertical-align:top">'.screenCustomizationFormatDataType($obj,$col).'</td>';
	      echo '<td style="min-width:50px;padding:3px 10px;vertical-align:top">'.screenCustomizationFormatAllAttributes($obj,$col).'</td>';
	    }
      echo '<td style="min-width:45px;text-align:center;">';
      if ($edit and $col!='_Link') {
        echo ' <a onClick="plg_screenCustomization_edit(\''.$objectClass.'\',\''.$col.'\')" class="roundedButtonSmall"/> '.formatSmallButton('Edit').'</a>'; 
      }
      if (in_array($col,$customFields)) {
        echo ' <a onClick="plg_screenCustomization_remove(\''.$objectClass.'\',\''.$col.'\',\''.htmlEncode($obj->getColCaption($col),'quotes').'\')" class="roundedButtonSmall" /> '.formatSmallButton('Remove').'</a>';    
      }
      echo '</td>';
     
      // Types
      foreach ($listType as $id=>$val) {
      	$hidden=(isset($extraHiddenFields[$id]) and in_array($col,$extraHiddenFields[$id]))?true:false;
      	$readonly=(isset($extraReadonlyFields[$id]) and in_array($col,$extraReadonlyFields[$id]))?true:false;
      	$required=(isset($extraRequiredFields[$id]) and isset($extraRequiredFields[$id][$col]) and $extraRequiredFields[$id][$col]=='required')?true:false;
      	$showHidden=true;
      	$showReadonly=true;
      	$showRequired=true;
      	if ($isSection or $isSpecific or $col=='_Note' or $col=='_Attachment' or $col=='_Link') {
      	  $showReadonly=false;
      	  $showRequired=false;
      	}
      	if ($isArray and $col!="_Note" and $col!="_Attachment") {
      	  $showReadonly=false;
      	  $showRequired=false;
      	  $showHidden=false;
      	} 
      	if ($col=='_Link') {
      	  $showHidden=false;
        }
      	if ($dataType=='boolean') {
      	  $showRequired=false;
      	}
      	if ($col=='id') {
      	  $showRequired=false;
      	  $showReadonly=false;
      	} else if ($obj->isAttributeSetToField($col,'hidden')) {
      	  $showHidden=false;
      	  $showReadonly=false;
      	  $showRequired=false;
      	} else if ($obj->isAttributeSetToField($col,'readonly')) {
      	  $showReadonly=false;
      	  $showRequired=false;
      	} else if ($obj->isAttributeSetToField($col,'required')) {
      	  $showRequired=false;
      	  //$showReadonly=false;
      	}
        echo '<td style="text-align:left; padding-left:10px;white-space:nowrap">';
        if ($showHidden) {
          echo '<input type="hidden" style="width:10px" name="checkHidden_'.$col.'_'.$id.'" id="checkHidden_'.$col.'_'.$id.'"';
          echo ' value="'.(($hidden)?'1':'0').'" />';
          echo '<img style="margin:0px 2px;" id="imgHidden_'.$col.'_'.$id.'" src="../plugin/screenCustomization/iconHidden'.(($hidden)?'':'No').'.png" title="'.i18n('attributeHidden').'" onClick="plg_screenCustomization_toggleField(\'Hidden\',\''.$col.'\',\''.$id.'\')"/>';
        }
        if ($showReadonly) {
          echo '<input type="hidden" style="width:10px" name="checkReadonly_'.$col.'_'.$id.'" id="checkReadonly_'.$col.'_'.$id.'"';
          echo ' value="'.(($readonly)?'1':'0').'" />';
          echo '<img style="margin:0px 2px;" id="imgReadonly_'.$col.'_'.$id.'" src="../plugin/screenCustomization/iconReadonly'.(($readonly)?'':'No').'.png" title="'.i18n('attributeReadonly').'" onClick="plg_screenCustomization_toggleField(\'Readonly\',\''.$col.'\',\''.$id.'\')"/>';
        }
        if ($showRequired) {
          echo '<input type="hidden" style="width:10px" name="checkRequired_'.$col.'_'.$id.'" id="checkRequired_'.$col.'_'.$id.'"';
          echo ' value="'.(($required)?'1':'0').'" />';
          echo '<img style="margin:0px 2px;" id="imgRequired_'.$col.'_'.$id.'" src="../plugin/screenCustomization/iconRequired'.(($required)?'':'No').'.png" title="'.i18n('attributeRequired').'" onClick="plg_screenCustomization_toggleField(\'Required\',\''.$col.'\',\''.$id.'\')"/>';
        }
        
        echo '</td>';
        
      }
      echo '<td></td>'; // empty cell that will auto extend, so will give expected width for types
      if ($col=='_Note' or $col=='_Attachment') {
        echo '</tr><tr>';
        echo '<td class="label">'.i18n(substr($col,1)).'&nbsp;:&nbsp;</td>';
        echo '<td style="width:100px;padding:3px 10px;vertical-align:top">'.screenCustomizationFormatDataType($obj,$col).'</td>';
        echo '<td style="min-width:50px;padding:3px 10px;vertical-align:top"></td>';
        echo '<td></td>'; // empty cell that will auto extend, so will give expected width for types
      }
    }
    echo '</tr>';
    $previousIsSection=($isSection)?true:false;
  }
}  
  ?>


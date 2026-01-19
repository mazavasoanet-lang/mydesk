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
 
chdir('../');
require_once "../tool/projeqtor.php";
//require_once "../tool/file.php";
require_once "../plugin/screenCustomization/screenCustomizationFunctions.php";
scriptLog('   ->/plugin/screenCustomization/screenCustomizationSaveExtraHiddenType.php');  
if (! array_key_exists('screenCustomizationFieldsObjetClass',$_REQUEST)) {
  throwError('Parameter screenCustomizationFieldsObjetClass not found in REQUEST');
}
$objectClass=$_REQUEST['screenCustomizationFieldsObjetClass'];
Security::checkValidClass($objectClass);
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

$obj=new $objectClass();
$objectClassMain=$objectClass.'Main';
$objMain=new $objectClassMain();

$status="NO_CHANGE";
$error=null;

$testClass=$objectClass;
if (SqlElement::is_a($objectClass, 'PlanningElement')) {
  $testClass=str_replace('PlanningElement','',$objectClass);
} else if ($objectClass=='WorkElement') {
  $testClass='Ticket';
}
$typeClass=$testClass.'Type';
if ($typeClass=='TicketSimpleType') $typeClass=('TicketType');

$listType=array();
if ($hideScope=='Type' and SqlElement::class_exists($testClass) and property_exists($testClass, 'id'.$typeClass) ) {
  $listType=SqlList::getList($typeClass);
} else if ($hideScope=='Status' and SqlElement::class_exists($testClass) and property_exists($testClass, 'idStatus')) {
  $listType=SqlList::getStatusList($testClass);
} else if ($hideScope=='Profile') {
  $listType=SqlList::getList('Profile');
}
//var_dump($listType);
$extraHiddenFields=array();
$extraReadonlyFields=array();
$extraRequiredFields=array();
foreach ($listType as $id=>$val) {
  $idType=($hideScope=='Type')?$id:'*';
  $idStatus=($hideScope=='Status')?$id:'*';
  $idProfile=($hideScope=='Profile')?$id:'*';
  $extraHiddenFields[$id]=$obj->getExtraHiddenFields($idType,$idStatus,$idProfile);
  $extraReadonlyFields[$id]=$obj->getExtraReadonlyFields($idType,$idStatus,$idProfile);
  $extraRequiredFields[$id]=$obj->getExtraRequiredFields( $idType,$idStatus,'*', $idProfile);
}
//foreach ($obj as $col=>$val) {
foreach (screenCustomizationGetFieldsList($obj) as $col) {
  if (! property_exists($obj, $col)) continue;
  $val=(property_exists($obj, $col) and isset($obj->$col))?$obj->$col:null;
  $dataType=screenCustomizationGetDataType($obj,$col);
  $hide=screenCustomizationIsAlwaysHidden($obj,$col);
  if (substr($col,0,5)=='xxx') {
    // Section : nothing to do
  } else if ($hide) {
    // always hide : nothing to do
  } else {
    foreach ($listType as $idType=>$nameType) {
      $checkNameHidden="checkHidden_".$col."_".$idType;
      if (isset($_REQUEST[$checkNameHidden]) and $_REQUEST[$checkNameHidden]) { // Checked !
        if (isset($extraHiddenFields[$idType]) and in_array($col,$extraHiddenFields[$idType])) {
          // Already exists in extrahiddenfields => nothing to do
        } else {
          // Not exists in extrahiddenfields => store new line
          $extra=new ExtraHiddenField();
          $extra->scope=$hideScope.'#'.$objectClass;
          $extra->idType=$idType;
          $extra->field=$col;
          $extra->save();
          $status="OK";
        }
      } else { // Not checked !
        if (isset($extraHiddenFields[$idType]) and in_array($col,$extraHiddenFields[$idType])) {
          // Exists in extrahiddenfields => must delete
          $crit=array('scope'=>$hideScope.'#'.$objectClass, 'idType'=>$idType, 'field'=>$col);
          $extra=SqlElement::getSingleSqlElementFromCriteria('ExtraHiddenField', $crit);
          if ($extra->id) {
            $extra->delete();
            $status="OK";
          } else {
            traceLog("dupplicate entry in extrahiddenfield for scope=$hideScope, class=$objectClass, idType=$idType,  col=$col");
            $ehf=new ExtraHiddenField();
            $extraList=$ehf->getSqlElementsFromCriteria($crit);
            foreach ($extraList as $extra) {
              $extra->delete();
            }
            $status="OK";
          }
        } else {
          // Not exists in extrahiddenfields => nothing to do
        }
      }
      $checkNameReadonly="checkReadonly_".$col."_".$idType;
      if (isset($_REQUEST[$checkNameReadonly]) and $_REQUEST[$checkNameReadonly]) { // Checked !
      	if (isset($extraReadonlyFields[$idType]) and in_array($col,$extraReadonlyFields[$idType])) {
      		// Already exists in extrareadonlyfields => nothing to do
      	} else {
      		// Not exists in extrareadonlyfields => store new line
      		$extra=new ExtraReadonlyField();
      		$extra->scope=$hideScope.'#'.$objectClass;
      		$extra->idType=$idType;
      		$extra->field=$col;
      		$extra->save();
      		$status="OK";
      	}
      } else { // Not checked !
      	if (isset($extraReadonlyFields[$idType]) and in_array($col,$extraReadonlyFields[$idType])) {
      		// Exists in extrareadonlyfields => must delete
      		$crit=array('scope'=>$hideScope.'#'.$objectClass, 'idType'=>$idType, 'field'=>$col);
      		$extra=SqlElement::getSingleSqlElementFromCriteria('ExtraReadonlyField', $crit);
      		if ($extra->id) {
      			$extra->delete();
      			$status="OK";
      		} else {
      			traceLog("dupplicate entry in extrareadonlyfield for scope=$hideScope, class=$objectClass, idType=$idType,  col=$col");
      			$erf=new ExtraReadonlyField();
      			$extraList=$erf->getSqlElementsFromCriteria($crit);
      			foreach ($extraList as $extra) {
      				$extra->delete();
      			}
      			$status="OK";
      		}
      	} else {
      		// Not exists in extrareadonlyfields => nothing to do
      	}
      }
      $checkNameRequired="checkRequired_".$col."_".$idType;
      if (isset($_REQUEST[$checkNameRequired]) and $_REQUEST[$checkNameRequired]) { // Checked !
        if (isset($extraRequiredFields[$idType]) and isset($extraRequiredFields[$idType][$col]) and $extraRequiredFields[$idType][$col]=='required') {
          // Already exists in extrarequiredfields => nothing to do
        } else {
          // Not exists in extrarequiredfields => store new line
          $extra=new ExtraRequiredField();
          $extra->scope=$hideScope.'#'.$objectClass;
          $extra->idType=$idType;
          $extra->field=$col;
          $extra->save();
          $status="OK";
        }
      } else { // Not checked !
        if (isset($extraRequiredFields[$idType]) and isset($extraRequiredFields[$idType][$col]) and $extraRequiredFields[$idType][$col]=='required') {
          // Exists in extrarequiredfields => must delete
          $crit=array('scope'=>$hideScope.'#'.$objectClass, 'idType'=>$idType, 'field'=>$col);
          $extra=SqlElement::getSingleSqlElementFromCriteria('ExtraRequiredField', $crit);
          if ($extra->id) {
            $extra->delete();
            $status="OK";
          } else {
            // May be required for other reason ;)
            $status="OK";
          }
        } else {
          // Not exists in extrarequiredfields => nothing to do
        }
      }
    }
  }
}
  

// Return result ==============================================================================================
if ($status=='ERROR') {
  //Sql::rollbackTransaction();
  echo '<div class="messageERROR" >' .i18n('messageScreenCustomizationERROR').'<br/><br/>'.$error . '</div>';
} else if ($status=='OK'){
  unsetSessionValue('extraHiddenFieldsArray');
  unsetSessionValue('extraReadonlyFieldsArray');
  unsetSessionValue('extraRequiredFieldsArray');
  //Sql::commitTransaction();
  echo '<div class="messageOK" >' . i18n('messageScreenCustomizationOK') . '</div>';
} else {
  //Sql::rollbackTransaction();
  echo '<div class="messageNO_CHANGE" >' . i18n('messageScreenCustomizationNO_CHANGE') . '</div>';
}
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';
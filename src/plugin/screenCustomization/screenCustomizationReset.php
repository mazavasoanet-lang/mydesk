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
require_once "../plugin/screenCustomization/screenCustomizationFunctions.php";
require_once "../db/maintenanceFunctions.php";
require_once "../tool/file.php";
scriptLog('   ->/plugin/screenCustomization/screenCustomizationSaveField.php');

// Check access rights 
if (! securityCheckDisplayMenu(null,'ScreenCustomization') ) {
  traceHack("Invalid rights for screenCustomization");
}

if (! array_key_exists('screenCustomizationEditFieldObjectClass',$_REQUEST)) {
  throwError('Parameter screenCustomizationEditFieldObjectClass not found in REQUEST');
}
$objectClass=$_REQUEST['screenCustomizationEditFieldObjectClass'];
Security::checkValidClass($objectClass);
$obj=new $objectClass();
$table=$obj->getDatabaseTableName();

$error="";
// REMOVE CUSTOM FIELDS
$script="";
if (isset($objectClass::$_customFields)) {
	foreach ($objectClass::$_customFields as $field) {
	  $isSection=screenCustomizationIsSection($obj, $field);
	  $isMessage=screenCustomizationIsMessage($obj, $field);
		$dbFieldUsedByOtherObject=false;
		if (ucfirst($field)!=$field) {
		  $allObj=screenCustomisationGetAllClassList();
		  foreach ($allObj as $class=>$className) {
			$tmp=new $class();
				if ($class!=$objectClass and $tmp->getDatabaseTableName()==$obj->getDatabaseTableName()) {
				  foreach ($tmp as $tmpFld=>$tmpVal) {
						if ($obj->getDatabaseColumnName($tmpFld)==$field) {
						  $dbFieldUsedByOtherObject=true;
						  break 2;
						}
				  }
				}
		  }
		}
		if (!$dbFieldUsedByOtherObject and !$isSection and !$isMessage) {
		  $dbField=$obj->getDatabaseColumnName($field);
		  $script.="\n".'ALTER TABLE `'.$table.'` DROP COLUMN `'.$dbField.'` ;';
		  $cs=new ColumnSelector();
		  $res=$cs->purge("scope='list' and objectClass='$objectClass' and attribute='$dbField'");
		}
	}
}
// Write and run script
if (!$error and $script) {
  $sqlfile=Plugin::getDir()."/screenCustomization/temp.sql";
  $handle=@fopen($sqlfile,"w");
  if (! $handle) {
    $status="ERROR";
    $error=i18n('errorWriteFile',array($sqlfile)).'<br/>..';
    errorLog($error);
  }
  if (! fwrite($handle,$script)) {
    $status="ERROR";
    $error=i18n('errorWriteFile',array($sqlfile)).'<br/>...';
    errorLog($error);
  }
  if (! fclose($handle)){
    $status="ERROR";
    $error=i18n('errorWriteFile',array($sqlfile)).'<br/>....';
    errorLog($error);
  }
  if (!$error) {
    $dbChangeToWrite=true;
    $nbErrors=runScript(null,$sqlfile);
    if ($nbErrors) {
       $status="ERROR";
       $error=i18n('errorWriteDatabase',array($nbErrors)).'<br/>';
    } else {
      unsetSessionValue('_tablesFormatList'); // To purge cache of desc
    }
  }
}

// Remove extra hidden fields and extra readonly fields
$extra=new ExtraHiddenField();
$extra->purge("scope like '%#$objectClass'");
unsetSessionValue('extraHiddenFieldsArray');
$extra=new ExtraReadonlyField();
$extra->purge("scope like '%#$objectClass'");
unsetSessionValue('extraReadonlyFieldsArray');

$customClassFile="../model/custom/$objectClass.php";
if (file_exists($customClassFile) ) {
  	kill($customClassFile);
}

if (!$error) {
  $status="OK";
}

// Return result ==============================================================================================
if ($status=='ERROR') {
  //Sql::rollbackTransaction();
  echo '<div class="messageERROR" >' . i18n('messageScreenCustomizationERROR').'<br/><br/>'.$error . '</div>';
} else if ($status=='INVALID' or $status=='WARNING') {
  //Sql::rollbackTransaction();
  echo '<div class="messageWARNING" >' .$error;
  echo '<br/><span style="color:#000;font-size:80%">'.i18n('messageScreenCustomizationReopen').'</span>';
  echo '</div>';
  echo '<input type="hidden" id="screenCustomizationReopenDialog" value="true" />';
} else if ($status=='OK'){
  //Sql::commitTransaction();
  echo '<div class="messageOK" >' . i18n('messageScreenCustomizationResetOK') . '</div>';
} else {
  //Sql::rollbackTransaction();
  echo '<div class="messageNO_CHANGE" >' . i18n('messageScreenCustomizationNO_CHANGE') . '</div>';
}
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';
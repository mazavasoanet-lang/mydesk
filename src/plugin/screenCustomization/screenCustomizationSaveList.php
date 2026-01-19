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
scriptLog('   ->/plugin/screenCustomization/screenCustomizationSaveList.php');

// Check access rights 
if (! securityCheckDisplayMenu(null,'ScreenCustomization') ) {
  traceHack("Invalid rights for screenCustomization");
}

if (! array_key_exists('customListClass',$_REQUEST)) {
  throwError('Parameter customListClass not found in REQUEST');
}
$listClass=$_REQUEST['customListClass'];
$listClass=ucfirst($listClass);

if (! array_key_exists('customListName',$_REQUEST)) {
  throwError('Parameter customListName not found in REQUEST');
}
$listName=$_REQUEST['customListName'];

$objectFileCustom="../model/custom/$listClass.php";
$objectFileSource="../plugin/screenCustomization/CustomListTemplate.php";

$status="NO_CHANGE";
$error=null;

// Controls for new item ===================================================================
if (!$error and SqlElement::class_exists($listClass)) {
  $status="INVALID";
  $error=i18n("errorListClassExists",array($listClass));
  errorLog($error);
}
if (!$error and (!trim($listClass) or !ctype_alnum($listClass) or !ctype_alpha(substr($listClass,0,1)))) {
  $status="INVALID";
  $error=i18n("errorListClassFormat",array($listClass));
  errorLog($error);
}
$keywords = array('abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor');
if (!$error and in_array(strtolower($listClass),$keywords)) {
  $status="INVALID";
  $error=i18n("errorListClassKeyword",array($listClass,'(<i>'.implode($keywords,', ').'</i>)'));
  errorLog($error);
}

// Read and Pre-format custom class file ===================================================================
if (! $error) {
  $globalCatchErrors=true;
  $file=@file_get_contents($objectFileSource);
  if (! $file) {
    $status="ERROR";
    $error=i18n('errorReadFile',array($objectFileSource));
    errorLog($error);
  }
  $globalCatchErrors=false;
}
// Write file !!! ===========================================================================================
if (!$error) {
  $file=str_replace('CustomListTemplate', $listClass, $file);
  $handle=@fopen($objectFileCustom,"w");
  if (! $handle) {
    $status="ERROR";
    $error=i18n('errorWriteFile',array($objectFileCustom)).'<br/>!!';
    errorLog($error);
  }
  if (! fwrite($handle,$file)) {
    $status="ERROR";
    $error=i18n('errorWriteFile',array($objectFileCustom)).'<br/>!!!';
    errorLog($error);
  }
  if (! fclose($handle)){
    $status="ERROR";
    $error=i18n('errorWriteFile',array($objectFileCustom)).'<br/>!!!!';
    errorLog($error);
  }
}

if (!$error and trim($listName)) { // Save new caption (if set)
  screenCustomisationAddTranslation('menu'.$listClass,ucfirst($listName),true);
  screenCustomisationAddTranslation($listClass,ucfirst($listName),true);
  screenCustomisationAddTranslation('colId'.$listClass,lcfirst($listName),true);
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
  echo '<div class="messageWARNING" >' .$error. '</div>';
} else if ($status=='OK'){
  //Sql::commitTransaction();
  echo '<div class="messageOK" >' . i18n('messageCustomListOK',array($listName)) . '</div>';
  echo '<input type="hidden" id="screenCustomisationSaveListNewClass" value="'.$listClass.'" />';
  echo '<input type="hidden" id="screenCustomisationSaveListNewClassName" value="'.$listName.'" />';
} else {
  //Sql::rollbackTransaction();
  echo '<div class="messageNO_CHANGE" >' . i18n('messageScreenCustomizationNO_CHANGE') . '</div>';
}
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';
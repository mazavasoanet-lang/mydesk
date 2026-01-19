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
scriptLog('   ->/plugin/screenCustomization/screenCustomizationDeleteList.php');

// Check access rights 
if (! securityCheckDisplayMenu(null,'ScreenCustomization') ) {
  traceHack("Invalid rights for screenCustomization");
}
if (! array_key_exists('customListClass',$_REQUEST)) {
  throwError('Parameter customListClass not found in REQUEST');
}
$listClass=$_REQUEST['customListClass'];
$reference='id'.$listClass;

$objectFileCustom="../model/custom/$listClass.php";

$status="NO_CHANGE";
$error=null;

// Controls before delete list ===================================================================
if (!$error and !SqlElement::class_exists($listClass)) { // $listClass must exist (as a class)
  $status="INVALID";
  $error=i18n("errorListClassNotExists",array($listClass));
  errorLog($error);
}

if (!$error ) { // $listClass must not be used (as $listClass)
  $classDir="../model/";
  $found="";
  if (is_dir($classDir)) {
    if ($dirHandler = opendir($classDir)) {
      while (($file = readdir($dirHandler)) !== false) {
        if ($file!="." and $file!="index.php" and $file!=".." and filetype($classDir . $file)=="file" and substr($file,-8)!='Main.php') {
          $split=explode('.',$file);
          $class=$split[0];
          if (property_exists($class,$reference)) {
            $found.='<br/> - '.$class;
          }
        }
      }
    }
  }
  if ($found!="") {
    $status="INVALID";
    $error=i18n("errorListClassReferenced",array($listClass, $found));
    errorLog($error);
  }
}
// Purge list (delete items) ============================================================
$plg=new PlgCustomList();
$clause="scope='$listClass'";
$plg->purge($clause);

// Delete class file ===================================================================
if (! $error) {
  $globalCatchErrors=true;
  if (! kill($objectFileCustom)) {
    $status="ERROR";
    $error=i18n('errorDeleteFile',array($objectFileCustom));
    errorLog($error);
  }
  $globalCatchErrors=false;
}

if (!$error and trim($listClass)) { // Save new caption (if set)
  screenCustomisationRemoveTranslation('menu'.$listClass,true);
  screenCustomisationRemoveTranslation($listClass,true);
  screenCustomisationRemoveTranslation('colId'.$listClass,true);
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
  echo '<div class="messageOK" >' . i18n('messageCustomListDeleteOK',array($listClass)) . '</div>';
} else {
  //Sql::rollbackTransaction();
  echo '<div class="messageNO_CHANGE" >' . i18n('messageScreenCustomizationNO_CHANGE') . '</div>';
}
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="delete">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';
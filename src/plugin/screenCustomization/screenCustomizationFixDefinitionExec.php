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
chdir("..");
//require_once('../model/_securityCheck.php');
require_once "../tool/projeqtor.php";
require_once "../plugin/screenCustomization/screenCustomizationFunctions.php";
require_once "../db/maintenanceFunctions.php";
if (file_exists('../plugin/screenCustomization/screenCustomizationFixDefinition.php')) {
  include_once('../plugin/screenCustomization/screenCustomizationFixDefinition.php');
}
$maintenance=true;
$doNotTriggerAlerts=true;
Sql::$maintenanceMode=true;
setSessionValue('setup', false, true);
Plugin::checkCustomDefinition();

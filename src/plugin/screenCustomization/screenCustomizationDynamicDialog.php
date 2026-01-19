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
include_once '../tool/projeqtor.php';
if (! array_key_exists('dialog', $_REQUEST)) {
	throwError('dialog parameter not found in REQUEST');
}
$dialog=$_REQUEST['dialog'];
//echo "<br/>".$dialog."<br/>";

$dialog=Security::checkValidAlphanumeric($dialog);
if (strtolower(substr($dialog,0,6))!='dialog' and strtolower(substr($dialog,0,4))!='list') {
  traceHack("dynamicDialog called with not allowed dialog parameter '$dialog'");
}
$dialogFile="../plugin/screenCustomization/screenCustomizationDynamic".ucfirst($dialog).'.php';
if (file_exists($dialogFile)) {
	include $dialogFile;
} else {
	echo "ERROR dialog=".htmlEncode($dialog)." is not an expected dialog";
}
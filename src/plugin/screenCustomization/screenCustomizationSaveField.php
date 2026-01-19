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

if (!isset($included)) $included=false;
if (!$included) chdir('../');
require_once "../tool/projeqtor.php";
require_once "../plugin/screenCustomization/screenCustomizationFunctions.php";
require_once "../db/maintenanceFunctions.php";
scriptLog('   ->/plugin/screenCustomization/screenCustomizationSaveField.php');

// Check access rights 
if (! $included and ! securityCheckDisplayMenu(null,'ScreenCustomization') ) {
  traceHack("Invalid rights for screenCustomization");
}

if (! array_key_exists('screenCustomizationEditFieldObjectClass',$_REQUEST)) {
  throwError('Parameter screenCustomizationEditFieldObjectClass not found in REQUEST');
}
$objectClass=$_REQUEST['screenCustomizationEditFieldObjectClass'];
Security::checkValidClass($objectClass);

if (! array_key_exists('screenCustomizationEditFieldField',$_REQUEST)) {
  throwError('Parameter screenCustomizationEditFieldField not found in REQUEST');
}
$field=$_REQUEST['screenCustomizationEditFieldField'];
//Security::checkValidAlphanumeric($field); // Will be check later

$delete=false;
if (array_key_exists('delete',$_REQUEST) and $_REQUEST['delete']=='true') {
  $delete=true;
  $new=false;
}
$stylingExist=false;
if (!$delete) {
  if (! array_key_exists('screenCustomizationEditFieldNew',$_REQUEST)) {
    throwError('Parameter screenCustomizationEditFieldNew not found in REQUEST');
  }
  $newVal=$_REQUEST['screenCustomizationEditFieldNew'];
  $new=($newVal=='true')?true:false;

  if (! array_key_exists('screenCustomizationEditFieldDataType',$_REQUEST)) {
    throwError('Parameter screenCustomizationEditFieldDataType not found in REQUEST');
  }
  $newDataType=$_REQUEST['screenCustomizationEditFieldDataType'];
  
  if (! array_key_exists('screenCustomizationEditFieldDataLength',$_REQUEST)) {
    throwError('Parameter screenCustomizationEditFieldDataLength not found in REQUEST');
  }
  $newDataLength=$_REQUEST['screenCustomizationEditFieldDataLength'];
  
  if (! array_key_exists('screenCustomizationEditFieldPosition',$_REQUEST)) {
    throwError('Parameter screenCustomizationEditFieldPosition not found in REQUEST');
  }
  $newPosition=$_REQUEST['screenCustomizationEditFieldPosition'];

  if (! array_key_exists('screenCustomizationEditFieldName',$_REQUEST)) {
    throwError('Parameter screenCustomizationEditFieldName not found in REQUEST');
  }
  $newFieldName=$_REQUEST['screenCustomizationEditFieldName'];
  
  if (! array_key_exists('screenCustomizationEditFieldDefaultValue',$_REQUEST)) {
    throwError('Parameter screenCustomizationEditFieldDefaultValue not found in REQUEST');
  }
  $newDefaultValue=$_REQUEST['screenCustomizationEditFieldDefaultValue'];
  
  if (RequestHandler::isCodeSet('screenCustomizationFontStyle')
      and RequestHandler::isCodeSet('screenCustomizationLabelStyle')
      and RequestHandler::isCodeSet('screenCustomizationFieldStyle'))  {
    $stylingExist=true;
  }
  $newStylingFont=RequestHandler::getValue('screenCustomizationFontStyle');
  $newStylingLabel=RequestHandler::getValue('screenCustomizationLabelStyle');
  $newStylingField=RequestHandler::getValue('screenCustomizationFieldStyle');
}

$obj=new $objectClass();
$objectClassMain=$objectClass.'Main';
$objMain=new $objectClassMain();

$objectFile="../model/$objectClass.php";
$objectFileMain="../model/$objectClassMain.php";
$objectFileCustom="../model/custom/$objectClass.php";

$dataType=screenCustomizationGetDataType($obj,$field);
$dataLength=screenCustomizationGetDataLength($obj,$field);
if ($field=='_Note') {
  $newPosition=null;
}
if ($field=='_Attachment') {
  $newPosition=null;
}
if ($delete) {
  // Retreive old values to avoid to detect unexpected changes
  $newDataType=$dataType;
  $newDataLength=$dataLength;
  $newPosition=screenCustomizationGetPredecessorField($obj,$field);
  $newFieldName=$obj->getColCaption($field);
  $newDefaultValue=null;
}
$status="NO_CHANGE";
$error=null;
$isSection=screenCustomizationIsSection($obj, $field);
$isSpecific=screenCustomizationIsSpecific($obj,$field);
$isMessage=screenCustomizationIsMessage($obj, $field);
$isArray=screenCustomizationIsArray($obj,$field);

$attributes=array();
$attributeChange=false;
if (!$delete) {
  foreach ($availableAttributes as $attr) {
    $val=false;
    if (array_key_exists('attribute'.ucfirst($attr),$_REQUEST)) {
      $val=true;
      if (!$obj->isAttributeSetToField($field,$attr)) {
        $attributeChange=true;
      }
    } else {
      if ($obj->isAttributeSetToField($field,$attr)) {
        $attributeChange=true;
      }
    }
    $attributes[$attr]=$val;
  }
}
$attributesMain=explode(',',$objMain->getFieldAttributes($field));

$moveField=false;
$oldPosition=screenCustomizationGetPredecessorField($obj,$field);
if ($oldPosition!=$newPosition) {
  $moveField=true;
}

$changeToWrite=false;
if ($attributeChange or $new or $delete or $moveField or $stylingExist) {
  $changeToWrite=true;
}

if ($newFieldName!=$obj->getColCaption($field)) {
  $changeToWrite=true;
}
if ($newDefaultValue!=$obj->getDefaultValueString($field)) {
  $changeToWrite=true;
}
$changeToWrite=true;
// Controls for new item ===================================================================
if ($new) {
  $newFieldName=trim($newFieldName);
  $field=trim($field);
  if (property_exists($objectClass, $field)) { // Dupplicate field
    $status="INVALID";
    $error=i18n("errorNewFieldDuplicate",array($field,$objectClass));
    errorLog($error);
  }
  $keyWords=array('add','alter','and','or','ascii','avg',
      'bool','boolean','before','by',
      'call','case','condition',
      'distinct','delete','desc','describe','drop','database',
      'first','from','function',
      'group',
      'index','in','insert','interval','is',
      'key','keys',
      'like','limit','long',
      'match',
      'not','new',
      'option','or','order',
      'partition','procedure',
      'references',
      'select',
      'table','to',
      'update',
      'where'
  );
  if (in_array(strtolower($field),$keyWords)) {
    $status="INVALID";
    $error=i18n("errorKeyword",array($field));
    errorLog($error);
  }
  if ($isMessage) {
  	$rightPart=substr($field,5);
  	if (in_array(strtolower($rightPart),$keyWords)) {
  		$status="INVALID";
  		$error=i18n("errorKeyword",array($field));
  		errorLog($error);
  	}
  } else if (strrpos($field,'_')!==null) {
  	$rightPart=substr($field,strrpos($field,'_')+1);
  	if (in_array(strtolower($rightPart),$keyWords)) {
  		$status="INVALID";
  		$error=i18n("errorKeyword",array($field));
  		errorLog($error);
  	}
  }
  if (substr($field,0,2)=='id' and strlen($field)>2 and substr($field,2,1)==strtoupper(substr($field,2,1))) {
    if ($newDataType!='reference') {
      $status="INVALID";
      $error=i18n("errorReferenceFormat",array($field));
      errorLog($error);
    }
    if (strpos($field,'__id')>0) {
      $referenceClass=substr($field,strpos($field,'__id')+4);
    } else {
    	$referenceClass=substr($field,2);
    }
    if (! class_exists($referenceClass)) {
      $status="INVALID";
      $error=i18n("errorReferenceNonExistingClass",array($field,$referenceClass));
      errorLog($error);
    }
  } else if ($isSection or $isMessage) {
    // OK
  } else if (ucfirst($field)==$field) { // Reference to Object
    if ($field!='Origin') {
      $status="INVALID";
      $error=i18n("errorFieldNameFormat",array($field));
      errorLog($error);
    }
  } 
  $fldNameCheck=($isSection or $isMessage)?substr($field,5):$field;
  if (!ctype_alnum(str_replace('__','',$fldNameCheck)) or !ctype_alpha(substr(str_replace('__','',$fldNameCheck),0,1)) ) {
    $status="INVALID";
    $error=i18n("errorFieldNameFormat",array($field));
    errorLog($error);
  }
}
if (!$error) {
  if (! trim($newFieldName) and substr($objMain->getColCaption($field),0,1)=="[" and substr($objMain->getColCaption($field),-1)=="]" and !isset($_automaticFixing) ) {
    $status="INVALID";
    $error=i18n("errorUndefinedName",array($field));
    errorLog($error);
  }
}
if (!$error and $newDefaultValue) {
  if (substr($newDefaultValue,0,strlen(SqlElement::$_evaluationString))==SqlElement::$_evaluationString) {
    ob_start();
    echo '<div class="messageWARNING" >'.i18n('messageScreenCustomizationERROR').'<br/><br/><div style="text-align:left">'.i18n("errorInvalidEvaluationString",array(substr($newDefaultValue,strlen(SqlElement::$_evaluationString)),implode(", ",SqlElement::$_evaluationStringForbiddenKeywords))).'</div>';
    echo '<br/><span style="color:#000;font-size:80%">'.i18n('messageScreenCustomizationReopen').'</span>';
    echo '</div>';
    echo '<input type="hidden" id="screenCustomizationReopenDialog" value="true" />';
    echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
    echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="INVALID">';
    if ( ! $obj->checkValidEvaluationString($newDefaultValue)) {
      $status="INVALID";
      $error=i18n("errorInvalidEvaluationString",array(substr($newDefaultValue,strlen(SqlElement::$_evaluationString)),implode(", ",SqlElement::$_evaluationStringForbiddenKeywords)));
    }
    ob_end_clean();
  } else if ($newDataType=='reference') {
    if (Security::checkValidId($newDefaultValue,false)===null or strlen($newDefaultValue)>12) {
      $status="INVALID";
      $error=i18n("errorInvalidFormat",array($newDefaultValue,$newDataType));
    }
  } else if ($newDataType=='int') {
    if (Security::checkValidInteger($newDefaultValue,false)===null or strlen($newDefaultValue)>$newDataLength) {
      $status="INVALID";
      $error=i18n("errorInvalidFormat",array($newDefaultValue,$newDataType.'('.$newDataLength.')'));
    }
  } else if ($newDataType=='numeric' or $newDataType=='decimal') {  
    $fmt=explode(',',$newDataLength);
    $fmtEnt=$fmt[0];
    $fmtDec=(count($fmt)>1)?$fmt[1]:0;
    $fmtEnt-=$fmtDec;
    $newDefaultValue=round($newDefaultValue,$fmtDec);
    if (Security::checkValidNumeric($newDefaultValue,false)===null or strlen(intval($newDefaultValue))>$fmtEnt) {
      $status="INVALID";
      $error=i18n("errorInvalidFormat",array($newDefaultValue,$newDataType.'('.$newDataLength.')'.$fmtEnt.','.$fmtDec));
    }
  } else if ($newDataType=='boolean') {   
    if (Security::checkValidBoolean($newDefaultValue,false)===null ) {
      $status="INVALID";
      $error=i18n("errorInvalidFormat",array($newDefaultValue,$newDataType));
    }   
  } else if ($newDataType=='date' or $newDataType=='datetime') {
    if (Security::checkValidDateTime($newDefaultValue,false)===null or ($newDataType=='date' and strlen($newDefaultValue)>10 )) {
      $status="INVALID";
      $error=i18n("errorInvalidFormat",array($newDefaultValue,$newDataType));
    }
  } else if ($newDataType=='varchar') {
    if (strlen($newDefaultValue)>$newDataLength) {
      $status="INVALID";
      $error=i18n("errorInvalidFormat",array($newDefaultValue,$newDataType.'('.$newDataLength.')'));
    }
  } else if ($newDataType=='mediumtext') {
    // No restriction
  } else {  
    $status="INVALID";
    $error="$newDataType is not an expected data type";
  }
}
// Generate Custom Object class ===================================================================
if ($changeToWrite) {
  if (! file_exists($objectFileCustom)) {
    if (! file_exists($objectFile)) {
      $status="ERROR";
      $error="file $objectFile does not exist for class $objectClass";
      throwError($error); // Will exit
    }
    if (! file_exists($objectFileMain)) {
      $status="ERROR";
      $error="file $objectFileMain does not exist for class $objectClass";
      throwError($error); // Will exit
    }
    $globalCatchErrors=true;
    if (! @copy($objectFile, $objectFileCustom)) {
      $status="ERROR";
      $error=i18n('errorCopyFile',array($objectFile,$objectFileCustom,'/model/custom'));
      errorLog($error);
    }
    $globalCatchErrors=false;
  }
}
// Read and Pre-format custom class file ===================================================================
if (! $error and $changeToWrite) {
  $globalCatchErrors=true;
  if (!isset($file)) $file=@file_get_contents($objectFileCustom); // if comes from screenCustomizationFixDefinition, must keep last $file version
  $fileMain=@file_get_contents($objectFileMain);
  if (! $file) {
    $status="ERROR";
    $error=i18n('errorReadFile',array($objectFileCustom));
    errorLog($error);
  }
  if (! @touch($objectFileCustom)) {
    $status="ERROR";
    $error=i18n('errorWriteFile',array($objectFileCustom)).'<br/>!';
    errorLog($error);
  }
  $globalCatchErrors=false;
}
if (!$error and $changeToWrite and $file and $fileMain) {
  
  // =================== Insert getStaticFieldsAttributes() if not exists -----
  if (strpos($file,'function getStaticFieldsAttributes()')==false) {
    $extra='
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
  ';
    $lastBracet=strrpos($file,'}');
    $file=substr($file,0,$lastBracet-1).$extra.substr($file, $lastBracet); 
  }
  
  // =================== Insert $_fieldsAttributes if not exists -----
  if (strpos($file,' $_fieldsAttributes')==false) {
    // Retreive $_fieldsAttribute in Main class
    $start=strpos($fileMain,' $_fieldsAttributes');
    $startArray=strpos($fileMain,'(',$start);
    $endArray=strpos($fileMain,');',$startArray-1);
    $mainFieldsAttribute=substr($fileMain,$startArray+1,$endArray-$startArray-1);
    $mainFieldsAttribute=str_replace("'",'"',$mainFieldsAttribute);
    $mainFieldsAttribute=str_replace(array(',,',', ,'),',',$mainFieldsAttribute);
    $extra='
      
  private static $_fieldsAttributes=array(
        '.$mainFieldsAttribute.'
      );
  
  ';
    $extra=str_replace("   \n","\n",$extra);
    $extra=str_replace("  \n","\n",$extra);
    $extra=str_replace(" \n","\n",$extra);
    $extra=str_replace("\n\n","\n",$extra);
    $firstBracet=strpos($file,'{');
    $descPos=strpos($file,'public $id;');
    if ($descPos and $descPos>$firstBracet) {
    	$constComPos=strpos($file,'/** =',$descPos);
    	$constPos=strpos($file,'function __construct',$descPos);
    	if ($constComPos and $constComPos<$constPos and $constComPos>$constPos-300) {
    		$firstBracet=$constComPos-1;
    	} else {
    		$firstBracet=$constPos-1;
    	}
    }
    $file=substr($file,0,$firstBracet+1).$extra.substr($file, $firstBracet+1);
  }
  // Retreive $_fieldsAttribute in Custom class
  $start=strpos($file,' $_fieldsAttributes');
  $startArray=strpos($file,'(',$start);
  $endArray=strpos($file,');',$startArray);
  $fieldsAttribute=substr($file,$startArray+1,$endArray-$startArray-2);
  $nb=0;
  while (strpos($fieldsAttribute, '//')) {
    $nb++;
    $debComment=strpos($fieldsAttribute, '//');
    $finComment=99999;
    $firstQuote=strpos($fieldsAttribute,'"',$debComment);
    if ($firstQuote and $firstQuote<$finComment) $finComment=$firstQuote;
    $firstComma=strpos($fieldsAttribute,',',$debComment);
    if ($firstComma and $firstComma<$finComment) $finComment=$firstComma;
    $firstLB=strpos($fieldsAttribute,"\r",$debComment);
    if ($firstLB and $firstLB<$finComment) $finComment=$firstLB;
    $firstNL=strpos($fieldsAttribute,"\r",$debComment);
    if ($firstNL and $firstNL<$finComment) $finComment=$firstNL;
    if (!$finComment) break;
    $fieldsAttribute=substr($fieldsAttribute,0,$debComment-1).substr($fieldsAttribute,$finComment);
  }
  $fieldsAttribute=str_replace(array("'"," ","\r\n","\n"),array('"','','',''),$fieldsAttribute);
  // Build new value for attributes
  $attributesToWrite=$attributesMain;
  foreach ($attributes as $attr=>$val) {
    if ($val) {
      if (! in_array($attr,$attributesToWrite)) {
        $attributesToWrite[]=$attr;
      }
      if ($attr=='hidden') {
        if (in_array('nobr',$attributesToWrite)) {
          $prec=screenCustomizationGetPredecessorField($obj,$field);
          if (! $obj->isAttributeSetToField($prec,'nobr')) {
            unset($attributesToWrite[array_search('nobr',$attributesToWrite)]);
          }
          //unset($attributesToWrite['nobr']);
        }
      }
    } else {
      if (in_array($attr,$attributesToWrite)) {
        $attributesToWrite=array_diff($attributesToWrite, array($attr));
      }
    }
  }
  $extra='';
  // Sort $attributesToWrite following $availableAttributes
  $sortedAttrList=array();
  foreach ($availableAttributes as $tempAttr) {
    if (in_array($tempAttr,$attributesToWrite)) {
      $sortedAttrList[]=$tempAttr;
    }
  }
  foreach ($attributesToWrite as $tempAttr) {
    if (!in_array($tempAttr,$attributesToWrite)) {
      $sortedAttrList[]=$tempAttr;
    }
  }
  $attributesToWrite=$sortedAttrList;
  foreach ($attributesToWrite as $attr) {
    $extra.=(($extra!='')?',':'').$attr;
  }
  $extra='"'.$field.'"=>"'.$extra.'"';
  // Replace field attributes in $fieldsAttribute
  $start=strpos($fieldsAttribute,'"'.$field.'"');
  if ($start===false) {
    $fieldsAttribute.=((strpos($fieldsAttribute,'=>')!=false)?',':'').$extra;
  } else {
    $end=strpos($fieldsAttribute,'"',$start+strlen($field)+5);
    $fieldsAttribute=substr($fieldsAttribute,0,$start).$extra.substr($fieldsAttribute,$end+1);
  }
  $fieldsAttribute=str_replace(array(',,',', ,'),',',$fieldsAttribute);
  $fieldsAttribute="\r\n    ".str_replace('","','"'.",\r\n    ".'"',$fieldsAttribute)."\r\n  ";
  $file=substr($file,0,$startArray+1).$fieldsAttribute.substr($file,$endArray);
}

if (!$error and ($stylingExist or $new or $delete) and $file and $fileMain) {
  // =================== Insert getStaticDisplayStyling() if not exists -----
  $styling=$obj->getStaticDisplayStyling();
  if ($delete) {
    if (isset($styling[$field])) unset($styling[$field]);
  } else if ($newStylingField or $newStylingFont or $newStylingLabel) {
    $styling[$field]=array('caption'=>$newStylingFont.$newStylingLabel,'field'=>$newStylingFont.$newStylingField);
  } else if (isset($styling[$field])) {
    unset($styling[$field]);
  }
  if (strpos($file,'function getStaticDisplayStyling()')==false) {
    $extra='
	/** ==========================================================================
	 * Return the specific styling for fields
	 * @return the fields styling
	 */	
	public function getStaticDisplayStyling() {
	  return self::$_staticDisplayStyling;
	}
  ';
    $lastBracet=strrpos($file,'}');
    $file=substr($file,0,$lastBracet-1).$extra.substr($file, $lastBracet);
  }
  // Insert $_fieldsAttributes if not exists -----
  if (strpos($file,' $_staticDisplayStyling')==false) {
    $extra='
  private static $_staticDisplayStyling=array(
  ); 
  
  ';
    $firstBracet=strpos($file,'{');
    $constComPos=strpos($file,'/** =',$firstBracet);
    $constPos=strpos($file,'function __construct');
    if ($constComPos and $constComPos<$constPos and $constComPos>$constPos-300) {
      $firstBracet=$constComPos-1;
    } else {
      $firstBracet=$constPos-1;
    }
    $file=substr($file,0,$firstBracet+1).$extra.substr($file, $firstBracet+1);
  }
  // Retreive $_fieldsAttribute in Custom class
  $start=strpos($file,' $_staticDisplayStyling');
  $startArray=strpos($file,'(',$start);
  $endArray=strpos($file,');',$startArray);
  $extra='';
  foreach ($styling as $attr=>$val) {
    if (!is_array($val)) $val=array('caption'=>'','field'=>'');
    $styleLabel=$val['caption'];
    $styleField=$val['field'];
    $extra.=(($extra!='')?',':'');
    $extra.="
        ";
    $extra.="'$attr'=>array('caption'=>'$styleLabel','field'=>'$styleField')";
  }
  $extra.="\r\n";
  // Replace field styliong in $_staticDisplayStyling
  $file=substr($file,0,$startArray+1).$extra.substr($file,$endArray);
}
$isCustomField=false;
if ($new) {
  $isCustomField=true;
} else if (property_exists($objectClass, '_customFields')) {
  $customFieldsArray=$objectClass::$_customFields;
  if (in_array($field,$customFieldsArray)) {
    $isCustomField=true;
  }
}
if (!$error and !$delete and $isSection) {
  $defaultStatusClosed=RequestHandler::getBoolean('screenCustomizationSectionDefaultStatusClosed');
  $scope=$objectClass.'_'.substr($field,5);
  $crit=array('scope'=>$scope, 'idUser'=>'0');
  $collapsed=SqlElement::getSingleSqlElementFromCriteria('Collapsed', $crit);
  if ($collapsed->id) {
    if (!$defaultStatusClosed) {
      //$collapsed->delete();
      Collapsed::expand($scope,'0');
      $status="OK";
    }
  } else {
    if ($defaultStatusClosed) {
      //$collapsed->scope=$scope;
      //$collapsed->idUser='0';
      //$collapsed->save();
      Collapsed::collapse($scope,'0');
      $status="OK";
    }
  }
} 
if (!$error and ($new or $delete or $moveField) and $file and $fileMain) {
  // =================== Insert fields definition if not exists -----
  $start=strpos($file,'{'); // Will include fields list just after class name line
  $end=strpos($file,"private static"); // Search end of public fields definition. Default is first private static field
  $construct=strpos($file,"function __construct"); // Where is construct (Must be found)
  if ($construct==0) {
    $status="ERROR";
    $error="file $objectFile does not include a __construct function. Fix issue and try again.";
    throwError($error); // Will exit
  }
  if ($construct<$end or $end==0) $end=$construct; // __construct is before first private static field or there is no private static field
  $extra="\r\n  ";
  $continueMode=false;
  $alreadyWritten=array();
  //foreach ($obj as $col=>$val) {
  foreach (screenCustomizationGetFieldsList($obj) as $col) {
    if (! property_exists($obj, $col)) continue;
    $val=(property_exists($obj, $col) and isset($obj->$col))?$obj->$col:null;
    if ($col=='_calculateForColumn' or $col=='_sortCriteriaForList' or $col=='_Attachment' or $col=='_Link' or $col=='_Note') continue;
    $fldValue="";
    if (is_array($obj->$col)) {
      $fldValue='=array(';
      $cpt=0;
      foreach ($obj->$col as $fldValueItem) {
        if ($cpt>0) $fldValue.=','; 
        $fldValue.="'$fldValueItem'";
        $cpt++;
      }
      $fldValue.=')';
    }
    else if (is_object($val)) $fldValue='';
    else if ($col=='_workVisibility' or $col=='_costVisibility') $fldValue='';
    else if ($val and $col!='idUser' and $val!=date('Y-m-d')) {
      if (is_numeric($val)) $fldValue='='.$val;
    }
    if ($col==$field and $newPosition!=$field) continue; // skip current field : will be inserted after identified position
    if (isset($alreadyWritten[$col])) { continue;}
    if (substr($col,0,1)!='_') {
      $fldValue='';
    }
    $extra.="\r\n  ".'public $'.$col.$fldValue.';';
    $alreadyWritten[$col]=$col;
    if ($col==$newPosition and !$delete and $newPosition!=$field) {
      if ($isArray) {
      	$fldValue='=array(';
      	if (substr($col,0,5)=='_tab_') {
      		foreach ($fldValue as $valAr) {
      			if ($fldValue!='=array(') $fldValue.=',';
      			$fldValue.="'$valAr'";
      		}
      	}
      	$fldValue.=')';
      } else {
        $fldValue='';
      }
      $extra.="\r\n  ".'public $'.$field.$fldValue.';';
      $alreadyWritten[$field]=$field;
      if ($obj->isAttributeSetToField($field,'nobr') or $isSection) { // Will move following fields
        $foundCurrent=false;
        //foreach ($obj as $colMv=>$valMv) {
        foreach (screenCustomizationGetFieldsList($obj) as $colMv) {
          if (! property_exists($obj, $col)) continue;
          $valMv=(property_exists($obj, $colMv) and isset($obj->$colMv))?$obj->$colMv:null;
          if ($isSection and $foundCurrent and (substr($colMv,0,5)=='_sec_' or $colMv=='_nbColMax'
          or $colMv=='_Link' or $colMv=='_Attachment' or $colMv=='_Note') ) {
            break;
          }
          if ($colMv==$field) {
            $foundCurrent=true;
            continue;
          } else if (!$foundCurrent) {
            continue;
          }
          // Current already found
          if (isset($alreadyWritten[$colMv])) {
            if (screenCustomizationIsTable($obj, $colMv)) {
              $tabValue='';
              foreach ($valMv as $vals) { $tabValue.=(($tabValue=='')?'':',')."'$vals'";}
              $extra=str_replace("\r\n  ".'public $'.$colMv.'=array('.$tabValue.');','',$extra);
            }else if (screenCustomizationIsArray($obj, $colMv)) {
              $extra=str_replace("\r\n  ".'public $'.$colMv.'=array();','',$extra);
            }else{
              $extra=str_replace("\r\n  ".'public $'.$colMv.';','',$extra);
            }
          }
          if (screenCustomizationIsTable($obj, $colMv)) {
            $tabValue='';
            if (!$valMv) $valMv=$obj->$colMv;
            foreach ($valMv as $vals) {$tabValue.=(($tabValue=='')?'':',')."'$vals'";}
            $extra.="\r\n  ".'public $'.$colMv.'=array('.$tabValue.');';
          } else if (screenCustomizationIsArray($obj, $colMv)) {
            $extra.="\r\n  ".'public $'.$colMv.'=array();';
          } else {
            $extra.="\r\n  ".'public $'.$colMv.';';
          }
          $alreadyWritten[$colMv]=$colMv;
          if (! $isSection and ! $obj->isAttributeSetToField($colMv,'nobr')) {
            break;
          }
        }
      }
    }
  }
  $extra.="\r\n\r\n  ";
  // Add custom fields array 
  $arrayCustomFields=array();
  if ( property_exists($objectClass, '_customFields')) $arrayCustomFields=$objectClass::$_customFields;
  if ($new) $arrayCustomFields[]=$field;
  if ($delete) unset($arrayCustomFields[array_search($field,$arrayCustomFields)]);
  if (count($arrayCustomFields)) {
    $extra.='public static $_customFields=array(';
    $nbCustom=0;
    foreach ($arrayCustomFields as $custFld) {
      $extra.="\r\n    ".(($nbCustom>0)?',':'')."'$custFld'";
      $nbCustom++;
    }
    $extra.="\r\n  );\r\n\r\n  ";
  }
  $file=substr($file,0,$start+1).$extra.substr($file,$end);
}
if (!$error and isset($file) and $fileMain) {
  // Insert getStaticColCaptionTransposition() if not exists -----
  if (strpos($file,'function setAttributes(')==false) {
    $extra='
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
';
    $lastBracet=strrpos($file,'}');
    $file=substr($file,0,$lastBracet-1).$extra.substr($file, $lastBracet);
  } else {
    str_replace('$parent=new $parentClass($this->id);', '$parent=new $parentClass($this->id,true);', $file);
  }
}

if (!$error and isset($file) and $fileMain) {
  // Insert getStaticColCaptionTransposition() if not exists -----
  if (strpos($file,'function getStaticColCaptionTransposition(')==false) {
    $extra='
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
  ';
    $lastBracet=strrpos($file,'}');
    $file=substr($file,0,$lastBracet-1).$extra.substr($file, $lastBracet);
  }
  if (strpos($file,'static $_colCaptionTransposition')==false) {
    $extra='
  private static $_colCaptionTransposition=array(
  ); 
  
  ';
    $firstBracet=strpos($file,'{');
    $constComPos=strpos($file,'/** =',$firstBracet);
    $constPos=strpos($file,'function __construct');
    if ($constComPos and $constComPos<$constPos and $constComPos>$constPos-300) {
      $firstBracet=$constComPos-1;
    } else {
      $firstBracet=$constPos-1;
    }
    $file=substr($file,0,$firstBracet+1).$extra.substr($file, $firstBracet+1);
  }
  // Retreive $_colCaptionTransposition in Custom class
  $start=strpos($file,' static $_colCaptionTransposition');
  $startArray=strpos($file,'(',$start);
  $endArray=strpos($file,');',$startArray);
  $fieldsCaptions=substr($file,$startArray+1,$endArray-$startArray-2);
  $fieldsCaptions=str_replace(array('"', "'"," ","\r\n","\n","\r"),array('','','','','',''),$fieldsCaptions);
  // Build new value for captions
  if (isset($objectClass::$_colCaptionTransposition)) {
    $captionsToWrite=$objectClass::$_colCaptionTransposition;
  } else {
    $captionsToWrite=array();
    $fieldsCaptionArray=explode(',',$fieldsCaptions);
    foreach ($fieldsCaptionArray as $capt) {
    	$captExp=explode('=>',$capt);
    	if (count($captExp)==2) $captionsToWrite[$captExp[0]]=$captExp[1];
    }

  } 
  $captionCode='plg'.ucfirst($field);
  if ($delete or !$newFieldName) {
    if ( isset($captionsToWrite[$field])) {
      unset($captionsToWrite[$field]);
    }
  } else if ($isCustomField) {
    $captionsToWrite[$field]=$captionCode;
  }
  $extra='';
  foreach ($captionsToWrite as $captCode=>$capt) {
    $extra.=(($extra!='')?',':'')."\r\n    ".'"'.$captCode.'"=>"'.$capt.'"';
  }
  $extra.="\r\n";
  $file=substr($file,0,$startArray+1).$extra.substr($file,$endArray);
}

if (!$error and isset($file) and $fileMain) {
  // Insert getStaticDefaultValues() if not exists -----
  if (strpos($file,'function getStaticDefaultValues(')==false) {
    $extra='
  /** ==========================================================================
	 * Return the generic defaultValues
	 * @return the layout
	 */
	protected function getStaticDefaultValues() {
	  return self::$_defaultValues;
	}
  ';
    $lastBracet=strrpos($file,'}');
    $file=substr($file,0,$lastBracet-1).$extra.substr($file, $lastBracet);
  }
  if (strpos($file,'static $_defaultValues')==false) {
    $extra='
  public static $_defaultValues=array(
  ); 
  
  ';
    $firstBracet=strpos($file,'{');
    $constComPos=strpos($file,'/** =',$firstBracet);
    $constPos=strpos($file,'function __construct');
    if ($constComPos and $constComPos<$constPos and $constComPos>$constPos-300) {
      $firstBracet=$constComPos-1;
    } else {
      $firstBracet=$constPos-1;
    }
    $file=substr($file,0,$firstBracet+1).$extra.substr($file, $firstBracet+1);
  }
  // Retreive $_defaultValues in Custom class
  $start=strpos($file,' static $_defaultValues');
  $startArray=strpos($file,'(',$start);
  $endArray=strpos($file,"); \n",$startArray);
  if (!$endArray) $endArray=strpos($file,"); \r\n",$startArray);
  if (!$endArray) $endArray=strpos($file,");\n",$startArray);
  if (!$endArray) $endArray=strpos($file,");\r\n",$startArray);
  if (!$endArray) $endArray=strpos($file,');',$startArray);
  $fieldsCaptions=substr($file,$startArray+1,$endArray-$startArray-2);
  // Build new value for
  if (isset($objectClass::$_defaultValues)) {
    $captionsToWrite=$objectClass::$_defaultValues;
  } else {
    $captionsToWrite=array();
  }
  if ($delete or !$newDefaultValue) {
    if ( isset($captionsToWrite[$field])) {
      unset($captionsToWrite[$field]);
    }
  } else if ($newDefaultValue) {
    $captionsToWrite[$field]=$newDefaultValue;
  }
  $extra='';
  foreach ($captionsToWrite as $captCode=>$capt) {
    if ($dataType!='varchar' and $dataType!='mediumtext') $capt=trim($capt);
    $extra.=(($extra!='')?',':'')."\r\n    ".'"'.$captCode.'"=>"'.str_replace(array("\\",'"','$'),array("\\\\",'\"','\$'),$capt).'"';
  }
  $extra.="\r\n";
  $file=substr($file,0,$startArray+1).$extra.substr($file,$endArray);
}
// ==========================================================================================
//Database changes
$table=$obj->getDatabaseTableName();
$dbField=$obj->getDatabaseColumnName($field);
$script="";
$dbChangeToWrite=false;
$dbFieldExists=false;
if ($new and ucfirst($field)!=$field and !$isSection and !$isMessage and !$isSpecific) {
  $dbFields=getExistingFields($objectClass);
  if (isset($dbFields[strtolower($field)])) {
    $dbFieldExists=true;
    //$existingDbField=$dbFields[strtolower($field)];
    //$split=explode('(', $existingDbField);
    //$existingDbField=$split[0];
	$existingDbField=screenCustomizationGetDataType($obj,$field);
    if ($existingDbField!=$newDataType) {
      $status="INVALID";
      $error=i18n('errorDbformatExisting',array($dbFields[strtolower($field)],$newDataType)).'<br/>';
      errorLog($error);
    }
  }
}
// Check if field (in db) is used by another object using same table : if so, do not delete field in db
$dbFieldUsedByOtherObject=false; 
if ($delete and ucfirst($field)!=$field and !$isSection and !$isSpecific and !$isMessage) {
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
// Check for format change 
if (! $error and !$isSection and !$isMessage and ($newDataLength!=$dataLength or $new)) { // Only length can be changed.
  if ($newDataType=='varchar') {
    if ($newDataLength==null or !trim($newDataLength) or !is_numeric($newDataLength) or $newDataLength<=0 or $newDataLength>4000) {
      $status="INVALID";
      $error=i18n('errorDbformatVarchar',array($newDataLength)).'<br/>';
      errorLog($error);
    }
  }
  if ($newDataType=='int') {
    if (!$newDataLength or !is_numeric($newDataLength) or $newDataLength<=1 or $newDataLength>255) {
      $status="INVALID";
      $error=i18n('errorDbformatInt',array($newDataLength)).'<br/>';
      errorLog($error);
    }
  }
  if ($newDataType=='decimal' or $newDataType=='numeric') {
    $split=explode(',', $newDataLength);
    $val1=$split[0];
    $val2=(count($split)>1)?$split[1]:0;
    if (!$newDataLength or count($split)>2 or !is_numeric($val1) or !is_numeric($val2) or $val1<0 or $val2<0 or $val1>65 or $val2>30) {
      $status="INVALID";
      $error=i18n('errorDbformatDecimal',array($newDataLength)).'<br/>';
      errorLog($error);
    }
  }
  if ( (!$new or $dbFieldExists) and ($newDataLength and $newDataLength!=$dataLength) ) {
    if ($newDataType=='boolean' or $newDataType=='varchar' or $newDataType=='decimal' or $newDataType=='int' or $newDataType=='numeric') {
      if ($newDataType=='boolean') {
        $script.="\n".'ALTER TABLE `'.$table.'` CHANGE `'.$dbField.'` `'.$dbField.'` int(1) unsigned DEFAULT 0 COMMENT \'1\';';
      } elseif ($dataLength) {
        $comment=($newDataType=='decimal' or $newDataType=='int' or $newDataType=='numeric')?" COMMENT '$newDataLength'":'';
        $script.="\n".'ALTER TABLE `'.$table.'` CHANGE `'.$dbField.'` `'.$dbField.'` '.$newDataType.'('.$newDataLength.') '.$comment.';';
        if ($dbField=='name' and $newDataType=='varchar') {
        	$peName=$objectClass.'PlanningElement';
        	if (property_exists($objectClass, $peName)) {       	  
        		$pe=new PlanningElement();
        		$peNameDataLength=screenCustomizationGetDataLength($pe,'refName');
        		if ($newDataLength>$peNameDataLength) {
        		  $peTable=$pe->getDatabaseTableName();
        		  $script.="\n".'ALTER TABLE `'.$peTable.'` CHANGE `refName` `refName` varchar('.$newDataLength.');';
        		  $script.="\n".'ALTER TABLE `'.$peTable.'baseline` CHANGE `refName` `refName` varchar('.$newDataLength.');';
        		}
        	}
        }
      } else  {
        $script.="\n".'ALTER TABLE `'.$table.'` CHANGE `'.$dbField.'` `'.$dbField.'` '.$newDataType.';';
      }
    }
  }
}
if (!$error and $new and !$dbFieldExists and !$isSection and !$isSpecific and !$isMessage) {
  if ($newDataType=='reference') {
    $newDataType='int';
    $newDataLength=12;
  }
  
  if ($newDataType=='varchar' or $newDataType=='decimal' or $newDataType=='int' or $newDataType=='numeric') {
    $comment=($newDataType=='decimal' or $newDataType=='int' or $newDataType=='numeric')?" COMMENT '$newDataLength'":'';
    $script.="\n".'ALTER TABLE `'.$table.'` ADD `'.$dbField.'` '.$newDataType.'('.$newDataLength.') '.$comment.';';
  } else if ($newDataType=='boolean') {
    $script.="\n".'ALTER TABLE `'.$table.'` ADD `'.$dbField.'` int(1) unsigned DEFAULT 0 COMMENT \'1\';';
  } else {
    $script.="\n".'ALTER TABLE `'.$table.'` ADD `'.$dbField.'` '.$newDataType.';';
  }
}

if (!$error and $delete and !$dbFieldUsedByOtherObject and !$isSection and !$isSpecific and !$isMessage) {
  $script="\n".'ALTER TABLE `'.$table.'` DROP COLUMN `'.$dbField.'` ;';
  $cs=new ColumnSelector();
  $res=$cs->purge("scope='list' and objectClass='$objectClass' and attribute='$field'");
}

// Write and run script
if (!$error and $script and !$isSection and !$isSpecific and !$isMessage) {
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
       //errorLog($error); already logged
    } else {
      unsetSessionValue('_tablesFormatList');
    }
  }
}

// Write file !!! ===========================================================================================
if (!$error and $changeToWrite) {
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
$fieldCaptionCode=$field;
$fieldCaptionWithoutPrefix=false;
if ($isSection) {
  $fieldCaptionCode='section'.ucfirst(substr($field,5));
  $fieldCaptionWithoutPrefix=true;
}
if ($isMessage){
	$fieldCaptionCode=substr($field,5);
	$fieldCaptionWithoutPrefix=true;
}
if (!$error and $new and $newFieldName) { // Save new caption
  screenCustomisationAddTranslation($fieldCaptionCode,$newFieldName,$fieldCaptionWithoutPrefix);
} else if (!$error and $newFieldName and $newFieldName!=$obj->getColCaption($field) and !$delete) {
  screenCustomisationAddTranslation($fieldCaptionCode,$newFieldName,$fieldCaptionWithoutPrefix);
  $status="OK"; // Will raise OK instead of NO_CHANGE if only change is caption
} else if (!$error and ($delete or !$newFieldName) and !$dbFieldUsedByOtherObject) {
  screenCustomisationRemoveTranslation($field);
  $status="OK"; // Will raise OK instead of NO_CHANGE if only change is caption
}

if (!$error and ($changeToWrite or $dbChangeToWrite)) {
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
  echo '<div class="messageOK" >' . i18n('messageScreenCustomizationOK') . '</div>';
} else {
  //Sql::rollbackTransaction();
  echo '<div class="messageNO_CHANGE" >' . i18n('messageScreenCustomizationNO_CHANGE') . '</div>';
}
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';

if (!$error and $changeToWrite and function_exists("opcache_invalidate") and file_exists($objectFileCustom)) {
  opcache_invalidate($objectFileCustom);
}
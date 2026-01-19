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
  
  function screenCustomizationGetDataType($obj,$col) {
    $dataType=$obj->getDataType($col);
    $dataLength=$obj->getDataLength($col);
    if ($dataType=='varchar') {
      if ($dataLength=='16777215') {
        $dataType='mediumtext';
      } else if ($dataLength=='16777215') {
        $dataType='mediumtext';
      } else if ($dataLength=='4294967295') {
        $dataType='longtext';
      } else if ($dataLength=='65535') {
        $dataType='text';
      }
    } else if ($dataType=='int') {
      if ($dataLength==1) {
        $dataType='boolean';
      } else if ($dataLength==12) {
        $dataType='reference';
      }
    } else if ($dataType=='undefined' and substr($col,0,5)=='_lib_') {
      $dataType='message';
    }
    return $dataType;
  }
  
  function screenCustomizationGetDataLength($obj,$col) {
    $dataLength=$obj->getDataLength($col);
    return $dataLength;
  }
  
  function screenCustomizationFormatDataType($obj,$col) {
    if (screenCustomizationIsArray($obj, $col)) return 'table';
    if ($col=='_productLanguage' or $col=='_productContext' or $col=='_productBusinessFeatures') return 'table';
    if (screenCustomizationIsSpecific($obj, $col)) return 'specific';
    if ($col=='OrganizationBudgetElementCurrent') return 'specific';
    if (substr($col,-15)=='PlanningElement' or substr($col,-11)=='WorkElement') return 'object';
    $dataType=screenCustomizationGetDataType($obj,$col);
    $dataLength=screenCustomizationGetDataLength($obj,$col);
    if (substr($col,0,7)=='_byMet_') return 'calculated';
    if ($obj->isAttributeSetTofield($col,'calculated')) $dataType='calculated';
    if ($dataType=='undefined') return '';
    
    $format=$dataType;
    if ($dataType=='varchar' or $dataType=='int' or $dataType=='numeric' or $dataType=='decimal') {
      $format.='('.$dataLength.')';
    }
    return $format;
  }
  function screenCustomizationFormatAllAttributes($obj,$col) {
    global $availableAttributes;
    $attributes=$obj->getFieldAttributes($col);
    $arrayAttr=array_unique(explode(',', $attributes));
    $result="";
    if ($col=='id') {
      $arrayAttr[]='readonly';
      $arrayAttr=array_reverse($arrayAttr);
    }
    $isReadOnly=false;
    foreach ($arrayAttr as $attr) {
      if (in_array($attr,$availableAttributes)) {
        if ($attr=='readonly') $isReadOnly=true;
        if ($result!='') $result.='&nbsp;&nbsp;';
        $result.='<img src="../plugin/screenCustomization/icon'.ucfirst($attr).'.png" title="'.i18n('attribute'.ucfirst($attr)).'" />';
      }
    }
    if ($obj->getDefaultValueString($col)) {
      if ($isReadOnly and substr($obj->getDefaultValueString($col),0,10)=='###EVAL###') $result.='<img src="../plugin/screenCustomization/iconCalculated.png" title="'.i18n('attributeCalculated').'" />';
      else $result.='<img src="../plugin/screenCustomization/iconDefault.png" title="'.i18n('attributeDefault').'" />';
    }
    if ($col=='id' and $result=='') $result=i18n('attributeReadonly');
    return $result;
  }
  function screenCustomizationIsAlwaysHidden($obj,$col) {
    global $notDisplayedSections;
    if ($col=='_calculateForColumn') return true;
    if ($col=='_sortCriteriaForList') return true;
    if ($col=='_spe_rf') return true;
    if ($col=='_spe_affectationGraph') return true;
    if ($col=='_spe_buttonAssignTeam') return true;
    if ($col=='_spe_idWorkUnits') return true;
    if ($col=='_spe_isLeaveMngActivity') return true;
    if ($col=='_productLanguage') return false;
    if ($col=='_productContext') return false;
    if ($col=='_productBusinessFeatures') return false;
    if (substr($col,0,9)=='_lib_help') return true;
    if ($col=='_lib_cancelled') return true;
    if (substr($col,0,7)=='_byMet_' and get_class($obj)=='Organization') return false;
    if (substr($col,0,5)=='_sec_') {
      $section=ucfirst(substr($col,5));
      if (in_array($section,$notDisplayedSections) ) {
        return true;
      } else {
        return false;
      }
    }
    if (substr($col,0,5)=='_spe_') return false;
    if (substr($col,0,5)=='_tab_') return true;
    if (substr($col,0,6)=='_Other') return true;
    if (substr($col,0,8)=='_button_') return false;
    if (substr($col,0,1)=='_' and substr($col,0,5)!='_lib_') return (!is_array($obj->$col));
    if ($col==get_class($obj).'PlanningElement' or $col=='MeetingPlanningElement') return false;
    if ($col == 'idUser' or $col == 'creationDate' or $col == 'creationDateTime') {
      return true;
    }
    if ( (get_class($obj)=='Requirement' or get_class($obj)=='TestSession') and ( substr($col,0,3)=='pct' or substr($col,0,3)=='run')) return true; 
    $mainClass=get_class($obj).'Main';
    $mainObj=new $mainClass();
    if ($mainObj->isAttributeSetToField($col,'hidden')) {
      return true;
    } else {
      return false;
    }
  }
  
  function screenCustomizationGetPredecessorField($obj,$field) {
    $prec='';
    //foreach ($obj as $fld=>$val) {
    foreach (screenCustomizationGetFieldsList($obj) as $fld) {      
      if (screenCustomizationIsAlwaysHidden($obj,$fld)) continue;
      if ($fld==$field) return $prec;
      $prec=$fld;
    }
    return $prec; // For new field, will place at last position
  }
  
  function screenCustomisationAddTranslation($col,$caption,$noPrefix=false) {
    global $currentLocale, $error, $status;
    $locale=$currentLocale;
    $source="default";
    $nlsRoot=dirname(__FILE__)."/../nls/$locale";
    if (! file_exists ( $nlsRoot )) {
      $globalCatchErrors=true;
      if (! @mkdir($nlsRoot) ) {
        $status="ERROR";
        $error=i18n('errorWriteFile',array($nlsRoot)).'<br/>!';
        errorLog($error);
      }
      $globalCatchErrors=false;
    }
    $langFile="$nlsRoot/lang.js";
    $langLines="";
    $langArray=array();
    if (file_exists ( $langFile )) { // Retreive existing custom translations for current locale
      $filename = $langFile;
      $file = fopen ( $filename, "r" );
      $nb=0;
      while ( $line = fgets ( $file ) ) {
        $split = explode ( ":", $line );
        if (isset ( $split [1] )) {
          $var = trim ( $split [0], ' ' );
          $valTab = explode ( ",", $split [1] );
          $val = trim ( $valTab [0], ' ' );
          $val = trim ( $val, '"' );
          if ($var=='currentLocaleOfFile') {
            break;
          }
          //if ($nb>0) echo ',';
          //$val=str_replace('&#44;',',',$val);
          //$val=str_replace('"','',$val);
          $replaceFrom=array('"',    ',',    ':');
          $replaceTo=  array('&#34;','&#44;','&#58;');
          str_replace($replaceFrom,$replaceTo,$val);
          $langArray[$var]=htmlEncodeJson($val);
          $nb++;
        }
      }
    }
    // Format caption
    $replaceFrom=array('"',    ',',    ':');
    $replaceTo=  array('&#34;','&#44;','&#58;');
    $caption=str_replace($replaceFrom,$replaceTo,$caption);
    if ($noPrefix) {
      $langArray[$col]=$caption;// Add new item;
    } else {
      $langArray['colPlg'.ucfirst($col)]=$caption;// Add new item;
    }
    ksort($langArray);
    $langLines="{ \n";
    foreach ($langArray as $cod=>$lib) {
      $langLines.=$cod.' : "'.str_replace(',','&#44;',$lib).'",'."\n";
    }    
    $langLines.="}";
    //Write file 
    $handle=@fopen($langFile,"w");
    if (! $handle) {
      $status="ERROR";
      $error=i18n('errorWriteFile',array($langFile)).'<br/>!!';
      errorLog($error);
    }
    if (! fwrite($handle,$langLines)) {
      $status="ERROR";
      $error=i18n('errorWriteFile',array($langFile)).'<br/>!!!';
      errorLog($error);
    }
    if (! fclose($handle)){
      $status="ERROR";
      $error=i18n('errorWriteFile',array($langFile)).'<br/>!!!!';
      errorLog($error);
    }
  }
  
  function screenCustomisationRemoveTranslation($col,$noPrefix=false) {
    global $currentLocale, $error, $status;
    $locale=$currentLocale;
    $source="default";
    $nlsRoot=dirname(__FILE__)."/../nls/$locale";
    if (! file_exists ( $nlsRoot )) {
      $globalCatchErrors=true;
      if (! @mkdir($nlsRoot) ) {
        $status="ERROR";
        $error=i18n('errorWriteFile',array($nlsRoot)).'<br/>!';
        errorLog($error);
      }
      $globalCatchErrors=false;
    }
    $langFile="$nlsRoot/lang.js";
    $langLines="";
    $langArray=array();
    if (file_exists ( $langFile )) { // Retreive existing custom translations for current locale
      $filename = $langFile;
      $file = fopen ( $filename, "r" );
      $nb=0;
      while ( $line = fgets ( $file ) ) {
        $split = explode ( ":", $line );
        if (isset ( $split [1] )) {
          $var = trim ( $split [0], ' ' );
          $valTab = explode ( ",", $split [1] );
          $val = trim ( $valTab [0], ' ' );
          $val = trim ( $val, '"' );
          if ($var=='currentLocaleOfFile') {
            break;
          }
          //if ($nb>0) echo ',';
          //$val=str_replace('&#44;',',',$val);
          //$val=str_replace('"','',$val);
          $replaceFrom=array('"',    ',',    ':');
          $replaceTo=  array('&#34;','&#44;','&#58;');
          str_replace($replaceFrom,$replaceTo,$val);
          $langArray[$var]=htmlEncodeJson($val);
          $nb++;
        }
      }
    }
    // Format caption
    if ($noPrefix) {
      if (isset($langArray[$col])) unset($langArray[$col]);
    } else {
      if (isset($langArray['colPlg'.ucfirst($col)])) unset($langArray['colPlg'.ucfirst($col)]);
    }
    ksort($langArray);
    $langLines="{ \n";
    foreach ($langArray as $cod=>$lib) {
      $langLines.=$cod.' : "'.str_replace(',','&#44;',$lib).'",'."\n";
    }
    $langLines.="}";
    //Write file
    $handle=@fopen($langFile,"w");
    if (! $handle) {
      $status="ERROR";
      $error=i18n('errorWriteFile',array($langFile)).'<br/>!!';
      errorLog($error);
    }
    if (! fwrite($handle,$langLines)) {
      $status="ERROR";
      $error=i18n('errorWriteFile',array($langFile)).'<br/>!!!';
      errorLog($error);
    }
    if (! fclose($handle)){
      $status="ERROR";
      $error=i18n('errorWriteFile',array($langFile)).'<br/>!!!!';
      errorLog($error);
    }
  }
  
  function screenCustomisationHasCustomTranslation($col) {
    global $currentLocale;
    $locale=$currentLocale;
    $source="default";
    $nlsRoot=dirname(__FILE__)."/../nls/$locale";
    $langFile="$nlsRoot/lang.js";
    if (file_exists ( $langFile )) { // Retreive existing custom translations for current locale
      $file = file_get_contents($langFile);
      if (strpos($file,'col'.ucfirst($col).':')>0) return true;
      if (strpos($file,'col'.ucfirst($col).' :')>0) return true;
      if (strpos($file,'colPlg'.ucfirst($col).':')>0) return true;
      if (strpos($file,'colPlg'.ucfirst($col).' :')>0) return true;
    }
    return false;
  }
  
  function screenCustomisationGetCustomClassList() {
    $dir='../model/custom';
    $handle = opendir($dir);
    $result=array();
    while ( ($file = readdir($handle)) !== false) {
      if ($file == '.' || $file == '..' || $file=='index.php' // exclude ., .. and index.php
      || substr($file,-4)!='.php'                           // exclude non php files
      || substr($file,0,1)=='_'                             // exclude non class files (_securityCheck.php)
      || $file=='PlgCustomList.php' )  {                    // exclude the *Main.php
        continue;
      }
      $class=pathinfo($file,PATHINFO_FILENAME);
      $ext=pathinfo($file,PATHINFO_EXTENSION);
      if (SqlElement::class_exists($class) && SqlElement::is_subclass_of($class, 'PlgCustomList')) {
        $result[$class]=i18n($class);
      }
    }
    closedir($handle);
    asort($result);
    return $result;
  }
  
  function screenCustomisationGetAllClassList() {
    $dir='../model';
    $handle = opendir($dir);
    $result=array();
    while ( ($file = readdir($handle)) !== false) {
      if ($file == '.' || $file == '..' || $file=='index.php' // exclude ., .. and index.php
      || substr($file,-4)!='.php'                           // exclude non php files
      || substr($file,0,1)=='_'    )  {                     // exclude non class files (_securityCheck.php)
        continue;
      }
      $class=pathinfo($file,PATHINFO_FILENAME);
      $ext=pathinfo($file,PATHINFO_EXTENSION);
      if (SqlElement::class_exists($class) && SqlElement::is_subclass_of($class, 'SqlElement')) {
        $result[$class]=i18n($class);
      }
    }
    closedir($handle);
    asort($result);
    return $result;
  }
  
  function getExistingFields($class) { // Similar to SqlElement->getFormatList() but with dbname instead of object field name
    $obj=new $class();
    $formatList= array();
    $query="desc " . $obj->getDatabaseTableName();
    if (Sql::isPgsql()) {
      $query="SELECT a.attname as field, pg_catalog.format_type(a.atttypid, a.atttypmod) as type"
          . " FROM pg_catalog.pg_attribute a "
              . " WHERE a.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname='".$obj->getDatabaseTableName()."')"
                  . " AND a.attnum > 0 AND NOT a.attisdropped"
                      . " ORDER BY a.attnum";
    }
    $result=Sql::query($query);
    while ( $line = Sql::fetchLine($result) ) {
      $fieldName=(isset($line['Field']))?$line['Field']:$line['field'];
      $type=(isset($line['Type']))?$line['Type']:$line['type'];
      $from=array();                               $to=array();
      if (Sql::isPgsql()) {
        $from[]='integer';                           $to[]='int(12)';
        $from[]='numeric(12,0)';                     $to[]='int(12)';
        $from[]='numeric(5,0)';                      $to[]='int(5)';
        $from[]='numeric(3,0)';                      $to[]='int(3)';
        $from[]='numeric(1,0)';                      $to[]='int(1)';
        $from[]=' without time zone';                $to[]='';
        $from[]='character varying';                 $to[]='varchar';
        $from[]='numeric';                           $to[]='decimal';
        $from[]='timestamp';                         $to[]='datetime';
      }
      $from[]='mediumtext';                          $to[]='varchar(16777215)';
      $from[]='longtext';                            $to[]='varchar(4294967295)';
      $from[]='text';                                $to[]='varchar(65535)';
      $type=str_ireplace($from, $to, $type);
      $formatList[strtolower($fieldName)] = $type;
    }
    return $formatList;
  }
  
  function getColCaption($obj,$col) {
    if (strtolower(substr($col,-9))=='structure') return trim(str_replace('#','',i18n("sectionStructure",array(i18n(get_class($obj)),''))));
    if (substr($col,-8)=='Language') return i18n("menuLanguage");
    if (substr($col,-7)=='Context') return i18n("menuContext");
    if (substr($col,-16)=='BusinessFeatures') return i18n("BusinessFeature");
    if (substr($col,-11)=='subproducts') return trim(str_replace('#','',i18n("sectionComposition",array(i18n(get_class($obj)),''))));
    if (substr($col,-11)=='Composition') return trim(str_replace('#','',i18n("sectionComposition",array(i18n(get_class($obj)),''))));
    if (substr($col,0,6)=='_Link_') return i18n(substr($col,6));
    if (substr($col,0,8)=='_button_') return i18n(substr($col,8));
    if ($col=='_byMet_hierarchicName') return i18n('colHierarchicString');
    if ($col=='OrganizationBudgetElementCurrent') return i18n('colBudget');
    if (substr($col,0,7)=='_byMet_') return i18n('col'.ucfirst(str_replace(array('daughters','Budget'),array('',''),substr($col,7))));
  	if (substr($col,0,5)=='_sec_') {
      $sectionName=substr($col,5);
      if(strstr($sectionName,'Link_')){
        $split=explode('_', $sectionName);
        $sectionName=$split[0].$split[1];
      }
      if (strpos($sectionName, '_')!=0) {
        $split=explode('_', $sectionName);
        $sectionName=$split[0];
      }
      return ucfirst(i18n('section'.ucfirst($sectionName)));
  		//return i18n('section'.ucfirst(str_replace(array("_left","_right"),array("",""),substr($col,5))));
  	}
  	if (substr($col,0,12)=="_Dependency_") {
  	  return i18n(substr($col,12));
  	}
  	if (substr($col,-15)=="PlanningElement") {
  	  return i18n("sectionProgress");
  	}
  	if (substr($col,0,5)=='_spe_') {
  		$test=substr($col,5);
  		if ($test=='image') return i18n('colPhoto');
  		if ($test=='dispatch') return i18n('dispatchWork');
  		if ($test=='run') return i18n('startWork');
  		if ($test=='paymentsList') return i18n('menuPayment');
  		if ($test=='tickets') return i18n('sectionTickets'.get_class($obj));
  		if ($test=='tenders') return i18n('menuTender');
  		if ($test=='Project') return i18n('menuProject');
  		if ($test=='buttonSendMail') return i18n('sendInfoToApprovers');
  		if ($test=='lockButton') return i18n('lock'.get_class($obj));
  		$test=i18n('col'.ucfirst(substr($col,5)));
  		if (substr($test,0,1)!='[' and substr($test,0,-1)!=']') return $test;
  		$test=i18n('section'.ucfirst(substr($col,5)));
  		if (substr($test,0,1)!='[' and substr($test,0,-1)!=']') return $test;
  	}
  	if (substr($col,0,1)=='_' and is_array($obj->$col)) {
  	  return i18n(substr($col,1));
  	}
  	return $obj->getColCaption($col);
  }
  
  function screenCustomizationIsArray($obj,$col) {
    if (!property_exists($obj, $col)) return false;
    if (! in_array($obj,screenCustomizationGetFieldsList($obj))) return false;
    if (substr($col,0,1)=='_' and is_array($obj->$col) and substr($col,0,5)!='_tab_') return true;
    else return false;
  }
  function screenCustomizationIsTable($obj,$col) {
    if (!property_exists($obj, $col)) return false;
    if (! in_array($obj,screenCustomizationGetFieldsList($obj))) return false;
    if (substr($col,0,1)=='_' and is_array($obj->$col) and substr($col,0,5)=='_tab_') return true;
    else return false;
  }
  function screenCustomizationIsSpecific($obj,$col) {
    if ($col=='Origin') return true;
    if (substr($col,0,5)=='_spe_') return true;
    return false;
  }
  function screenCustomizationIsMessage($obj,$col) {
    if (substr($col,0,5)=='_lib_') return true;
    return false;
  }
  
  function screenCustomizationIsSection($obj,$col) {
    return (substr($col,0,5)=='_sec_')?true:false;
  }
  
  function screenCustomizationGetFieldsList($obj) {
    if (method_exists($obj,'getFieldsList')) {
      $result=$obj->getFieldsList();
      foreach ($result as $id=>$prop) {
        if (! array_key_exists($prop, (array)$obj)) {
            unset($result[$id]);
        }
      }
      return $result;
    } else if (version_compare(PHP_VERSION, '8.1.0') >= 0) {
      $msg="PROJEQTOR ".Parameter::getGlobalParameter('dbVersion')." IS NOT COMPATIBLE WITH PHP ".PHP_VERSION;
      errorLog($msg);
      echo $msg;
      exit;
    } else {
      $res=array();
      foreach ($obj as $col=>$val) {
        $res[]=$col;
      }
      return $res;
    }
  
  }
  
  // Generic variables
  $notDisplayedSections=array('Void');
  $notMovableSections=array('Link','Assignment','Progress','Predecessor','Successor','TestCaseRun','Approver',
      'ExpenseDetail','ProductStructure_product','ProductStructure_component','ProductVersions','ComponentVersions',
      'Affectations', 'Versionproject_versions','Subprojects','SubProducts','RestrictTypes','Versionproject_projects',
      'ProductVersionStructure_product','Projects', 'Contacts', 'Trigger','ProductComposition','ProductVersionComposition',
	    'ComponentStructure','ComponentComposition','ComponentVersionStructure','ComponentVersionComposition',
      'Productproject_projects');
  //$availableDatatypes=array('varchar', 'mediumtext', 'reference', 'int', 'numeric', 'decimal', 'boolean' ,'date', 'datetime','section','message');
  $availableDatatypes=array('varchar', 'mediumtext', 'reference', 'int', 'decimal', 'boolean' ,'date', 'datetime','section');
  $availableDatatypesNolength=array('mediumtext', 'reference', 'boolean' ,'date', 'datetime','undefined','section','message');
  $availableAttributes=array('required','doNotAutoFill','readonly','hidden','unique','nobr');
  if (version_compare(Sql::getDbVersion(), 'V8.5.0',"<")){
    $availableAttributes=array('required','readonly','hidden','nobr');
  }
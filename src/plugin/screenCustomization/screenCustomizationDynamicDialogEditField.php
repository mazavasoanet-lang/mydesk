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

require_once('screenCustomizationFunctions.php');
if (! array_key_exists('objectClass',$_REQUEST)) {
  throwError('Parameter objectClass not found in REQUEST');
}
$objectClass=$_REQUEST['objectClass'];
Security::checkValidClass($objectClass);

$new=false;
if (array_key_exists('new',$_REQUEST)) {
  $new=true;
  $field=null;
}

if (!$new) {
  if (! array_key_exists('field',$_REQUEST)) {
    throwError('Parameter field not found in REQUEST');
  }
  $field=$_REQUEST['field'];
}
if (!$field) $field='';
Security::checkValidAlphanumeric(str_replace('_','',$field));
$obj=new $objectClass();
$objectClassMain=$objectClass.'Main';
$objMain=new $objectClassMain();

$isSection=screenCustomizationIsSection($obj,$field);
$isSpecific=screenCustomizationIsSpecific($obj,$field);
$isArray=screenCustomizationIsArray($obj,$field);
$hideName=false;
$canMove=true;
$sectionName='';
if (substr($field,0,5)=='_sec_') {
	$sectionName=ucfirst(substr($field,5));
}
if ($field=='_Attachment' or $field=='_Note') {
  $isSection=true;
  $hideName=true;
  $sectionName=substr($field,1);
  $canMove=false;
}
if ($field=='_Link' or $field=='_sec_Link') {
  $canMove=false;
  $hideName=true;
  $isSpecific=true;
}
if ($isSection) {
  $hideName=true;
}
if ($isSpecific) {
  $hideName=true;
}
if ($isArray) {
  $hideName=true;
}

if (!$new) {
  $dataType=screenCustomizationGetDataType($obj,$field);
  $dataLength=screenCustomizationGetDataLength($obj,$field);
} else {
  $dataType='';
  $dataLength='';
}

$lock=array();
$nobrWithLib=false;
foreach ($availableAttributes as $attr) {
  $lock[$attr]=false;
  if (($attr=='readonly' or $attr=='hidden') and $objMain->isAttributeSetToField($field,"required")) {
    $lock[$attr]=true;
  }
  if ($attr=='nobr' and property_exists($obj, '_lib_help'.ucfirst($field))) {
    $lock[$attr]=true;
    $nobrWithLib=true;
  }
}
if ($objMain->isAttributeSetToField($field,"readonly") or $dataType=='undefined') {
  $lock['readonly']=true;
  $lock['required']=true;
  $lock['doNotAutoFill']=true;
  $lock['unique']=true;
}
if ($dataType=='boolean') {
  $lock['required']=true;
}
if ($field=='id') {
  $lock['required']=true;
  $lock['readonly']=true;
}

$customField=false;
if (property_exists($objectClass, '_customFields')) {
  $customFieldsArray=$objectClass::$_customFields;
  if (in_array($field,$customFieldsArray)) {
    $customField=true;
  }
}

$inTable=0;
$inTableFields=array();
$inTableLast=array();
//foreach ($obj as $col=>$val) {
foreach (screenCustomizationGetFieldsList($obj) as $col) {
  if (substr($col,0,5)=='_tab_') {
    $expl=explode('_',$col);
    if (count($expl)>3) {
      $inTable=$expl[2]*$expl[3];
    }
  } else {
    if ($inTable>0) {
      $inTableFields[$col]=$col;
      if ($inTable==1) {
        $inTableLast[$col]=$col;
      }
      $inTable--;
    }
  }
}
?>
<div>
  <table style="width:100%;">
    <tr>
      <td>
       <form id='screenCustomizationEditFieldForm' name='screenCustomizationEditFieldForm' onSubmit="return false;" >
         <input id="screenCustomizationEditFieldObjectClass" name="screenCustomizationEditFieldObjectClass" type="hidden" value="<?php echo $objectClass;?>" />
         <input id="screenCustomizationEditFieldField" name="screenCustomizationEditFieldField" type="hidden" value="<?php echo $field;?>" />
         <input id="screenCustomizationEditFieldNew" name="screenCustomizationEditFieldNew" type="hidden" value="<?php echo ($new)?'true':'false';?>" />
         <br/>
         <table><tr><td style="width:49%;vertical-align:top">
         <table>
           <tr>
             <td colspan="3" class="listTitle section"><?php echo i18n('colElement').' '.i18n($objectClass);?></td>
           <tr/>
           <tr>
             <td colspan="3">&nbsp;</td>
           <tr/>
           <tr class="screenCustomizationLine" <?php if ($hideName) echo 'style="display:none"';?> >
             <td class="dialogLabel"><label for="" ><?php echo i18n('fieldId');?>&nbsp;:&nbsp;</label></td>
             <td>
               <input id="screenCustomizationEditFieldField" name="screenCustomizationEditFieldField"
                 dojoType="dijit.form.TextBox" type="text" maxlength="100" class="input" style="width:200px"
                 <?php if (!$new) echo " readonly ";?>
                 onkeypress="setTimeout('plg_screenCustomization_updateDbName();',20);"
                 value="<?php echo $field;?>" />
             </td>
             <td></td>
           </tr>
           <tr class="screenCustomizationLine">
             <td class="dialogLabel"><label for="" ><?php echo i18n('fieldName');?>&nbsp;:&nbsp;</label></td>
             <td>
               <div id="screenCustomizationEditFieldName" name="screenCustomizationEditFieldName"
                 dojoType="dijit.form.TextBox" type="text" maxlength="100" class="input" style="width:200px"
                 <?php if (!$new and !$customField) echo " readonly ";?>
                 <?php 
                 $fldName=$obj->getColCaption($field);
                 if ($isSection) $fldName=i18n('section'.$sectionName);
                 if ($dataType=='message') $fldName=i18n(substr($field,5));
                 ?>
                 value="<?php echo $fldName;?>" >
                 <script type="dojo/method" event="onKeypress" >
                   
                 </script>
               </div>
             </td>
             <td></td>
           </tr>
           <tr class="screenCustomizationLine notForSection" <?php if ($isSection or $isSpecific or $isArray) echo ' style="display:none;"';?> >
             <td class="dialogLabel"><label for="" ><?php echo i18n('fieldDbName');?>&nbsp;:&nbsp;</label></td>
             <td>
               <div id="screenCustomizationEditFieldDbColumnName" name="screenCustomizationEditFieldDbColumnName"
                 dojoType="dijit.form.TextBox" type="text" maxlength="100" class="input" style="width:200px"
                 readonly
                 value="<?php if (!$isSection) echo $obj->getDatabaseColumnName($field);?>" >
               </div>
             </td>
             <td></td>
           </tr>
           <tr class="screenCustomizationLine notForSection" <?php if ($isSection or $isSpecific or $isArray) echo ' style="display:none;"';?> >
             <td class="dialogLabel"><label for="screenCustomizationEditFieldDataType" ><?php echo i18n('fieldFormat');?>&nbsp;:&nbsp;</label></td>
             <td><select dojoType="dijit.form.FilteringSelect" class="input" style="width:200px;"
              <?php echo autoOpenFilteringSelect ();?>
               <?php if (!$new) echo "readonly";?>
               id="screenCustomizationEditFieldDataType" name="screenCustomizationEditFieldDataType" >
               <?php 
               foreach ($availableDatatypes as $type) {?>
               <option value="<?php echo $type;?>" <?php if ($type==$dataType) echo 'selected';?>><?php echo $type;?></option>
               <?php } 
               if (!$new and $dataType=='undefined') {?>
               <option value="undefined" selected ></option>
               <?php }?>
               <script type="dojo/method" event="onChange" >
                 if (this.value=='section') {
                   var nameFld=dijit.byId('screenCustomizationEditFieldField');
                   if (nameFld.get('value').substr(0,5)!='_sec_') {
                     nameFld.set('value','_sec_'+nameFld.get('value'));
                   }
                   plg_screenCustomization_showHideSectionAttributes(nameFld.get('value'));
                 } else if (this.value=='varchar' || this.value=='int' || this.value=='decimal' || this.value=='numeric') {
                   dijit.byId('screenCustomizationEditFieldDataLength').set('readonly',false);
                   dojo.removeClass(dijit.byId('screenCustomizationEditFieldDataLength').domNode,'dijitTextBoxReadOnly ');
                 } else {
                   dijit.byId('screenCustomizationEditFieldDataLength').set('value',null);
                   dijit.byId('screenCustomizationEditFieldDataLength').set('readonly',true);
                   dojo.addClass(dijit.byId('screenCustomizationEditFieldDataLength').domNode,'dijitTextBoxReadOnly');
                   if (this.value=='boolean') {
                     dijit.byId('attributeRequired').set('checked',false);
                     dijit.byId('attributeRequired').set('disabled','disabled');
                   }
                 }
               </script>
              </select></td>
             <td></td>
           </tr>
           <tr class="screenCustomizationLine notForSection" <?php if ($isSection or $isSpecific or $isArray) echo ' style="display:none;"';?> >
             <td class="dialogLabel"><label for="screenCustomizationEditFieldDataLength" ><?php echo i18n('fieldLength');?>&nbsp;:&nbsp;</label></td>
             <td><input dojoType="dijit.form.TextBox"
                <?php if (in_array($dataType,$availableDatatypesNolength)) echo 'readonly';?>
			          id="screenCustomizationEditFieldDataLength" name="screenCustomizationEditFieldDataLength"
			          style="width:100px;"
			          class="input" 
			          value="<?php if (!in_array($dataType,$availableDatatypesNolength)) echo $dataLength;?>" />
             <td></td>
           </tr>
           <tr class="screenCustomizationLine" <?php if (!$canMove) echo 'style="display:none"';?> >
             <td class="dialogLabel"><label for="screenCustomizationEditFieldPosition" ><?php echo i18n('fieldPlaceAfter');?>&nbsp;:&nbsp;</label></td>
             <td><div dojoType="dijit.form.Select" class="input" style="width:200px;"
               <?php //if (!$new) echo " readonly ";
               $prec=screenCustomizationGetPredecessorField($obj,$field);
               if (SqlElement::is_a($obj,'PlanningElement') or isset($inTableFields[$field]) or $obj->isAttributeSetToField($prec,'nobr')) {echo " readonly ";}
               ?>
               value="<?php echo $prec;?>"
               id="screenCustomizationEditFieldPosition" name="screenCustomizationEditFieldPosition" >
               <?php 
               if (!$prec) { // first item
                 echo '<span value="" selected > </span>';
               }
               //foreach ($obj as $lstcol=>$lstval) {
               foreach (screenCustomizationGetFieldsList($obj) as $lstcol) {  
                 if (screenCustomizationIsAlwaysHidden($obj,$lstcol)) continue;
                 if (isset($inTableFields[$lstcol]) and ! isset($inTableLast[$field]) and $lstcol!=$prec) continue;
                 if ($obj->isAttributeSetToField($lstcol,'nobr') and $lstcol!=$prec) continue; // Do not move after field with nobr (will move after next only)
                 if ($lstcol=='_Link' or $lstcol=='_Attachment' or $lstcol=='_Note' or $lstcol=='_sec_Link') continue;
                 ?>
                 <span value="<?php echo $lstcol;?>" <?php if ($lstcol==$prec) echo " selected" ?>><?php 
                 echo (substr($lstcol,0,5)=='_sec_')?'<span class="section">'.getColCaption($obj, $lstcol).'</span>':getColCaption($obj, $lstcol);?></span>
               <?php 
               } ?>
              </div></td>
             <td></td>
           </tr>
           <?php foreach ($availableAttributes as $attribute) {?>
           <tr class="screenCustomizationLine<?php if ($attribute!='hidden') echo ' notForSection';?>" <?php if ((($isSection or $isSpecific ) and $attribute!='hidden') or ($isArray and $field!="_Note" and $field!="_Attachment")) echo ' style="display:none;"';?>>
             <td class="dialogLabel"><label for="attribute<?php echo ucfirst($attribute);?>" ><?php echo i18n('attribute'.ucfirst($attribute));?>&nbsp;:&nbsp;</label></td>
             <td style="position:relative"><div dojoType="dijit.form.CheckBox" type="checkbox"
		            <?php if ($obj->isAttributeSetToField($field,$attribute) or ($attribute=='nobr' and $nobrWithLib)) echo " checked ";?>
		            <?php if ($lock[$attribute]) echo " readonly ";?>
			          id="attribute<?php echo ucfirst($attribute);?>" name="attribute<?php echo ucfirst($attribute);?>">
			          <script type="dojo/method" event="onChange" >
                  if (this.name=="attributeRequired" && this.checked) {
                      <?php if (!$lock['readonly']) {?>dijit.byId('attributeReadonly').set('checked',false);<?php }?>
                      <?php if (!$lock['hidden']) {?>dijit.byId('attributeHidden').set('checked',false);<?php }?>
                  } else if (this.name=="attributeReadonly" && this.checked) {
                      <?php if (!$lock['required']) {?>dijit.byId('attributeRequired').set('checked',false);<?php }?>
                      <?php if (!$lock['hidden']) {?>dijit.byId('attributeHidden').set('checked',false);<?php }?>
                  } if (this.name=="attributeHidden" && this.checked) {
                      <?php if (!$lock['readonly']) {?>dijit.byId('attributeReadonly').set('checked',false);<?php }?>
                      <?php if (!$lock['required']) {?>dijit.byId('attributeRequired').set('checked',false);<?php }?>
                  }
                </script>
			          </div>
			          &nbsp;&nbsp;<img style="position:absolute; top:3px;" src="../plugin/screenCustomization/icon<?php echo ucfirst($attribute);?>.png" title="<?php echo i18n('attribute'.ucfirst($attribute));?>" />
			          &nbsp;&nbsp;<?php if ($attribute=='nobr' and $nobrWithLib) echo '<div style="position:absolute;left:55px;top:5px;width:300px">'.i18n("fieldWithlibOnSameLine").'</div>';?>
			       </td>
             <td></td> 
           </tr>
           <?php }?>
           <tr class="screenCustomizationLine notForSection" <?php if ($isSection or $isSpecific or $isArray) echo ' style="display:none;"';?>>
             <td class="dialogLabel"><label for="screenCustomizationEditFieldDefaultValue" ><?php echo i18n('defaultValue');?>&nbsp;:&nbsp;</label></td>
             <td>
               <div id="screenCustomizationEditFieldDefaultValue" name="screenCustomizationEditFieldDefaultValue"
                 <?php if ($isSection) echo 'readonly';?>
                 dojoType="dijit.form.Textarea" type="text" maxlength="4000" class="input" style="width:200px"><?php echo $obj->getDefaultValueString($field);?><script type="dojo/method" event="onKeypress" >
                   
                 </script></div>
             </td>
           </tr>
            <tr class="screenCustomizationLine" <?php if (!$isSection) echo ' style="display:none;"';?> >
             <td class="dialogLabel"><label for="" ><?php echo i18n('sectionDefaultStatusClosed');?>&nbsp;:&nbsp;</label></td>
             <td>
             <?php $scope=$objectClass.'_'.substr($field,5);
                   $crit=array('scope'=>$scope, 'idUser'=>'0');
                   $collapsed=SqlElement::getSingleSqlElementFromCriteria('Collapsed', $crit);?>
               <div dojoType="dijit.form.CheckBox" type="checkbox"
                <?php if ($collapsed->id) echo " checked ";?>
		            id="screenCustomizationSectionDefaultStatusClosed" name="screenCustomizationSectionDefaultStatusClosed">
			          <script type="dojo/method" event="onChange" >
                </script>
			          </div>
               
             </td>
             <td></td>
           </tr>
         </table>
         </td><td style="width:2%;">&nbsp;&nbsp;</td><td style="width:49%; vertical-align:top">
         <table>
         <?php 
         if (! $isSpecific and (!$isArray or $field=="_Note" or $field=="_Attachment")) {
          //retreive current styling attributes
         $availableFontFamilies=array("","Arial","Comic Sans Ms","Courier New","Georgia","Lucida Sans Unicode","Tahoma","Times New Roman","Trebuchet MS","Verdana");
         $availableFontSizes=array("","8","9","10", "11","12","14","16","18","20","22","24","26","28","36","48","72");
         $attr=$obj->getDisplayStyling($field);
         $labelAttr=splitCssAttributes($attr['caption']);
         $fieldAttr=splitCssAttributes($attr['field']);
         $currentFontFamily=(isset($fieldAttr['font-family']))?$fieldAttr['font-family']:'';
         $currentFontSize=(isset($fieldAttr['font-size']))?$fieldAttr['font-size']:'';
         $currentLabelFontWeight=(isset($labelAttr['font-weight']))?$labelAttr['font-weight']:'';
         $currentLabelFontStyle=(isset($labelAttr['font-style']))?$labelAttr['font-style']:'';
         $currentLabelColor=(isset($labelAttr['color']))?$labelAttr['color']:'';
         $currentLabelBackground=(isset($labelAttr['background']))?$labelAttr['background']:'';
         $currentFieldFontWeight=(isset($fieldAttr['font-weight']))?$fieldAttr['font-weight']:'';
         $currentFieldFontStyle=(isset($fieldAttr['font-style']))?$fieldAttr['font-style']:'';
         $currentFieldColor=(isset($fieldAttr['color']))?$fieldAttr['color']:'';
         $currentFieldBackground=(isset($fieldAttr['background']))?$fieldAttr['background']:'';
         $fontStyle='';
         if ($currentFontFamily) $fontStyle.="font-family:$currentFontFamily;";
         if ($currentFontSize) $fontStyle.="font-size:$currentFontSize;";
         $labelStyle='';
         if ($currentLabelFontWeight) $labelStyle.="font-weight:$currentLabelFontWeight;";
         if ($currentLabelFontStyle) $labelStyle.="font-style:$currentLabelFontStyle;";
         if ($currentLabelColor) $labelStyle.="color:$currentLabelColor !important;text-shadow:none;";
         if ($currentLabelBackground) $labelStyle.="background:$currentLabelBackground !important;";
         $fieldStyle='';
         if ($currentFieldFontWeight) $fieldStyle.="font-weight:$currentFieldFontWeight;";
         if ($currentFieldFontStyle) $fieldStyle.="font-style:$currentFieldFontStyle;";
         if ($currentFieldColor) $fieldStyle.="color:$currentFieldColor !important;";
         if ($currentFieldBackground) $fieldStyle.="background:$currentFieldBackground !important;";
          
         ?>
           <tr>
             <td colspan="3" class="listTitle section"><?php echo i18n('sectionStyling');?></td>
           <tr/>
           <tr>
             <td colspan="3">&nbsp;</td>
           <tr/>
           <tr class="screenCustomizationLine" >
             <td class="dialogLabel"><label for="" ><?php echo i18n('colFont');?>&nbsp;:&nbsp;</label></td>
             <td>
               <div dojoType="dijit.form.Select" class="input" style="width:200px;"
               id="screenCustomizationFontFamily" name="screenCustomizationFontFamily" value="<?php echo $currentFontFamily;?>">
               <?php  foreach ($availableFontFamilies as $font) {?>
               <span value="<?php echo $font;?>" ><font style="<?php if ($font) echo "font-family:.$font;";?>"><?php echo $font;?></font></span>
               <?php } ?>
               <script type="dojo/method" event="onChange" >
                 screenCustomizationChangeAttribute(null,'FontFamily',this.value);
               </script>
              </div>
             </td>
             <td></td>
           </tr>
           <tr class="screenCustomizationLine" >
             <td class="dialogLabel"><label for="" ><?php echo i18n('colFontSize');?>&nbsp;:&nbsp;</label></td>
             <td>
               <div dojoType="dijit.form.Select" class="input" style="width:50px;" value="<?php echo intval($currentFontSize);?>"
               id="screenCustomizationFontSize" name="screenCustomizationFontSize" >
               <?php  foreach ($availableFontSizes as $size) {?>
               <span value="<?php echo $size;?>" ><font style="font-size:<?php echo $size;?>px"><?php echo $size;?></font></span>
               <?php } ?>
               <script type="dojo/method" event="onChange" >
                 screenCustomizationChangeAttribute('All','FontSize',this.value);
               </script>
              </div>
              <input type="hidden" name="screenCustomizationFontStyle" id="screenCustomizationFontStyle" value="<?php echo $fontStyle;?>"/>
             </td>
             <td></td>
           </tr>
           <?php 
           $hideFieldAttribute=false;
           $hideLabelAttribute=false;
           if ($isSection) $hideFieldAttribute=true;
           if ($dataType=='boolean') $hideFieldAttribute=true;
           if (substr($col,0,5)=='color') $hideFieldAttribute=true;
           ?>
           <tr class="screenCustomizationLine" <?php if ($hideLabelAttribute) echo ' style="display:none;"';?> >
             <td class="dialogLabel"><label for="" ><?php echo i18n("captionStyling")?>&nbsp;:&nbsp;</label></td>
             <td style="padding-top:5px;">
               <div id="screenCustomizationLabelFontWeight" onclick="screenCustomizationChangeAttribute('Label','FontWeight',null);" class="screenCustomizationPseudoButton <?php echo (($currentLabelFontWeight=='bold')?'selected':'');?>" style="font-weight:bold">B</div>
               <div style="display:table-cell">&nbsp;&nbsp;</div>
               <div id="screenCustomizationLabelFontStyle" onclick="screenCustomizationChangeAttribute('Label','FontStyle',null);" class="screenCustomizationPseudoButton <?php echo (($currentLabelFontStyle=='italic')?'selected':'');?>" style="font-style:italic">I</div>
               <div style="display:table-cell">&nbsp;&nbsp;</div>
               <div value="<?php echo $currentLabelColor;?>" id="screenCustomizationLabelColor" dojoType="dijit.form.DropDownButton" 
                class="screenCustomizationPseudoButton" value="<?php echo $currentLabelColor;?>"
                style="border:0;<?php if ($currentLabelColor) echo 'border-left:10px solid '.$currentLabelColor.';';?>">A
                <div dojoType="dijit.ColorPalette" >';
                  <script type="dojo/method" event="onChange" >
                    screenCustomizationChangeAttribute('Label','Color',this.value);
                  </script>
                </div>
               </div>
               <div style="display:table-cell; width:10px;position:relative">
                 <div onclick="screenCustomizationChangeAttribute('Label','Color',null);" class="screenCustomizationPseudoButtonReset"></div>
               </div>
               <div style="display:table-cell">&nbsp;&nbsp;</div>
               <div value="<?php echo $currentLabelBackground;?>" id="screenCustomizationLabelBackground" dojoType="dijit.form.DropDownButton" 
               class="screenCustomizationPseudoButton" value="<?php echo $currentLabelBackground;?>"
               style="border:0;<?php if ($currentLabelBackground) echo 'border-left:10px solid '.$currentLabelBackground.';';?>"><span style="display:inline-block;padding:1px 3px;border-radius:2px;background:#000000;color:#FFFFFF">A</span>
               <div dojoType="dijit.ColorPalette" >';
                  <script type="dojo/method" event="onChange" >
                    screenCustomizationChangeAttribute('Label','Background',this.value);
                  </script>
                </div>
              </div>
              <div style="display:table-cell; width:10px;position:relative">
                 <div onclick="screenCustomizationChangeAttribute('Label','Background',null);" class="screenCustomizationPseudoButtonReset"></div>
               </div>
              
              <input type="hidden" name="screenCustomizationLabelStyle" id="screenCustomizationLabelStyle" value="<?php echo $labelStyle;?>"/> 
             </td>               
             <td></td>
           </tr>
           
           <tr class="screenCustomizationLine" <?php if ($hideFieldAttribute) echo ' style="display:none;"';?> >
             <td class="dialogLabel"><label for="" ><?php echo i18n("fieldStyling")?>&nbsp;:&nbsp;</label></td>
             <td style="padding-top:7px;">
               <div id="screenCustomizationFieldFontWeight" onclick="screenCustomizationChangeAttribute('Field','FontWeight',null);" 
               class="screenCustomizationPseudoButton <?php echo (($currentFieldFontWeight=='bold')?'selected':'');?>" style="font-weight:bold">B</div>
               <div style="display:table-cell">&nbsp;&nbsp;</div>
               <div id="screenCustomizationFieldFontStyle" onclick="screenCustomizationChangeAttribute('Field','FontStyle',null);" 
               class="screenCustomizationPseudoButton <?php echo (($currentFieldFontStyle=='italic')?'selected':'');?>" style="font-style:italic">I</div>
               <div style="display:table-cell">&nbsp;&nbsp;</div>
               <div value="<?php echo $currentFieldColor;?>" id="screenCustomizationFieldColor" dojoType="dijit.form.DropDownButton" 
                class="screenCustomizationPseudoButton" value="<?php echo $currentFieldColor;?>"
                style="border:0;<?php if ($currentFieldColor) echo 'border-left:10px solid '.$currentFieldColor.';';?>">A
                <div dojoType="dijit.ColorPalette" >';
                  <script type="dojo/method" event="onChange" >
                    screenCustomizationChangeAttribute('Field','Color',this.value);
                  </script>
                </div>
               </div>
               <div style="display:table-cell; width:10px;position:relative">
                 <div onclick="screenCustomizationChangeAttribute('Field','Color',null);" class="screenCustomizationPseudoButtonReset"></div>
               </div>
               <div style="display:table-cell">&nbsp;&nbsp;</div>
               <div value="<?php echo $currentFieldBackground;?>" id="screenCustomizationFieldBackground" dojoType="dijit.form.DropDownButton" 
               class="screenCustomizationPseudoButton" value="<?php echo $currentFieldBackground;?>"
               style="border:0;<?php if ($currentFieldBackground) echo 'border-left:10px solid '.$currentFieldBackground.';';?>"><span style="display:inline-block;padding:1px 3px;border-radius:2px;background:#000000;color:#FFFFFF">A</span>
               <div dojoType="dijit.ColorPalette" >';
                  <script type="dojo/method" event="onChange" >
                    screenCustomizationChangeAttribute('Field','Background',this.value);
                  </script>
                </div>
              </div>
              <div style="display:table-cell; width:10px;position:relative">
                 <div onclick="screenCustomizationChangeAttribute('Field','Background',null);" class="screenCustomizationPseudoButtonReset"></div>
               </div>
               <input type="hidden" name="screenCustomizationFieldStyle" id="screenCustomizationFieldStyle" value="<?php echo $fieldStyle;?>"/>
             </td>
             <td></td>
           </tr>
           <tr><td colspan="3">&nbsp;</td></tr>
           <tr><td colspan="3" style="position:relative;border-bottom:1px solid #C0C0C0;color:#C0C0C0"><div style="xposition:absolute;"><?php echo i18n('colPreview');?></div></td></tr>
           <?php if ($isSection) {?>
            <tr class="screenCustomizationLine">
             <td colspan="2" class="section" id="screenCustomizationPreviewLabel" style="width:100%;<?php echo $fontStyle.$labelStyle;?>"><?php echo i18n("fieldName");?></td>
              <input dojoType="dijit.form.TextBox"  type="hidden" class="input" id="screenCustomizationPreviewField" value="<?php echo i18n("fieldValue");?>" style="width:200px;<?php echo $fontStyle.$fieldStyle;?>"/>
             <td></td>
           </tr>
           <?php } else {?>
           <tr class="screenCustomizationLine">
             <td class="dialogLabel"><label id="screenCustomizationPreviewLabel" for="" style="<?php echo $fontStyle.$labelStyle;?>"><?php echo i18n("fieldName");?>&nbsp;:&nbsp;</label></td>
             <td>
              <input dojoType="dijit.form.TextBox"  type="<?php echo ($hideFieldAttribute)?'hidden':'text';?>" class="input" id="screenCustomizationPreviewField" value="<?php echo i18n("fieldValue");?>" style="width:200px;<?php echo $fontStyle.$fieldStyle;?>"/>
             </td>
             <td></td>
           </tr>
           <?php }?>
           <tr><td colspan="3" style="border-top:1px solid #C0C0C0;"></td></tr>
         <?php }?>
         </table>
         </td></tr></table>
       </form>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
      <td align="center">
        <button class="mediumTextButton"  dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogEditField').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton"  id="dialogEditFieldSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);plg_screenCustomization_saveField('<?php echo $objectClass;?>');return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
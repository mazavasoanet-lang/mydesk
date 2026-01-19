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
  scriptLog('   ->/plugin/screenCustomization/screenCustomization.php');  
  $user=getSessionUser();
  $objectClass=null;
  if (isset($_REQUEST["objectClass"])) {
    $objectClass=$_REQUEST["objectClass"];
  }else if (sessionValueExists('plgScreenCustomizationObjectClass')){
    $objectClass=getSessionValue('plgScreenCustomizationObjectClass');
  }
?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="Plugin_screenCustomization" />
<div class="container" dojoType="dijit.layout.BorderContainer">
  <!-- Button Section -->
  <div id="customizationButtonDiv" class="listTitle" dojoType="dijit.layout.ContentPane" region="top" style="z-index:3;overflow:visible">
    <table width="100%">
      <tr height="100%" style="vertical-align: middle;">
        <td width="50px" align="center">
          <?php echo formatIcon('ScreenCustomization',32,null,true);?>
        </td>
        <td width="200px"><span class="title"><?php echo i18n("menuScreenCustomization");?>&nbsp;</span>        
        </td>
        <td width="30px" >&nbsp;</td>
        <td style="white-space: nowrap;width:200px;"> 
          <form dojoType="dijit.form.Form" id="screenCustomizationForm" jsId="screenCustomizationForm" name="screenCustomizationForm" encType="multipart/form-data" action="" method="" >
               <?php                 
                echo i18n('colElement').'&nbsp;:&nbsp;';
                //$langs=Parameter::getList('lang');
                echo '<select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" name="customizationClass" id="customizationClass" ';
                echo autoOpenFilteringSelect();
				        echo ' onChange="plg_screenCustomization_refreshClass(this.value,this.text);"';
                echo ' style="width:200px">';
                $lstCustom=getClassList();
                echo '<option '.(($objectClass===null)?' selected ':'').' value=""> </option>';
                foreach ($lstCustom as $key=>$val) {
                  if ($key=='DocumentVersion' or $key=='Status' or $key=='Workflow' 
                  or $key=='xWorkElement' or $key=='User' or $key=='OrganizationBudgetElement') continue;
                  echo '<option '.(($key==$objectClass)?' selected ':'').' value="'.$key.'">'.$val.'</option>';
                }
        				echo '</select>';
               ?>
          </form>
        </td>
        <td style="width:150px;text-align:right">
          <input type="hidden" name="updateRight" value="YES" />
          <button id="saveButton" dojoType="dijit.form.Button" showlabel="false"
           title="<?php echo i18n("translationSave");?>"
           iconClass="dijitButtonIcon dijitButtonIconSave" class="detailButton">
            <script type="dojo/connect" event="onClick" args="evt">
		          plg_screenCustomization_save();
            </script>
          </button>
          <button id="undoButton" dojoType="dijit.form.Button" showlabel="false"
           title="<?php echo i18n("translationUndo");?>"
           iconClass="dijitButtonIcon dijitButtonIconUndo" class="detailButton">
            <script type="dojo/connect" event="onClick" args="evt">
             dojo.byId("undoButton").blur();
             disableWidget("saveButton");
             disableWidget("undoButton");
             loadContent('objectDetail.php?refresh=true&objectClass='+dijit.byId('customizationClass').get('value')+'&objectId=','formDiv',null);
             loadContent('../plugin/screenCustomization/screenCustomizationFields.php?objectClass='+dijit.byId('customizationClass').get('value'),'screenCustomizationFields',null);
             formChangeInProgress=false;
            </script>
          </button>      
          <button id="customListsButton" dojoType="dijit.form.Button" showlabel="false"
           title="<?php echo i18n("editCustomLists");?>"
           iconClass="imageColorNewGui iconListOfValues22 iconListOfValues iconSize22" class="detailButton">
            <script type="dojo/connect" event="onClick" args="evt">
		          plg_screenCustomization_customLists();
            </script>
          </button> 
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </div>
  
  <!-- Main Section -->
  <div id="screenCustomizationMainDiv" dojoType="dijit.layout.ContentPane" region="center" height="100%" splitter="false">
    <div class="container" dojoType="dijit.layout.BorderContainer" style="width:100%;hreight:100%">
      <div dojoType="dijit.layout.ContentPane" region="left" style="width:50%;overflow:auto" splitter="true" id="screenCustomizationFields"></div>
      <div dojoType="dijit.layout.ContentPane" region="center" style="padding:5px 0px 5px 10px" id="formDiv"></div>
    </div>
    <?php if ($objectClass){?>
    <script type="dojo/connect" event="onShow">
       var refresh=function() {
         var width=(dojo.byId('formDiv'))?'&destinationWidth='+dojo.byId('formDiv').offsetWidth:'';
         loadContent('objectDetail.php?refresh=true&objectClass=<?php echo$objectClass;?>&objectId='+width,'formDiv',null);
         loadContent('../plugin/screenCustomization/screenCustomizationFields.php?objectClass=<?php echo$objectClass;?>','screenCustomizationFields',null);
       }
       setTimeout(refresh,100);
    </script>
    <?php }?>
  </div>
</div>
<?php function getClassList() {
  $dir='../model/';
  $handle = opendir($dir);
  $result=array();
  while ( ($file = readdir($handle)) !== false) {
    if ($file == '.' || $file == '..' || $file=='index.php' // exclude ., .. and index.php
      || substr($file,-4)!='.php'                           // exclude non php files 
      || substr($file,-8)=='Main.php') {                    // exclude the *Main.php
      continue;
    }
    $class=pathinfo($file,PATHINFO_FILENAME);
    $ext=pathinfo($file,PATHINFO_EXTENSION);
    if (file_exists($dir.$class.'Main.php')) {
      $result[$class]=i18n($class);
    }
  }
  closedir($handle);
  asort($result);
  return $result;
}?>
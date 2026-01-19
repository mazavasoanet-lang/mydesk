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
  require_once "../plugin/screenCustomization/screenCustomizationFunctions.php";
  scriptLog('   ->/plugin/screenCustomization/screenCustomizationEditLists.php');  
  $user=getSessionUser();
?>

<div class="container" dojoType="dijit.layout.BorderContainer">
  <!-- Button Section -->
  <div id="customizationButtonDiv" class="listTitle" dojoType="dijit.layout.ContentPane" region="top" style="z-index:3;overflow:visible">
    <table width="100%">
      <tr height="100%" style="vertical-align: middle;">
        <td width="50px" align="center">
            <?php echo formatIcon('ListOfValues',32,null,true);?>
        </td>
        <td width="200px"><span class="title"><?php echo i18n("menuScreenCustomizationEditList");?>&nbsp;</span>        
        </td>
        <td width="30px" >&nbsp;</td>
        <td style="white-space: nowrap;width:200px;"> 
          <form dojoType="dijit.form.Form" id="screenCustomizationForm" jsId="screenCustomizationForm" name="screenCustomizationForm" encType="multipart/form-data" action="" method="" >
               <?php                 
                echo i18n('editCustomListSelect').'&nbsp;:&nbsp;';
                //$langs=Parameter::getList('lang');
                echo '<select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" name="customizationListClass" id="customizationListClass" ';
                echo autoOpenFilteringSelect();
                echo ' onChange="plg_screenCustomization_customLists_edit(this.value);"';
                echo ' style="width:200px">';
                $lstCustom=screenCustomisationGetCustomClassList();
                echo '<option selected value=""> </option>';
                foreach ($lstCustom as $key=>$val) {
                  echo '<option value="'.$key.'">'.$val.' ('.$key.')</option>';
                }
        				echo '</select>';
               ?>
          </form>
        </td>
        <td style="width:150px;text-align:right">
          <button id="newListButton" dojoType="dijit.form.Button" showlabel="false"
           title="<?php echo i18n("editCusmtomListNew");?>"
           iconClass="dijitButtonIcon dijitButtonIconNew" class="detailButton">
            <script type="dojo/connect" event="onClick" args="evt">
		          plg_screenCustomization_customLists_new();
            </script>
          </button>
          <button id="deleteListButton" dojoType="dijit.form.Button" showlabel="false"
           title="<?php echo i18n("editCustomListDelete");?>"
           iconClass="dijitButtonIcon dijitButtonIconDelete" class="detailButton">
            <script type="dojo/connect" event="onClick" args="evt">
		          plg_screenCustomization_customLists_delete();
            </script>
          </button>
          <button id="backToMainButton" dojoType="dijit.form.Button" showlabel="false"
           title="<?php echo i18n("editCustomListsBack");?>"
           iconClass="dijitButtonIcon dijitButtonIconExit" class="detailButton">
            <script type="dojo/connect" event="onClick" args="evt">
		          plg_screenCustomization_customLists_back();
            </script>
          </button>
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </div>
  <div id="customizationMainDiv" dojoType="dijit.layout.ContentPane" region="center">
    <div style="padding-top:200px;height:20px;width:100%;text-align:center;vertical-align:middle;"><?php echo i18n("editCustomListSelect");?></div>
  </div>
 
</div>
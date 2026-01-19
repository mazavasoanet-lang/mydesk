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

?>
<div>
  <table style="width:100%;">
    <tr>
      <td>
       <form id='addCustomListForm' name='addCustomListForm' onSubmit="return false;" >
         <br/>
         <table>
           <tr>
             <td colspan="3">&nbsp;</td>
           <tr/>
           <tr class="screenCustomizationLine">
             <td class="dialogLabel"><label for="" ><?php echo i18n('customListClass');?>&nbsp;:&nbsp;</label></td>
             <td>
               <input id="customListClass" name="customListClass"
                 dojoType="dijit.form.TextBox" type="text" maxlength="100" class="input" style="width:200px"
                 value="" /><br/>
                 
             </td>
             <td></td>
           </tr>
           <tr>
             <td></td>
             <td style="font-size:80%;"><div><?php echo i18n('customListClassHelp');?></div><br/></td>
             <td></td>
           </tr>
           <tr class="screenCustomizationLine">
             <td class="dialogLabel"><label for="" ><?php echo i18n('customListName');?>&nbsp;:&nbsp;</label></td>
             <td>
               <div id="customListName" name="customListName"
                 dojoType="dijit.form.TextBox" type="text" maxlength="100" class="input" style="width:200px"
                 value="" >
               </div>
             </td>
             <td></td>
           </tr>
           <tr>
             <td></td>
             <td style="font-size:80%;"><div><?php echo i18n('customListNameHelp');?></div><br/></td>
             <td></td>
           </tr>
         </table>
       </form>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
      <td align="center">
        <button class="mediumTextButton"  dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogAddCustomList').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton"  id="dialogAddCustomListSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);plg_screenCustomization_customList_save(dijit.byId('customListClass').get('value'));return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
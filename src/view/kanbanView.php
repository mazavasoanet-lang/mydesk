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

require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
require_once "../tool/kanbanConstructPrinc.php";
require_once '../tool/kanbanFunction.php';
global $typeKanbanC;
global $orderBy;
$orderBy="";
if(array_key_exists('kanbanOrderBy',$_REQUEST)){
  $orderBy=$_REQUEST['kanbanOrderBy'];
  setSessionValue('kanbanOrderBy', $orderBy);
}else if(sessionValueExists('kanbanOrderBy')){
  $orderBy=getSessionValue('kanbanOrderBy');
}
$typeKanbanC="Ticket";
$seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
$seeWork=($seeWork=='on' or $seeWork=='1')?true:false;
if(PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile) != "ALL")$seeWork=false;
if(Parameter::getUserParameter("kanbanShowIdle")==null){
  Parameter::storeUserParameter("kanbanShowIdle", 'off');
}
$idKanban=-1;
if (array_key_exists('idKanban',$_REQUEST)) {
  $idKanban=$_REQUEST['idKanban'];
  if (is_numeric($idKanban)) Parameter::storeUserParameter("kanbanIdKanban",$idKanban);
}else{
  if(Parameter::getUserParameter("kanbanIdKanban")!==null){
    $idKanban=Parameter::getUserParameter("kanbanIdKanban");
  }
}
$kanTest=new Kanban($idKanban,true);
if($kanTest->name=='')$idKanban=-1;
$json="";
$type="";
$name="";
if($idKanban!=-1){
  $kanB=new Kanban($idKanban,true);
  $json=$kanB->param;
  $type=$kanB->type;
  $name=$kanB->name;
  $jsonDecode=json_decode($json,true);
  if(!isset($jsonDecode['typeData'])){
    $jsonDecode['typeData']='Ticket';
    $kanB->param=json_encode($jsonDecode);
    $kanB->save();
  }
  $typeKanbanC=$jsonDecode['typeData'];
}

$arrayProject=array();
$hasVersion=(property_exists($typeKanbanC,'idTargetProductVersion'))?true:false;

if($typeKanbanC != 'Activity' and $orderBy == 'validatedenddate')$orderBy="";
?>
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div id="titleKanban" class="listTitle" style="z-index:3;overflow:visible;min-height:65px;"
    dojoType="dijit.layout.ContentPane" region="top">
    <table width="100%">
      <tr height="100%" style="vertical-align: middle;">
        <td align="center" style="width:50px;">          
          <div style="position:absolute;top:2px">
            <?php echo formatIcon('Kanban',32,null,true);?>
          </div>
        </td>
        <td class="title" style="height:35px;width:100px;">    
          <div style="width:100%;height:100%;position:relative;">
            <div id="menuName" style="float:left;width:100%;position:absolute;top:8px;text-overflow:ellipsis;overflow:hidden;">
              <?php if (isNewGui()) {?>           
              <span id="gridRowCountShadow1" style="display:none;" class=""></span>
              <span id="gridRowCountShadow2" style="display:none;" class=""></span>
              <span id="gridRowCount" style="padding-left:5px" class=""></span>
              <?php }?> 
              <span id="classNameSpan" style="">
              <?php echo i18n('kanbanTitleButton');?>
              </span>
            </div>
          </div>
        </td>
        <td style="width:32px;">
         <?php if($idKanban==-1){ ?>
          <div style="float:left">
            <div dojoType="dijit.form.Button" class="detailButton" onclick="loadDialog('dialogKanbanUpdate', function(){kanbanFindTitle('addKanban');}, true, '&typeDynamic=addKanban', true, false);"
            style="float:left;position:relative;margin-right:8px;"><?php echo formatIcon('KanbanAdd',22,i18n('kanbanAdd')); ?>
            </div>
          </div>
          <?php } ?>
        </td>
        <td style="width:75px;"><?php echo i18n('labelKanbanList');?> : </td>
        <td><?php kanbanListSelect($user,$name,$type,$idKanban);?></td>
      </tr>
    </table>
    <input type="hidden" name="objectClassManual" id="objectClassManual" value="Kanban" />
    <input type="hidden" id="objectClassList" name="objectClassList" value="<?php echo $typeKanbanC;?>">
    <input type="hidden" name="idKanban" id=""idKanban"" value="<?php echo $idKanban;?>" />
    <input dojoType="dijit.form.TextBox" type="hidden" id="refreshActionAdd<?php echo $typeKanbanC;?>" value="-1" onchange="if(dijit.byId(this.id).get('value')!=-1)loadContent('../view/kanbanView.php?idKanban=<?php echo $idKanban;?>', 'divKanbanContainer');dijit.byId(this.id).set('value',-1);">
    <input dojoType="dijit.form.TextBox" type="hidden" id="objectClass" name="objectClass" value="<?php echo $typeKanbanC;?>">

  <input dojoType="dijit.form.TextBox" type="hidden" id="idKanban" value="<?php echo $idKanban;?>">
  <div style="width:100%; margin: 0px 10px 3px 10px" dojoType="dijit.layout.ContentPane" region="bottom">
  <?php if($idKanban!=-1){?>
  <?php echo i18n("colName");?> : <input dojoType="dijit.form.TextBox" onKeyUp="kanbanStart();" class="dijit dijitReset dijitInline dijitLeft filterField rounded dijitTextBox" type="text" id="searchByName" value="<?php echo getSessionValue('kanbanname');?>">
    <?php echo i18n("colResponsible");?> : 
      <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
        <?php echo autoOpenFilteringSelect ();?>
        style="width: 150px;" onChange="kanbanStart();" name="searchByResponsible" id="searchByResponsible"
        value="<?php echo getSessionValue('kanbanresponsible');?>">
          <option value=""></option>
            <?php $specific='diary';
              include '../tool/drawResourceListForSpecificAccess.php';?> 
      </select>
  <?php if($type!='Status'){echo i18n("colIdStatus");?> : 
    <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" style="width: 150px;" 
    <?php echo autoOpenFilteringSelect ();?>
    onChange="kanbanStart();" name="listStatus" id="listStatus" value="<?php echo getSessionValue('kanbanstatus');?>" >
      <?php htmlDrawOptionForReference("idStatus", null);?>
    </select>
  <?php } if($type!='TargetProductVersion' and $hasVersion){echo i18n("colIdVersion"); ?> : 
    <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" style="width: 150px;" 
    <?php echo autoOpenFilteringSelect ();?>
    onChange="kanbanStart();" name="listTargetProductVersion" id="listTargetProductVersion" 
    value="<?php echo getSessionValue('kanbantargetProductVersion');?>">
      <?php if(is_numeric(getSessionValue("project"))){
        htmlDrawOptionForReference("idTargetProductVersion", null, null, false, 'idProject', getSessionValue("project"));
      }else{
        htmlDrawOptionForReference("idTargetProductVersion", null);
      }?>
    </select>
      <?php } echo i18n("sortedBy"); ?> : 
        <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" style="width:150px;margin-right:15px;"
        <?php echo autoOpenFilteringSelect ();?>
        onChange="kanbanChangeOrderBy(dijit.byId('kanbanOrderBy').get('value'),<?php echo $idKanban;?>);" name="kanbanOrderBy" id="kanbanOrderBy">
          <option <?php if($orderBy=="")echo "selected";?> value=""></option>
          <option <?php if($orderBy=="name")echo "selected";?> value="name"><?php echo i18n("colName");?></option>
          <option <?php if($orderBy=="idresponsible")echo "selected";?> value="idresponsible"><?php echo i18n("colResponsible");?></option>
          <option <?php if($orderBy=="idstatus")echo "selected";?> value="idstatus"><?php echo i18n("colIdStatus");?></option>
          <?php if ($hasVersion) {?><option <?php if($orderBy=="idtargetproductversion")echo "selected";?> value="idtargetproductversion"><?php echo i18n("colIdTargetProductVersion");?></option><?php }?>
          <?php if ($typeKanbanC == 'Activity') {?><option <?php if($orderBy=="validatedenddate")echo "selected";?> value="validatedenddate"><?php echo i18n("colValidatedEndDate");?></option><?php }?>
          <option <?php if($orderBy=="idpriority")echo "selected";?> value="idpriority"><?php echo i18n("colIdPriority");?></option>
          <option <?php if($orderBy=="id")echo "selected";?> value="id"><?php echo i18n("colId");?></option>
        </select>
        <button title="<?php echo i18n('advancedFilter')?>" class="comboButton" dojoType="dijit.form.DropDownButton" id="listFilterFilter" 
        name="listFilterFilter" style="margin-right:15px;"
        iconClass="dijitButtonIcon icon<?php echo (isset(getSessionUser()->_arrayFilters[$typeKanbanC]) && is_array(getSessionUser()->_arrayFilters[$typeKanbanC]) && count(getSessionUser()->_arrayFilters[$typeKanbanC])!=0 ? 'Active' : '');?>Filter" showLabel="false">
           <?php if (!isNewGui()){ ?>
            <script type="dojo/connect" event="onClick" args="evt">
            showFilterDialog();
          </script>
            <script type="dojo/method" event="onMouseEnter" args="evt">
            clearTimeout(closeFilterListTimeout);
            clearTimeout(openFilterListTimeout);
            openFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').openDropDown();",popupOpenDelay);
          </script>
            <script type="dojo/method" event="onMouseLeave" args="evt">
            clearTimeout(openFilterListTimeout);
            closeFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').closeDropDown();",2000);
          </script>
         <?php }?>
          <div dojoType="dijit.TooltipDialog" id="directFilterList" style="z-index: 999999;display:none; position: absolute;">
            <?php 
              //$_REQUEST['filterObjectClass']=$objectClass;
              //$_REQUEST['context']="directFilterList";
              $_REQUEST['context']='directFilterList';
              $_REQUEST['contentLoad']="../view/kanbanView.php?idKanban=".$idKanban;
              $_REQUEST['container']="divKanbanContainer";
              $_REQUEST['filterObjectClass']=$typeKanbanC;
              if(isNewGui()){
                $filterObjectClass = $typeKanbanC;
                $dontDisplay = true;
                include "../tool/displayQuickFilterList.php";
              }
              //ajout de mehdi
              include "../tool/displayFilterList.php";
            ?>
           <?php if (!isNewGui()){ ?>
              <script type="dojo/method" event="onMouseEnter" args="evt">
                clearTimeout(closeFilterListTimeout);
                clearTimeout(openFilterListTimeout);
              </script>
              <script type="dojo/method" event="onMouseLeave" args="evt">
                dijit.byId('listFilterFilter').closeDropDown();
              </script>
           <?php }?>
          </div> 
        </button>
  <?php }?>
        <div style="float:right;padding-right:1%;">
          <?php if($idKanban!=-1){ ?>
          <div dojoType="dijit.form.DropDownButton"							    
           id="extraButtonKanban" jsId="extraButtonKanban" name="extraButtonKanban" 
           showlabel="false" class="comboButton" iconClass="dijitButtonIcon dijitButtonIconExtraButtons" class="detailButton" 
           title="<?php echo i18n('extraButtons');?>">
             <div dojoType="dijit.TooltipDialog" class="white" id="extraButtonKanbanDialog" tyle="position: absolute; top: 50px; right: 40%">        
               <table style="margin:5px">
                 <tr style="width:100%;">
                  <td><?php kanbanParameterList($idKanban);?></td>
                 </tr>
               </table>
             </div>
          </div>
          <?php }?>
          <?php if($idKanban!=-1){?>
          <div dojoType="dijit.form.Button" class="detailButton" style="float:left;position:relative;cursor:pointer;margin-right:10px;margin-top:5px;"
            onclick="showDetail('refreshActionAdd<?php echo $typeKanbanC;?>',1,'<?php echo $typeKanbanC;?>',false,'new');"><?php echo formatIcon('KanbanAdd'.$typeKanbanC,22, i18n('kanbanAdd'.$typeKanbanC)); ?>
          </div>
          <?php }?>
      </div>
  </div>
</div>
  <div id="kanbanContainer" style="height:100%;overflow-x:scroll;padding:8px;" dojoType="dijit.layout.ContentPane" region="center" 
  onscroll="kanbanScrollTop=this.scrollTop">  
    <table width="100%" style="min-height:100%;">
      <tr>
        <?php if($idKanban!=-1)drawColumnKanban($type,$jsonDecode,$idKanban); ?>
      </tr>
    </table>
    <div class="contextMenuClass comboButtonInvisible" dojoType="dijit.form.DropDownButton" id="kanbanContextMenu" name="kanbanContextMenu" style="position:absolute;top:0px;left:0px;width:0px;height:0px;overflow:hidden;">
      <div dojoType="dijit.TooltipDialog" id="dialogKanbanContextMenu" tabindex="0"" onMouseEnter="clearTimeout(hideKanbanContextMenuTimeout);" onMouseLeave="hideKanbanContextMenu(200)" onfocusout="hideElementOnFocusOut(null, hideKanban(200))">
        <input type="hidden" id="objectClass" name="objectClass" value="" />
        <input type="hidden" id="objectId" name="objectId" value="" />
        <input type="hidden" id="objectClassRow" name="objectClassRow" value="" />
        <input type="hidden" id="objectIdRow" name="objectIdRow" value="" />
        <table style="width:100%;height:100%">
          <tr id='addFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('Add', false, false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='addFromKanban_label'><?php echo i18n('contextMenuButtonNew');?></td>
          </tr>
          <tr id='editFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('Edit', false, false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='editFromKanban_label'><?php echo i18n('contextMenuButtonEdit');?></td>
          </tr>
          <tr id='copyFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('Copy', false, false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='copyFromKanban_label'><?php echo i18n('contextMenuButtonCopy');?></td>
          </tr>
          <tr id='addCommentFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('AddComment', false, false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='addCommentFromKanban_label'><?php echo i18n('commentImputationAdd');?></td>
          </tr>
          <tr id='removeFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('Remove', false, false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='removeFromKanban_label'><?php echo i18n('contextMenuButtonDelete');?></td>
          </tr>
          <tr id='printFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('Print', true , false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='printFromKanban_label'><?php echo i18n('contextMenuButtonPrint');?></td>
          </tr>
          <tr id='printPdfFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('Pdf', false, false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='printPdfFromKanban_label'><?php echo i18n('reportPrintPdf');?></td>
          </tr>
          <tr id='mailFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('Email', false, false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='mailFromKanban_label'><?php echo i18n('contextMenuButtonMail');?></td>
          </tr>
          <?php if($typeKanbanC=="Activity"){ ?>
          <tr id='searchFromKanban' class='contextMenuRow' onClick=''>
            <td style="padding-top:5px;padding-bottom:5px;"><?php echo formatSmallButton('SearchPlanning', false, false);?></td>
            <td style="padding-left:10px;padding-top:5px;padding-bottom:5px;" id='searchFromKanban_label'><?php echo i18n('buttonSearch');?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
    </div>
  </div>
    
  <script type="dojo/connect">       
  kanbanStart();
  </script>
</div>
<?php 

function getLastStatus(){
  $status=new Status();
  $tableName=$status->getDatabaseTableName();
  $result=Sql::query("SELECT t.id as id
    FROM $tableName t where idle=0 order by t.sortOrder desc");
  while ($line = Sql::fetchLine($result)) {
    return $line["id"];
  }
  return '';
}

function drawColumnKanban($type,$jsonD,$idKanban){
  global $typeKanbanC;
  $statusList=SqlList::getList('Status','name',null,true);
  $allowedStatus=array();
  $kanbanFullWidthElement = Parameter::getUserParameter ( "kanbanFullWidthElement" );
  $hideBacklog = (Parameter::getUserParameter ( "kanbanHideBacklog" )=='on' or Parameter::getUserParameter ( "kanbanHideBacklog" )=='1')?1:0;
  if(count($jsonD['column'])!=0){
  	$jsonArray=array();
  	$keyJsonOrder=array();
  	$sortedColumns=array();
  	foreach ($jsonD['column'] as $key=>$itemKanban) {
  	  if((isset($itemKanban['cantDelete']) or $itemKanban['name'] == 'Backlog') and $hideBacklog == 0 and count($jsonD['column']) > 1){
  	    unset($jsonD['column'][$key]);
  	    continue;
  	  }
  	  if($itemKanban['from']!='n'){
  	    $obj = new $type($itemKanban['from'],true);
  	    if(isset($obj->sortOrder)){
  	      $jsonArray[str_pad($obj->sortOrder,5,'0', STR_PAD_LEFT).'-'.$obj->id]=$itemKanban;
  	    }else{
  	      $jsonArray[$obj->name.'-'.$obj->id]=$itemKanban;
  	    }
  	  }else{
  	    $jsonArray['00000-'.$itemKanban['from']]=$itemKanban;
  	  }
  	}
  	ksort($jsonArray);
  	foreach ($jsonArray as $key=>$itemKanban) {
  	  $keyJsonOrder[]=$key;
  	  $sortedColumns[]=$itemKanban;
  	}
    $numCol = count($jsonD['column']);
    $isStatus=$type=="Status";
    $mapAccept=array();
    $accept="[";
    $iterateur=0;
    if(!$isStatus){ // Form Kanban on other than Status, Accept is simple : no restriction for moves
      foreach ($jsonD['column'] as $itemKanban) {
        $accept.='\'typeRow'.$itemKanban['from'].'\'';
        if($iterateur!=count($jsonD['column'])-1)$accept.=',';
      }
    }else{ // For Kanban on Status, Accept must respect workflow, corresponding to user profile
      $user=getSessionUser();
      $mapWorkflow=array();
      $curCol=null;
      $culSta=null;
      for ($i=0;$i<count($sortedColumns);$i++) {
      	$itemKanban=$sortedColumns[$i];
      	$idFrom=$itemKanban['from'];
        $allowedStatus[$idFrom]=array($idFrom=>$idFrom);
        $found=false;
      	foreach ($statusList as $idS=>$nameS) {
      		if ($found) {
      			if (isset($sortedColumns[$i+1]) and $idS==$sortedColumns[$i+1]['from']) {
      			  break;
      			} else {
      				$allowedStatus[$idFrom][$idS]=$idS;
      			}
      		} else if ($idS==$idFrom) {
      			$found=true;
      		}
      	}
      }
      //$visibleProjects=pq_explode(',',pq_trim(getVisibleProjectsList(true),'()'));
      foreach ($user->getAllProfiles() as $idProfile){ // For each profile of the user (on any project)
        //$idProfil=$user->getProfile($idProject);
        foreach (SqlList::getList("Status",'id') as $idStatus){ // For every status
          foreach (SqlList::getList($typeKanbanC."Type",'id') as $idTicketType){ // For every type (Ticket type or Activity Type)
            $workflowId=SqlList::getFieldFromId($typeKanbanC."Type", $idTicketType, 'idWorkflow');
            if(!isset($mapWorkflow[$workflowId])){
              $woTmp=new Workflow($workflowId);
              $mapWorkflow[$workflowId]=$woTmp->getWorkflowstatusArray();
            }
            foreach ($jsonD['column'] as $itemKanban) { // For all defined columns on the Kanban (id of status is in the from field            	
            	foreach ($allowedStatus[$itemKanban['from']] as $idStatusTo) {
            		$toPut="";
                if($idStatusTo!=$idStatus) {
                  if(isset($idProfile) 
                  && isset($mapWorkflow[$workflowId][$idStatus]) 
                  && isset($mapWorkflow[$workflowId][$idStatus][$idStatusTo])) {
                    if(isset($mapWorkflow[$workflowId][$idStatus][$idStatusTo][$idProfile])
                    && $mapWorkflow[$workflowId][$idStatus][$idStatusTo][$idProfile]) {
                      $toPut='typeRow'.$idStatus.'-'.$idTicketType.'-'.pq_trim($idProfile);
                    }
                  }
                }              
                if($toPut!=""){
                  $exist=false;
                  if(isset($mapAccept[$itemKanban['from']]))if(pq_strpos($mapAccept[$itemKanban['from']], $toPut) !== false)$exist=true;
                  if(!$exist){
                    if(!isset($mapAccept[$itemKanban['from']])){
                      $mapAccept[$itemKanban['from']]="'$toPut'";
                    }else{
                      $mapAccept[$itemKanban['from']].=",'$toPut'";
                    }
                  }
                }
            	}
            }
          }
        }
      }
    }
    $accept.="]";
    $percent=100/count($jsonD['column']);
    $iterateur=0;
    foreach ($jsonArray as $itemKanban) {
      $nextFrom=$itemKanban['from'];
      if($iterateur<count($jsonArray)-1 && $isStatus){
        $nextFrom=getNextFrom($itemKanban['from'],$jsonArray[$keyJsonOrder[$iterateur+1]]['from'],$type); //bug offset too high
      }else if($isStatus){
        $nextFrom=getLastStatus();
      }
      $result=queryToDo($itemKanban['from'],$nextFrom,$type,$isStatus);
      $realWork=0;
      $plannedWork=0;
      $leftWork=0;
      foreach($result as $line){
        $realWork+=$line['realwork'];
        $plannedWork+=$line['plannedwork'];
        $leftWork+=$line['leftwork'];
      }
      $nbItems=Sql::$lastQueryNbRows;
      $acceptTmp=$accept;
      if(isset($mapAccept[$itemKanban['from']]))$acceptTmp='['.$mapAccept[$itemKanban['from']].']';
      if($type=="Activity")$acceptTmp="[".SqlList::getFieldFromId("Activity", $itemKanban['from'], "idProject")."]";
      if($itemKanban['from']=="n" || $type=="TargetProductVersion")$acceptTmp="[";
      if($type=="TargetProductVersion")$acceptTmp.="'n',";
      if($itemKanban['from']=="n" || $type=="TargetProductVersion"){
        $iterateur2=0;
        foreach($jsonD['column'] as $keyy=>$vall){
          if($vall['from']!='n'){
            if($type=='Activity')$acceptTmp.=SqlList::getFieldFromId('Activity', $vall['from'], 'idProject');
            else $acceptTmp.=$vall['from'];
            $iterateur2++;
            if($iterateur2!=count($jsonD['column'])-1)$acceptTmp.=",";
          }
        }
      }
      if($itemKanban['from']=="n" || $type=="TargetProductVersion")$acceptTmp.="]";
      if($type=="Milestone"){
        $projectList = getVisibleProjectsList();
        $projectList = pq_str_replace('(', '', $projectList);
        $projectList = pq_str_replace(')', '', $projectList);
        $acceptTmp = "[".$projectList."]";
      }
      $destHeight=RequestHandler::getValue('destinationHeight');
      $destWidth=RequestHandler::getValue('destinationWidth');
      if ($destHeight) {
        $maxHeight=($destHeight-163);
        $seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
        $seeWork=($seeWork=='on' or $seeWork=='1')?true:false;
        if ($seeWork) $maxHeight-=32;
        if (isNewGui()) $maxHeight-=6;
        $maxHeight.='px';
      } else {
        $maxHeight='100%';
      }
      if ($destWidth) {
        $nbCols=count($jsonD['column']);
        $maxWidth=((($destWidth)/$nbCols)-20)."px";
      } else {
        $maxWidth="332px";
      }
      
      echo '<td style="vertical-align:top;;width:'.$maxWidth.';min-width:355px;">
            <table style="width:100%;"><tr style="min-height:47px;height:47px;max-height:47px;">
            <td class="kanbanColumn" style="position:relative;background-color:'.((isNewGui())?'var(--color-light);border-radius:10px 10px 0 0':'#e2e4e6').';padding:3px 8px 0px;border-bottom:2px solid #ffffff;min-width:355px;">';
      getNameFromTypeKanban($itemKanban,$nextFrom,$type,$isStatus,$nbItems,$idKanban,$realWork,$plannedWork,$leftWork);
      echo '</td></tr><tr>';
      echo '
        <td class="kanbanColumn" style="overflow-y:scroll;overflow-x:hidden;display:block; height:'.$maxHeight.';max-height:'.$maxHeight.'; position:relative;background-color:'.((isNewGui())?'var(--color-light);border:2px solid var(--color-light);border-radius:0 0 10px 10px':'#e2e4e6').';padding:'.(($kanbanFullWidthElement=='on')?'8px':'6px 0px 6px 4px').';width:auto;min-width:355px;" id="dialogRow'.$itemKanban['from']. '" 
        jsId="dialogRow'.$itemKanban['from']. '" dojotype="dojo.dnd.Source" dndType="typeRow'.$itemKanban['from']. '" withhandles="true"
        '.($acceptTmp!='[]' ? 'data-dojo-props="accept: '.$acceptTmp.',singular:true, horizontal:'.(($kanbanFullWidthElement == 'on')?'false':'true').'"':'data-dojo-props="singular:true, horizontal:'.(($kanbanFullWidthElement == 'on')?'false':'true').'"').' width="'.((100/count($jsonArray))).'%" valign="top">';
      echo '
      <script type="dojo/connect" event="onDndStart" args="evt">
        anchorTmp=evt.anchor;
        evt.anchor.style.display=\'none\';
        return true;
      </script>
      <script type="dojo/connect" event="onDndCancel" args="evt">
      anchorTmp.style.display=\'block\';
        return true;
      </script>';

      getItemsFromTypeIdKanban($itemKanban['from'], $nextFrom, $type,$isStatus,$result,$jsonD);
      
      echo '</td></tr></table>
      </td>';
      $iterateur++;
      if ($iterateur<count($jsonArray)) {
        echo '
        <td style="min-width:10px;max-width:10px;width:10px" width="10px"></td>';
      }
    }
  }
}

function getNextFrom($from,$next,$type){
  global $typeKanbanC;
  $min=SqlList::getFieldFromId($type, $from, "sortOrder");
  
  $obT=new $type();
  $tableName=$obT->getDatabaseTableName();
  if ($type=='Status') {
    $workflowStatus = new WorkflowStatus ();
    $tableName2 = $workflowStatus->getDatabaseTableName ();
    $type = new Type ();
    $tableName3 = $type->getDatabaseTableName ();
    $result = Sql::query ( "SELECT s.id as typen, s.sortOrder as sortorder from $tableName s where s.idle=0 and (s.id in (select idStatusFrom from $tableName2 w, $tableName3 t where t.idWorkflow=w.idWorkflow and t.scope='$typeKanbanC')
      or s.id in (select idStatusTo from $tableName2 w, $tableName3 t where t.idWorkflow=w.idWorkflow and t.scope='$typeKanbanC') ) order by s.sortOrder" );
   } else {
     $result=Sql::query("SELECT t.id as typen, t.sortOrder as sortorder FROM $tableName t WHERE t.sortOrder>=$min order by t.sortOrder ");
   }
  $ite=0;
  while ($line = Sql::fetchLine($result)) {
    $listId[]=$line;
  }
  $last=-1;
  foreach($listId as $line){
    if(count($listId)-1!=$ite+1){
    	if(isset($listId[$ite+1]) && $listId[$ite+1]['typen']==$next) {
    		return $line['typen'];
    	}
    }
    $last=$line['typen'];
    $ite++;
  }
  return $last;
}

function getNameFromTypeKanban($itemKanban,$to,$type,$isStatus,$nb,$idKanban,$realWork,$plannedWork,$leftWork){
  $name=$itemKanban['name'];
  $from=$itemKanban['from'];
  $itemWork['realWork']=$realWork;
  $itemWork['plannedWork']=$plannedWork;
  $itemWork['leftWork']=$leftWork;
  $itemWork['id']=$itemKanban['from'];
  $seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
  $seeWork=($seeWork=='on' or $seeWork=='1')?true:false;
  if(PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile) != "ALL")$seeWork=false;
  if($seeWork && PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile)=="ALL")$seeWork=true; else $seeWork=false;
  $addHeight='';
  if($seeWork)$addHeight="height:65px;";
  echo '<div style="margin-bottom:10px;'.$addHeight.'>';
  if($isStatus){
    echo '<h2 style="font-size: 14px;font-weight:bold;margin: 8px 8px 2px;color:#4d4d4d">'.htmlEncode($name);
    if(!isset($itemKanban['cantDelete']) && myKanban($idKanban))
    echo ' <a onClick="delKanban('.$idKanban.', \''.i18n("kanbanDelColumn").'\','.$from.')" title="' . i18n('kanbanColumnDelete') . '" class="smallButton"/> '.formatSmallButton('Remove').'</a>';     
    if(myKanban($idKanban)) { 
      echo '<a onClick="loadDialog(\'dialogKanbanUpdate\', function(){kanbanFindTitle(\'editColumnKanban\');}, true, \'&typeDynamic=addColumnKanban&typeD='.$type.'&idKanban='.$idKanban.'&idFrom='.$from.'\', true, false);"title="' . i18n('kanbanColumnEdit') . '" class="smallButton"  /> '.formatSmallButton('Edit').'</a>';     
    }
    echo '</h2><div id="numberTickets'.$from.'" class="sectionBadge">'.$nb.'</div>'.displayAllWork($itemWork);
    echo '<h3 class="kanbanTextTitle" style="font-size: 10px;font-weight:bold;margin: 0 8px 9px;">'.i18n("from").' '.SqlList::getNameFromId($type, $from).' '.i18n("to").' '.SqlList::getNameFromId($type, $to).'</h3>';
  }else{
    $nameN=SqlList::getNameFromId(((pq_substr($type,-7)=='Version')?'Version':$type), $from);
    if($name!='')$nameN=$name;
    echo '<h2 style="font-size: 14px;font-weight:bold;margin: 8px 8px 2px;color:#4d4d4d">'.$nameN;
        if(!isset($itemKanban['cantDelete']) && myKanban($idKanban))        
        echo ' <a onClick="delKanban('.$idKanban.', \''.i18n("kanbanDelColumn").'\','.$from.')" title="' . i18n('kanbanColumnDelete') . '" class="smallButton"/> '.formatSmallButton('Remove').'</a>';      
        if(myKanban($idKanban) && $from!= 'n')
          echo ' <a onClick="loadDialog(\'dialogKanbanUpdate\', function(){kanbanFindTitle(\'editColumnKanban\');}, true, \'&typeDynamic=addColumnKanban&typeD='.$type.'&idKanban='.$idKanban.'&idFrom='.$from.'\', true, false);" title="' . i18n('kanbanColumnEdit') . '" class="smallButton"  /> '.formatSmallButton('Edit').'</a>';          
        echo '</h2><div id="numberTickets'.$from.'" class="sectionBadge">'.$nb.'</div>'.displayAllWork($itemWork);
  }
  echo '</div>';
}

function myKanban($idKanban){
   $kanban = new Kanban($idKanban,true);
   return $kanban->idUser==getSessionUser()->id;
}

function queryToDo($from,$nextFrom,$type,$isStatus){

  global $typeKanbanC,$hasVersion;
  global $orderBy;
  $obT=new $typeKanbanC();
  $obT2=new WorkElement();
  $obT3=new Status();
  $obT4=new Resource();
  $obT5=new TargetProductVersion();
  $obj=$obT;
  if($typeKanbanC=='Activity')$obT2=new PlanningElement();
  $tableName=$obT->getDatabaseTableName();
  if($typeKanbanC!='Requirement' and $typeKanbanC!='Action'){
    $tableName2=$obT2->getDatabaseTableName();
  }else{
    $tableName2=null;
  }
  $tableName3=$obT3->getDatabaseTableName();
  $tableName4=$obT4->getDatabaseTableName();
  if ($hasVersion) {
    $tableName5=$obT5->getDatabaseTableName();
  } else {
    $tableName5=null;
  }
  if($from=='n'){
    $listType=' is null ';
  }else{
    $listType='in ('.getIdsOfType($from,$nextFrom,$type).') ';
  }
  $arrayFilter=array();
  if(isset(getSessionUser()->_arrayFilters[$typeKanbanC]) && is_array(getSessionUser()->_arrayFilters[$typeKanbanC]))$arrayFilter=getSessionUser()->_arrayFilters[$typeKanbanC];
  $queryFrom=$tableName;
  $queryWhere=" and 1=1 ";
  $queryOrderBy='';
  $idTab=0;
  $crit=$obT->getDatabaseCriteria();
  /*foreach ($crit as $col => $val) {
    $queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.= $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . "=" . Sql::str($val) . " ";
  }*/
  $table=$tableName;
  foreach ($arrayFilter as $crit) {
    if (pq_trim($crit['sql']['operator'])=='exists') {
      $crit['sql']['attribute']=null;
    }
    if ($crit['sql']['operator']!='SORT') { // Sorting already applied above
      $split=pq_explode('_', $crit['sql']['attribute']);
      $critSqlValue=$crit['sql']['value'];
      if (array_key_exists('isDynamic', $crit) and $crit['isDynamic']=='1' and ($crit['sql']['operator']=='IN' or $crit['sql']['operator']=='NOT IN')) {
        if ($crit['sql']['value']==0) continue;
      }
      if (pq_substr($crit['sql']['attribute'], -4, 4) == 'Work') {
        if ($typeKanbanC=='Ticket') {
          $critSqlValue=Work::convertImputation(pq_trim($critSqlValue,"'"));
        } else {
          $critSqlValue=Work::convertWork(pq_trim($critSqlValue,"'"));
        }
      }
      
      if ($crit['sql']['operator']=='IN'
          and ($crit['sql']['attribute']=='idProduct' or $crit['sql']['attribute']=='idProductOrComponent' or $crit['sql']['attribute']=='idComponent')) {
        $critSqlValue=pq_str_replace(array(' ','(',')'), '', $critSqlValue);
        $splitVal=pq_explode(',',$critSqlValue);
        $critSqlValue='(0';
        foreach ($splitVal as $idP) {
          $prod=new Product($idP,true);
          $critSqlValue.=', '.$idP;
          $list=$prod->getRecursiveSubProductsFlatList(false, false); // Will work only if selected is Product, not for Component
          foreach ($list as $idPrd=>$namePrd) {
            $critSqlValue.=', '.$idPrd;
          }
        }
        $critSqlValue.=')';
      }
      if (count($split)>1 ) {        
        $externalClass=$split[0];
        $externalObj=new $externalClass();
        $externalTable = $externalObj->getDatabaseTableName();
        $idTab+=1;
        $externalTableAlias = 'T' . $idTab;
        $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
        ' on ( ' . $externalTableAlias . ".refType='" . get_class($obj) . "' and " .  $externalTableAlias . '.refId = ' . $table . '.id )';
        $queryWhere.=($queryWhere=='')?'':' and ';
        $queryWhere.=$externalTableAlias . "." . $split[1] . ' '
            . $crit['sql']['operator'] . ' '
                . $critSqlValue;
      } else {
        $queryWhere.=($queryWhere=='')?'':' and ';
        if ($crit['sql']['operator']!=' exists ') {
          $queryWhere.="(".$table . "." . $crit['sql']['attribute'] . ' ';
        }
        $queryWhere.= $crit['sql']['operator'] . ' ' . $critSqlValue;
        if (pq_strlen($crit['sql']['attribute'])>=9
        and pq_substr($crit['sql']['attribute'],0,2)=='id'
            and ( pq_substr($crit['sql']['attribute'],-7)=='Version' and SqlElement::is_a(pq_substr($crit['sql']['attribute'],2), 'Version') )
                and $crit['sql']['operator']=='IN') {
          $scope=pq_substr($crit['sql']['attribute'],2);
          $vers=new OtherVersion();
          $queryWhere.=" or exists (select 'x' from ".$vers->getDatabaseTableName()." VERS "
              ." where VERS.refType=".Sql::str($typeKanbanC)." and VERS.refId=".$table.".id and scope=".Sql::str($scope)
              ." and VERS.idVersion IN ".$critSqlValue
              .")";
        }
        if ($crit['sql']['operator']=='NOT IN') {
          $queryWhere.=" or ".$table . "." . $crit['sql']['attribute']. " IS NULL ";
        }
        if ($crit['sql']['operator']!=' exists ') {
          $queryWhere.=")";
        }
      }
    }
  }
  foreach ($arrayFilter as $crit) {
    if ($crit['sql']['operator']=='SORT') {
      $doneSort=false;
      $split=pq_explode('_', $crit['sql']['attribute']);
      if (count($split)>1 ) {
        $externalClass=$split[0];
        $externalObj=new $externalClass();
        $externalTable = $externalObj->getDatabaseTableName();
        $idTab+=1;
        $externalTableAlias = 'T' . $idTab;
        $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
        ' on ( ' . $externalTableAlias . ".refType='" . get_class($obj) . "' and " .  $externalTableAlias . '.refId = ' . $table . '.id )';
        $queryOrderBy .= ($queryOrderBy=='')?'':', ';
        $queryOrderBy .= " " . $externalTableAlias . '.' . $split[1]
        . " " . $crit['sql']['value'];
        $doneSort=true;
      }
      if (pq_substr($crit['sql']['attribute'],0,2)=='id' and pq_strlen($crit['sql']['attribute'])>2 ) {
        $externalClass = pq_substr($crit['sql']['attribute'],2);
        $externalObj=new $externalClass();
        $externalTable = $externalObj->getDatabaseTableName();
        $sortColumn='id';
        if (property_exists($externalObj,'sortOrder')) {
          $sortColumn=$externalObj->getDatabaseColumnName('sortOrder');
        } else {
          $sortColumn=$externalObj->getDatabaseColumnName('name');
        }
        $idTab+=1;
        $externalTableAlias = 'T' . $idTab;
        $queryOrderBy .= ($queryOrderBy=='')?'':', ';
        $queryOrderBy .= " " . $externalTableAlias . '.' . $sortColumn
        . " " . pq_str_replace("'","",$crit['sql']['value']);
        $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
        ' on ' . $table . "." . $obj->getDatabaseColumnName('id' . $externalClass) .
        ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
        $doneSort=true;
      }
      if (! $doneSort) {
        $queryOrderBy .= ($queryOrderBy=='')?'':', ';
        $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName($crit['sql']['attribute'])
        . " " . $crit['sql']['value'];
      }
    }
  }
  $queryWhere.=($queryWhere)?' and ':'';
  //$queryWhere.= "$tableName.idProject in ".getVisibleProjectsList(false);
  $queryWhere.=getAccesRestrictionClause(get_class($obT),$tableName, true);
  if(Parameter::getGlobalParameter('hideItemTypeRestrictionOnProject')=='YES'){
    $user=getSessionUser();
    $objectClass=get_class($obj);
    $showIdleProjects=(sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1)?1:0;
    $showIdle=1;
    $lstGetClassList = Type::getClassList();
    $objType = $obj->getDatabaseColumnName($objectClass . 'Type');
    $lstGetClassList = array_flip($lstGetClassList);
    if(in_array($objType,$lstGetClassList)){
      $queryWhere.=($queryWhere)?' and ':'';
      $queryWhere.= $user->getItemTypeRestriction($obj,$objectClass,$user,$showIdle,$showIdleProjects);
    }
  }
  $newOrderBy="";
  if($orderBy!='' && $queryOrderBy!='')$queryOrderBy=','.$queryOrderBy;
  if($orderBy=="idstatus")$newOrderBy=$tableName3.'.sortOrder';
  if($orderBy=="idresponsible")$newOrderBy=$tableName4.'.fullName';
  if($orderBy=="idtargetproductversion")$newOrderBy=$tableName5.'.name';
  if($orderBy!= "idtargetproductversion" && $orderBy!= "idresponsible" && $orderBy!= "idstatus" && $orderBy!='')$newOrderBy=$tableName.'.'.$orderBy;
  if($orderBy=="idpriority"){
    if($typeKanbanC == 'Activity'){
      $plan = new PlanningElement();
      $tableName6=$plan->getDatabaseTableName();
      $newOrderBy=$tableName6.'.priority';
    }else{
      $newOrderBy=$tableName.'.idPriority';
    }
  }
  if($typeKanbanC == 'Activity'){
    $plan = new PlanningElement();
    $tableName6=$plan->getDatabaseTableName();
    if($orderBy=="validatedenddate")$newOrderBy=$tableName6.'.validatedEndDate';
  }

//   if(!$isStatus){
//     $query="SELECT $tableName.id as id,
//            $tableName.name as name,
//            $tableName.id".$typeKanbanC."Type as idtickettype,
//            $tableName.idStatus as idstatus,
//            $tableName.idProject as idproject,";
//     if(property_exists($typeKanbanC, "idPriority")) {
//     	$query.="$tableName.idPriority as idpriority, ";
//     } else {
//     	$query.=" 0 as idpriority, ";
//     }
//     $query.=" $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')." as idtargetproductversion, ";
//     if(property_exists($typeKanbanC, "idActivity")){
//       $query.="$tableName.idActivity as idactivity,";  
//     }else{
//       $query.="0 as idactivity, ";
//     }
//     if(property_exists($obj, "WorkElement")){
//       $query.="$tableName2.plannedWork as plannedwork,";
//     }else if (property_exists($typeKanbanC, 'plannedWork')){
//       $query .=" $tableName.plannedWork as plannedwork,";
//     }else{
//       $query .=" 0 as plannedwork, ";
//     }
//     $query.=" $tableName2.realWork as realwork,
//               $tableName2.leftWork as leftwork,
//               $tableName.description as description,
//               $tableName.idResource as iduser,
//               $tableName3.sortOrder,
//               $tableName4.fullName as name4,
//               $tableName5.name as name5"; 
//     $query.=" FROM  $tableName2, $tableName3, $queryFrom";
//     $query.=" left join $tableName4 on $tableName.idresource=$tableName4.id";
//     $query.=" left join $tableName5 on $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')."=$tableName5.id";
//     $query.=" WHERE $tableName3.id=$tableName.idStatus";
//     $query.=" AND $tableName.".$obT->getDatabaseColumnName('id'.$type)." $listType";
//     $query.=" AND $tableName2.refType='".$typeKanbanC."' AND $tableName2.refId=$tableName.id $queryWhere";
//     $query.=" AND $tableName.idProject in ".getVisibleProjectsList(false).(Parameter::getUserParameter("kanbanShowIdle") ? '' : ' AND '.$tableName.'.idle=0');
//     if ($queryOrderBy!='' || $orderBy!='') {
//     	$query.=" order by $newOrderBy $queryOrderBy ";
//     }
//     $result=Sql::query($query);
//   }else{
    /*$result=Sql::query("SELECT $tableName.id as id,
    $tableName.name as name,
    $tableName.id".$typeKanbanC."Type as idtickettype,
    $tableName.idStatus as idstatus,
    $tableName.idProject as idproject,
    ".(property_exists($typeKanbanC, "idPriority") ? "$tableName.idPriority as idpriority, " : "")."
    $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')." as idtargetproductversion,
    $tableName.idActivity as idactivity,
    ".( (isset($tableName2)) ? " $tableName2.plannedWork as plannedwork, " : ( (property_exists($typeKanbanC, 'plannedWork'))?" $tableName.plannedWork as plannedwork,":"0 as plannedwork,") )."
    ".(isset($tableName2) ? "   $tableName2.realWork as realwork, " : "")."
    $tableName.description as description,
    ".(isset($tableName2) ? "    $tableName2.leftWork as leftwork, " : "")."
    $tableName.idResource as iduser,
    $tableName3.sortOrder,
    $tableName4.fullName as name4,
    $tableName5.name as name5
    FROM  $tableName2, $tableName3, $queryFrom left join $tableName4 on $tableName.idresource=$tableName4.id left join $tableName5 on $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')."=$tableName5.id WHERE $tableName3.id=$tableName.idStatus AND $tableName.".$obT->getDatabaseColumnName('id'.$type)." $listType AND $tableName2.refType='".$typeKanbanC."' AND $tableName2.refId=$tableName.id $queryWhere AND $tableName.idProject in ".getVisibleProjectsList(false).(Parameter::getUserParameter("kanbanShowIdle") ? '' : ' AND '.$tableName.'.idle=0').( ($queryOrderBy!='' || $orderBy!='') ? " order by $newOrderBy ".$queryOrderBy : ''));*/
    $query="SELECT $tableName.id as id,
    		$tableName.name as name,
    		$tableName.id".$typeKanbanC."Type as idtickettype,
    		$tableName.idStatus as idstatus,
    		$tableName.idProject as idproject,";
    if(property_exists($typeKanbanC, "idUrgency"))$query.="$tableName.idUrgency as idurgency,";
    if(property_exists($typeKanbanC, "idPriority")) {
      $query.="$tableName.idPriority as idpriority, ";
    } else {
      $query.=" 0 as idpriority, ";
    }
    if(property_exists($typeKanbanC, "idCriticality")) {
      $query.="$tableName.idCriticality as idcriticality, ";
    } else {
      $query.=" 0 as idcriticality, ";
    }
    if ($hasVersion) $query.=" $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')." as idtargetproductversion,";
    else $query.=" null as idtargetproductversion,";
    if (property_exists($typeKanbanC, "idActivity")) {
    	$query.=" $tableName.idActivity as idactivity,";
    }else{
      $query.=" null as idactivity, ";
    }
    if(isset($tableName2)){
      $query.=" $tableName2.plannedWork as plannedwork,"; 
    }else if(property_exists($typeKanbanC, 'plannedWork')){
      $query .=" $tableName.plannedWork as plannedwork, ";
    }else{
      $query .=" 0 as plannedwork, ";
    }
    if(isset($tableName2)){
      $query .=" $tableName2.realWork as realwork, ";
    }else{
      $query.= " 0 as realwork, ";
    }
    $obj = new $typeKanbanC();
    if ($typeKanbanC == 'Activity'){
      if(isset($tableName2)){
        $query.=" $tableName2.validatedEndDate as validatedenddate,";
        $query.=" $tableName2.validatedStartDate as validatedstartdate,";
        $query.=" $tableName2.plannedEndDate as plannedenddate,";
        $query.=" $tableName2.plannedStartDate as plannedstartdate,";
        $query.=" $tableName2.notPlannedWork as notplannedwork,";
        $query.=" $tableName2.assignedWork as assignedwork,";
        $query.=" $tableName2.validatedDuration as validatedduration,";
        $query.=" $tableName2.plannedDuration as plannedduration,";
        $query.=" $tableName2.idPlanningMode as idplanningmode,";
        $query.=" $tableName2.inheritedEndDate as inheritedenddate,";
        $query.=" $tableName2.surbooked as surbooked,";
      }
    }else if($typeKanbanC == 'Action' or $typeKanbanC == 'Requirement'){
      $query.=" $tableName.actualDueDate as plannedenddate,";
    }else if($typeKanbanC == 'Ticket'){
      $query.=" $tableName.actualDueDateTime as plannedenddate,";
    }
    $query .=" $tableName.description as description, ";
    if(isset($tableName2)){
      $query .=" $tableName2.leftWork as leftwork, ";
    } else {  
      $query .=" 0 as leftwork, ";
    }
    $query .=" $tableName.idResource as iduser,
               $tableName3.sortOrder,
               $tableName4.fullName as name4";
    if ($hasVersion) $query .=", $tableName5.name as name5";
    $query.=" FROM ";
    if (isset($tableName2)) {
    $query.=" $tableName2,";
    }else {
      $query.="";
    }
    $query.=" $tableName3, $queryFrom"; 
    $query.=" left join $tableName4 on $tableName.idresource=$tableName4.id";
    if ($hasVersion) $query.=" left join $tableName5 on $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')."=$tableName5.id";
    $query.=" WHERE $tableName3.id=$tableName.idStatus";
    $query.=" AND $tableName.".$obT->getDatabaseColumnName('id'.$type)." $listType";
    if (isset($tableName2)) {
      $query.=" AND $tableName2.refType='".$typeKanbanC."' AND $tableName2.refId=$tableName.id $queryWhere";
    }else {
      $query.=" $queryWhere";
    }
    $query.=" AND ($tableName.idProject in ".getVisibleProjectsList(false).(($typeKanbanC=='Requirement' and getSessionValue('project')=='*')?" or $tableName.idProject is null":"").')'.(Parameter::getUserParameter("kanbanShowIdle") == 'on' ? '' : ' AND '.$tableName.'.idle=0');
    if ($queryOrderBy!='' || $orderBy!='') {
      $query.=" order by $newOrderBy $queryOrderBy ";
    }
    
    $result=Sql::query($query);
  $final=array();

  $isColorBlind=(Parameter::getUserParameter('colorBlindPlanning') == 'YES')?true:false;
  while ($line = Sql::fetchLine($result)) {
    if ($typeKanbanC == 'Activity'){
      $pColor='#50BB50';
      $pColorBlindColor = $pColor;
      if ($line['notplannedwork'] > 0) { // Some left work not planned
        $pColor = '#9933CC';
        $pColorBlindColor = '#BB5050';
      } else if (pq_trim($line['validatedenddate']) != "" and $line['validatedenddate'] < $line['plannedenddate']) { // Not respected constraints end date : red
        if ($typeKanbanC!='Milestone' and ( ! $line['assignedwork'] or $line['assignedwork']==0 ) and ( ! $line['leftwork'] or $line['leftwork']==0 ) and ( ! $line['realwork'] or $line['realwork']==0 )) {
          $pColor = '#BB9099';
          $pColorBlindColor = 'linear-gradient(45deg, #63226b 5%, #9a3ec9 5%, #9a3ec9 45%, #63226b 45%, #63226b 55%, #9a3ec9 55%, #9a3ec9 95%, #63226b 95%);';
        } else {
          $pColor = '#BB5050';
          $pColorBlindColor = 'linear-gradient(45deg, #63226b 5%, #9a3ec9 5%, #9a3ec9 45%, #63226b 45%, #63226b 55%, #9a3ec9 55%, #9a3ec9 95%, #63226b 95%);';
        }
      } else if ( ( ($line['idplanningmode']==8 or $line['idplanningmode']==14) and intval($line['validatedduration']) < intval($line['plannedduration']) )
          or ( ($line['idplanningmode']==25 or $line['idplanningmode']==26) and $line['plannedstartdate'] != $line['validatedstartdate'] )
          or ( ($line['idplanningmode']==19 or $line['idplanningmode']==21) and $line['plannedstartdate'] < $line['validatedstartdate'] )  ) {
            $pColor = '#BB5050';
            $pColorBlindColor = 'linear-gradient(45deg, #63226b 5%, #9a3ec9 5%, #9a3ec9 45%, #63226b 45%, #63226b 55%, #9a3ec9 55%, #9a3ec9 95%, #63226b 95%);';
          } else if ($typeKanbanC!='Milestone' and ( ! $line['assignedwork'] or $line['assignedwork']==0 ) and ( ! $line['leftwork'] or $line['leftwork']==0 ) and ( ! $line['realwork'] or $line['realwork']==0 ) ) { // No workassigned : greyed
            $pColor = '#AEC5AE';
          }
          if ($line['surbooked']==1) {
            $pColor='#f4bf42';
            $pColorBlindColor='#bfbfbf';
          }
          // Color for late from inheritedEndDate
          if (pq_trim($line['validatedenddate'])=="" and pq_trim($line['inheritedenddate'])!="" and $line['inheritedenddate'] < $line['plannedenddate']) {
            if ($line['assignedwork']>0) $pColor = '#DA70D6';
            else $pColor = '#DDA0DD';
          }
          $line['plannedcolor'] = ($isColorBlind)?$pColorBlindColor:$pColor;
    } else {
      $pColor='#F1F1F1';
      $pColorBlindColor = $pColor;
      $line['plannedcolor'] = ($isColorBlind)?$pColorBlindColor:$pColor;
    }
    $final[]=$line;
  }
  return $final;
}

function getItemsFromTypeIdKanban($from,$nextFrom,$type,$isStatus,$result,$jsonD){
  global $typeKanbanC,$arrayProject;
  $arrayProfile=array();
  $nb=0;
  $nListQuery=array();
  foreach($result as $line) {
    $nListQuery[$line['id']]=$line;
  }
  foreach ($nListQuery as $line) {
    $idType=$from;
    $add="";
    if(!isset($arrayProject[$line['idproject']])){
      $proJ=new Project($line['idproject'],true);
      $arrayProject[$line['idproject']]=$proJ->getColor();
    }
    if(!isset($arrayProfile[$line['idproject']])){
      $arrayProfile[$line['idproject']]=getSessionUser()->getProfile($line['idproject']);
    }
    $color=$arrayProject[$line['idproject']];
    if($isStatus){
      $idType=$line['idstatus'];
      $add='-'.$line['idtickettype'].'-'.$arrayProfile[$line['idproject']];
    }
    $seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
    $seeWork=($seeWork=='on' or $seeWork=='1')?true:false;
    if($seeWork && PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile)=="ALL")$seeWork=true; else $seeWork=false;

    $idKanban = Parameter::getUserParameter("kanbanIdKanban");
    $handle='dojoDndHandle';
    
    if(securityGetAccessRightYesNo("menu".$typeKanbanC, "update", new $typeKanbanC($line['id'],true))!="YES")$handle="";
    	$numCol = count($jsonD['column']);
    $mode = "display";
    kanbanDisplayTicket($line['id'],$type, $idKanban,$from, $line, $add, $mode);
    $nb++;
  }
}


function getIdsOfType($from,$nextFrom,$type){
  if($from==$nextFrom){
    return $from;
  }else{
    $listId=Array();
    $min=-100000000;
    $max=100000000;
    if($from!=0)$min=SqlList::getFieldFromId($type, $from, "sortOrder");
    if($nextFrom!=0)$max=SqlList::getFieldFromId($type, $nextFrom, "sortOrder");
    $sub=$min;
    if($min>$max){
      $min=$max;
      $max=$sub;
    }
    $obT=new $type();
    $tableName=$obT->getDatabaseTableName();
    //$result=Sql::query("SELECT t.id as typen FROM $tableName t WHERE t.sortOrder<=$max and t.sortOrder>=$min ");
    $fromBacklog = ($type == 'Status' and ($from == 'n' or $from == '1'))?' or t.isCopyStatus = 1':'';
    $result=Sql::query("SELECT t.id as typen FROM $tableName t WHERE (t.sortOrder<$max or t.id=$nextFrom) and (t.sortOrder>$min or t.id=$from) $fromBacklog");
    while ($line = Sql::fetchLine($result)) {
      $listId[]=$line["typen"];
    }
    $final="";
    $ite=0;
    if (count($listId)==0) return 0;
    foreach ($listId as $idType){
      $final.=$idType;
      if(count($listId)!=$ite+1) $final.=",";
      $ite++;
    }
    return $final;
  }
}

function kanbanListSelect($user,$name,$type,$idKanban) {
  global $typeKanbanC;
  $kanban=new Kanban();
  $mineList=$kanban->getSqlElementsFromCriteria(null, false," idUser=$user->id ");
  $res= new Resource();
  $reTable= $res->getDatabaseTableName();
  $clauseWhere=" idUser in (Select id from $reTable where id!=$user->id and idle=0 ) AND isShared=1 ";
  $kanbanList=$kanban->getSqlElementsFromCriteria(null, false,$clauseWhere, "idUser ASC");
  $kanbanName = ($idKanban != -1)?SqlList::getNameFromId('Kanban', $idKanban, false):i18n("noKanbanSelected");
  // Display Result
  echo '<div style="float:left;">
            <div dojoType="dijit.form.DropDownButton"
              style="min-width:75px;height:24px;margin:0 auto;color:#000;float:left;margin-right:15px;'.(($idKanban == -1)?'font-style:italic;':'').'"
              id="kanbanListSelect" name="entity">
              <span>'.$kanbanName.'</span>
                <div data-dojo-type="dijit/TooltipDialog">';
  $iterateur=0;
  echo '<span class="kanbanTextTitle" style="float:left;height:15px;font-weight:bold;" disabled="disabled" value="-2" '
      . ' title="' . i18n("kanbanSelectKanban") . '" >'.i18n("kanbanMine").'</span><br/>';
  if(count($mineList)==0)echo '<span disabled="disabled" onclick="dijit.byId(\'kanbanListSelect\').closeDropDown();" style="float:left;height:15px;" '
        . ' >&nbsp;&nbsp;&nbsp;&nbsp;'.i18n('noDataFound').'</span><br/>';
  echo '<div style="position:absolute;top:20px;right:'. (empty($mineList) ? '10' : '70') .'px;"';
  echo 'onclick="loadDialog(\'dialogKanbanUpdate\', function(){kanbanFindTitle(\'addKanban\');}, true, \'&typeDynamic=addKanban\', true, false);" title="'.i18n('kanbanAdd').'">'.formatSmallButton('KanbanAdd',true).'</div>';
  foreach ($mineList as $line) {
    $jsonDecode=json_decode($line->param,true);
    if(!isset($jsonDecode['typeData'])){
      $jsonDecode['typeData']='Ticket';
      $line->param=json_encode($jsonDecode);
      $line->save();
    }
    $typeKanbanCTmp=$jsonDecode['typeData'];
    if (isNewGui()) echo '<div style="margin-top:5px">';
    echo '
    <div class="imageColorNewGuiNoSelection icon'.$typeKanbanCTmp.'16 icon'.$typeKanbanCTmp.' iconSize16" style="width:16px;height:16px;float:left"></div>
    <span onclick="kanbanGoToKan('.$line->id.');dijit.byId(\'kanbanListSelect\').closeDropDown();" class="kanbanMenuTree" style="float:left;height:15px;'.((isNewGui())?'position:relative;top:-2px;':'').'" '
        . ' >&nbsp;&nbsp'
            . htmlEncode($line->name)
            . "</span>";
          echo '  <a class="" onClick="copyKanban('.$line->id.')" title="' . i18n('kanbanCopy'). '" >'
              .formatSmallButton('Copy')
              .'</a> ';
          echo '  <a class="" onClick="editKanban('.$line->id.')" title="' . i18n('kanbanEdit'). '" >'
              .formatSmallButton('Edit')
              .'</a> ';
          if($line->isShared==0) echo '  <a class="" onClick="plgShareKanban('.$line->id.')" title="' . i18n('kanbanShare'). '" >'
              .formatSmallButton('Share')
              .'</a> ';
          if($line->isShared==1) echo '  <a class="" onClick="plgShareKanban('.$line->id.')" title="' . i18n('kanbanUnshare'). '" >'
              .formatSmallButton('Shared')
              .'</a> ';
          echo '  <a class="" onClick="delKanban('.$line->id.', \''.i18n("kanbanDel").'\')" title="' . i18n('kanbanDelete'). '" >'
              .formatSmallButton('Remove')
              .'</a> ';
          if (isNewGui()) echo '</div>';
          else echo "<br/>";
  }
  echo '<span style="float:left;height:15px;" value="-1" '
      . ' title="' . i18n("kanbanSelectKanban") . '" ></span><br/>';
  echo '<span class="kanbanTextTitle" style="float:left;height:15px;font-weight:bold;" disabled="disabled" value="-2" '
      . ' title="' . i18n("kanbanSelectKanban") . '" >'.i18n("kanbanShared").'</span><br/>';
  if(count($kanbanList)==0)echo '<span disabled="disabled" onclick="dijit.byId(\'kanbanListSelect\').closeDropDown();" style="float:left;height:15px;" '
        . ' >&nbsp;&nbsp;&nbsp;&nbsp;'.i18n('noDataFound').'</span><br/><br/>';
  $lastUser="";
  foreach ($kanbanList as $line) {
    if($lastUser!=$line->idUser){
      $lastUser=$line->idUser;
      echo '<div style="width:100%;height:18px;" ><span class="kanbanTextTitle" style="float:left;height:15px;font-weight:bold;margin-top:4px;">'.htmlEncode(SqlList::getNameFromId('Affectable', $lastUser)).'</span></div>';
    }
    $jsonDecode=json_decode($line->param,true);
    if(!isset($jsonDecode['typeData'])){
      $jsonDecode['typeData']='Ticket';
      $line->param=json_encode($jsonDecode);
      $line->save();
    }
    $typeKanbanCTmp=$jsonDecode['typeData'];
    if (isNewGui()) echo '<div style="margin-top:5px">';
    echo '
        <div class="imageColorNewGuiNoSelection icon'.$typeKanbanCTmp.'16 icon'.$typeKanbanCTmp.' iconSize16" style="width:16px;height:16px;float:left;"></div>
        <span onclick="kanbanGoToKan('.$line->id.');dijit.byId(\'kanbanListSelect\').closeDropDown();" class="kanbanMenuTree" style="float:left;height:15px;'.((isNewGui())?'position:relative;top:-2px;':'').'" '
        . ' >&nbsp;&nbsp'
            . htmlEncode($line->name)
            . "</span>";
    echo '  <a onClick="copyKanban('.$line->id.')" title="' . i18n('kanbanCopy'). '" class="">'
        .formatSmallButton('Copy')
        .'</a> ';
    if (isNewGui()) echo '</div>';
    else echo "<br/>";
  }
  echo "</div></div>";
 // }
}

function kanbanParameterList($idKanban){
  global $typeKanbanC, $type;
  
  $seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
  $seeWork=($seeWork=='on' or $seeWork=='1')?true:false;
  if($seeWork && PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile)=="ALL")$seeWork=true; else $seeWork=false;
  if ($typeKanbanC=='Requirement' or $typeKanbanC=='Action') $seeWork=false;
  
  $showIdle = Parameter::getUserParameter("kanbanShowIdle");
  $showIdle = ($showIdle=='on' or $showIdle=='1')?true:false;
  
  $fullWidthElement = Parameter::getUserParameter("kanbanFullWidthElement");
  $fullWidthElement = ($fullWidthElement=='on' or $fullWidthElement=='1')?true:false;
  
  $hideBacklog = Parameter::getUserParameter("kanbanHideBacklog");
  $hideBacklog = ($hideBacklog=='on' or $hideBacklog=='1')?true:false;
  
  $hideStatus = Parameter::getUserParameter("kanbanHideStatus");
  $hideStatus = ($hideStatus=='on' or $hideStatus=='')?true:false;
  
  $hideProduct = Parameter::getUserParameter("kanbanHideProduct");
  $hideProduct = ($hideProduct=='on' or $hideProduct=='')?true:false;
  
  $hideActivityPlanning = Parameter::getUserParameter("kanbanHideActivityPlanning");
  $hideActivityPlanning = ($hideActivityPlanning=='on' or $hideActivityPlanning=='')?true:false;
  
  $hideResponsible = Parameter::getUserParameter("kanbanHideResponsible");
  $hideResponsible = ($hideResponsible=='on' or $hideResponsible=='')?true:false;
  
  $hidePriority = Parameter::getUserParameter("kanbanHidePriority");
  $hidePriority = ($hidePriority=='on' or $hidePriority=='')?true:false;
  
  $hideCriticality = Parameter::getUserParameter("kanbanHideCriticality");
  $hideCriticality = ($hideCriticality=='on' or $hideCriticality=='')?true:false;
  
  $hidePlannedDate = Parameter::getUserParameter("kanbanHidePlannedDate");
  $hidePlannedDate = ($hidePlannedDate=='on' or $hidePlannedDate=='')?true:false;
  
  $hidedeType = Parameter::getUserParameter("kanbanHideType");
  $hidedeType = ($hidedeType=='on' or $hidedeType=='')?true:false;
  
  echo '<table style="width:100%;">';
  echo '  <tr>';
  echo '    <td style="vertical-align:top;">';
        echo '<table style="width:240px;margin:5px 15px 5px 5px;display:inline-block;">';
        echo '  <tr>';
        echo '    <td style="width:40px;"><div class="iconDisplayOnKanban iconSize32 imageColorNewGuiNoSelection" style="border:0"></div></td>';
        echo '    <td class="dependencyHeader planningDialogTitle" style="width:200px;text-align:left;padding-left: 5px;">'.i18n('displayOnKanban').'</td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:10px;">';
        echo '      <div id="kanbanSeeWork" name="kanbanSeeWork" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($seeWork)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanSeeWork",this.value,true);';
        echo '          kanbanSeeWork()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanSeeWork\');">'.pq_ucfirst(i18n("kanbanSeeWork")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanShowIdle" name="kanbanShowIdle" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($showIdle)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanShowIdle",this.value,true);';
        echo '          kanbanShowIdle('.$idKanban.')';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'listShowIdle\');">'.pq_ucfirst(i18n("labelKanbanShowIdle")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanFullWidthElement" name="kanbanFullWidthElement" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($fullWidthElement)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanFullWidthElement",this.value,true);';
        echo '          kanbanFullWidthElement()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanFullWidthElement\');">'.pq_ucfirst(i18n("labelKanbanFullWidthElement")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHideBacklog" name="kanbanHideBacklog" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hideBacklog)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHideBacklog",this.value,true);';
        echo '          kanbanHideBacklog()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHideBacklog\');">'.pq_ucfirst(i18n("labelKanbanHideBacklog")).'</span></td>';
        echo '  </tr>';
        echo '</table>';
  echo '    </td>';
  echo '    <td rowspan="2" style="vertical-align:top;">';
        echo '<table style="width:240px;margin:5px 5px 0px 5pxpx;display:inline-block;">';
        echo '  <tr style="padding:5px;">';
        echo '    <td style="width:40px;"><div class="iconDisplayOnKanbanTiles iconSize32 imageColorNewGuiNoSelection" style="border:0"></div></td>';
        echo '    <td class="dependencyHeader planningDialogTitle" style="width:200px;text-align:left;padding-left: 5px;">'.i18n('displayOnKanbanTiles').'</td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHideStatus" name="kanbanHideStatus" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hideStatus)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHideStatus",this.value,true);';
        echo '          kanbanHideStatus()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHideStatus\');">'.pq_ucfirst(i18n("labelKanbanHideStatus")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHideProduct" name="kanbanHideProduct" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hideProduct)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHideProduct",this.value,true);';
        echo '          kanbanHideProduct()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHideProduct\');">'.pq_ucfirst(i18n("labelKanbanHideProduct")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHideActivityPlanning" name="kanbanHideActivityPlanning" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hideActivityPlanning)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHideActivityPlanning",this.value,true);';
        echo '          kanbanHideActivityPlanning()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHideActivityPlanning\');">'.pq_ucfirst(i18n("labelKanbanHideActivityPlanning")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHideResponsible" name="kanbanHideProduct" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hideResponsible)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHideResponsible",this.value,true);';
        echo '          kanbanHideResponsible()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHideResponsible\');">'.pq_ucfirst(i18n("labelKanbanHideResponsible")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHidePriority" name="kanbanHidePriority" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hidePriority)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHidePriority",this.value,true);';
        echo '          kanbanHidePriority()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHidePriority\');">'.pq_ucfirst(i18n("labelKanbanHidePriority")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHideCriticality" name="kanbanHideProduct" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hideCriticality)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHideCriticality",this.value,true);';
        echo '          kanbanHideCriticality()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHideCriticality\');">'.pq_ucfirst(i18n("labelKanbanHideUrgency")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHidePlannedDate" name="kanbanHidePlannedDate" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hidePlannedDate)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHidePlannedDate",this.value,true);';
        echo '          kanbanHidePlannedDate()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHidePlannedDate\');">'.pq_ucfirst(i18n("labelKanbanHidePlannedDate")).'</span></td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td style="padding-top:2px;">';
        echo '      <div id="kanbanHideType" name="kanbanHideType" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" '.(($hidedeType)?'value="on"':'value="off"').' leftLabel="" rightLabel="" style="width:25px;">';
        echo '        <script type="dojo/method" event="onStateChanged" >';
        echo '          saveDataToSession("kanbanHideType",this.value,true);';
        echo '          kanbanHideType()';
        echo '        </script>';
        echo '      </div>';
        echo '    </td>';
        echo '    <td style="padding-left:5px;" class="checkboxLabel"><span onclick="invertSwitchValue(\'kanbanHideType\');">'.pq_ucfirst(i18n("labelKanbanHideType")).'</span></td>';
        echo '  </tr>';
        echo '</table>';
  echo '    </td>';
  echo '  </tr>';
  echo '  <tr>';
  echo '    <td style="vertical-align:top;">';
        echo '<table style="width:240px;margin:5px 15px 0px 5px;display:inline-block;">';
        echo '  <tr>';
        echo '    <td style="width:40px;"><div class="iconDisplayOnKanbanManagement iconSize32 imageColorNewGuiNoSelection" style="border:0"></div></td>';
        echo '    <td class="dependencyHeader planningDialogTitle" style="width:200px;text-align:left;padding-left: 5px;">'.i18n('displayOnKanbanManagement').'</td>';
        echo '  </tr>';
        echo '  <tr>';
        echo '    <td colspan="2">';
        echo '      <table style="width:100%;text-align:center;margin-top: 5px;">';
        echo '        <tr>';
        echo '          <td style="width:80px;text-align:center;vertical-align:top;">';
        if($idKanban!=-1){
          echo '          <div dojoType="dijit.form.Button" class="detailButton" style="position:relative;cursor:pointer;padding-right: 5px"';
          echo '            onclick="loadDialog(\'dialogKanbanUpdate\', function(){kanbanFindTitle(\'addKanban\');}, true, \'&typeDynamic=addKanban\', true, false);">'.formatIcon('KanbanAdd',32,i18n('kanbanAdd')).'</div>';
        }
        echo '          </td>';
        echo '          <td style="width:80px;text-align:center;vertical-align:top;">';
        if($idKanban!=-1 && myKanban($idKanban)){
          echo '          <div dojoType="dijit.form.Button" class="detailButton" style="position:relative;cursor:pointer;padding-right: 5px"';
          echo '            onclick="loadDialog(\'dialogKanbanUpdate\', function(){kanbanFindTitle(\'kanbanEdit\');}, true, \'&typeDynamic=addColumnKanban&typeD='.$type.'&idKanban='.$idKanban.'\', true, false);">'.formatIcon('KanbanAddColumns',32,i18n('kanbanAddColumn')).'</div>';
        }
        echo '          </td>';
        echo '          <td style="width:80px;text-align:center;vertical-align:top;">';
        if($idKanban!=-1){
          echo '          <div dojoType="dijit.form.Button" class="detailButton" style="position:relative;cursor:pointer;padding-right: 5px"';
          echo '            onclick="showDetail(\'refreshActionAdd'.$typeKanbanC.'\',1,\''.$typeKanbanC.'\',false,\'new\');">'.formatIcon('KanbanAdd'.$typeKanbanC,32, i18n('kanbanAdd'.$typeKanbanC)).'</div>';
        }
        echo '          </td>';
        echo '        </tr>';
        echo '        <tr>';
        echo '          <td style="font-size:80%;color:#a0a0a0;vertical-align: top;">'.i18n('kanbanAddShort').'</td>';
        echo '          <td style="font-size:80%;color:#a0a0a0;vertical-align: top;">'.i18n('kanbanAddColumnShort').'</td>';
        echo '          <td style="font-size:80%;color:#a0a0a0;vertical-align: top;">'.i18n('kanbanAdd'.$typeKanbanC.'Short').'</td>';
        echo '        </tr>';
        echo '      </table>';
        echo '    </td>';
        echo '  </tr>';
        echo '</table>';
  echo '    </td>';
  echo '  </tr>';
  echo '</table>';
}

/*function kanbanSortJsonOrder($a, $b) {
  $expa=pq_explode('-', $a);
  $expb=pq_explode('-', $b);
  $aVal=(count($expa)>1)?$expa[1]:$expa[0];
  $bVal=(count($expb)>1)?$expb[1]:$expb[0];
  if (is_int($aVal) and is_int($bVal)) {
    return intval($aVal)-intval($bVal);
  } else {
    return ($aVal<$bVal)?(-1):($aVal>$bVal)?1:0;
  }
}*/
?>
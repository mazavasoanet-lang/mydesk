<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

require_once "../tool/kanbanConstructPrinc.php";
function kanbanDisplayTicket($id, $type, $idKanban, $from, $line, $add, $mode) {
	global $typeKanbanC, $arrayProject;
	$kanB = new Kanban ( $idKanban, true );
	$json = $kanB->param;
	$jsonDecode = json_decode ( $json, true );
	$idType = $from;
	if ($type=='Status' and isset($line['idstatus'])) {
		$idType=$line['idstatus'];
	}
	if (! $typeKanbanC) {
		$typeKanbanC = $jsonDecode ['typeData'];
	}
	$handle = 'dojoDndHandle';
	if (securityGetAccessRightYesNo ( "menu" . $typeKanbanC, "update", new $typeKanbanC ( $line ['id'], true ) ) != "YES")
		$handle = "";
	
	$proJ = new Project ( $line ['idproject'], true );
	$arrayProject [$line ['idproject']] = $proJ->getColor ();
	$color = $arrayProject [$line ['idproject']];
	$kanbanFullWidthElement = Parameter::getUserParameter ( "kanbanFullWidthElement" );
	$destWidth=RequestHandler::getValue('destinationWidth');
	if (!$destWidth) $destWidth=1920;
	$nbCol=(isset($jsonDecode['column']) and is_array($jsonDecode['column']))?count($jsonDecode['column']):1;
	$spaces=10*($nbCol+1);
	$ticketWidth=(($destWidth-$spaces)/$nbCol)-40;
	if ($ticketWidth<305) $ticketWidth=305;
	$nbTktPerCol=intval($ticketWidth/150);
	$hidePlannedDate = Parameter::getUserParameter("kanbanHidePlannedDate");
	$hidePlannedDate = ($hidePlannedDate!='off')?true:false;
	$ticketWidthSmall=(round($ticketWidth/$nbTktPerCol,1)-(2*$nbTktPerCol)).'px';
	$ticketRelativeWidth=($kanbanFullWidthElement == "on")?$ticketWidth:(round($ticketWidth/$nbTktPerCol,1)-(2*$nbTktPerCol));
	
	if (isset ( $line ['description'] )) {
	  $description=$line ['description'];
	  $minChar = 200;
	  if(pq_strpos($description, '<img ') != ''){
	    $minChar = 400;
	    $description=pq_str_replace('<img ','<img style="max-width:'.($ticketRelativeWidth-10).'px;" onClick="showImage(\'Note\',this.src,\' \');"',$description);
	    $img = pq_substr($description, pq_strpos($description, '<img '), (pq_strpos($description, '/>')-pq_strpos($description, '<img '))+2);
	    $description = pq_substr($description, 0, pq_strpos($description, '<img ')).'<div>#IMGREPLACED#</div>'.pq_substr($description, pq_strpos($description, '<img '));
	    $text = new Html2Text ($description);
	    $descr = $text->getText ();
	    $descr=pq_htmlspecialchars($descr);
	    $descr=pq_str_replace('#IMGREPLACED#',$img,$descr);
	  }else{
	    $text = new Html2Text ($description);
	    $descr = $text->getText ();
	    $descr=pq_htmlspecialchars($descr);
	  }
	  if (pq_strlen ($description) > 4000) {
	        $descr1 = pq_substr ( $descr, 0, 4000);
			$ticketDescr = nl2brForPlainText ( $descr1 );
			$descr2 = pq_substr ( $descr, 0, $minChar );
			$ticketDescr2 = nl2brForPlainText ( $descr2 );
	  } else {
	    $ticketDescr=$description;
	    $descr2 = pq_substr ( $descr, 0, $minChar);
	    $ticketDescr2 = nl2brForPlainText ( $descr2 );
	  }
	} else {
		$ticketDescr = '<div style="font-style:italic; color:#CDCADB; ">' . i18n ( "kanbanNoDescription" ) . '</div>';
		$ticketDescr2 = '<div style="font-style:italic; color:#CDCADB; ">' . i18n ( "kanbanNoDescription" ) . '</div>';
	}

	$titleObject=$typeKanbanC . ' #' . $line ['id'] 
	  ." (".SqlList::getNameFromId('Type', $line['idtickettype']).')'
	  ."\n".i18n('Project').' #'.$line['idproject'].' - '.$proJ->name;
	if ($kanbanFullWidthElement == "on") {
	$numCol = count ( $jsonDecode ['column'] );
	
	echo ' <script type="dojo/connect">
      //divWidthKanban(' . $id . ',\'' . $type . '\',' . $numCol . ');
     </script>';
	if ($mode != "refresh") {
	  echo '
      <div class="dojoDndItem ' . $handle . ' ticketKanBanStyleFull ticketKanBanColor " style="'.((isNewGui())?'border-radius:10px;':'').'" fromC="' . $from . '" id="itemRow' . $line ['id'] . '-' . $type . '"
      dndType="' . ($type == 'Status' ? 'typeRow' . $idType . $add : ($type == 'TargetProductVersion' ? $from : SqlList::getFieldFromId ( $typeKanbanC, $line ['id'], "idProject" ))) . '" 
          oncontextmenu="openKanbanContextMenu(' . $line['id'] . ', \''.$typeKanbanC.'\', \''.$line ['idproject'].'\', \''.$type.'\')">';
	}
	echo '<div style="position: relative;background: #FFFFFF;border-radius: 10px;">';
	echo '<div id="topTicket' . $line['id'] . '" style="position: relative;background-color:#FFFFFF;padding:3px;min-height:20px;max-height:20px;border-radius: 10px;cursor:move;">
      		<table style="width:100%">
      		  <tr title="'.$titleObject.'">
          		<td style="font-size:10px;font-family:arial;width:50%">';
                  echo '<div style="float:left;margin: 2px 5px 0px 2px;">'.formatColorRounded ($color, 12, 3, 'left', $titleObject).'</div>';
                  echo '<div style="float:left;margin-top: 3px;" title="'.$titleObject.'">#' . $line ['id'] . '</div>';
                  $hideType = Parameter::getUserParameter("kanbanHideType");
                  $hideType = ($hideType!='off')?true:false;
                  if($hideType){
                    $libType=SqlList::getNameFromId('Type', $line['idtickettype']);
            		  if ($libType){
            		    echo '<div style="float:left;margin: 3px 0px 0px 5px;">' . $libType . '</div>';
            		  }
                  }
          		echo '</td><td style="width:50%">';
          		$hidePlannedDate = Parameter::getUserParameter("kanbanHidePlannedDate");
          		$hidePlannedDate = ($hidePlannedDate!='off')?true:false;
          		if($hidePlannedDate){
          		  if(isset($line['plannedcolor']) and isset($line['plannedenddate'])){
          		    $noDynamicFontColor = ($typeKanbanC != 'Activity')?false:true;
          		    echo '<div style="float:right;padding:2px;opacity:0.9;">'.formatColorRounded ($line['plannedcolor'], 15  , 8, 'left', i18n('colPlannedEndDate'), htmlFormatDate($line['plannedenddate']), 6, $noDynamicFontColor).'</div>';
          		  }
          		}else{
          		  echo '&nbsp';
          		}
          		echo '</td>
              </tr>
            </table>
	  </div>';
	echo ' 
      <div id="titleTicket' . $line['id'] . '" style="position:relative;background-color:#FFFFFF;width:100%;z-index:99;min-height: 25px;max-height: 25px;cursor:move;">
        <div class="kanbanTitleTicket" id="name' . $line ['id'] . '">' . htmlEncode ( $line ['name'] ) . '</div>
      </div>';
     $hideStatus = Parameter::getUserParameter("kanbanHideStatus");
     $hideStatus = ($hideStatus!='off')?true:false;
     if($hideStatus){
      echo '
      <div id="divPrincItem' . $line ['id'] . '" style="position: relative;cursor:move;">
        ' . kanbanAddPrinc ( $line ) . '        
      </div>';
     }
     echo '
      <div id="objectDescr' . $line ['id'] . '" dojoType="dijit.layout.ContentPane" region="center" class=""
      style="width:95%;max-width:'.$ticketWidth.'px;padding:5px 5px 0px 5px;margin-bottom:5px;font-size:12px;font-family:arial;word-wrap:break-word;max-height:300px;overflow-x:hidden;overflow-y:auto;cursor:move;"
      onScroll="kanbanShowDescr(\'description\',\'' . $typeKanbanC . '\', ' . $ticketWidth . ', ' . $line ['id'] . ');">
      ' . $ticketDescr . '</div>';
     echo '<input dojoType="dijit.form.TextBox" id="descr_' . $line ['id'] . '" type="hidden" value="truncated" />';
     $hideProduct = Parameter::getUserParameter("kanbanHideProduct");
     $hideProduct = ($hideProduct!='off')?true:false;
     if($hideProduct){
      echo '
      <div id="divProductItem' . $line ['id'] . '" style="position: relative;cursor:move;font-size:8pt;">
        ' . kanbanAddProduct ( $line ) . '        
      </div>';
     }
     $hideActivityPlanning = Parameter::getUserParameter("kanbanHideActivityPlanning");
     $hideActivityPlanning = ($hideActivityPlanning!='off')?true:false;
     if($hideActivityPlanning){
       echo '
      <div id="divActivityPlanningItem' . $line ['id'] . '" style="position: relative;cursor:move;font-size:8pt;">
        ' . kanbanAddActivityPlanning ( $line ) . '
      </div>';
     }
     $seeWork = Parameter::getUserParameter ( "kanbanSeeWork" . Parameter::getUserParameter ( "kanbanIdKanban" ) );
     $seeWork=($seeWork=='on' or $seeWork=='1')?true:false;
     if($seeWork){
       echo '<div id="divWorkItem'.$line ['id'].'" style="position: relative;background-color:#eeeeee">'. displayAllWork ( $line, 1, 4 ) . '</div>';
     }
	 echo '<div id="bottomTicket'.$line ['id'].'" style="position: relative;cursor:move;width:100%;height:26px;bottom:0;padding-top: 5px;">';
     $hideResponsible = Parameter::getUserParameter("kanbanHideResponsible");
     $hideResponsible = ($hideResponsible!='off')?true:false;
     if($hideResponsible){
       echo '<div class="" style="float:left;margin: 3px 4px 3px 4px;" id="userThumbTicket' . $line ['id'] . '">';
       if(isset ( $line ['iduser']) and $line ['iduser']){
  	        echo '<div style="position:relative;float:left;padding: 0px 5px 3px 0px;" >';
  	        $file=Affectable::getThumbUrl('Affectable', $line ['iduser'], 32);
  	        $title = SqlList::getNameFromId ( "Affectable", $line ['iduser'] ).'<br/><span style="font-size:80%"><i>('.i18n('colResponsible').')</i></span>';
  	        if (pq_substr($file,0,6)=='letter') {
  	          $title=htmlEncode($title,'quotes');
  	          echo '<div '.(($line ['id']>0) ? 'id="responsible'.$line ['id'].'"' : '') .' valueuser="'.$title.'">'.formatIconNewGui('Resource', 20, null, false).'</div>';
  	        } else {
              echo formatUserThumb ( $line ['iduser'], $title, "", 20, 'left', false, $line ['id'], true );
  	        }
  	        echo '</div>';
            echo '<div style="float:left;padding-top: 2px;" >'.formatUserThumbRounded ($line ['iduser'], SqlList::getNameFromId ( "Affectable", $line ['iduser'] ), i18n('colResponsible')).'</div>';
       }
       echo '</div>';
     }
     $hidePriority = Parameter::getUserParameter("kanbanHidePriority");
     $hidePriority = ($hidePriority!='off')?true:false;
     if($hidePriority){
       if(isset ( $line ['idpriority']) and $line ['idpriority']){
         echo '<div style="float:left;margin: 3px 4px 3px 4px;" id="priotityThumbTicket' . $line ['id'] . '">
    	          <div style="float:left;padding: 1px 4px 0px 0px;" >'.formatIconNewGui('Priority', 20, null, false).'</div>
                <div style="float:left;padding-top: 2px;" >'.formatColorThumbRounded ( "idPriority", $line ['idpriority'], 'left', SqlList::getNameFromId ( "Priority", $line ['idpriority'] )).'</div>
              </div>';
       }
     }
     $hideCriticality = Parameter::getUserParameter("kanbanHideCriticality");
     $hideCriticality = ($hideCriticality!='off')?true:false;
     if($hideCriticality){
       if(isset ( $line ['idurgency'] ) and $line ['idurgency']){
         echo '<div style="float:left;margin: 3px 4px 3px 4px;" id="urgencyThumbTicket' . $line ['id'] . '">
        	       <div style="float:left;padding: 1px 4px 0px 0px;" >'.formatIconNewGui('Urgency', 20, null, false).'</div>
                 <div style="float:left;padding-top: 2px;" >'.formatColorThumbRounded ("idUrgency", $line ['idurgency'], 'left', SqlList::getNameFromId ( "Urgency", $line ['idurgency'] )).'</div>
              </div>';
       }
     }
     $object= new $typeKanbanC ($line['id']);
	 $nbBadge=((isset($object->_Note))?count ($object->_Note):'');
	 $margin=($nbBadge>9)?'-10':'-7';
	 $badge= '<div id="kanbanBadge_'.$line['id'].'" class="kanbanBadge" style="">'.$nbBadge.'</div>';
      echo '<table style="margin:5px;float:right;">
        <tr>';
      echo'  <td>
              <div id="badges" style="position:relative">
              <div id="addComent" onclick="activityStreamKanban(' . $line ['id'] . ', \'' . $typeKanbanC . '\', \''.$type.'\');" style="margin-right:5px;margin-top: 3px;" title=" ' . i18n ( 'commentImputationAdd' ) . ' ">
                ' . formatSmallButton ( 'AddComment' ) . '
                    <div  style="pointer-events: none;position:absolute;bottom:'.((isNewGui())?'-3px':'-1px').';margin-left:'.$margin.'px;width:5px;">
                    '.((count($object->_Note)!=0)?$badge:'').'
                    </div>
              </div>
            </div>
            </td>';
      if(isset($object->VotingItem) and $object->VotingItem and !$object->VotingItem->locked){
        $voteAttr = new VotingAttribution();
        $typeName=SqlElement::getTypeName($object->VotingItem->refType);
        $idType = $object->$typeName;
        $canVote = $voteAttr->canVote($object->VotingItem->refType, $object->VotingItem->refId, $idType);
        $voteExist = false;
        $vote = SqlElement::getSingleSqlElementFromCriteria('Voting', array('refType'=>$object->VotingItem->refType,'refId'=>$object->VotingItem->refId,'idUser'=>getCurrentUserId()));
        if($vote->id)$voteExist=true;
        if(!$voteExist){
//           $idUser = getCurrentUserId();
//           $affectable = new Affectable($idUser);
//           if($affectable->isContact){
//             $contact = new Contact($idUser);
//             if($contact->idClient){
//               $vote = SqlElement::getSingleSqlElementFromCriteria('Voting', array('refType'=>$this->refType,'refId'=>$this->refId,'idClient'=>$contact->idClient));
//               if($vote->id)$voteExist=true;
//             }
//           }
          $cpt=$vote->countSqlElementsFromCriteria(array('refType'=>$object->VotingItem->refType,'refId'=>$object->VotingItem->refId));
          if ($cpt>0) $voteExist=true;
        }
        $idRule=VotingItem::getIdUseRule($object->VotingItem->refType, $object->VotingItem->refId);
        $iconLogoVote = 'AddVote';
        if($object->VotingItem->pctRate > 0){
          $iconLogoVote = 'AddVote25';
        }
        if($object->VotingItem->pctRate > 24){
          $iconLogoVote = 'AddVote25';
        }
        if($object->VotingItem->pctRate > 49){
          $iconLogoVote = 'AddVote50';
        }
        if($object->VotingItem->pctRate > 74){
          $iconLogoVote = 'AddVote75';
        }
        if($object->VotingItem->pctRate >=  100){
          $iconLogoVote = 'AddVote100';
        }
        $voteButtonTitle=i18n('colPctRate').' : '.$object->VotingItem->pctRate.' %';
        if ($canVote) $voteButtonTitle.="\n". i18n ('addVoteKanban');
        $onClick="";
        if ($canVote and $idRule) $onClick="addVote('".$object->VotingItem->refType."','".$object->VotingItem->refId."','".getEditorType()."','add',".$idRule.",true);";
        if($canVote and !$voteExist and $idRule){
          echo'
           <td>
              <div id="badges2" style="position:relative">
              <div id="addComent" onclick="'.$onClick.'" style="margin-right:5px;margin-top: 3px;" title="'.$voteButtonTitle.'">
                ' . formatSmallButton ( $iconLogoVote,false,($onClick=="")?false:true ) . '
              </div>
            </div>
            </td>';
        }
        if($voteExist and $idRule){
          echo'
           <td>
              <div id="badges2" style="position:relative">
              <div id="addComent" onclick="'.$onClick.'" style="margin-right:5px;margin-top: 3px;" title="'.$voteButtonTitle.'">
                ' . formatSmallButton ( $iconLogoVote,false,($onClick=="")?false:true ) . '
              </div>
            </div>
            </td>';
        }
        echo'
            <td>
              <div class="roundedButtonSmall"
                style="width:20px;height:16px;cursor:pointer;float:right;vertical-align:text-bottom;"
         		    onclick="gotoElement(\'' . $typeKanbanC . '\',' . htmlEncode ( $line ['id'] ) . ', true);"	title="' .i18n('kanbanGotoItem',array($line ['id'])) . '" style="width:18px;" >
         		   ' . formatSmallButton ( 'Goto',true ) . '     
              </div>
           </td>';
      }
          echo'
        </tr>
      </table>
     </div>';
echo '</div>';
		if ($mode != "refresh") {
			echo '</div>';
		}
	} else {
		// if button is unchecked elements are in normal mode
		if ($mode != "refresh") {
			echo '
    <div class="dojoDndItem ' . $handle . ' ticketKanBanStyle ticketKanBanColor " style="width:'.$ticketWidthSmall.';min-width: 160px;" fromC="' . $from . '" id="itemRow' . $line ['id'] . '-' . $type . '"
    dndType="' . ($type == 'Status' ? 'typeRow' . $idType . $add : ($type == 'TargetProductVersion' ? $from : SqlList::getFieldFromId ( $typeKanbanC, $line ['id'], "idProject" ))) . '"
         oncontextmenu="openKanbanContextMenu(' . $line['id'] . ', \''.$typeKanbanC.'\', \''.$line ['idproject'].'\', \''.$type.'\')">';
		}
		echo '<div style="position: relative;background: #FFFFFF;border-radius: 8px;cursor:move;">';
		echo '<div id="topTicket' . $line['id'] . '" style="position: relative;background-color:#FFFFFF;padding:3px;min-height:20px;max-height:20px;border-radius: 5px;cursor:move;">
        		<table style="width:100%">
        		  <tr title="'.$titleObject.'">
            		<td style="font-size:10px;font-family:arial;width:75%">';
                    echo '<div style="float:left;margin: 2px 5px 0px 2px;">'.formatColorRounded ($color, 12, 3, 'left', $titleObject).'</div>';
                    echo '<div style="float:left;margin-top: 3px;">#' . $line ['id'] . '</div>';
	                $hideType = Parameter::getUserParameter("kanbanHideType");
                    $hideType = ($hideType!='off')?true:false;
                    if($hideType){
                      $libType=SqlList::getFieldFromId('Type', $line['idtickettype'],'code');
            		  if (!$libType) $libType=pq_substr(SqlList::getNameFromId('Type', $line['idtickettype']),0,3);
              		  if ($libType){
              		    echo '<div style="float:left;margin: 3px 0px 0px 5px;overflow: hidden;width:45px;text-overflow: ellipsis;">' . $libType . '</div>';
              		  }
                    }
            		echo '</td>';
            		$hidePlannedDate = Parameter::getUserParameter("kanbanHidePlannedDate");
            		$hidePlannedDate = ($hidePlannedDate!='off')?true:false;
            		if($hidePlannedDate){
            		  if(isset($line['plannedcolor']) and isset($line['plannedenddate'])){
            		    $noDynamicFontColor = ($typeKanbanC != 'Activity')?false:true;
            		    echo '<td style="width:25%">';
            		    echo '<div style="float:right;padding:2px;opacity:0.9;">'.formatColorRounded ($line['plannedcolor'], 15  , 8, 'left', i18n('colPlannedEndDate'), htmlFormatDate($line['plannedenddate']), 6, $noDynamicFontColor).'</div>';
            		    echo '</td>';
            		  }
            		}
            		echo '</tr>
              </table>
		  </div>';
		echo ' 
        <div id="titleTicket' . $line['id'] . '" style="position:relative;background-color:#FFFFFF;width:100%;z-index:99;min-height: 25px;max-height: 25px;cursor:move;">
          <div class="kanbanTitleTicket" id="name' . $line ['id'] . '">' . htmlEncode ( $line ['name'] ) . '</div>
        </div>';
       $hideStatus = Parameter::getUserParameter("kanbanHideStatus");
       $hideStatus = ($hideStatus!='off')?true:false;
       if($hideStatus){
        echo '
        <div id="divPrincItem' . $line ['id'] . '" style="position: relative;cursor:move;">
          ' . kanbanAddPrinc ( $line ) . '        
        </div>';
       }
       $hideProduct = Parameter::getUserParameter("kanbanHideProduct");
       $hideProduct = ($hideProduct!='off')?true:false;
       $hideActivityPlanning = Parameter::getUserParameter("kanbanHideActivityPlanning");
       $hideActivityPlanning = ($hideActivityPlanning!='off')?true:false;
       $descMaxHeight = 50;
	   if($hideProduct and !isset($line['idtargetproductversion'])){
         $descMaxHeight += 20;
       }
       if($hideActivityPlanning and !isset($line['idactivity'])){
         $descMaxHeight += 20;
       }
       echo '
      <div id="objectDescr' . $line ['id'] . '" class=""
        style="position: relative;padding:5px 5px 0px 5px;margin-bottom:5px;font-size:12px;font-family:arial;max-width:'.$ticketWidthSmall.';max-height:'.$descMaxHeight.'px;'.((isNewGui())?'min-height:'.$descMaxHeight.'px;':'').'overflow-y:auto;overflow-x: hidden;"
        onScroll="kanbanShowDescr(\'description\',\'' . $typeKanbanC . '\', ' . $ticketRelativeWidth . ', ' . $line ['id'] . ');" >
          ' . $ticketDescr2 . '
      </div>';
       echo '<input dojoType="dijit.form.TextBox" id="descr_' . $line ['id'] . '" type="hidden" value="truncated" />';
       if($hideProduct){
        echo '
        <div id="divProductItem' . $line ['id'] . '" style="position: relative;cursor:move;font-size:8pt;">
          ' . kanbanAddProduct ( $line ) . '        
        </div>';
       }
       if($hideActivityPlanning){
         echo '
        <div id="divActivityPlanningItem' . $line ['id'] . '" style="position: relative;cursor:move;font-size:8pt;">
          ' . kanbanAddActivityPlanning ( $line ) . '
        </div>';
       }
       $seeWork = Parameter::getUserParameter ( "kanbanSeeWork" . Parameter::getUserParameter ( "kanbanIdKanban" ) );
       $seeWork=($seeWork=='on' or $seeWork=='1')?true:false;
       if($seeWork){
         echo '<div id="divWorkItem'.$line ['id'].'" style="position: relative;background-color:#eeeeee">'. displayAllWork ( $line, 1, 4 ) . '</div>';
       }
       echo '<div id="bottomTicket'.$line ['id'].'" style="position: relative;cursor:move;width:100%;height:26px;bottom:0;">';
       $hideResponsible = Parameter::getUserParameter("kanbanHideResponsible");
       $hideResponsible = ($hideResponsible!='off')?true:false;
       if($hideResponsible){
         echo '<div style="float:left;margin:5px 0px 5px 5px;position:relative" id="userThumbTicket' . $line ['id'] . '">';
         if(isset ( $line ['iduser'] ) and $line ['iduser']){
           echo formatUserThumb ( $line ['iduser'], SqlList::getNameFromId ( "Affectable", $line ['iduser'] ).'<br/><span style="font-size:80%"><i>('.i18n('colResponsible').')</i></span>', "", 20, 'left', false, $line ['id'], true );
         }
         echo '</div>';
       }
       $hidePriority = Parameter::getUserParameter("kanbanHidePriority");
       $hidePriority = ($hidePriority!='off')?true:false;
       if($hidePriority){
         if(isset ( $line ['idpriority'] ) and $line ['idpriority']){
          echo '<div style="float:left;margin:5px 0px 5px 5px;position:relative">
                ' . formatColorThumb ( "idPriority", $line ['idpriority'], 20, 'left', SqlList::getNameFromId ( "Priority", $line ['idpriority'] ), $line ['id'], true ) . '
                </div>';
         }
       }
       $hideCriticality = Parameter::getUserParameter("kanbanHideCriticality");
       $hideCriticality = ($hideCriticality!='off')?true:false;
       if($hideCriticality){
        if(isset ( $line ['idurgency'] ) and $line ['idurgency']){
          echo '<div style="float:left;margin:5px 0px 5px 5px;position:relative">
                ' . formatColorThumb ("idUrgency", $line ['idurgency'], 20, 'left', SqlList::getNameFromId ( "Urgency", $line ['idurgency'] ), $line ['id'], true ) . '
                </div>';
        }
       }
		$object= new $typeKanbanC ($line['id']);
		$nbBadge=((isset($object->_Note) )?count ($object->_Note):'');
		$margin=($nbBadge>9)?'-10':'-7';
		$badge= '<div id="'.$line['name'].'BadgeTab" class="kanbanBadge" style="">'.$nbBadge.'</div>';
		//gautier #voting
       echo  '<table style="float:right;margin:5px;">
          <tr>';
       echo'
            <td>
              <div id="badges" style="position:relative">
              <div id="addComent" onclick="activityStreamKanban(' . $line ['id'] . ', \'' . $typeKanbanC . '\', \''.$type.'\');" style="margin-right:5px;margin-top: 3px;" title=" ' . i18n ( 'commentImputationAdd' ) . ' ">
                ' . formatSmallButton ( 'AddComment' ) . '
                    <div  style="pointer-events: none;position:absolute;bottom:'.((isNewGui())?'-3px':'-1px').';margin-left:'.$margin.'px;width:5px;">
                    '.((count($object->_Note)!=0)?$badge:'').'
                    </div>
              </div>
            </div>
            </td>';
      if(isset($object->VotingItem) and $object->VotingItem and !$object->VotingItem->locked){
        $voteAttr = new VotingAttribution();
        $typeName=SqlElement::getTypeName($object->VotingItem->refType);
        $idType = $object->$typeName;
        $canVote = $voteAttr->canVote($object->VotingItem->refType, $object->VotingItem->refId, $idType);
        $voteExist = false;
        $vote = SqlElement::getSingleSqlElementFromCriteria('Voting', array('refType'=>$object->VotingItem->refType,'refId'=>$object->VotingItem->refId,'idUser'=>getCurrentUserId()));
        if($vote->id)$voteExist=true;
        if(!$voteExist){
//           $idUser = getCurrentUserId();
//           $affectable = new Affectable($idUser);
//           if($affectable->isContact){
//             $contact = new Contact($idUser);
//             if($contact->idClient){
//               $vote = SqlElement::getSingleSqlElementFromCriteria('Voting', array('refType'=>$this->refType,'refId'=>$this->refId,'idClient'=>$contact->idClient));
//               if($vote->id)$voteExist=true;
//             }
//           }
          $cpt=$vote->countSqlElementsFromCriteria(array('refType'=>$object->VotingItem->refType,'refId'=>$object->VotingItem->refId));
          if ($cpt>0) $voteExist=true;
        }
        $idRule=VotingItem::getIdUseRule($object->VotingItem->refType, $object->VotingItem->refId);
        $iconLogoVote = 'AddVote';
        if($object->VotingItem->pctRate > 0){
          $iconLogoVote = 'AddVote25';
        }
        if($object->VotingItem->pctRate > 24){
          $iconLogoVote = 'AddVote25';
        }
        if($object->VotingItem->pctRate > 49){
          $iconLogoVote = 'AddVote50';
        }
        if($object->VotingItem->pctRate > 74){
          $iconLogoVote = 'AddVote75';
        }
        if($object->VotingItem->pctRate >=  100){
          $iconLogoVote = 'AddVote100';
        }
        $voteButtonTitle=i18n('colPctRate').' : '.$object->VotingItem->pctRate.' %';
        if ($canVote) $voteButtonTitle.="\n". i18n ('addVoteKanban');
        $onClick="";
        if ($canVote and $idRule) $onClick="addVote('".$object->VotingItem->refType."','".$object->VotingItem->refId."','".getEditorType()."','add',".$idRule.",true);";
        if($canVote and !$voteExist and $idRule){
          echo'
           <td>
              <div id="badges2" style="position:relative">
              <div id="addComent" onclick="'.$onClick.'" style="margin-right:5px;margin-top: 3px;" title="'.$voteButtonTitle.'">
                ' . formatSmallButton ( $iconLogoVote,false,($onClick=="")?false:true) . '
              </div>
            </div>
            </td>';
        }
        if($voteExist){
          echo'
           <td>
              <div id="badges2" style="position:relative">
              <div id="addComent" onclick="'.$onClick.'" style="margin-right:5px;margin-top: 3px;" title="'.$voteButtonTitle.'">
                ' . formatSmallButton ( $iconLogoVote,false,($onClick=="")?false:true) . '
              </div>
            </div>
            </td>';
        }
        echo'
            <td>
              <div class="roundedButtonSmall"
                style="width:20px;height:16px;cursor:pointer;float:right;vertical-align:text-bottom;"
         		    onclick="gotoElement(\'' . $typeKanbanC . '\',' . htmlEncode ( $line ['id'] ) . ', true);"	title="' .i18n('kanbanGotoItem',array($line ['id'])) . '" style="width:18px;" >
         		   ' . formatSmallButton ( 'Goto',true ) . '
              </div>
           </td>';
     }
      echo'
          </tr>
        </table>
      </div>
    </div>';
    echo '</div>';
		if ($mode != "refresh") {
			echo '</div>';
		}
	}
}

?>
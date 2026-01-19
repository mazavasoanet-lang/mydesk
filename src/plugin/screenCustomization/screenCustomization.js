/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 *
 ******************************************************************************
 *** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
 ******************************************************************************
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
 
/* =============================================================================== */
/* Extra JavaScript for custom screen management                           */
/* =============================================================================== */
pluginMenuPage['menuScreenCustomization']='../plugin/screenCustomization/screenCustomization.php'; // Screen to load on menu call

var screenCustomizationOldSelectedClass=null;
var screenCustomizationSelectInProgress=false;
function plg_screenCustomization_refreshClass(newClass) {
  if (screenCustomizationSelectInProgress) return;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    screenCustomizationSelectInProgress=true;
    dijit.byId('customizationClass').set('value',screenCustomizationOldSelectedClass);
    setTimeout("screenCustomizationSelectInProgress=false;",100);
    return;
  }
  saveDataToSession('plgScreenCustomizationObjectClass',newClass,false);
  screenCustomizationOldSelectedClass=newClass;
  if (newClass) {
    loadContent('objectDetail.php?refresh=true&objectClass='+newClass+'&objectId=','formDiv',null);
    loadContent('../plugin/screenCustomization/screenCustomizationFields.php?objectClass='+newClass,'screenCustomizationFields',null);
  } else {
    dijit.byId('formDiv').set('content','');
    dijit.byId('screenCustomizationFields').set('content','');
  }
}

function plg_screenCustomization_updateDbName() {
  // Have to setTimeout to ensure new value is set
  setTimeout('plg_screenCustomization_updateDbName_delayed()',20);
}
function plg_screenCustomization_updateDbName_delayed() {
  var value=dijit.byId('screenCustomizationEditFieldField').get('value');
  dijit.byId('screenCustomizationEditFieldDbColumnName').set('value',value);
  if ( (value.substr(0,2)=='id' && value.length>2 && value.substr(2,1)==value.substr(2,1).toUpperCase()) 
      || (value.indexOf('__id')>0 && value.substr(value.indexOf('__id')+4,1)==value.substr(value.indexOf('__id')+4,1).toUpperCase()) ) {
    dijit.byId('screenCustomizationEditFieldDataType').set("value","reference");
    dijit.byId('screenCustomizationEditFieldDataLength').set("value",null);
  }
  if (value.substr(0,5)=='color' && (value=='color' || value.substr(5,1)==value.substr(5,1).toUpperCase())) {
    dijit.byId('screenCustomizationEditFieldDataType').set("value","varchar");
    dijit.byId('screenCustomizationEditFieldDataLength').set("value",7);
  }
  if (value.substr(0,5)=='_lib_') {
    dijit.byId('screenCustomizationEditFieldDataType').set("value","message");
  }
  plg_screenCustomization_showHideSectionAttributes(value);
}
function plg_screenCustomization_showHideSectionAttributes(value) {
  if (value.substr(0,5)=='_sec_') {
    dijit.byId('screenCustomizationEditFieldDataType').set("value","section");
    dijit.byId('screenCustomizationEditFieldDataLength').set("value",null);
    dojo.query(".notForSection").forEach(function(domNode){
      domNode.style.display='none';
    });
  } else {  
    if (dijit.byId('screenCustomizationEditFieldDataType').get("value")=='section') dijit.byId('screenCustomizationEditFieldDataType').set("value",'varchar');
    dojo.query(".notForSection").forEach(function(domNode){
      domNode.style.display='table-row';
    });
  }
}

function plg_screenCustomization_save() {
  dojo.byId("saveButton").blur();
  disableWidget("saveButton");
  disableWidget("undoButton");
  formChangeInProgress=false;
  var callBackFunc=function() {
    loadContent('objectDetail.php?refresh=true&objectClass='+dijit.byId('customizationClass').get('value')+'&objectId=','formDiv',null);
    loadContent('../plugin/screenCustomization/screenCustomizationFields.php?objectClass='+dijit.byId('customizationClass').get('value'),'screenCustomizationFields',null);
    disableWidget("saveButton");
  };
  loadContent("../plugin/screenCustomization/screenCustomizationSaveExtraHiddenType.php", "resultDivMain", "screenCustomizationFieldsForm", true, null,null, null, callBackFunc);
}

function plg_screenCustomization_saveField(objectClass) {
  var callBackFunc=function() {
    loadContent('objectDetail.php?refresh=true&objectClass='+objectClass+'&objectId=','formDiv',null);
    loadContent('../plugin/screenCustomization/screenCustomizationFields.php?objectClass='+objectClass,'screenCustomizationFields',null);
    disableWidget("saveButton");
    if (dojo.byId("screenCustomizationReopenDialog") && dojo.byId("screenCustomizationReopenDialog").value=='true') {
      clickCloseBoxOnMessageAction=function() {
        if (dijit.byId('dialogEditField')) dijit.byId('dialogEditField').show();
      };
    } else {
      clickCloseBoxOnMessageAction=null;
    }
  };
  loadContent("../plugin/screenCustomization/screenCustomizationSaveField.php", "resultDivMain", "screenCustomizationEditFieldForm", true, null,null, null, callBackFunc);
  dijit.byId('dialogEditField').hide();
}

function plg_screenCustomization_edit(objectClass, field) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&objectClass="+objectClass+"&field="+field;
  plg_screenCustomization_loadDialog("dialogEditField",null,true,params, false);
}

function plg_screenCustomization_add(objectClass) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&objectClass="+objectClass+"&new=true";
  plg_screenCustomization_loadDialog("dialogEditField",null,true,params, false);
}

function  plg_screenCustomization_remove(objectClass,field, fieldName)  {
  var param="?screenCustomizationEditFieldObjectClass="+objectClass;
  param+="&screenCustomizationEditFieldField="+field;
  param+="&delete=true";
  actionOK=function() {
    var callBackFunc=function() {
      loadContent('objectDetail.php?refresh=true&objectClass='+objectClass+'&objectId=','formDiv',null);
      loadContent('../plugin/screenCustomization/screenCustomizationFields.php?objectClass='+objectClass,'screenCustomizationFields',null);
      disableWidget("saveButton");
    };
    loadContent("../plugin/screenCustomization/screenCustomizationSaveField.php"+param, "resultDivMain", null, true, null,null, null, callBackFunc);
  };
  msg=i18n('confirmDeleteCustomField', new Array(fieldName));
  showConfirm(msg, actionOK);
}
function  plg_screenCustomization_reset(objectClass)  {
  var param="?screenCustomizationEditFieldObjectClass="+objectClass;
  actionOK=function() {
    var callBackFunc=function() {
      loadContent('objectDetail.php?refresh=true&objectClass='+objectClass+'&objectId=','formDiv',null);
      loadContent('../plugin/screenCustomization/screenCustomizationFields.php?objectClass='+objectClass,'screenCustomizationFields',null);
      disableWidget("saveButton");
    };
    loadContent("../plugin/screenCustomization/screenCustomizationReset.php"+param, "resultDivMain", null, true, null,null, null, callBackFunc);
  };
  msg=i18n('confirmResetCustomScreen');
  showConfirm(msg, actionOK);
}

function plg_screenCustomization_loadDialog(dialogDiv, callBack, autoShow, params, clearOnHide) {
  var hideCallback=function() {
  };
  if (clearOnHide) {
    hideCallback=function() {
      dijit.byId(dialogDiv).set('content', null);
    };
  }
  extraClass="projeqtorDialogClass";
  if (!dijit.byId(dialogDiv)) {
    dialog=new dijit.Dialog({
      id : dialogDiv,
      title : i18n(dialogDiv),
      width : '500px',
      onHide : hideCallback,
      content : i18n("loading"),
      'class' : extraClass
    });
  } else {
    dialog=dijit.byId(dialogDiv);
  }
  if (!params) {
    params="";
  }
  showWait();
  dojo.xhrGet({
    url : '../plugin/screenCustomization/screenCustomizationDynamicDialog.php?dialog=' + dialogDiv + '&isIE='
        + ((dojo.isIE) ? dojo.isIE : '') + params,
    handleAs : "text",
    load : function(data) {
      var contentWidget=dijit.byId(dialogDiv);
      if (!contentWidget) {
        return;
      }
      contentWidget.set('content', data);
      if (autoShow) {
        setTimeout("dijit.byId('" + dialogDiv + "').show();", 100);
      }
      hideWait();
      if (callBack) {
        setTimeout(callBack, 10);
      }
    },
    error : function() {
      consoleTraceLog("error loading dialog " + dialogDiv);
      hideWait();
    }
  });
}

function plg_screenCustomization_customLists() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBackFunc=function() {
  };
  loadContent("../plugin/screenCustomization/screenCustomizationEditLists.php", "centerDiv", null, false, null,null, null, callBackFunc);
}
function plg_screenCustomization_customLists_back() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  loadContent("../plugin/screenCustomization/screenCustomization.php", "centerDiv", null, false, null,null, null, null);
}

var plg_screenCustomization_noCheck=false;
function plg_screenCustomization_customLists_edit(listClass,listClassName) {
  if (plg_screenCustomization_noCheck==false) {
    if (checkFormChangeInProgress()) {
      showAlert(i18n('alertOngoingChange'));
      return;
    }
  }
  plg_screenCustomization_noCheck=false;
  cleanContent("detailDiv");
  formChangeInProgress=false;
  
  if (!listClass || trim(listClass=='')) {
    resetCustomListScreen();
  } else {
    loadContent("../view/objectMain.php?objectClass=" + listClass, "customizationMainDiv");
  }
  return true;
}

function plg_screenCustomization_customLists_new() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  resetCustomListScreen();
  plg_screenCustomization_noCheck=true;
  dijit.byId('customizationListClass').set('value',null);
  params=null;
  plg_screenCustomization_loadDialog("dialogAddCustomList",null,true,params, false);    
}

function plg_screenCustomization_customList_save(newCustomClass) {
  formChangeInProgress=false;
  plg_screenCustomization_noCheck=true;
  var callBackFunc=function() {
    formChangeInProgress=false;
    var lastOperationStatus = dojo.byId('lastOperationStatus');
    var status=(lastOperationStatus)?lastOperationStatus.value:'ERROR';
    if (status!='OK') return;
    plg_screenCustomization_refreshCustomList_refreshList();
    if (dojo.byId('screenCustomisationSaveListNewClass')) {
      plg_screenCustomization_noCheck=true;
      formChangeInProgress=false;
      var lastCreatedClass=dojo.byId('screenCustomisationSaveListNewClass').value;
      var lastCreatedClassName=dojo.byId('screenCustomisationSaveListNewClass').value;
      setTimeout("formChangeInProgress=false;plg_screenCustomization_noCheck=true;"
          +"dojo.byId('customizationListClass').value='"+lastCreatedClass+"';" 
          +"formChangeInProgress=false;plg_screenCustomization_noCheck=true;"
          +"dijit.byId('customizationListClass').set('value','"+lastCreatedClass+"');",300);
    }
    
  };
  loadContent("../plugin/screenCustomization/screenCustomizationSaveList.php", "resultDivMain", "addCustomListForm", true, null,null, null, callBackFunc);
  dijit.byId('dialogAddCustomList').hide();
  
}
function plg_screenCustomization_refreshCustomList_refreshList() {
  var urlList='../plugin/screenCustomization/refreshCustomList.php';
  var datastore=new dojo.data.ItemFileReadStore({ url : urlList });
  var store=new dojo.store.DataStore({ store : datastore });
  store.query({ id : "*" });
  var selectList=dijit.byId('customizationListClass');
  selectList.set('store', store);
  formChangeInProgress=false;
}


function plg_screenCustomization_customLists_delete() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var listClassSelected="";
  if (dijit.byId('customizationListClass')) {
    listClassSelected=dijit.byId('customizationListClass').get('value');
  }
  if (! listClassSelected) {
    showAlert(i18n('errorListClassNotExists',new Array(listClassSelected)));
    return;
  }
  actionOK=function() {
    var listClassSelected=dijit.byId('customizationListClass').get('value');
    var param="?customListClass="+listClassSelected;
    resetCustomListScreen();
    var callBackFunc=function() {
      var lastOperationStatus = dojo.byId('lastOperationStatus');
      var status=(lastOperationStatus)?lastOperationStatus.value:'ERROR';
      if (status!='OK') return;
      formChangeInProgress=false;
      plg_screenCustomization_refreshCustomList_refreshList();
      setTimeout("dijit.byId('customizationListClass').reset();formChangeInProgress=false;",500);
    };
    loadContent("../plugin/screenCustomization/screenCustomizationDeleteList.php"+param, "resultDivMain", null, true, null,null, null, callBackFunc);
  };
  msg=i18n('confirmDeleteCustomList', new Array(dijit.byId('customizationListClass').get('displayedValue')));
  showConfirm(msg, actionOK); 
}
function resetCustomListScreen() {
  var newContentNoSelectedList='<div style="padding-top:200px;height:20px;width:100%;text-align:center;vertical-align:middle;">'
    +i18n("editCustomListSelect")+'</div>';
  dijit.byId('customizationMainDiv').set('content',newContentNoSelectedList);
}

function plg_screenCustomization_changeScope(objectClass, scope) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  loadContent('../plugin/screenCustomization/screenCustomizationFields.php?objectClass='+objectClass+'&hideScope='+scope,'screenCustomizationFields',null);
}

function plg_screenCustomization_toggleField(attribute,col,id) {
  var inputFld=dojo.byId('check'+attribute+'_'+col+'_'+id);
  var img=dojo.byId('img'+attribute+'_'+col+'_'+id);
  if (! inputFld) { return;} // Error
  formChanged();
  if (inputFld.value==1) {
    inputFld.value=0;
  } else {
    inputFld.value=1;
  }
  if (img) img.src='../plugin/screenCustomization/icon'+attribute+((inputFld.value==0)?'No':'')+'.png';
  if (inputFld.value==1) { // If new attribute is enabled, disable all others
    var availableAttributes=new Array('Hidden','Readonly','Required');
    for (i=0;i<availableAttributes.length;i++) {
      var attr=availableAttributes[i];
      if (attr!=attribute) {
        var inputFldOther=dojo.byId('check'+attr+'_'+col+'_'+id);
        if (inputFldOther && inputFldOther.value==1) {
          inputFldOther.value=0;
          var imgOther=dojo.byId('img'+attr+'_'+col+'_'+id);
          if (imgOther) imgOther.src='../plugin/screenCustomization/icon'+attr+'No.png';
        }
      }
    }
  }
}
function screenCustomizationChangeAttribute(zone,attr,value) {
  if (zone=='Field' || zone=='Label') {
    var fldName='screenCustomization'+zone+attr;
    if (attr=='FontWeight' || attr=='FontStyle') {
      if (dojo.hasClass(dojo.byId(fldName),'selected')) {
        dojo.removeClass(dojo.byId(fldName),'selected');
      } else {
        dojo.addClass(dojo.byId(fldName),'selected');
      }
    }
    if (attr=='Color' || attr=='Background') {
      dijit.byId(fldName).set('value',value);
      dijit.byId(fldName).domNode.style='border:0;border-left:10px solid '+value;
    }
    var style='';
    if (dojo.hasClass(dojo.byId('screenCustomization'+zone+'FontWeight'),'selected')) style+='font-weight:bold;';
    if (dojo.hasClass(dojo.byId('screenCustomization'+zone+'FontStyle'),'selected')) style+='font-style:italic;';
    if (dijit.byId('screenCustomization'+zone+'Color').get('value')) style+='color:'+dijit.byId('screenCustomization'+zone+'Color').get('value')+' !important;text-shadow:none;';
    if (dijit.byId('screenCustomization'+zone+'Background').get('value')) style+='background:'+dijit.byId('screenCustomization'+zone+'Background').get('value')+' !important;';
    dojo.byId('screenCustomization'+zone+'Style').value=style;
  } else { // zone not set
    var fontStyle='';
    var family=dijit.byId('screenCustomizationFontFamily').get('value');
    if (family) {
      fontStyle+='font-family:'+family+';';
    }
    var size=dijit.byId('screenCustomizationFontSize').get('value');
    if (size) {
      fontStyle+='font-size:'+size+'px;';
    }
    dojo.byId('screenCustomizationFontStyle').value=fontStyle;
  }
  dojo.byId('screenCustomizationPreviewLabel').style=dojo.byId('screenCustomizationFontStyle').value+dojo.byId('screenCustomizationLabelStyle').value;
  dojo.byId('widget_screenCustomizationPreviewField').style='width:200px;'+dojo.byId('screenCustomizationFontStyle').value+dojo.byId('screenCustomizationFieldStyle').value;
  
}
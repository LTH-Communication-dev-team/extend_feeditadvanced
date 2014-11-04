Ext.namespace('TYPO3.Backend.NavigationContainer');

var fsMod;
//TYPO3.Backend.NavigationContainer.PageTree = null;
TYPO3.Backend.NavigationContainer.PageTree = null;
//TYPO3.LLL.core.csh_tooltip_loading = null;

TYPO3.FeEdit.ExtendEditWindow = Ext.extend(TYPO3.FeEdit.Base, {
	editPanel: null,
	targetID: null,
	
	constructor: function(editPanel) {
		this.editPanel = editPanel;
		if (!Ext.ux.Lightbox.hasListener('close')) {
			Ext.ux.Lightbox.addListener('close', this.close, this);
		}
	},
	
	displayLoadingMessage: function(message) {
		Ext.ux.Lightbox.openMessage(message, 200, 120, true);
	},
	
	displayStaticMessage: function(message) {
		Ext.ux.Lightbox.openMessage(message, 200, 100, false);
	},

	displayIframe: function(headerText, url) {
		height = 800; //TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
		width = 1024; //TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;


		Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
	},

	close: function() {
		name = 'ux-lightbox-shim';
		if (window.frames[name].response) {
			json = window.frames[name].response;

			if (json.error) {
				this.displayStaticMessage(json.error);
			} else if (json.url) {
				window.location = json.url;
			} else if (json.uid == 'NEW') {
				// New element was not saved so do nothing and discard it.
			} else if (this.editPanel) {
				this.editPanel.pushContentUpdate(json);
			} else if (this.targetID) {
				Ext.DomHelper.insertAfter(Ext.get(this.targetID), json.content);
				FrontendEditing.scanForEditPanels();
			} else {
				alert(TYPO3.LLL.generalError);
			}
		}
		FrontendEditing.editPanelsEnabled = true;

			// Reset elements to be validated by TBE_EDITOR.
		if (typeof(TBE_EDITOR) != 'undefined') {
			TBE_EDITOR.elements = {};
			TBE_EDITOR.nested = {'field':{}, 'level':{}};
		}
	},

	setTargetID: function(id) {
		this.targetID = id;
	}
});

/*
 * 
 * @type @exp;Ext@call;extend|@exp;Ext@call;extend
 * // Original code in main.js
var theProperty = init;

function init(){
     doSomething();
}

// Extending it by replacing and wrapping, in extended.js
theProperty = (function(old) {
    function extendsInit() {
        old();
        doSomething();
    }

    return extendsInit;
})(theProperty);
 */
//(function () {
//TYPO3.FeEdit.EditPanel = Ext.extend(TYPO3.FeEdit.Base, {
	// Object for an entire content element and its EditPanel.

//}) ();
/*TYPO3.FeEdit.EditPanel.removeContent = TYPO3.FeEdit.ExtendEditPanel.removeContent; // Copy original before overwriting

TYPO3.FeEdit.ExtendEditPanel = Ext.extend(TYPO3.FeEdit.Base, {
        	removeContent: function() {
		this.el.remove();
		this.el = null;
	},
   });

*/

TYPO3.FeEdit.DeleteAction = Ext.extend(TYPO3.FeEdit.EditPanelAction, {
	_process: function(json) {
		if (this.parent && this.parent.getTableName() != 'pages') {
                    var parentDiv = this.parent.el.dom.parentElement.id;
			FrontendEditing.editPanels.removeKey(this.parent.record);
                        
			this.parent.removeContent();
                        var noColumn = $("#content_sidebar_wrapper").find(".feEditAdvanced-allWrapper").length;
                        if(noColumn==0 && $("#content_sidebar_wrapper").length>0) {
                            removeRightColumn();
                        }
                        //feEditAdvanced-allWrapper
		}
	},

	trigger: function() {
		if (confirm(resources.confirmDeleteElement)) {
			TYPO3.FeEdit.DeleteAction.superclass.trigger.apply(this);
		}
	},

	_getCmd: function() {
		return 'delete';
	},

	_getNotificationMessage: function() {
		return "Deleting content.";
	},

	_isModalAction: false
});

function ajax(cmd,table,uid,parentUid)
{
    //console.log(cmd+table+uid+parentUid);
    $("html, body").animate({ scrollTop: 0 }, "slow");
    var pid = $('#pageid').val();
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'extend_feeditadvanced',
            cmd : cmd,
            table : table,
            uid : uid,
            pid : pid,
            parentUid : parentUid,
            lang : $('#beuser_lang').val(),
            sid : Math.random()
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(data) {
            //console.log('data?:'+data);
            if(cmd=='deletePage') {
                //
		if(data.pid){
                    redirectToParentPage(data.pid);
                }
                showMessage(resources.showMessageDeleteHeader, resources.showMessageDeleteText);
            } else if(cmd=='getPidForNewArticles') {
                var pidForNewArticles = data.pidForNewArticles;
                var pid = $('#pageid').val();
                if(!pidForNewArticles) {
                    pidForNewArticles = pid;
                }
                var url = 'index.php?eID=feeditadvanced&TSFE_EDIT%5Brecord%5D=tt_news%3A'+pidForNewArticles+'&TSFE_EDIT%5Bpid%5D='+pid+'&TSFE_EDIT[cmd]=new&pid='+pid;
                //var url = 'index.php?eID=feeditadvanced&TSFE_EDIT[record]=tt_news:'+pidForNewArticles+'&TSFE_EDIT[pid]='+pid+'&TSFE_EDIT[cmd]=new&pid='+pid;
                //ttp://locindex.php?eID=feeditadvanced&TSFE_EDIT[record]=tt_news:                 8390&TSFE_EDIT[pid]=8&TSFE_EDIT[cmd]=new&pid=8
                //url += '&returnUrl=%2Ftypo3conf%2Fext%2Fextend_feeditadvanced%2Fview%2Fcloseframe.php%2F%3F';
                var headerText = 'New Newsrecord<span onclick="closeIframe();return false;" class="fancybox-close"></span>';
                //height = TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
                height = 900;
                width = TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
                Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
            } else if(cmd=='copyContentElement') {
                var newId = data.newId;
                var colpos = data.colpos;
                var pasteContent = data.content; //getCookie('extend_feeditadvanced_copycutitem');
                console.log(data.content);
                //var pasteContentArray = pasteContent.split(':');
                //var table = pasteContentArray[1];
                //var uid = pasteContentArray[2];
                //pasteContent = pasteContentArray[3];
                //pasteContent = unescape(pasteContent);
                if(pasteContent.indexOf('class="feEditAdvanced-editButton pasteAction"') < 0) {
                    //console.log(pasteContent.indexOf('class="feEditAdvanced-editButton pasteAction"'));
                    pasteContent = pasteContent.replace('<input type="button" class="feEditAdvanced-editButton hideAction"','<input type="button" onclick="pasteAction(\''+table+':'+newId+'\');return false;" class="feEditAdvanced-editButton pasteAction" value="'+resources.editPanelPaste+'" title="'+resources.editPanelPasteTooltip+'"><input type="button" class="feEditAdvanced-editButton hideAction"');
                }
                //pasteContent = pasteContent.replace('value="'+table+':'+uid+'"','value="'+table+':'+newId+'"');
                pasteContent = pasteContent.replace(/id="ext-gen/g,'id="ext-gen'+newId);
                if(colpos==2) {
                    pasteContent = pasteContent.replace('feEditAdvanced-draggable draggable','feEditAdvanced-draggable draggable feEditAdvanced-contentWrapperSmall');
                } else if(colpos==0) {
                    pasteContent = pasteContent.replace(' feEditAdvanced-contentWrapperSmall','');
                }
                if(parentUid==='') {
                    //Get the last element in column 0
                    var wrapperId = $('.feEditAdvanced-firstWrapper:first').parent().attr('id');
                    parentUid = $('#'+wrapperId+' .feEditAdvanced-allWrapper:last').attr('id');
                }
                $('#'+parentUid.replace(':','\\:')).after(pasteContent);
               /* if(!parentUid) {
                    $('.feEditAdvanced-firstWrapper:first').after(pasteContent.replace('id="'+table+':'+uid+'"','id="'+table+':'+newId+'"').replace(' feEditAdvanced-allWrapperHover','').replace('visibility: visible;','visibility: hidden;'));
                } else {
                    $('#'+parentUid.replace(':','\\:')).after(pasteContent.replace('id="'+table+':'+uid+'"','id="'+table+':'+newId+'"').replace(' feEditAdvanced-allWrapperHover','').replace('visibility: visible;','visibility: hidden;'));
                }*/
                var myObject = new TYPO3.FeEdit.EditPanel(table+':'+newId);
                //myObject.setupEventListeners();
                showMessage(resources.showMessagePasteHeader, resources.showMessagePasteCutText);
            } else if(cmd=='cutContentElement') {
                var colpos = data.colpos;
                var pasteContent = data.content; //getCookie('extend_feeditadvanced_copycutitem');
                console.log(data.content);
                //var pasteContentArray = data.content; //pasteContent.split(':');
                //var table = pasteContentArray[1];
                //var uid = pasteContentArray[2];
                //pasteContent = pasteContentArray[3];
                //pasteContent = unescape(pasteContent);
                //if(pasteContent.indexOf('class="feEditAdvanced-editButton pasteAction"') < 0) {
                //    pasteContent = pasteContent.replace('<input type="button" class="feEditAdvanced-editButton hideAction"','<input type="button" onclick="pasteAction(\''+table+':'+uid+'\');return false;" class="feEditAdvanced-editButton pasteAction" value="'+resources.editPanelPaste+'" title="'+resources.editPanelPasteTooltip+'"><input type="button" class="feEditAdvanced-editButton hideAction"');
                //}
                //pasteContent = pasteContent.replace(/id="ext-gen/g,'id="ext-gen'+Math.floor((Math.random() * 10000) + 1));
                if(colpos==2) {
                    pasteContent = pasteContent.replace('feEditAdvanced-draggable draggable','feEditAdvanced-draggable draggable feEditAdvanced-contentWrapperSmall');
                } else if(colpos==0) {
                    pasteContent = pasteContent.replace(' feEditAdvanced-contentWrapperSmall','');
                }
                if(parentUid==='') {
                    //Get the last element in column 0
                    var wrapperId = $('.feEditAdvanced-firstWrapper:first').parent().attr('id');
                    parentUid = $('#'+wrapperId+' .feEditAdvanced-allWrapper:last').attr('id');
                }
                /*if(!parentUid) {
                    $('.feEditAdvanced-firstWrapper:first').after(pasteContent.replace(' feEditAdvanced-allWrapperHover','').replace('visibility: visible;','visibility: hidden;'));
                } else {
                    $('#'+parentUid.replace(':','\\:')).after(pasteContent.replace(' feEditAdvanced-allWrapperHover','').replace('visibility: visible;','visibility: hidden;'));
                }*/
                $('#'+parentUid.replace(':','\\:')).after(pasteContent);
                //$('#'+parentUid.replace(':','\\:')).after(pasteContent.replace(' feEditAdvanced-allWrapperHover','').replace('visibility: visible;','visibility: hidden;'));
                //document.cookie="extend_feeditadvanced_copycutitem=copy:"+table+':'+uid+':'+escape(pasteContent);

                setCookie('extend_feeditadvanced_copycutitem', 'copy:'+table+':'+uid,1);
                showMessage(resources.showMessagePasteHeader, resources.showMessagePasteCopyText);
                var myObject = new TYPO3.FeEdit.EditPanel(table+':'+uid);
                //myObject.setupEventListeners();
            } else if(cmd=='hidePage') {
                $('#hideShowPage').html('<a title="'+resources.pageShowPageTooltip+'" href="#" onclick="showPage();return false;">'+resources.pageShowPage+'</a>');
                $('.menuItem.selected').addClass('feEditAdvanced-hiddenPage');
                $('.menuItem.selected').removeClass('feEditAdvanced-hiddenInMenu');
                $('.menuItem.selected .icon-eye-close').remove();
                $('.menuItem.selected').find('span').find('a').append('<span class="icon-ban-circle"></span>')
                showMessage(resources.showMessageShowHeader, resources.showMessageHidePageText);
            } else if(cmd=='showPage') {
                $('#hideShowPage').html('<a title="'+resources.pageHidePageTooltip+'" href="#" onclick="hidePage();return false;">'+resources.pageHidePage+'</a>');
                $('.menuItem.selected .icon-ban-circle').remove();
                $('.menuItem.selected').removeClass('feEditAdvanced-hiddenPage');
                showMessage(resources.showMessageShowHeader, resources.showMessageShowPageText);
            } else if(cmd=='hidePageInMenu') {
                $('#hideShowPageInMenu').html('<a title="'+resources.pageShowPageInMenuTooltip+'" href="#" onclick="showPageInMenu();return false;">'+resources.pageShowPageInMenu+'</a>');
                $('.menuItem.selected').addClass('feEditAdvanced-hiddenInMenu');
                $('.menuItem.selected').find('span').find('a').append('<span class="icon-eye-close"></span>')
                showMessage(resources.showMessageShowHeader, resources.showMessageHidePageInMenuText);
            } else if(cmd=='showPageInMenu') {
                $('#hideShowPageInMenu').html('<a title="'+resources.pageHidePageInMenuTooltip+'" href="#" onclick="hidePageInMenu();return false;">'+resources.pageHidePageInMenu+'</a>');
                $('.menuItem.selected .icon-eye-close').remove();
                $('.menuItem.selected').removeClass('feEditAdvanced-hiddenInMenu');
                showMessage(resources.showMessageShowHeader, resources.showMessageShowPageInMenuText);

            } else if(cmd=='logout') {
                if(data.url) {
                    window.location = '/typo3/logout.php?redirect=' + data.url + '?no_cache=1';
                } else {
                    console.log('no data');
                }
            }
        },
        error: function(xhr, status, error) {
            //var err = eval("(" + xhr.responseText + ")");
            console.log(status+error);
        },
        complete: function(data) {
            console.log('complete'+data.table);
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}

function hideMessage(divtag)
{
    $(divtag).hide();
}

function showMessageFromOuterSource(msgType)
{
    if(msgType=='copyPage') {
        showMessage(resources.showMessageCopyPageHeader, resources.showMessageCopyPageText);
    } else if(msgType=='movePage') {
        showMessage(resources.showMessageMovePageHeader, resources.showMessageMovePageText);
    }
}

function showMessage(header,message)
{
    var content = '<div class="typo3-message message-ok" style="width: 400px">';
    content += '<div onclick="hideMessage(\'.typo3-message\');return false;" class="t3-icon t3-icon-actions t3-icon-actions-message t3-icon-actions-message-close t3-icon-message-ok-close" id=""></div>';
    content += '<div class="header-container"><div class="message-header">'+header+'</div></div>';
    content += '<div class="message-body">'+message+'</div></div>';
    $('#msg-div').html(content);
    $('#msg-div').toggle('slow').delay(6000).toggle('slow');
}

function closeIframe(pageId)
{
    var lightbox = Ext.get('ux-lightbox');
    var overlay = Ext.get('ux-lightbox-overlay');
    var shim = Ext.get('ux-lightbox-shim');
    lightbox.hide();
    overlay.fadeOut({
	duration: 1
    });
    shim.hide();
    $('#ux-lightbox-header').css('display','none');
    if(pageId) {
        parent.redirectToParentPage(pageId);
    }
}

function movePage(copy)
{
    showAjaxLoadingIcon();
    var copyString = '';
    var closeString = '';
    var pid = $('#pageid').val();
    var headerTextValue = '';
    if(copy==='copy') {
        copyString = '&makeCopy=1';
        closeString = '_copy';
        headerTextValue = resources.copyPageHeader;
    } else {
        headerTextValue = resources.movePageHeader;
    }
    var url = '/typo3/move_el.php?table=pages&uid='+pid+copyString;
    url += '&returnUrl=%2Ftypo3conf%2Fext%2Fextend_feeditadvanced%2Fview%2Fcloseframe.php?id='+pid+closeString;
    var headerText = headerTextValue+'<span onclick="closeIframe();return false;" class="fancybox-close"></span>';
    height = TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
    width = TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
    Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
    //toggleItem('.feEditAdvanced-pageItemsMenu');
}

function deletePage()
{
    var pid = $('#pageid').val();

    if (confirm(resources.confirmDeletePage)) {
        ajax('deletePage','pages',pid,'');
    }
}

function createNews()
{
    showAjaxLoadingIcon();
    ajax('getPidForNewArticles','','','');
    //toggleItem('.feEditAdvanced-newItemsMenu');
}

/*function createNewsFinal(pidForNewArticles)
{
    
}*/

function fileManager()
{
    //window.open('/typo3/backend.php?returnUrl=closeframe&module=txdamM1_file','mywindow','width=1024,height=725');
    //window.open('typo3conf/ext/dam/mod_main/tx_dam_navframe.php?&folderOnly=1','mywindow','width=1024,height=725');
    //window.open('/typo3conf/ext/dam/mod_file/index.php','mywindow','width=1024,height=725');
    //window.open('/typo3conf/ext/extend_feeditadvanced/view/tx_extend_feeditadvanced_dam.php','mywindow','width=1024,height=725');
    //hideItems('');
    $('.feEditAdvanced-secondRow-wrap').hide();
    showAjaxLoadingIcon();
    var headerText = resources.damHeader+'<span onclick="closeIframe();return false;" class="fancybox-close"></span>';
    var url='/typo3conf/ext/extend_feeditadvanced/view/tx_extend_feeditadvanced_dam.html';
    var height = TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
    var width = TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
    Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
    return false;
}

function mailFormAdmin()
{
    $('.feEditAdvanced-secondRow-wrap').hide();
    showAjaxLoadingIcon();
    var pid = $('#pageid').val();
    var headerText = resources.mailFormAdminHeader+'<span onclick="closeIframe();return false;" class="fancybox-close"></span>';
    var url = 'index.php?eID=tx_mailformplusadmin&scope='+pid+'pid='+pid+'&action=printList&firstrun=1&sid='+Math.random();
    var height = TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
    var width = TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
    Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
    return false;
}

function userSettings()
{
    var pid = $('#pageid', window.parent.document).val();
    showAjaxLoadingIcon();
    var url = '/typo3/sysext/setup/mod/index.php';
    var headerText = resources.usersettingsHeader+'<span onclick="closeIframe(\''+pid+'\');return false;" class="fancybox-close"></span>';
    var height = TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
    var width = TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
    Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
    //toggleItem('.feEditAdvanced-userItemsMenu');
}

function loadHelp()
{
    $('.feEditAdvanced-secondRow-wrap').hide();
    //showAjaxLoadingIcon();
    var url = '/typo3conf/ext/extend_feeditadvanced/help/index.php?lang='+$('#beuser_lang').val();
    /*var headerText = resources.helpHeader+'<span onclick="closeIframe();return false;" class="fancybox-close"></span>';
    var height = TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
    var width = TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
    Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);*/
    var $dialog = $('<div></div>')
    .load(url)
    .dialog({
            autoOpen: false,
            title: resources.helpHeader,
            width: 700,
            height: 700,
            resizable: false
    });
    $dialog.dialog('open');
}

function editPage()
{
    showAjaxLoadingIcon();
    //http://typotest-2.kansli.lth.se/index.php?eID=feeditadvanced&TSFE_EDIT%5Brecord%5D=pages%3A5&TSFE_EDIT%5Bpid%5D=5&TSFE_EDIT%5BnewRecordInPid%5D=5&TSFE_EDIT[cmd]=edit&pid=5
    var pid = $('#pageid').val();
    var url = 'index.php?eID=feeditadvanced&TSFE_EDIT%5Brecord%5D=pages%3A'+pid+'&TSFE_EDIT%5Bpid%5D='+pid+'&TSFE_EDIT%5BnewRecordInPid%5D='+pid+'&TSFE_EDIT[cmd]=edit&pid='+pid;
    var headerText = resources.editPageHeader+'<span onclick="closeIframe();return false;" class="fancybox-close"></span>';
    height = window.innerHeight; //TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
    width = window.innerWidth; //TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
    Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
    //toggleItem('.feEditAdvanced-pageItemsMenu');
}

function newPage()
{
    showAjaxLoadingIcon();
    var pid = $('#pageid').val();
    var url = 'index.php?eID=feeditadvanced&TSFE_EDIT%5Brecord%5D=pages%3A'+pid+'&TSFE_EDIT%5Bpid%5D='+pid+'&TSFE_EDIT%5BnewRecordInPid%5D='+pid+'&TSFE_EDIT[cmd]=new&pid='+pid;
    var headerText = resources.newPageHeader+'<span onclick="closeIframe();return false;" class="fancybox-close"></span>';
    height = TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
    width = TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
    Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
    //toggleItem('.feEditAdvanced-newItemsMenu');
}

function hidePage()
{
    ajax('hidePage','pages','','');
}

function showPage()
{
    ajax('showPage','pages','','');
}

function hidePageInMenu()
{
    ajax('hidePageInMenu','pages','','');
}

function showPageInMenu()
{
    ajax('showPageInMenu','pages','','');
}

function newContent()
{
    toggleItem('.feEditAdvanced-secondRow-wrap');
    //$('.feEditAdvanced-newItemsMenu').hide();
    //toggleItem('.feEditAdvanced-newItemsMenu');
}

function redirectToParentPage(pageId)
{
    window.top.location.href = '/index.php?id='+pageId;
}

function loadCategorySelector(tt_newsid)
{
    showAjaxLoadingIcon();
    var pid = $('#pageid').val();
    var url = 'index.php?eID=extend_feeditadvanced&cmd=loadCategorySelector&uid='+tt_newsid+'&sid='+Math.random();
    var headerText = resources.newPageHeader+'<span onclick="closeIframe();return false;" class="fancybox-close"></span>';
    height = TYPO3.configuration.feeditadvanced.editWindow.height ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.height) : 600;
    width = TYPO3.configuration.feeditadvanced.editWindow.width ? parseInt(TYPO3.configuration.feeditadvanced.editWindow.width) : 800;
    Ext.ux.Lightbox.openUrl({'href': url, 'title': headerText}, width, height);
    //toggleItem('.feEditAdvanced-newItemsMenu');
    
}

/*function moveItemBetweenListboxes(frombox,tobox,tt_newsid)
{
    var selectedOpts = $("#ux-lightbox-shim").contents().find(frombox+" option:selected");
    var id = null;
    if (selectedOpts.length == 0) {
        alert("Nothing to move.");
        //e.preventDefault();
    } else {
        id = $("#ux-lightbox-shim").contents().find(frombox+" option:selected").val();
    }
    
    $("#ux-lightbox-shim").contents().find(tobox).append($(selectedOpts).clone());
    ajax('changeCategory','tt_news_cat_mm',id,tt_newsid);
    $(selectedOpts).remove();
    //e.preventDefault();
    showMessage('News', 'The news categories has been updated.');

}*/

function showAjaxLoadingIcon()
{
    Ext.ux.Lightbox.openMessage(resources.pleaseWait, 200, 120, true);
}

function addRightColumn()
{
    var noColumn = $('#content_sidebar_wrapper').length;
    if(noColumn>0) {
        alert('Just one right column.');
    } else {
        var pid = $('#pageid').val();
        var content = '';
        //content += '<div class="feEditAdvanced-firstWrapper" id="feEditAdvanced-firstWrapper-colPos-2-pages-'+pid+'"></div>';
        content += '<div id="content_sidebar_wrapper" class="grid-8 omega">';
        content += '<div id="content_sidebar">';
        content += '<div class="feEditAdvanced-firstWrapper" id="feEditAdvanced-firstWrapper-colPos-2-pages-'+pid+'"></div>';
        content += '</div>';
        content += '</div>';
        //content += '</div>';
        $('#text_content_main').attr('class', '');
        $('#text_wrapper').attr('class', 'grid-15');
        $('#text_wrapper').after(content);
    }
    //toggleItem('.feEditAdvanced-pageItemsMenu');
}
/*function onDivHide()
{
    console.log('tjo');
}
*/
function removeRightColumn()
{
    $('#content_sidebar_wrapper').remove();
    $('#text_wrapper').attr('class', 'grid-23 omega');
    $('#text_content_main').attr('class', 'no-sidebar');
}

function toggleItem(selector,eType)
{
    if(eType=='me') {
        $('.feEditAdvanced-secondRow-wrap').hide();
    }
    $(selector).toggle();
}

function hideItems(exception)
{
    $('.top_menu_div').not(exception).hide();
}

function fsModules()
{
    this.recentIds=new Array();					// used by frameset modules to track the most recent used id for list frame.
    this.navFrameHighlightedID=new Array();		// used by navigation frames to track which row id was highlighted last time
    this.currentMainLoaded="";
    this.currentBank="0";
}

function toggleHiddenObject(inputClass,myType)
{
    $('.'+inputClass).toggle();
    var displayString = getCookie('extend_feeditadvanced_usersettings');
    displayString = unescape(displayString);
    
    var displayObject = JSON.parse(displayString);
    
    if(displayObject[myType]=='none') {
        displayObject[myType] = 'block';
        $('#'+myType).css('display','inline-block');
    } else {
        displayObject[myType] = 'none';
        $('#'+myType).css('display','none');
    }
    setCookie('extend_feeditadvanced_usersettings', JSON.stringify(displayObject),0);
}

function feeditadvanced_logout(url)
{
    ajax('logout',url,'','');
}

function urldecode(str) {
    return decodeURIComponent((str + '').replace(/%(?![\da-f]{2})/gi, function() {
      // PHP tolerates poorly formed escape sequences
      return '%25';
    }).replace(/\+/g, '%20'));
}
Ext.onReady(function() {
    /*Tar bort edit-wrap på själva pluginen*/
    //if ($('div.feEditAdvanced-contentWrapper .tt_news_container').length) {
        //$('div.feEditAdvanced-contentWrapper .tt_news_container').parent().prev().remove();
    //}
    //console.log(window.navigator);
    if(getCookie('be_typo_user')) {
       var id = null;
        $('div.feEditAdvanced-contentWrapper .tt_news_container').parent().unwrap();
        $('div.feEditAdvanced-contentWrapper .tt_news_container').unwrap();
 
        //feEditAdvanced-contentWrapper feEditAdvanced-editButton editAction
        $('.news-edit-item').each(function( index ) {
            //var contentWrapper = $(this).find('.feEditAdvanced-contentWrapper');

           var test = $(this).find('.feEditAdvanced-contentWrapper').parent();
           id = $(this).find('.feEditAdvanced-contentWrapper').parent().attr('id');
           var formContent = $(this).find('.feEditAdvanced-contentWrapper').prev();
           $(this).find('.feEditAdvanced-contentWrapper').unwrap();
           $(this).find('.feEditAdvanced-contentWrapper').replaceWith($(this).find('.feEditAdvanced-contentWrapper').html());
           //test.remove();
            //$(this).wrap('<div></div>');
            $(this).wrap('<div class="feEditAdvanced-contentWrapper feEditAdvanced-editButton editAction"></div>');
            $(this).parent().wrap(test);
            $(this).parent().parent().prepend(formContent);
            //console.log(id);
            if(id) var myObject = new TYPO3.FeEdit.EditPanel(id);
            //
            //myObject.setupEventListeners();
            //console.log($(this).attr('id'));
            //$(this).wrap(this
                    
            //feEditAdvanced-allWrapper
            //tmpId = $(this).attr('id');
            //$(this).before('<a href="#" onclick="loadCategorySelector(\''+tmpId+'\');return false;">Share</a>');
        });
        //$('.newswrapper').before('<a href="#" onclick="loadCategorySelector();return false;">Share</a>');
    };
    //Show Insert news in menu
    if($('.tt_news_container').length) {
        $('#extend_feeditadvanced_create_news').css('display','block');
    }
    
    //Hide insert content elements menu
    $( ".feEditAdvanced-secondRow" ).wrap( "<div class='feEditAdvanced-secondRow-wrap'></div>" );
    
    //Show pastebutton if copycutitem cookie exist
    var copyCutCookie = getCookie('extend_feeditadvanced_copycutitem');
    if(copyCutCookie) {
        
        if($('.pasteAction').length==0) {
            $('.feEditAdvanced-editButton.cutAction').each(function(){
                id = $(this).parent().parent().parent().attr('id');
                $(this).after('<input type="button" onclick="pasteAction(\''+id+'\');return false;" class="feEditAdvanced-editButton pasteAction" value="'+resources.editPanelPaste+'" title="'+resources.editPanelPasteTooltip+'">');
            });
        }
        
        //Trunkera etiketter
        $('.grid-15 .feEditAdvanced-editButton').each(function(){
            tmpStr='';
            tmpStr = $(this).val().substring(0,3)+'.';
            $(this).val(tmpStr);
        });
        
        //Göm utklippta element
        $('.feEditAdvanced-allWrapper').each(function(){
            id = $(this).attr('id');
            if(copyCutCookie == 'cut:'+id) {
                $(this).remove();
            }
        });
    }

    var beuser = $('#beuser').val();
    
    fsMod = new fsModules();
    fsMod.navFrameHighlightedID["web"]=null;
        
    /*var tmpBtn1 = '<a class="feEditAdvanced-button feEditAdvanced-editButton movePageAction" onclick="movePage();return false;" title="'+resources.movePageActionTitle+'"';
    tmpBtn1 += '><span class="feEditAdvanced-buttonText">'+resources.movePageActionValue+'</span></a>';
       
    var tmpBtn2 = '<a class="feEditAdvanced-button feEditAdvanced-editButton deletePageAction" onclick="deletePage();return false;" title="'+resources.deletePageActionTitle+'"';
    tmpBtn2 += '><span class="feEditAdvanced-buttonText">'+resources.deletePageActionValue+'</span></a>';
    
    var tmpBtn3 = '<a class="feEditAdvanced-button feEditAdvanced-editButton fileManager" onclick="fileManager();return false;" title="'+resources.fileManagerTitle+'"';
    tmpBtn3 += '><span class="feEditAdvanced-buttonText">'+resources.fileManagerValue+'</span></a>';
    
    var tmpBtn4 = '<a class="feEditAdvanced-button feEditAdvanced-editButton addColumn" onclick="addRightColumn();return false;" title="'+resources.addColumnTitle+'"';
    tmpBtn4 += '><span class="feEditAdvanced-buttonText">'+resources.addColumnValue+'</span></a>';
    
    var displayNews = 'none';
    if($('.tt_news_container .feEditAdvanced-allWrapper').length) {
        displayNews = 'block';
    }
    var tmpBtn5 = '<a style="display:'+displayNews+'" class="feEditAdvanced-button feEditAdvanced-editButton newNewsAction" onclick="createNews();return false;" title="'+resources.newNewsRecordActionTitle+'"';
    tmpBtn5 += '><span class="feEditAdvanced-buttonText">'+resources.newNewsRecordActionValue+'</span></a>';
 
    var user = '<a class="user-settings-wrapper" title="Change user settings" onclick="userSettings();return false;"><span class="user_settings">' + beuser + '</span></a>';
    
    var backend = '<a class="feEditAdvanced-button feEditAdvanced-editButton backend" title="Go to Typo3 backend" href="/typo3/">';
        backend += '<span class="feEditAdvanced-buttonText">Typo3 Backend</span></a>';

    var help = '<a class="feEditAdvanced-button feEditAdvanced-editButton help" title="Help" onclick="loadHelp();return false;">';
    help += '<span class="feEditAdvanced-buttonText">Help</span></a>';*/
    /*var tmpBtn1 = '<div class="extend_feEditAdvanced_editButton"><a onclick="movePage();return false;" title="'+resources.movePageActionTitle+'"';
    tmpBtn1 += '><span class="extend_feEditAdvanced-buttonText">'+resources.movePageActionValue+'</span></a></div>';
       
    var tmpBtn2 = '<div class="extend_feEditAdvanced_editButton"><a onclick="deletePage();return false;" title="'+resources.deletePageActionTitle+'"';
    tmpBtn2 += '><span class="extend_feEditAdvanced-buttonText">'+resources.deletePageActionValue+'</span></a></div>';
    
    var tmpBtn3 = '<div class="extend_feEditAdvanced_editButton"><a class="" onclick="fileManager();return false;" title="'+resources.fileManagerTitle+'"';
    tmpBtn3 += '><span class="extend_feEditAdvanced-buttonText">'+resources.fileManagerValue+'</span></a></div>';
    
    var tmpBtn4 = '<div class="extend_feEditAdvanced_editButton"><a class="" onclick="addRightColumn();return false;" title="'+resources.addColumnTitle+'"';
    tmpBtn4 += '><span class="extend_feEditAdvanced-buttonText">'+resources.addColumnValue+'</span></a></div>';
    
    var displayNews = 'none';
    if($('.tt_news_container .feEditAdvanced-allWrapper').length) {
        displayNews = 'block';
    }
    var tmpBtn5 = '<div class="extend_feEditAdvanced_editButton"><a style="display:'+displayNews+'" class="" onclick="createNews();return false;" title="'+resources.newNewsRecordActionTitle+'"';
    tmpBtn5 += '><span class="Text">'+resources.newNewsRecordActionValue+'</span></a></div>';
 
    var userMenu = '<div class="user-menu-outer-wrapper"><a class="user-menu-wrapper" title="Change user settings or logout" onclick="toggleItem(\'.userMenuDropdown\');">';
    userMenu += '<span class="user_menu">' + beuser + '</span></a><div class="userMenuDropdown">User Settings Logout</div></div>';
    
    
    var backend = '<div class="extend_feEditAdvanced_editButton backend"><a class="" title="Go to Typo3 backend" href="/typo3/">';
    backend += '<span class="extend_feEditAdvanced-buttonText">Typo3 Backend</span></a></div>';

    var help = '<div class="extend_feEditAdvanced_editButton"><a class="" title="Help" onclick="loadHelp();return false;">';
    help += '<span class="extend_feEditAdvanced-buttonText">Help</span></a></div>';*/
    
    /*$('.feEditAdvanced-menuToolbar .feEditAdvanced-editPanelDiv .newRecordAction').after(tmpBtn1);
    $('.feEditAdvanced-menuToolbar .feEditAdvanced-editPanelDiv .movePageAction').after(tmpBtn2);
    $('.feEditAdvanced-menuToolbar .feEditAdvanced-editPanelDiv .deletePageAction').after(tmpBtn5);
    $('.feEditAdvanced-menuToolbar .feEditAdvanced-editPanelDiv .newNewsAction').after(tmpBtn3);
    $('.feEditAdvanced-menuToolbar .feEditAdvanced-editPanelDiv .fileManager').after(tmpBtn4);
    
    $('.feEditAdvanced-menuToolbar .feEditAdvanced-editPanelDiv').append(user);
    $('.feEditAdvanced-menuToolbar .feEditAdvanced-editPanelDiv').append(backend);
    $('.feEditAdvanced-menuToolbar .feEditAdvanced-editPanelDiv').append(help);*/
    //
    /*$('.feEditAdvanced-leftMenu').append(tmpBtn1);
    $('.feEditAdvanced-leftMenu').append(tmpBtn2);
    $('.feEditAdvanced-leftMenu').append(tmpBtn5);
    $('.feEditAdvanced-leftMenu').append(tmpBtn3);
    $('.feEditAdvanced-leftMenu').append(tmpBtn4);
    
    $('#feEditAdvanced-contextToolbar').prepend(userMenu);

    $('#feEditAdvanced-showHiddenContent').after(backend);
    $('.extend_feEditAdvanced_editButton .backend').after(help);*/
    //console.log(getCookie('extend_feeditadvanced_copycutitem'));
});
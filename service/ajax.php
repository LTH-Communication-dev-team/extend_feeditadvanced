<?php
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');
	
define('TYPO3_MODE','FE');

require_once(PATH_tslib.'class.tslib_fe.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
require_once(PATH_t3lib.'class.t3lib_cs.php');
require_once(PATH_t3lib.'class.t3lib_userauth.php');
require_once(PATH_tslib.'class.tslib_feuserauth.php');
require_once(PATH_tslib.'class.tslib_content.php');
require_ONCE(PATH_tslib.'index_ts.php');

//$TSFEclassName = t3lib_div::makeInstance('tslib_fe');
//$id = isset($HTTP_GET_VARS['id'])?$HTTP_GET_VARS['id']:0;
//unction __construct($TYPO3_CONF_VARS, $id, $type, $no_cache='', $cHash='', $jumpurl='',$MP='',$RDCT='')	{
$pid = t3lib_div::_GP('pid');

//$GLOBALS['TSFE'] = new $TSFEclassName($TYPO3_CONF_VARS, $id, '0', 1, '','','','');
$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $TYPO3_CONF_VARS, $id, 0, true);
//$GLOBALS['TSFE']->connectToMySQL();
$GLOBALS['TSFE']->initFEuser();
$GLOBALS['TSFE']->fetch_the_id();
$GLOBALS['TSFE']->getPageAndRootline();
$GLOBALS['TSFE']->initTemplate();
$GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
$GLOBALS['TSFE']->forceTemplateParsing = 1;
//$GLOBALS['TSFE']->getConfigArray();
$TSFE->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
$TSFE->tmpl = t3lib_div::makeInstance('t3lib_tstemplate');
$TSFE->tmpl->init();

        // fetch rootline and extract ts setup:
$TSFE->rootLine = $TSFE->sys_page->getRootLine(intval($pid));
$TSFE->getConfigArray();

$feUserObj = tslib_eidtools::initFeUser();
tslib_eidtools::connectDB();
$lang = t3lib_div::_GP('lang');
tslib_eidtools::initLanguage($lang);

$TSFE->includeTCA();
// Get the page
$TSFE->fetch_the_id();
$TSFE->getPageAndRootline();
$TSFE->initTemplate();
$TSFE->tmpl->getFileName_backPath = PATH_site;
$TSFE->forceTemplateParsing = true;
$TSFE->getConfigArray();
              
$template = t3lib_div::makeInstance('t3lib_tsparser_ext'); // Defined global here!
$template->tt_track = 0;
$template->init();
$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
$rootLine = $sys_page->getRootLine($pid);
$template->runThroughTemplates($rootLine); // This generates the constants/config + hierarchy info for the template.
$template->generateConfig();

        // Save the setup
$setup = $template->setup;
$cmd = t3lib_div::_GP('cmd');
$table = t3lib_div::_GP('table');
$uid = t3lib_div::_GP('uid');
$sid = t3lib_div::_GP('sid');
$pageId = t3lib_div::_GP('pid');
$parentUid = t3lib_div::_GP('parentUid'); 
$templateclass = t3lib_div::_GP('templateclass');
$tmpContent = t3lib_div::_GP('tmpContent');

$content = array();
switch($cmd) {
    case "copyContentElement":
	$content = copyContentElement($cmd,$table,$uid,$pageId,$parentUid);
	break;
    case "cutContentElement":
	$content = cutContentElement($cmd,$table,$uid,$pageId,$parentUid);
	break;
    case "deletePage":
	$content = deletePage($cmd,$table,$uid);
	break;
    case "hidePage":
	$content = hideShowPage($cmd,$table,$pageId,1);
	break;
    case "showPage":
	$content = hideShowPage($cmd,$table,$pageId,0);
	break;
    case "hidePageInMenu":
	$content = hideShowPage($cmd,$table,$pageId,1);
	break;
    case "showPageInMenu":
	$content = hideShowPage($cmd,$table,$pageId,0);
	break;    
    case "getPidForNewArticles":
        $content = getPidForNewArticles($pageId);
        break;
    case "loadCategorySelector":
        $content = loadCategorySelector($uid);
        break;
    case "changeCategory":
        $content = changeCategory($uid,$parentUid);
        break;
    case "logout":
        $content = logout($table);
        break;
    case "tmpContent":
        $content = tmpContent($tmpContent);
	break;
}

echo json_encode($content);

function tmpContent($tmpContent)
{
    if ($_COOKIE['be_typo_user']) {
        require_once (PATH_t3lib.'class.t3lib_befunc.php');
        require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
        require_once (PATH_t3lib.'class.t3lib_beuserauth.php');
        require_once (PATH_t3lib.'class.t3lib_tsfebeuserauth.php');

        // the value this->formfield_status is set to empty in order to disable login-attempts to the backend account through this script
        // @todo 	Comment says its set to empty, but where does that happen?

        $GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
        $GLOBALS['BE_USER']->start();
        $GLOBALS['BE_USER']->unpack_uc('');
        $beuserid = $GLOBALS['BE_USER']->user['uid'];
	
	$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_feeditadvanced_tmpcontent', 'cruser_id='.intval($beuserid));
	
	$insertArray = array('cruser_id' => $beuserid, 'crdate' => time(), tstamp => time(), 'tmpcontent' => $tmpContent);
	$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_feeditadvanced_tmpcontent', $insertArray) or die("132; ".mysql_error());
    }
}

function cutContentElement($cmd,$table,$uid,$pid,$parentUid)
{
    $colpos = 0;
    if($parentUid) {
        $parentUidArray = explode(':',$parentUid);
        $parentUid = $parentUidArray[1];
        // Get sorting values
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('colpos', $table, 'uid='.intval($parentUid), '', '', '') or die('72; '.mysql_error());
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $colpos = $row['colpos'];
    } 
    
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    $tce = t3lib_div::makeInstance('t3lib_TCEmain');
    $tce->stripslashes_values = 0;
    $sortRes = $tce->getSortNumber($table,$uid,'-'.$parentUid);
    
    if(is_array($sortRes)) {
        $newSorting = $sortRes['sortNumber'];
    } else {
        $newSorting = $sortRes;
    }

    $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.intval($uid), array('pid' => $pid, 'sorting' => $newSorting, 'colpos' => $colpos)) or die("88; ".mysql_error());
    
    $returnArray = array();
    $returnArray['colpos'] = $colpos;
    $returnArray['content'] = renderContentElement($table, $uid);
    
    return $returnArray;
}

function copyContentElement($cmd,$table,$uid,$pid,$parentUid)
{
                        
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, 'uid='.intval($uid), '', '', '') or die('139; '.mysql_error());
    $original_record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    
    if($parentUid) {
        $parentUidArray = explode(':',$parentUid);
        $parentUid = '-'.$parentUidArray[1];
            // Get sorting values
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('colpos', $table, 'uid='.intval($parentUid), '', '', '') or die('104; '.mysql_error());
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $colpos = $row['colpos'];
    } else {
        $parentUid = 0;
        $colpos = 0;
    }
    
    $GLOBALS['TYPO3_DB']->sql_free_result($res);
    
    $tce = t3lib_div::makeInstance('t3lib_TCEmain');
    $tce->stripslashes_values = 0;
    $sortRes = $tce->getSortNumber($table,0,$parentUid);
    //function       getSortNumber($table, $uid, $pid) {

    if(is_array($sortRes)) {
        $newSorting = $sortRes['sortNumber'];
    } else {
        $newSorting = $sortRes;
    }

        // insert the new record and get the new auto_increment id
    $insertArray = array ('uid' => null);
    $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertArray) or die("126; ".mysql_error());
    $newId = mysql_insert_id();
    
    // generate the query to update the new record with the previous values
    foreach ($original_record as $key => $value) {
        if ($key != 'uid' and $key != 'pid' and $key != 'sorting' and $key != 'colpos') {
                //$query .= '`'.$key.'` = "'.str_replace('"','\"',$value).'", ';
            $updateArray[$key] = $value;
        } else if($key == 'pid') {
            $updateArray[$key] = $pid;
        } else if($key == 'sorting') {
            $updateArray[$key] = $newSorting;
        } else if($key == 'colpos') {
            $updateArray[$key] = $colpos;
        }
    }

    if(is_array($updateArray)) {
        $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.intval($newId), $updateArray) or die("84; ".mysql_error());
    }
    

    $returnArray = array();
    $returnArray['newId'] = $newId;
    $returnArray['colpos'] = $colpos;
    $returnArray['content'] = renderContentElement($table, $newId);
    
    return $returnArray;
}

function renderContentElement($table, $uid)
{
    global $setup;
        
    require_once (PATH_t3lib.'class.t3lib_befunc.php');
    require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
    require_once (PATH_t3lib.'class.t3lib_beuserauth.php');
    require_once (PATH_t3lib.'class.t3lib_tsfebeuserauth.php');
    $GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
    $GLOBALS['BE_USER']->OS = TYPO3_OS;
    $GLOBALS['BE_USER']->lockIP = $GLOBALS['TYPO3_CONF_VARS']['BE']['lockIP'];
    $GLOBALS['BE_USER']->workspace = 0;
    $GLOBALS['BE_USER']->start();
    $beuserid = $GLOBALS['BE_USER']->user['uid'];

    $GLOBALS['TSFE']->newCObj();
    if(intval($uid)) {
            $contentElementRow = getRow($table, $uid);
    } else {
            $contentElementRow = array();
            $contentElementRow['uid'] = $uid;
    }

    $cObj = t3lib_div::makeInstance('tslib_cObj');
    //$cObj->start($contentElementRow, 'tt_content');
    //$conf = array('allow' => 'edit, new, delete, hide', 'cut', 'copy');
    $conf = array('allow' => 'move,new,edit,hide,unhide,delete,cut,copy', 'line' => 5, 'label' => '%s', 'onlyCurrentPid' => 1, 'previewBorder' => 4, 'edit.' => Array ( 'displayRecord' => 1 ) );
    
            // @todo	Hack to render editPanel for records other than tt_content.
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tmpcontent', 'tx_feeditadvanced_tmpcontent', 'cruser_id='.intval($beuserid), '', '', '') or die('261; '.mysql_error());
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    $cObjOutput = $row['tmpcontent'];
    $GLOBALS['TYPO3_DB']->sql_free_result($res);
    if(($table == 'tt_content') && ($uid != 'NEW')) {
        //$cObjOutput = $cObj->cObjGetSingle($setup['tt_content'], $setup['tt_content.']);
	/*$conf['tables'] = 'tt_content';
	$conf['source'] = 602;
	$conf['dontCheckPid'] = 1;
	return $cObj->cObjGetSingle('RECORDS', $conf);*/
    } else {
            
            if ($uid == 'NEW') {
                    $conf['newRecordFromTable'] = $table;
            }
            if (isset($GLOBALS['BE_USER']->frontendEdit->TSFE_EDIT['newRecordInPid'])) {
                    $conf['newRecordInPid'] = $GLOBALS['BE_USER']->frontendEdit->TSFE_EDIT['newRecordInPid'];
            }
            //$cObjOutput = $cObj->editPanel('', $conf, $table . ':' . $uid, $contentElementRow);
    }
    require_once(t3lib_extMgm::extPath('feeditadvanced') . 'view/class.tx_feeditadvanced_editpanel.php');
    $panelObj = new tx_feeditadvanced_editpanel;
    $cObjOutput = $panelObj->editPanel($cObjOutput,$conf,"$table:$uid",$contentElementRow,$table,array('move'=>0,'new'=>1,'edit'=>2,'hide'=>3,'unhide'=>4,'delete'=>5,'cut'=>6,'copy'=>7));
    
/*
            // Set a simplified template file for use in the AJAX response.  No title, meta tags, etc.
            // @todo Should we account for footer data too?
    $pageRenderer = $GLOBALS['TSFE']->getPageRenderer();
    $pageRenderer->setTemplateFile(t3lib_extMgm::extPath('feeditadvanced') . 'res/template/content_element.tmpl');
    //$pageRenderer->setCharSet($GLOBALS['TSFE']->metaCharset);
    $pageRenderer->enableConcatenateFiles();

            // Set the BACK_PATH for the pageRenderer concatenation.
            // FIXME should be removed when the sprite manager, RTE, and pageRenderer are on the same path about concatenation.
    $GLOBALS['BACK_PATH'] = TYPO3_mainDir;

    //$header = $this->renderHeaderData();
    $content = $cObjOutput;

    if ($GLOBALS['TSFE']->isINTincScript()) {
            $GLOBALS['TSFE']->content = $content;
            $GLOBALS['TSFE']->INTincScript();
            $content = $GLOBALS['TSFE']->content;
    }

    //$this->ajaxObj->addContent('header', $header);
    //$this->ajaxObj->addContent('content', $content);
    */
    return $cObjOutput;
}

/**
* Gets the database row for a specific content element.
*
* @param	integer		UID of the content element.
* @return	array
*/
function getRow($table, $uid) {
       $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, 'uid=' . $uid);
       $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
       $GLOBALS['TYPO3_DB']->sql_free_result($res);

       return $row;
}

function loadCategorySelector($uid)
{
    $uidArray=explode(':',$uid);
    $tt_newsid = $uid;
    $tt_newsUid = $uidArray[1];
    $tt_news_categorymounts = null;
    $content = null;
    if ($_COOKIE['be_typo_user']) {
        require_once (PATH_t3lib.'class.t3lib_befunc.php');
        require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
        require_once (PATH_t3lib.'class.t3lib_beuserauth.php');
        require_once (PATH_t3lib.'class.t3lib_tsfebeuserauth.php');

        // the value this->formfield_status is set to empty in order to disable login-attempts to the backend account through this script
        // @todo 	Comment says its set to empty, but where does that happen?

        $GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
        $GLOBALS['BE_USER']->start();
        $GLOBALS['BE_USER']->unpack_uc('');
        $beuserid = $GLOBALS['BE_USER']->user['uid'];
        
        $returnArray = array();
        
        if($beuserid) {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tt_news_categorymounts', 'be_users', 'uid='.intval($beuserid), '', '', '') or die('175; '.mysql_error());
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            $tt_news_categorymounts = $row['tt_news_categorymounts'];
            $content .= "<div>";
            if($tt_news_categorymounts) {
                $content .= "<div style=\"float:left;width:225px;\">My categories that are not already selected<br />";
                $content .= "<select id=\"tt_news_categorymounts\" name=\"\" size=\"10\" style=\"width:200px;\">";
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("DISTINCT TC.uid,TC.pid,TC.hidden,TC.sorting,TC.title", "tt_news_cat_mm TCM join tt_news_cat TC on TCM.uid_foreign=TC.uid", "TC.uid IN($tt_news_categorymounts) AND TC.uid NOT IN(SELECT uid_foreign FROM tt_news_cat_mm WHERE uid_local=".intval($tt_newsUid).")", "", "TC.title", "") or die('179; '.mysql_error());
                while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                    $uid = $row['uid'];
                    $pid = $row['pid'];
                    $title = $row['title'];
                    $content .= "<option value=\"$uid\">$title</option>";
                }
                $content .= "</select></div>";
            }
            
            $content .= "<div style=\"float:left;padding-top:100px;height:200px;\"><input type=\"Button\" value=\">>\" onclick=\"parent.moveItemBetweenListboxes('#tt_news_categorymounts','#uid_foreign','$tt_newsid');return false;\" />";
            $content .= "<br /><input type=\"Button\" value=\"<<\" onclick=\"parent.moveItemBetweenListboxes('#uid_foreign','#tt_news_categorymounts','');return false;\" /></div>";
            
            if($tt_newsid) {
                $content .= "<div style=\"float:left;width:225px;\">The categories of the news-item ($tt_newsid)</br />";
                $content .= "<select id=\"uid_foreign\" name=\"\" size=\"10\" style=\"width:200px;\">";
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("TCM.uid_foreign,TCM.sorting,TC.title", "tt_news_cat_mm TCM join tt_news_cat TC on TCM.uid_foreign=TC.uid", "TCM.uid_local=".intval($tt_newsUid)." AND TCM.uid_foreign IN($tt_news_categorymounts)", "", "TCM.sorting", "") or die("175; ".mysql_error());
                while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
                    $uid_foreign = $row['uid_foreign'];
                    $title = $row['title'];
                    $content .= "<option value=\"$tt_newsUid:$uid_foreign\">$title</option>";
                }
                $content .= "</select></div>";
            }
            $content .= "</div>";
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        echo $content;
    }
}

function changeCategory($uid,$pid)
{
    $returnArray = array();
    
    if(stristr($uid,':')) {
        $uidArray = explode(':',$uid);
        $uid_local = $uidArray[0];
        $uid_foreign = $uidArray[1];
        //Remove from tt_news_cat_mm
        $GLOBALS['TYPO3_DB']->exec_DELETEquery('tt_news_cat_mm', 'uid_local='.intval($uid_local).' AND uid_foreign='.intval($uid_foreign));
      //  echo 'del?';
    } else {
        //Add to tt_news_cat_mm
        $pidArray = explode(':',$pid);
        $uid_local = $pidArray[1];
        $uid_foreign = $uid;
        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news_cat_mm', array('uid_local' => $uid_local, 'uid_foreign' => $uid_foreign));
//        echo 'insert?'.$uid.stristr($uid,':');
    }
    
    $returnArray['uid'] = uid;
    $returnArray['pid'] = $pid;
    return $returnArray;
}

function getPidForNewArticles($pageId)
{
    if ($_COOKIE['be_typo_user']) {
        /*require_once (PATH_t3lib.'class.t3lib_befunc.php');
        require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
        require_once (PATH_t3lib.'class.t3lib_beuserauth.php');
        require_once (PATH_t3lib.'class.t3lib_tsfebeuserauth.php');

        // the value this->formfield_status is set to empty in order to disable login-attempts to the backend account through this script
        // @todo 	Comment says its set to empty, but where does that happen?

        $GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
        $GLOBALS['BE_USER']->start();
        $GLOBALS['BE_USER']->unpack_uc('');
        $beuserid = $GLOBALS['BE_USER']->user['uid'];*/
        
        //Get tt_content from pageid
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pi_flexform', 'tt_content', 'deleted=0 AND list_type=9 AND pid='.intval($pageId), '', '', '1') or die('68; '.mysql_error());
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $pi_flexform = $row['pi_flexform'];
        if($pi_flexform) {
            $xml = simplexml_load_string($pi_flexform);
            //$PIDitemDisplay = $xml->data->sheet[3]->language->field[2]->value;
            $test = $xml->data->sheet[3]->language;
            foreach ($test->field as $n) {
                foreach($n->attributes() as $name => $val) {
                    if ($val == 'pages') {
                        $pages = $n->value;
                    }
                }
            }
            if($pages) {
                $pagesArray = explode("\n", $pages);
                $firstpage = $pagesArray[0];
                
                //Get tt_news from folder above
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_news', 'deleted=0 AND pid='.intval($firstpage), '', '', '1') or die('86; '.mysql_error());
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                if($row) {
                    $uid = $row['uid'];
                } else {
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news', array('pid' => $firstpage, 'hidden' => 1, 'editlock' => 1, 'title' => 'Needed for the system, do not edit or delete.'));
                    $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
                }
            }
        }
    }
        
        /*Get options from user
        if($beuserid) {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('TSconfig', 'be_users', 'uid='.intval($beuserid), '', '', '') or die('48; '.mysql_error());
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            $TSconfig = $row['TSconfig'];
            
            if($TSconfig) {
                $TSconfigArray = explode("\n",$TSconfig);
                foreach($TSconfigArray as $key => $value) {
                    if(stristr($value,'mod.extendfeadv.pidForNewArticles')) {
                        $value = str_replace(' ','',$value);
                        $valueArray = explode('=',$value);
                        $pidForNewArticles = $valueArray[1];
                    }
                }
                if($pidForNewArticles) {
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_news', 'pid='.intval($pidForNewArticles), '', '', '0,1') or die('62; '.mysql_error());
                    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                    if($row) {
                        $uid = $row['uid'];
                    } else {
                        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news', array('pid' => $pidForNewArticles, 'hidden' => 1, 'editlock' => 1, 'title' => 'Needed for the system, do not edit or delete.'));
                        $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
                    }
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
    } else {
        $content = 'Aja baja!';
    }*/
        
    $returnArray = array();
    $returnArray['pidForNewArticles'] = $uid;
    
    return $returnArray;
    
}

function deletePage($cmd,$table,$uid)
{
    if ($_COOKIE['be_typo_user']) {
        require_once (PATH_t3lib.'class.t3lib_befunc.php');
        require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
        require_once (PATH_t3lib.'class.t3lib_beuserauth.php');
        require_once (PATH_t3lib.'class.t3lib_tsfebeuserauth.php');

        // the value this->formfield_status is set to empty in order to disable login-attempts to the backend account through this script
        // @todo 	Comment says its set to empty, but where does that happen?

        $GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
        $GLOBALS['BE_USER']->start();
        $GLOBALS['BE_USER']->unpack_uc('');
        $beuserid = $GLOBALS['BE_USER']->user['uid'];
        
        $returnArray = array();
        
        if($beuserid) {
            //if($GLOBALS['BE_USER']->isInWebMount($uid) or $GLOBALS['BE_USER']->user['admin']) {
                // get the pid of the current page   
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("pid", $table, "uid=".intval($uid). " AND pid != 0") or die('355; '.mysql_error());
                $row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res);
                $pid = $row['pid'];
                
                $GLOBALS['TYPO3_DB']->sql_free_result($res);

                if($pid) {
                            //Set deleted to 1
                    $values = array ('deleted' => 1, 'tstamp' => time());
                    $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.intval($uid), $values) or die("363; ".mysql_error());

                    
                    $returnArray['pid'] = $pid;
                    $returnArray['msg'] = $pid.$table.intval($uid);
                    
                } else {
                    $returnArray['msg'] = 'Root?'.$table.$uid.$pid;
                }
            /*} else {
                $returnArray['msg'] = 'No access.';
            }*/
        } else {
            $returnArray['msg'] = 'No user logged in.';
        }
    } else {
        $returnArray['msg'] = 'No user logged in.';
    }
    return $returnArray;
        
}

function hideShowPage($cmd,$table,$pageId,$type)
{
    if ($_COOKIE['be_typo_user']) {
        require_once (PATH_t3lib.'class.t3lib_befunc.php');
        require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
        require_once (PATH_t3lib.'class.t3lib_beuserauth.php');
        require_once (PATH_t3lib.'class.t3lib_tsfebeuserauth.php');

        // the value this->formfield_status is set to empty in order to disable login-attempts to the backend account through this script
        // @todo 	Comment says its set to empty, but where does that happen?

        $GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
        $GLOBALS['BE_USER']->start();
        $GLOBALS['BE_USER']->unpack_uc('');
        $beuserid = $GLOBALS['BE_USER']->user['uid'];
        
        $returnArray = array();
        
        if($beuserid) {
            if($GLOBALS['BE_USER']->isInWebMount($pageId)) {
                            //Set hidden to 0 or 1
                
                if($cmd=='hidePageInMenu' or $cmd == 'showPageInMenu') {
                    $values = array ('nav_hide' => $type, 'tstamp' => time());
                } else if($cmd=='hidePage' or $cmd =='showPage') {
                    $values = array ('hidden' => $type, 'tstamp' => time());
                }   
                $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.intval($pageId), $values) or die("289; ".mysql_error());
                    
                $returnArray['pid'] = $pid;
                $returnArray['msg'] = $pid.$table.intval($uid);
            } else {
                $returnArray['msg'] = 'No access.';
            }
        } else {
            $returnArray['msg'] = 'No user logged in.';
        }
    } else {
        $returnArray['msg'] = 'No user logged in.';
    }
    
    return $returnArray;
}

function logout($url)
{
    if ($_COOKIE['be_typo_user'] and $_COOKIE['extend_feeditadvanced_usersettings']) {
        require_once (PATH_t3lib.'class.t3lib_befunc.php');
        require_once (PATH_t3lib.'class.t3lib_userauthgroup.php');
        require_once (PATH_t3lib.'class.t3lib_beuserauth.php');
        require_once (PATH_t3lib.'class.t3lib_tsfebeuserauth.php');

        // the value this->formfield_status is set to empty in order to disable login-attempts to the backend account through this script
        // @todo 	Comment says its set to empty, but where does that happen?

        $GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
        $GLOBALS['BE_USER']->start();
        $GLOBALS['BE_USER']->unpack_uc('');
        $beuserid = $GLOBALS['BE_USER']->user['uid'];
        
        $returnArray = array();
        
        $values = array ('tx_feeditadvanced_usersettings' => $_COOKIE['extend_feeditadvanced_usersettings'], 'tstamp' => time());
        
        $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('be_users', 'uid='.intval($beuserid), $values) or die("289; ".mysql_error());
        $returnArray['url'] = $url;
        return $returnArray;
    }
}
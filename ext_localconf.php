<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TYPO3_CONF_VARS['SC_OPTIONS']['EXT:feeditadvanced/view/class.tx_feeditadvanced_adminpanel.php']['addIncludes'][] = 'EXT:extend_feeditadvanced/res/class.tx_extend_feeditadvanced_js.php:tx_extend_feeditadvanced_js';

// Add AJAX support
$TYPO3_CONF_VARS['FE']['eID_include']['extend_feeditadvanced'] = 'EXT:extend_feeditadvanced/service/ajax.php';

t3lib_extMgm::addUserTSConfig('
	FeEdit.skin.cssFile = typo3conf/ext/extend_feeditadvanced/res/css/fe_edit_advanced.css
        FeEdit.skin.cssFormFile = typo3conf/ext/extend_feeditadvanced/res/css/fe_formsOnPage.css
        FeEdit.skin.templateFile = typo3conf/ext/extend_feeditadvanced/res/template/feedit.tmpl
        FeEdit.skin.imagePath = typo3conf/ext/extend_feeditadvanced/res/icons/
        FeEdit.editWindow.height = 9000
        FeEdit.editWindow.width = 9000
');

t3lib_extMgm::addTypoScript('extend_feeditadvanced', 'setup', '
#############################################
## TypoScript added by extension "Extend FE Editing Advanced"
#############################################
');

/*t3lib_extMgm::addUserTSConfig('
        mod.extendfeadv.pidForNewArticles =
');*/

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extend_feeditadvanced/view/class.tx_extend_feeditadvanced_editpanel.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extend_feeditadvanced/view/class.tx_extend_feeditadvanced_editpanel.php']);
}
?>
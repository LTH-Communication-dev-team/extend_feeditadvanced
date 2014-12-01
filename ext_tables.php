<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('pages');

if (t3lib_extMgm::isLoaded('mailformplus_admin')) {
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['tx_mailformplusadmin_pi2_wizicon'] = t3lib_extMgm::extPath('mailformplus_admin') . 'pi2/class.tx_mailformplusadmin_pi2_wizicon.php';
}
/*t3lib_extMgm::addPageTSConfig('
	mod.extendfeadv {
	list {
		pidForNewArticles =
	}
}
');*/

?>
<?php
class tx_extend_feeditadvanced_js {
    
    function addIncludes() {
        $content = '<script type="text/javascript" src="typo3conf/ext/extend_feeditadvanced/res/tx_extend_feeditadvanced.js"></script>';
        $content .= '<script type="text/javascript" src="typo3conf/ext/extend_feeditadvanced/res/lang/lang_'.$GLOBALS['BE_USER']->user['lang'].'.js"></script>';
        $content .= '<input type="hidden" id="pageid" value="' . $GLOBALS["TSFE"]->id . '" />';
        $content .= '<input type="hidden" id="beuser" value="' . $GLOBALS["BE_USER"]->user["username"] . '" />';
        $content .= '<input type="hidden" id="beuser_lang" value="' . $GLOBALS["BE_USER"]->user["lang"] . '" />';
        return $content;
    }
}
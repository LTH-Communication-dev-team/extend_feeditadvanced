<?php
$lang = '';
$content = '';
$menuContent ='';
$lang = $_GET['lang'];
if($lang != 'sv') {
    $lang='en';
}
$menuArray = array();
$menuArray[0]['head']['en'] = 'Edit';
$menuArray[2]['content']['en'] = 'Edit the title and other properties of the page,edit_page.html';
$menuArray[9]['content']['en'] = 'Edit content elements,edit_content.html';
$menuArray[3]['content']['en'] = 'Move the page,move_page.html';
$menuArray[4]['content']['en'] = 'Copy the page,copy_page.html';
$menuArray[5]['content']['en'] = 'Delete the page,delete_page.html';
$menuArray[6]['content']['en'] = 'Hide or show the page,hideshow_page.html';

$menuArray[8]['head']['en'] = 'New';
$menuArray[1]['content']['en'] = 'Create a new page,add_new_page.html,create_new_page.html';
$menuArray[10]['content']['en'] = 'Create new content,create_new_content.html';
$menuArray[11]['content']['en'] = 'Cut content,cut_content.html';
$menuArray[12]['content']['en'] = 'Copy content,copy_content.html';
$menuArray[13]['content']['en'] = 'Hide or show content elements,hideshow_content.html';
$menuArray[15]['content']['en'] = 'Delete content,delete_content.html';

$menuArray[16]['head']['en'] = 'Tools';
$menuArray[19]['content']['en'] = 'File manager,add_new_page.html';
$menuArray[19]['content']['en'] = 'Formhandler manager,add_new_page.html';


$menuArray[18]['head']['en'] = 'Display';

$menuArray[20]['head']['en'] = 'Misc';
$menuArray[21]['content']['en'] = 'Change user settings,add_new_page.html';
$menuArray[22]['content']['en'] = 'Open Typo3 backend,add_new_page.html';
$menuArray[23]['content']['en'] = 'Log out,add_new_page.html';

//sv
$menuArray[0]['head']['sv'] = 'Page';
$menuArray[1]['content']['sv'] = 'Add a new page,add_new_page.html';
$menuArray[2]['content']['sv'] = 'Edit the title and other properties of the page,add_new_page.html';
$menuArray[3]['content']['sv'] = 'Move the page,add_new_page.html';
$menuArray[4]['content']['sv'] = 'Copy the page,add_new_page.html';
$menuArray[5]['content']['sv'] = 'Delete the page,add_new_page.html';
$menuArray[6]['content']['sv'] = 'Hide or show the page,add_new_page.html';
$menuArray[7]['content']['sv'] = 'Add a rightcolumn to the page,add_new_page.html';

$menuArray[8]['head']['sv'] = 'Content';
$menuArray[9]['content']['sv'] = 'Edit content,add_new_page.html';
$menuArray[10]['content']['sv'] = 'Create new content,add_new_page.html';
$menuArray[11]['content']['sv'] = 'Cut content,add_new_page.html';
$menuArray[12]['content']['sv'] = 'Copy content,add_new_page.html';
$menuArray[13]['content']['sv'] = 'Hide content elements,add_new_page.html';
$menuArray[14]['content']['sv'] = 'Hide content elements,add_new_page.html';
$menuArray[15]['content']['sv'] = 'Delete content,add_new_page.html';

$menuArray[16]['head']['sv'] = 'News';
$menuArray[17]['content']['sv'] = 'Add a news-item,add_new_page.html';


$menuArray[18]['head']['sv'] = 'Files';
$menuArray[19]['content']['sv'] = 'Open file manager,add_new_page.html';

$menuArray[20]['head']['sv'] = 'Misc';
$menuArray[21]['content']['sv'] = 'Change user settings,add_new_page.html';
$menuArray[22]['content']['sv'] = 'Open Typo3 backend,add_new_page.html';
$menuArray[23]['content']['sv'] = 'Log out,add_new_page.html';
//print_r($menuArray);
$content .= "<ul>";
foreach($menuArray as $key => $value) {
    foreach($value as $key1=>$value1) {
        $content .= "<li>";
        $valueArray = explode(',',$value1[$lang]);

        if($key1=='content') {
            $content .= "<a href=\"#\" onclick=\"loadHTML('" . $valueArray[1] . "');return false;\">" . $valueArray[0] . "</a>";
            $menuContent .= "<li><a href=\"#\" onclick=\"loadHTML('" . $valueArray[1] . "');return false;\">" . $valueArray[0] . "</a></li>";
        } else {
            $content .= "<b>".$value1[$lang]."</b>";
        }
    }
}
$content .= "<ul>";
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>Help</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="typo3conf/ext/extend_feeditadvanced/res/css/fe_edit_advanced.css" rel="stylesheet" type="text/css"/>
        <script language="javascript">
            var content = <?php echo "'" . urlencode($content) . "'"; ?>;
            
            if (typeof(jQuery) == "undefined") {
                var iframeBody = document.getElementsByTagName("body")[0];
                var jQuery = function (selector) { return parent.jQuery(selector, iframeBody); };
                var $ = jQuery;
            }

            function toggleItem(myType)
            {
                //console.log(selector);
                //$('.feEditAdvanced-pageItemsMenu').show();
                document.getElementById('helpmenu').style.display = myType;
            }

            function loadHTML(file)
            {
                var request;
                if (window.XMLHttpRequest) {
                    // IE7+, Firefox, Chrome, Opera, Safari
                    request = new XMLHttpRequest();
                } else {
                    // code for IE6, IE5
                    request = new ActiveXObject('Microsoft.XMLHTTP');
                }
                // load
                request.open('GET', 'typo3conf/ext/extend_feeditadvanced/help/'+file, false);
                request.send();
                //parseCSV(request.responseText);
                document.getElementById('helpcontent').innerHTML = request.responseText;
                toggleItem('none');
            }
            
            function startAgain()
            {
                document.getElementById('helpcontent').innerHTML = decodeURIComponent(content.replace(/\+/g, ' '));
            }
        </script>
    </head>
    <body>
	<div class="feEditAdvanced-firstRow" style="background-color: #000000;">
            <div class="feEditAdvanced-menuToolbar">
                    <ul class="extend_feeditadvanced_top_menu">
                        <li class="top_menu_item">
                            <a href="#" onclick="startAgain();"><span class="icon-white-home"></span>Home</a>

                        </li>
                        <li class="top_menu_item">
                            <a href="#" onclick="toggleItem('block');return false;"><span class="icon-white-align-justify"></span>Menu</a>
                            <div id="helpmenu" class="feEditAdvanced-pageItemsMenu top_menu_div">
                                <ul class="extend_feeditadvanced_top_menu">
                                <?php echo $menuContent; ?>
                                </ul>
                            </div>
                        </li>
                    </ul>
            </div>
        </div>
        <div id="helpcontent">
        <h1>Welcome</h1>
        <h2>What do you want to do today?</h2>
        <?php
        echo $content;
        ?>
        </div>
    </body>
</html>
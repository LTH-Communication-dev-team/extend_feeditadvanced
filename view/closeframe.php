<?php
$id=null;
$copy=null;

if(isset($_GET['id'])) $id = $_GET['id'];
if($id) {
    $idArray = explode('_',$id);
    $id = $idArray[0];
    if(isset($idArray[1])) $copy = $idArray[1];
}
$content = "<script language=\"javascript\">";
//$content .= "parent.document.getElementById('ux-lightbox').hide;";
//$content .= "parent.document.getElementById('ux-lightbox-overlay').hide;";
//$content .= "parent.document.getElementById('ux-lightbox-shim').hide;";
//$content .= "parent.NewOrEditItem('$id');";
if($copy) {
    $content .= "parent.closeIframe();parent.redirectToParentPage($id);parent.showMessageFromOuterSource('copyPage');</script>";
} else {
    $content .= "parent.closeIframe();parent.redirectToParentPage($id);parent.showMessageFromOuterSource('movePage');</script>";
}

echo $content;
?>
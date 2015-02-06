<?php
header('Content-type: text/css');
header("Cache-Control: must-revalidate");
$offset = 72000 ;
$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
header($ExpStr);
require($_SERVER['DOCUMENT_ROOT']."/wp-load.php");
$options = get_option('cts_style_options');
$tmp = @$options['cts_client_stylesheet'];
if (is_serialized($tmp)){
    $stylesheet = unserialize($tmp);
} else{
    $stylesheet = @$options['cts_client_stylesheet'];
}
echo $stylesheet;
?>

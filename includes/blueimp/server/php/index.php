<?php
/*
 * jQuery File Upload Plugin PHP Example 5.7
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(0);
ini_set('display_errors',0);
if (! function_exists('blueimp_get_perms')){
    function blueimp_get_perms($file){
        return substr(sprintf('%o', fileperms($file)), -4);
    }
}

global $cts_blog_id;
require_once($_SERVER['DOCUMENT_ROOT']."/wp-load.php");
if (is_multisite()){
    if (isset($_REQUEST['cts_blog_id'])){
        $cts_blog_id = $_REQUEST['cts_blog_id'];
    } else { 
        if (isset($_SERVER['HTTP_REFERER'])){
            $cts_query = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY );
            if (strpos($cts_query, 'cts_blog_id') !== false){
                parse_str($cts_query, $cts_arry);
                if (! empty($cts_arry['cts_blog_id'])){
                    $cts_blog_id = $cts_arry['cts_blog_id'];
                }
            }
        }
    }
    if (! empty($cts_blog_id)) switch_to_blog($cts_blog_id);
}
$uploads_arry = wp_upload_dir();
$images_upload_dir = $uploads_arry["basedir"] . '/cts_theme/images/';
$images_upload_url = $uploads_arry["baseurl"] . '/cts_theme/images/';
$thumbs_upload_dir = $uploads_arry["basedir"] . '/cts_theme/thumbnails/';
$thumbs_upload_url = $uploads_arry["baseurl"] . '/cts_theme/thumbnails/';
$uploads_dir_exists = false;
$thumbs_dir_exists = false;

if ( ! file_exists($images_upload_dir)){
    $uploads_dir_exists = mkdir($images_upload_dir, 0777, true);
    if ($uploads_dir_exists){
        chmod($images_upload_dir, 0777);
    }
    $image_perms = blueimp_get_perms($images_upload_dir);
    $image_dir_writable = (0777 == $image_perms);
} else{
    $uploads_dir_exists = true;
    $image_perms = blueimp_get_perms($images_upload_dir);
    if (0777 != $image_perms){
        $image_dir_writable = chmod($images_upload_dir, 0777);
    } else{
        $image_dir_writable = true;
    }
}

if ( ! file_exists($thumbs_upload_dir)){
    $thumbs_dir_exists = mkdir($thumbs_upload_dir, 0777, true);
    if ($thumbs_dir_exists){
        chmod($thumbs_upload_dir, 0777);
    }
    $thumb_perms = blueimp_get_perms($thumbs_upload_dir);
    $thumbs_dir_writable = (0777 == $thumb_perms);
} else{
    $thumbs_dir_exists = true;
    $thumb_perms = blueimp_get_perms($thumbs_upload_dir);
    if (0777 != $thumb_perms){
        $thumbs_dir_writable = chmod($thumbs_upload_dir, 0777);
    } else{
        $thumbs_dir_writable = true;
    }
}

/*if ($uploads_dir_exists === false){
    echo '<div id="message" class="error"><p>' . $images_upload_dir . ' can\'t be created.</p></div>';    
} elseif (($image_dir_writable === false) && (! chmod($images_upload_dir, 0777))){
    echo '<div id="message" class="error"><p>' . $images_upload_dir . ' is not writable.</p></div>';    
}

if ($thumbs_dir_exists === false){
    echo '<div id="message" class="error"><p>' . $images_upload_dir . ' can\'t be created.</p></div>';    
} elseif (($thumbs_dir_writable === false) && (! chmod($thumbs_upload_dir, 0777))){
    echo '<div id="message" class="error"><p>' . $thumbs_upload_dir . ' is not writable.</p></div>';    
}*/

$options = array(
    'upload_dir' => $images_upload_dir,
    'upload_url' => $images_upload_url,
    'thumbnails_dir' => $thumbs_upload_dir,
    'thumbnails_url' => $thumbs_upload_url,
    'thumbnail' => array(
        'upload_dir' => $thumbs_upload_dir,
        'upload_url' => $thumbs_upload_url,
        'max_width' => 80,
        'max_height' => 80
    ),
    'cts_blog_id' => $cts_blog_id
);

require('upload.class.php');
 
$upload_handler = new UploadHandler($options);

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        break;
    case 'HEAD':
    case 'GET':
        $upload_handler->get();
        break;
    case 'POST':
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            $upload_handler->delete();
        } else {
            $upload_handler->post();
        }
        break;
    case 'DELETE':
        $upload_handler->delete();
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');
}

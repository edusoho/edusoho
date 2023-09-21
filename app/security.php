<?php

/**
 * 判断请求来源是否为老版本的API 注意区分环境
 */
function isOldApiCall(string $environment)
{
    return (!(isset($_SERVER['HTTP_ACCEPT']) && 'application/vnd.edusoho.v2+json' == $_SERVER['HTTP_ACCEPT']))
        && ((0 === strpos($_SERVER['REQUEST_URI'], '/api')) || (0 === strpos($_SERVER['REQUEST_URI'], 'prod' == $environment ? '/app.php/api' : '/app_dev.php/api')));
}

function fix_gpc_magic()
{
    if (get_magic_quotes_gpc()) {
        array_walk($_GET, '_fix_gpc_magic');
        array_walk($_POST, '_fix_gpc_magic');
        array_walk($_COOKIE, '_fix_gpc_magic');
        array_walk($_REQUEST, '_fix_gpc_magic');
        array_walk($_FILES, '_fix_gpc_magic_files');
    }
}

function _fix_gpc_magic(&$item)
{
    if (is_array($item)) {
        array_walk($item, '_fix_gpc_magic');
    } else {
        $item = stripslashes($item);
    }
}

function _fix_gpc_magic_files(&$item, $key)
{
    if ('tmp_name' != $key) {
        if (is_array($item)) {
            array_walk($item, '_fix_gpc_magic_files');
        } else {
            $item = stripslashes($item);
        }
    }
}

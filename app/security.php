<?php

/**
 * 判断请求来源是否为老版本的API 注意区分环境
 */
function isOldApiCall(string $environment)
{
    return (!(isset($_SERVER['HTTP_ACCEPT']) && 'application/vnd.edusoho.v2+json' == $_SERVER['HTTP_ACCEPT']))
        && ((0 === strpos($_SERVER['REQUEST_URI'], '/api')) || (0 === strpos($_SERVER['REQUEST_URI'], $environment == 'prod' ? '/app.php/api' : '/app_dev.php/api')));
}

/**
 * 设置cookie的安全模式 对于HTTPS的请求可以获取到cookie的信息
 */
function setCookieSecure()
{
    if (!function_exists('ini_set')) {
        return;
    }

    ini_set("session.cookie_secure", true);
}


/**
 * 检测请求是否为https请求 注意nginx配置环境
 */
function isHttpsRequest()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
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

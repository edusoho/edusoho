<?php
date_default_timezone_set('Asia/Shanghai');

if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
    header("Location: start-install.php");
    exit();
}


header('Content-Type: text/html; charset=utf-8');

echo "<h1 style='font-size:18px;'>PHP版本太低</h1>";
echo "<p style='color:#ff3300;'>EduSoho要求PHP版本为<strong>5.5.0</strong>及以上，您当前的PHP版本为<strong>".PHP_VERSION."</strong>，请先升级您的PHP版本。</p>";

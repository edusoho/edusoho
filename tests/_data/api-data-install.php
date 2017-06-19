<?php

$config = array(
    'database_host' => $argv[1],
    'database_user' => 'root',
    'database_password' => '',
    'database_name' => 'edusoho_test',
);

$pdo = new PDO("mysql:host={$config['database_host']};", "{$config['database_user']}", "{$config['database_password']}");

$pdo->exec('SET NAMES utf8');

$pdo->exec("drop database if exists `{$config['database_name']}`;");

$pdo->exec("create database `{$config['database_name']}`;");

$pdo->exec("USE `{$config['database_name']}`;");

$pdo->exec('SET GLOBAL max_allowed_packet=1073741824;');

$sql = file_get_contents(__DIR__.'/api-test-data.sql');

$result = $pdo->exec($sql);

if ($result === false) {
    echo '执行sql失败'."\n";
    print_r($pdo->errorInfo());
}

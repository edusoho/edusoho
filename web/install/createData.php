<?php

if(empty($_POST['dbpw'])){
    $_POST['dbpw'] = null;
}

$pdo = new PDO("mysql:host={$_POST['dbhost']}","{$_POST['dbuser']}","{$_POST['dbpw']}");
$pdo->query("create database `{$_POST['dbname']}`;");
$pdo->query("use `{$_POST['dbname']}`;");
$sql = file_get_contents('/var/www/edusoho/app/config/edusoho.sql');
$pdo->exec($sql);

$yamlFile = '/var/www/edusoho/app/config/parameters.yml';
$parameters = "parameters:
    database_driver: pdo_mysql
    database_host: {$_POST['dbhost']}
    database_port: null
    database_name: {$_POST['dbname']}
    database_user: {$_POST['dbuser']}
    database_password: {$_POST['dbpw']}
    mailer_transport: smtp
    mailer_host: 127.0.0.1
    mailer_user: null
    mailer_password: null
    locale: en
    secret: ThisTokenIsNotSoSecretChangeIt";
file_put_contents($yamlFile,$parameters);

header("Location: /install/init/system"); 
?>
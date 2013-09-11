<?php

function generate_password($length) 
{  
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; 
    $password = '';  
    for ( $i = 0; $i < $length; $i++ ){  
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
    }  
    return $password;
}

$secret =  generate_password(24);
if(empty($_POST['dbpw'])){
    $_POST['dbpw'] = null;
}

$pdo = new PDO("mysql:host={$_POST['dbhost']}","{$_POST['dbuser']}","{$_POST['dbpw']}");
$result = $pdo->query("create database `{$_POST['dbname']}`;");

if(!$result){
    header("Location: ./dataBasepage.php?dataBaseExist=yes"); 
    exit();
}

$pdo->query("use `{$_POST['dbname']}`;");
$sql = file_get_contents('./edusoho.sql');
$pdo->exec($sql);

$yamlFile = '../../app/config/parameters.yml';
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
    locale: zh_cn
    secret: {$secret}";

file_put_contents($yamlFile,$parameters);

header("Location: /install/init/system"); 
?>
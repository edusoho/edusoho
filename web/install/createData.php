<?php

if(empty($_POST['dbpw'])){
    $_POST['dbpw'] = null;
}

$pdo = new PDO("mysql:host={$_POST['dbhost']}","{$_POST['dbuser']}","{$_POST['dbpw']}");

$pdo->query("create database `{$_POST['dbname']}`;");
$pdo->query("use `{$_POST['dbname']}`;");

$sql = file_get_contents('/var/www/edusoho/app/config/edusoho.sql');
$pdo->exec($sql);

header("Location: /createData"); 

?>
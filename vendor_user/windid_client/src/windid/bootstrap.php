<?php

if (!defined('WEKIT_VERSION')) {
	error_reporting(E_ERROR | E_PARSE);
	require_once (WINDID_BOOT . '../wekit.php');
	Wekit::init('windidclient');
	Wind::application('windidclient', Wekit::S());
	$clientConfig = include __DIR__ . '/../../../../app/config/windid_client_config.php';
	$database =  $clientConfig['database'];
	$windidConfig =  $clientConfig['conf'];	
	Wind::register(WINDID_PATH . 'service', 'SRV');
} else {
	$windidConfig = Wekit::C('windid');
	if ($windidConfig['windid'] == 'local') {
		$database = Wind::getComponent('db')->getConfig();
		$database['tableprefix'] .= 'windid_';
		$windidConfig['connect'] = 'db';
	} else {
		$database = array(
			'dsn' => 'mysql:host=' . $windidConfig['db.host'] . ';dbname=' . $windidConfig['db.name'] . ';port=' . $windidConfig['db.port'],
			'user' => $windidConfig['db.user'],
			'pwd' => $windidConfig['db.pwd'],
			'charset' => $windidConfig['db.charset'],
			'tableprefix' => $windidConfig['db.prefix']
		);
	}
	$windidConfig['charset'] = Wekit::V('charset'); 
}

Wind::register(WINDID_PATH . 'service', 'WSRV');
Wind::import('WSRV:base.WindidBaseDao');
Wind::import('WSRV:base.WindidUtility');
Wind::import('WSRV:base.WindidError');
Wind::registeComponent(array('path' => 'WIND:db.WindConnection', 'config' => $database), 'windiddb', 'singleton');

define('WINDID_CONNECT', $windidConfig['connect']);
define('WINDID_SERVER_URL', $windidConfig['serverUrl']);
define('WINDID_CLIENT_ID', $windidConfig['clientId']);
define('WINDID_CLIENT_KEY', $windidConfig['clientKey']);
define('WINDID_CLIENT_CHARSET', $windidConfig['charset']);
Wekit::createapp('windidclient', 'windid');
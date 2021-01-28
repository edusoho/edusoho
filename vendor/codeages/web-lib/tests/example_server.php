<?php

use Codeages\Weblib\Auth\Authentication;
use Codeages\Weblib\Auth\MockKeyProvider;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

$keyProvider = new MockKeyProvider();
$authentication = new Authentication($keyProvider);

$request = Request::createFromGlobals();

try {
    $key = $authentication->auth($request);
    echo json_encode(array(
        'key_id' => $key->id,
        'secret' => $key->secret,
        'status' => $key->status,
        'expired_time' => $key->expiredTime,
    ));
} catch(\Exception $e) {
    $error = array(
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
    );
    echo json_encode(array('error' => $error, 'SERVER' => $request->server->all()));
}
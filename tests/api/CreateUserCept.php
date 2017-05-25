<?php
$I = new ApiTester($scenario);
$I->wantTo('create a user via API');
$I->amHttpAuthenticated('ruanwenqin', 'kaifazhe');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->haveHttpHeader('Accept', 'application/x-www-form-urlencoded');
$I->sendPOST('/app/channels', ['name' => 'davert', 'email' => 'davert@codeception.com']);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
$I->seeResponseIsJson();
$I->seeResponseContains('{"result":"ok"}');

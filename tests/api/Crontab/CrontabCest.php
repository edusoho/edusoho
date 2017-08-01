<?php

namespace Category;

use ApiTester;

class CrontabCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function getCrontabStatus(ApiTester $I)
    {
        $I->wantTo('Get system crontab status');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->amHttpAuthenticated('测试管理员', 'testedusoho!@#');
        $I->sendGET('/api/crontab/status');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(array(
            'enabled' => 'boolean',
        ));
    }
}

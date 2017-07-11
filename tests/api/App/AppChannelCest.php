<?php

namespace App;

use ApiTester;

class AppChannelCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function search(ApiTester $I)
    {
        $I->wantTo('Search app channel via API');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->sendGET('/api/app/channels');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}

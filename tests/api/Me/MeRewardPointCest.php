<?php

namespace App;

use ApiTester;

class MeRewardPointCest
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
        $I->wantTo('Get my reward point via API');
        $I->amHttpAuthenticated('st001', 'edusoho504');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->sendGET('/api/me/reward_pint');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}

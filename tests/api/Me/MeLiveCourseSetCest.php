<?php

namespace App;

use ApiTester;

class MeLiveCourseSetCest
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
        $I->amHttpAuthenticated('st001', 'edusoho504');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->sendGET('/api/me/live_course_sets');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}

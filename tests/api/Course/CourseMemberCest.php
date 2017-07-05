<?php

namespace Category;

use ApiTester;

class CourseMemberCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function joinFreeCourse(ApiTester $I)
    {
        $I->wantTo('Join a free course');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->amHttpAuthenticated('st001', 'edusoho504');
        $I->sendPOST('/api/courses/195memberscourse');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}

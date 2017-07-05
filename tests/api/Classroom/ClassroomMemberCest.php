<?php

namespace Classroom;

use ApiTester;

class ClassroomMemberCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function joinFreeClassroom(ApiTester $I)
    {
        $I->wantTo('Get a classroom info');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->amHttpAuthenticated('st001', 'edusoho504');
        $I->sendPost('/api/classroom/1');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}

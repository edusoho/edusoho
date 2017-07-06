<?php

namespace Classroom;

use ApiTester;

class ClassroomCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function getClassroom(ApiTester $I)
    {
        $I->wantTo('Get a classroom info');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->amHttpAuthenticated('st001', 'edusoho504');
        $I->sendGET('/api/classroom/1');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}

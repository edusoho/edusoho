<?php

namespace Category;

use ApiTester;

class CourseCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function getCourse(ApiTester $I)
    {
        $I->wantTo('Get a course info');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->amHttpAuthenticated('st001', 'edusoho504');
        $I->sendGET('/api/courses/3');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }

    public function getNotExistCourseWithNnvelope(ApiTester $I)
    {
        $I->wantTo('Get a does-not-exist course');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->amHttpAuthenticated('st001', 'edusoho504');
        $I->sendGET('/api/courses/does-not-exist?envelope=true');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND); // 404
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(array(
            'status' => 'integer',
            'headers' => 'array',
            'response' => 'array',
        ));
    }
}

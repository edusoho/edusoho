<?php

namespace Category;

use ApiTester;

class CategoryCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function getCourseCategory(ApiTester $I)
    {
        $I->wantTo('Search category tree via API');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->sendGET('/api/categories/course');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }

    public function getClassroomCategory(ApiTester $I)
    {
        $I->wantTo('Search category tree via API');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->sendGET('/api/categories/classroom');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}

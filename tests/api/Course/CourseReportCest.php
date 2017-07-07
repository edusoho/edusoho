<?php

namespace Category;

use ApiTester;

class CourseReportCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function getCourse(ApiTester $I)
    {
        $I->wantTo('Get a course completion rate trend');
        $I->haveHttpHeader('Accept', 'application/vnd.edusoho.v2+json');
        $I->amHttpAuthenticated('测试管理员', 'testedusoho!@#');
        $I->sendGET('/api/courses/3/reports/completion_rate_trend');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}

<?php

namespace MarketingMallBundle\Api\Resource\MallCourse;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TimeMachine;
use Biz\Course\Service\CourseService;

class MallCourse extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['excludeStatus'] = 'draft';
        $conditions['parentId'] = 0;
        //过滤约排课
        $conditions['excludeTypes'] = ['reservation'];
        $sort = [
            'createdTime' => 'DESC'
        ];
        list($offset, $limit) = $this->preparePageCondition($conditions);
        $courses = $this->getCourseService()->searchCourses($conditions, $sort, $offset, $limit, ['id', 'courseSetId', 'title', 'price', 'cover']);
        $total = $this->getCourseService()->countWithJoinCourseSet($conditions);
        $this->getOCUtil()->multiple($courses, ['courseSetId'], 'courseSet');

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
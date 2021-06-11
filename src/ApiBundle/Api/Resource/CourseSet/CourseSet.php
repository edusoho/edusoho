<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Course\CourseSetException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;

class CourseSet extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if (empty($courseSet)) {
            throw CourseSetException::NOTFOUND_COURSESET();
        }

        $this->getOCUtil()->single($courseSet, ['creator', 'teacherIds']);

        $this->appendMaxOriginPriceAndMinOriginPrice($courseSet);

        return $courseSet;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['showable'] = 1;
        $conditions['parentId'] = 0;
        //过滤约排课
        $conditions['excludeTypes'] = ['reservation'];
        if (isset($conditions['type']) && 'all' == $conditions['type']) {
            unset($conditions['type']);
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = $this->getSort($request);

        if (array_key_exists('recommendedSeq', $sort)) {
            $courseSets = $this->getRecommendedSeq($conditions, $sort, $offset, $limit);
        } else {
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                $sort,
                $offset,
                $limit,
                [],
                true
            );
        }

        $this->getOCUtil()->multiple($courseSets, ['creator', 'teacherIds']);

        $total = $this->getCourseSetService()->countCourseSets($conditions);

        return $this->makePagingObject($courseSets, $total, $offset, $limit);
    }

    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_TEACHER")
     */
    public function add(ApiRequest $request)
    {
        if (!$this->getCourseSetService()->hasCourseSetManageRole()) {
            throw CourseSetException::FORBIDDEN_CREATE();
        }

        $data = $request->request->all();
        if (!ArrayToolkit::requireds($data, ['type', 'title', 'teachers', 'assistants'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        try {
            $this->biz['db']->beginTransaction();
            $courseSet = $this->getCourseSetService()->createCourseSet(['type' => $data['type'], 'title' => $data['title']]);
            $courseSet = $this->getCourseSetService()->updateCourseSet($courseSet['id'], $this->filterCourseSetData($data));
            if (!empty($data['images'])) {
                $this->getCourseSetService()->changeCourseSetCover($courseSet['id'], $data['images']);
            }

            $data = $this->prepareExpiryMode($data);
            $course = $this->getCourseService()->updateBaseInfo($courseSet['defaultCourseId'], $this->filterCourseData($data));

            $this->getMemberService()->setCourseTeachers($course['id'], $this->filterCourseMember($data['teachers']));
            $this->getMemberService()->setCourseAssistants($course['id'], $data['assistants']);

            $this->getCourseSetService()->publishCourseSet($courseSet['id']);

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return $this->getCourseSetService()->getCourseSet($courseSet['id']);
    }

    public function update(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $data = $request->request->all();

        if (!ArrayToolkit::requireds($data, ['title', 'teachers', 'assistants'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        try {
            $this->biz['db']->beginTransaction();
            $courseSet = $this->getCourseSetService()->updateCourseSet($courseSet['id'], $this->filterCourseSetData($data));
            if (!empty($data['images'])) {
                $this->getCourseSetService()->changeCourseSetCover($courseSet['id'], $data['images']);
            }

            $data = $this->prepareExpiryMode($data);
            $course = $this->getCourseService()->updateBaseInfo($courseSet['defaultCourseId'], $this->filterCourseData($data));

            $this->getMemberService()->setCourseTeachers($course['id'], $this->filterCourseMember($data['teachers']));
            $this->getMemberService()->setCourseAssistants($course['id'], $data['assistants']);
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return $this->getCourseSetService()->getCourseSet($courseSet['id']);
    }

    private function filterCourseMember($userIds)
    {
        $members = [];
        foreach ($userIds as $userId) {
            $members[] = ['id' => $userId, 'isVisible' => 1];
        }

        return $members;
    }

    private function filterCourseSetData($data)
    {
        $data = array_merge($data, [
            'serializeMode' => 'none',
            'categoryId' => 0,
        ]);

        return ArrayToolkit::parts($data, [
            'serializeMode',
            'categoryId',
            'subtitle',
            'summary',
            'title',
        ]);
    }

    private function filterCourseData($data)
    {
        return ArrayToolkit::parts($data, [
            'learnMode',
            'enableFinish',
            'originPrice',
            'buyable',
            'enableBuyExpiryTime',
            'buyExpiryTime',
            'expiryStartDate',
            'expiryEndDate',
            'expiryMode',
            'expiryDays',
            'deadlineType',
            'maxStudentNum',
        ]);
    }

    private function prepareExpiryMode($data)
    {
        if (empty($data['expiryMode']) || 'days' != $data['expiryMode']) {
            unset($data['deadlineType']);
        }
        if (!empty($data['deadlineType'])) {
            if ('end_date' == $data['deadlineType']) {
                $data['expiryMode'] = 'end_date';
                if (isset($data['deadline'])) {
                    $data['expiryEndDate'] = $data['deadline'];
                }

                return $data;
            } else {
                $data['expiryMode'] = 'days';

                return $data;
            }
        }

        return $data;
    }

    private function getRecommendedSeq($conditions, $sort, $offset, $limit)
    {
        $conditions['recommended'] = 1;
        $recommendCount = $this->getCourseSetService()->countCourseSets($conditions);
        $recommendAvailable = $recommendCount - $offset;
        $courseSets = [];

        if ($recommendAvailable >= $limit) {
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                $sort,
                $offset,
                $limit
            );
        }

        if ($recommendAvailable <= 0) {
            $conditions['recommended'] = 0;
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                ['createdTime' => 'DESC'],
                abs($recommendAvailable),
                $limit
            );
        }

        if ($recommendAvailable > 0 && $recommendAvailable < $limit) {
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                $sort,
                $offset,
                $recommendAvailable
            );
            $conditions['recommended'] = 0;
            $coursesTemp = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                ['createdTime' => 'DESC'],
                0,
                $limit - $recommendAvailable
            );
            $courseSets = array_merge($courseSets, $coursesTemp);
        }

        return $courseSets;
    }

    private function appendMaxOriginPriceAndMinOriginPrice(&$courseSet)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);

        $maxOriginPrice = 0;
        $minOriginPrice = 0;
        foreach ($courses as $course) {
            if ('published' != $course['status']) {
                continue;
            }

            if ($course['originPrice'] > $maxOriginPrice) {
                $maxOriginPrice = $course['originPrice'];
            }

            if (!$minOriginPrice) {
                $minOriginPrice = $course['originPrice'];
            }

            if ($course['originPrice'] < $minOriginPrice) {
                $minOriginPrice = $course['originPrice'];
            }
        }

        $courseSet['maxOriginPrice'] = $maxOriginPrice;
        $courseSet['minOriginPrice'] = $minOriginPrice;
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    private function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}

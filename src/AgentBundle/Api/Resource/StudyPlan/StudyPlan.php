<?php

namespace AgentBundle\Api\Resource\StudyPlan;

use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\MemberException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;

class StudyPlan extends AbstractResource
{
    public function add(ApiRequest $request) {
        $params = $request->request->all();
        file_put_contents("/tmp/jc123", '1111111', 8);
        $errors = [];

        if (!ArrayToolkit::requireds($params, ['startDate', 'endDate', 'weekDays', 'courseId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        foreach ($params['weekDays'] as $index => $day) {
            $dayInt = filter_var($day, FILTER_VALIDATE_INT);
            if ($dayInt === false) {
                $errors['weekDays'][$index] = "Must be an integer.";
            } elseif ($dayInt < 1 || $dayInt > 7) {
                $errors['weekDays'][$index] = "Must be between 1 and 7.";
            }
        }

        // 验证courseId
        if (empty($params['courseId'])) {
            $errors['courseId'] = 'Course ID is required.';
        }
        $course = $this->getCourseService()->getCourse($params['courseId']);
        if (empty($course) || $course['status'] !== 'published' || $course['canLearn'] != 1) {
            throw CourseException::FORBIDDEN_LEARN_COURSE();
        }
        $courseMember = $this->getCourseMemberService()->getCourseMember($course['id'], $this->getCurrentUser()->getId());
        if (empty($courseMember)) {
            throw MemberException::FORBIDDEN_NOT_MEMBER();
        }

        // 调用服务方法
        $startTimestamp = strtotime($params['startDate']);
        $endTimestamp = strtotime($params['endDate']);
        $this->getStudyPlanService()->generate(
            $startTimestamp,
            $endTimestamp,
            $params['weekDays'],
            $params['courseId']
        );
    }

    /**
     * @return StudyPlanService
     */
    private function getStudyPlanService()
    {
        return $this->service('StudyPlan:StudyPlanService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}

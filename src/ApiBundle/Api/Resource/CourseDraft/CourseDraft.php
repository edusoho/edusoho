<?php

namespace ApiBundle\Api\Resource\CourseDraft;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseDraftService;
use Biz\Course\Service\CourseService;

class CourseDraft extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @param int        $draftId
     *
     * @return mixed
     * @Access(roles="ROLE_USER")
     */
    public function get(ApiRequest $request, $draftId)
    {
        if (!empty($draftId)) {
            return $this->getCourseDraftService()->getCourseDraft($draftId);
        }
        $params = $request->query->all();
        if (!ArrayToolkit::requireds($params, array('courseId', 'activityId'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->tryManageCourse($params['courseId']);
        $draft = $this->getCourseDraftService()->getCourseDraftByCourseIdAndActivityIdAndUserId($course['id'], $params['activityId'], $user['id']);

        return empty($draft) ? null : $draft;
    }

    /**
     * @param ApiRequest $request
     * @Access(roles="ROLE_USER")
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, array('courseId', 'activityId', 'content'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->tryManageCourse($params['courseId']);
        $draft = $this->getCourseDraftService()->getCourseDraftByCourseIdAndActivityIdAndUserId($params['courseId'], $params['activityId'], $user['id']);

        if (empty($draft)) {
            $draft = array(
                'activityId' => $params['activityId'],
                'title' => '',
                'content' => $params['content'],
                'courseId' => $course['id'],
            );

            return $this->getCourseDraftService()->createCourseDraft($draft);
        } else {
            $draft['content'] = $params['content'];

            return $this->getCourseDraftService()->updateCourseDraft($draft['id'], $draft);
        }
    }

    /**
     * @return CourseDraftService
     */
    protected function getCourseDraftService()
    {
        return $this->service('Course:CourseDraftService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}

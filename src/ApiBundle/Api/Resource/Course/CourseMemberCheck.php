<?php


namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\MemberService;
use Biz\User\Service\UserService;

class CourseMemberCheck extends AbstractResource
{
    public function add(ApiRequest $request, $courseId)
    {
        $title = $request->request->get('title');
        $user = $this->getUserService()->getUserByLoginField($title, true);

        if (!$user) {
            return ['result' => false, 'message' => '该用户不存在'];
        } else {
            $isCourseStudent = $this->getCourseMemberService()->isCourseStudent($courseId, $user['id']);

            if ($isCourseStudent) {
                return ['result' => false, 'message' => '该用户已是本课程的学员了'];
            } else {
                $isCourseTeacher = $this->getCourseMemberService()->isCourseTeacher($courseId, $user['id']);

                if ($isCourseTeacher) {
                    return ['result' => false, 'message' => '该用户是本课程的教师，不能添加'];
                }
            }
        }

        return ['result' => true];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
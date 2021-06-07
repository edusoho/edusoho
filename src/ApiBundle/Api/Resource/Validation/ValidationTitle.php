<?php

namespace ApiBundle\Api\Resource\Validation;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\UserService;

class ValidationTitle extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $type)
    {
        $title = $request->query->get('title', '');
        $exceptId = $request->query->get('exceptId', 0);

        switch ($type) {
            case 'multiClass':
                $result = $this->getMultiClassService()->getMultiClassByTitle($title);
                break;
            case 'multiClassProduct':
                $result = $this->getMultiClassProductService()->getProductByTitle($title);
                break;
            case 'courseStudent':
                return $this->checkStudentAction($title, $exceptId);
            default:
                break;
        }

        if (empty($result)) {
            return ['result' => true];
        }

        if (!empty($result) && !empty($exceptId) && $result['id'] == $exceptId) {
            return ['result' => true];
        }

        return ['result' => false];
    }

    public function checkStudentAction($title, $courseId)
    {
        if (!$courseId){
            return ['result' => false, 'message' => '参数错误'];
        }
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

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return MultiClassProductService
     */
    private function getMultiClassProductService()
    {
        return $this->service('MultiClass:MultiClassProductService');
    }
}

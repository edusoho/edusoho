<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class CourseMemberBatchManage extends AbstractResource
{
    public function add(ApiRequest $request, $courseId)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $userIds = array_values($request->request->get('userIds', []));
        if (empty($userIds)) {
            return [];
        }
        $price = $request->request->get('price', 0.00);
        $remark = $request->request->get('remark', '通过API接口批量添加');
        $course = $this->getCourseService()->getCourse($courseId);
        $orderData = [
            'amount' => $price,
            'remark' => $remark,
        ];
        $existsUserCount = 0;
        $successCount = 0;
        $userData = $this->getUserService()->findUsersByIds($userIds);
        foreach ($userData as $key => $data) {
            if (!empty($data['nickname'])) {
                $user = $this->getUserService()->getUserByNickname($data['nickname']);
            } else {
                if (!empty($data['email'])) {
                    $user = $this->getUserService()->getUserByEmail($data['email']);
                } else {
                    $user = $this->getUserService()->getUserByVerifiedMobile($data['verifiedMobile']);
                }
            }

            $isCourseStudent = $this->getCourseMemberService()->isCourseStudent($course['id'], $user['id']);
            $isCourseTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']);

            if ($isCourseStudent || $isCourseTeacher) {
                ++$existsUserCount;
            } else {
                $data = [
                    'price' => $orderData['amount'],
                    'remark' => empty($orderData['remark']) ? '通过API批量添加' : $orderData['remark'],
                    'source' => 'outside',
                ];
                $this->getCourseMemberService()->becomeStudentAndCreateOrder($user['id'], $course['id'], $data);

                ++$successCount;
            }
        }

        $this->getLogService()->warning('course', 'import_user', '导入学员数据');

        return ['existsUserCount' => $existsUserCount, 'successCount' => $successCount];
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}

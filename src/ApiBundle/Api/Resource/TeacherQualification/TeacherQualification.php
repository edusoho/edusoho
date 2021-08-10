<?php

namespace ApiBundle\Api\Resource\TeacherQualification;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\SettingService;
use Biz\TeacherQualification\Service\TeacherQualificationService;
use Biz\TeacherQualification\TeacherQualificationException;
use Biz\User\Service\UserService;
use Topxia\Service\Common\ServiceKernel;

class TeacherQualification extends AbstractResource
{
    private $requiredFields = [
        'truename',
        'userId',
        'avatarFileId',
        'code',
    ];

    public function get(ApiRequest $request, $userId)
    {
        if (!$this->isTeacher($userId)) {
            throw TeacherQualificationException::TEACHER_QUALIFICATION_NOT_TEACHER();
        }

        $qualification = $this->getTeacherQualificationService()->getByUserId($userId);

        if ($qualification['avatar']) {
            $qualification['url'] = $this->getWebExtension()->getFpath($qualification['avatar']);
        }

        $profile = $this->getUserService()->getUserProfile($userId);
        $qualification['truename'] = $profile['truename'] ?: '';

        return $qualification;
    }

    public function add(ApiRequest $request)
    {
        $qualification = $this->getSettingService()->get('qualification', []);
        $enable = $qualification['qualification_enabled'] ?: 0;

        if (!$enable) {
            throw TeacherQualificationException::TEACHER_QUALIFICATION_NOT_ENABLE();
        }

        $fields = $request->request->all();

        if (!$this->isTeacher($fields['userId'])) {
            throw TeacherQualificationException::TEACHER_QUALIFICATION_NOT_TEACHER();
        }

        $this->checkRequiredFields($this->requiredFields, $fields);

        return $this->getTeacherQualificationService()->changeQualification($fields['userId'], $fields);
    }

    public function search(ApiRequest $request)
    {
        $condition = ['roles' => '|ROLE_TEACHER|'];
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $total = $this->getTeacherQualificationService()->countTeacherQualification($condition);
        $qualification = $this->getTeacherQualificationService()->searchTeacherQualification(
            $condition,
            ['updated_time' => 'DESC'],
            0,
            $total
        );
        $this->getOCUtil()->multiple($qualification, ['user_id'], 'profile', 'profile');

        return $this->makePagingObject($qualification, $total, $offset, $limit);
    }

    protected function isTeacher($userId)
    {
        $user = $this->getUserService()->getUser($userId);

        return in_array('ROLE_TEACHER', $user['roles']);
    }

    /**
     * @return TeacherQualificationService
     */
    private function getTeacherQualificationService()
    {
        return ServiceKernel::instance()->createService('TeacherQualification:TeacherQualificationService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}

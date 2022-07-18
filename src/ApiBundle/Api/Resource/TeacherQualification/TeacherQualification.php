<?php

namespace ApiBundle\Api\Resource\TeacherQualification;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ApiConf;
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

    /**
     * @param $userId
     *
     * @return mixed
     * @Access(roles="ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_EDUCATIONAL_ADMIN")
     */
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

    /**
     * @return mixed
     * @Access(roles="ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_EDUCATIONAL_ADMIN")
     */
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

        $qualification = $this->getTeacherQualificationService()->changeQualification($fields['userId'], $fields);

        if ($qualification['avatar']) {
            $qualification['url'] = $this->getWebExtension()->getFpath($qualification['avatar']);
        }

        return $qualification;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $condition = ['roles' => '|ROLE_TEACHER|'];
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $total = $this->getTeacherQualificationService()->countTeacherQualification($condition);
        $qualifications = $this->getTeacherQualificationService()->searchTeacherQualification(
            $condition,
            ['updated_time' => 'DESC'],
            0,
            $total
        );

        foreach ($qualifications as $key => $qualification) {
            if ($qualification['avatar']) {
                $qualifications[$key]['url'] = $this->getWebExtension()->getFpath($qualification['avatar']);
            }
        }
        $this->getOCUtil()->multiple($qualifications, ['user_id'], 'profile', 'profile');

        return $this->makePagingObject($qualifications, $total, $offset, $limit);
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
}

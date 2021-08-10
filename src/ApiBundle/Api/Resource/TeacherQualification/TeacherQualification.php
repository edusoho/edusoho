<?php

namespace ApiBundle\Api\Resource\TeacherQualification;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\TeacherQualification\Service\TeacherQualificationService;
use Topxia\Service\Common\ServiceKernel;

class TeacherQualification extends AbstractResource
{
    private $requiredFields = [
        'userId',
        'avatarFileId',
        'code',
    ];

    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        $this->checkRequiredFields($this->requiredFields, $fields);

        return $this->getTeacherQualificationService()->changeQualification($fields['userId'], $fields);
    }

    public function search(ApiRequest $request)
    {
        $total = $this->getTeacherQualificationService()->count([]);
        $qualification = $this->getTeacherQualificationService()->search(
            [],
            ['updated_time' => 'DESC'],
            0,
            $total
        );
        $this->getOCUtil()->multiple($qualification, ['user_id'], 'profile', 'profile');

        return ['data' => $qualification];
    }

    /**
     * @return TeacherQualificationService
     */
    private function getTeacherQualificationService()
    {
        return ServiceKernel::instance()->createService('TeacherQualification:TeacherQualificationService');
    }
}

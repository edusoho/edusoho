<?php

namespace ApiBundle\Api\Resource\Certificate;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;

class Certificate extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $certificate = $this->getCertificateService()->get($id);
        if (empty($certificate)) {
            throw CertificateException::NOTFOUND_CERTIFICATE();
        }

        $certificate = $this->refineCertificate($certificate);

        return $certificate;
    }

    protected function refineCertificate($certificate)
    {
        if ('classroom' == $certificate['targetType']) {
            $certificate['classroom'] = $this->getClassroomService()->getClassroom($certificate['targetId']);
        } else {
            $certificate['course'] = $this->getCourseService()->getCourse($certificate['targetId']);
        }

        $certificate['isObtained'] = $this->getCertificateRecordService()->isObtained($this->getCurrentUser()->getId(), $certificate['id']);

        return $certificate;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();

        $conditions['status'] = 'published';
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $certificates = $this->getCertificateService()->search($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $total = $this->getCertificateService()->count($conditions);

        $user = $this->getCurrentUser();
        $isObtaineds = $this->getCertificateRecordService()->isCertificatesObtained($user['id'], ArrayToolkit::column($certificates, 'id'));

        foreach ($certificates as &$certificate) {
            $certificate['isObtained'] = $isObtaineds[$certificate['id']];
        }

        return $this->makePagingObject($certificates, $total, $offset, $limit);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return RecordService
     */
    protected function getCertificateRecordService()
    {
        return $this->service('Certificate:RecordService');
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->service('Certificate:CertificateService');
    }
}

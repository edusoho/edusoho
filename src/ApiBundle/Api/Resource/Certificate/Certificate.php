<?php


namespace ApiBundle\Api\Resource\Certificate;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Certificate\CertificateException;
use AppBundle\Common\ArrayToolkit;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\CourseException;
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
        switch ($certificate['targetType']) {
            case 'classroom':
                $target = $this->getClassroomService()->getClassroom($certificate['targetId']);
                if (empty($target)) {
                    throw ClassroomException::NOTFOUND_CLASSROOM();
                }
                $this->getOCUtil()->single($target, array('creator', 'teacherIds', 'assistantIds', 'headTeacherId'));
                $certificate['classroom'] = $target;
                break;
            case 'course':
                $target = $this->getCourseService()->getCourse($certificate['targetId']);
                if (empty($target)) {
                    throw CourseException::NOTFOUND_COURSE();
                }
                $this->getOCUtil()->single($target, array('creator', 'teacherIds'));
                $this->getOCUtil()->single($target, array('courseSetId'), 'courseSet');
                $certificate['course'] = $target;
                break;
        }

        $certificate['isObtained'] = $this->getCertificateRecordService()->isObtained([
            'userId' => $this->getCurrentUser()->getId(),
            'certificateId' => $certificate['id'],
            'statuses' => ['valid', 'expired'],
        ]);

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
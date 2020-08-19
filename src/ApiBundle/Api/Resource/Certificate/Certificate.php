<?php


namespace ApiBundle\Api\Resource\Certificate;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
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
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $target = $this->getTarget($conditions);

        $conditions['status'] = 'published';
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $certificates = $this->getCertificateService()->search($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $total = $this->getCertificateService()->count($conditions);

        $user = $this->getCurrentUser();
        $obtainedCertificates = $this->getCertificateRecordService()->search(
            ['targetType' => $conditions['targetType'], 'statuses' => ['valid', 'expired'], 'userId' => $user['id']],
            [],
            0,
            PHP_INT_MAX
        );

        $obtainedCertificates = ArrayToolkit::index($obtainedCertificates, 'certificateId');
        foreach ($certificates as &$certificate) {
            $certificate['isObtained'] = empty($obtainedCertificates[$certificate['id']]) ? false : true;
        }

        return $this->makePagingObject($certificates, $total, $offset, $limit);
    }

    protected function getTarget($condition)
    {
        if ($condition['targetType'] == 'classroom') {
            $target = $this->getClassroomService()->getClassroom($condition['targetId']);
            if (empty($target)) {
                throw ClassroomException::NOTFOUND_CLASSROOM();
            }
        } else {
            $target = $this->getCourseService()->getCourse($condition['targetId']);
            if (empty($target)) {
                throw CourseException::NOTFOUND_COURSE();
            }
        }

        return $target;
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
<?php


namespace ApiBundle\Api\Resource\Certificate;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Certificate\CertificateException;
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
        $conditions = $request->query->all();
        $target = $this->getTarget($conditions);

        $certificate = $this->getCertificateService()->get($id);
        if (empty($certificate)) {
           throw CertificateException::NOTFOUND_CERTIFICATE();
        }

        $conditions['userId'] = $this->getCurrentUser()->getId();
        $conditions['certificateId'] = $certificate['id'];
        $conditions['statuses'] = ['valid', 'expired'];
        $certificate['isObtained'] = $this->getCertificateRecordService()->isObtained($conditions);
        $certificate['targetTitle'] = $conditions['targetType'] == 'course' ? $target['courseSetTitle'] : $target['title'];

        return $certificate;
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
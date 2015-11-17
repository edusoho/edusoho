<?php


namespace Mooc\Service\Course\Impl;

use Topxia\Service\Course\Impl\CourseDeleteServiceImpl as BaseCourseDeleteService;

class CourseDeleteServiceImpl extends BaseCourseDeleteService
{
    protected function deleteCourse($course)
    {
        parent::deleteCourse($course);
        if ('periodic' == $course['type']) {
            $this->_deletePeriodicCourse($course);
        }
        return 0;
    }

    private function _deletePeriodicCourse($course)
    {
        if($course['type'] != 'periodic'){
            throw $this->createServiceException("{$course['title']}不是周期课程");
        }

        $this->_deleteCourseScore($course);
        $this->_deleteCourseCertificate($course);
        $this->getCourseDao()->subPeriodsByRootId($course['rootId'], $course['periods']);
    }

    private function _deleteCourseScore($course)
    {
        $this->getCourseScoreService()->deleteCourseScoreByCourseId($course['id']);
    }

    private function _deleteCourseCertificate($course)
    {
        $certificate = $this->getAppService()->findInstallApp('Certificate');

        if(empty($certificate)){
            return;
        }

        $this->getCertificateService()->deleteCourseCertificateByCourseId($course['id']);
    }

    protected function getCourseScoreService()
    {
        return $this->createService('Mooc:Course.CourseScoreService');
    }

    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }
}
<?php


namespace ApiBundle\Api\Resource\Certificate;


use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CertificateFilter extends Filter
{
    protected $simpleFields = [
        'id', 'name',
    ];

    protected $publicFields = [
        'id', 'name', 'targetType', 'targetId', 'description', 'templateId',
        'code', 'status', 'expiryDay', 'autoIssue', 'createdUserId', 'createdTime', 'updatedTime', 'classroom', 'course', 'isObtained',
    ];

    protected function publicFields(&$data)
    {
        if (isset($data['createdUserId'])) {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['createdUserId']);
        }

        if (isset($data['classroom'])) {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::SIMPLE_MODE);
            $classroomFilter->filter($data['classroom']);
            unset($data['classroom']);
        }

        if (isset($data['course'])) {
            $courseFilter = new CourseFilter();
            $courseFilter->setMode(Filter::SIMPLE_MODE);
            $courseFilter->filter($data['course']);
            unset($data['course']);
        }

        if (!empty($data['description'])) {
            $data['description'] = $this->convertAbsoluteUrl($data['description']);
        }
    }
}
<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\Converter;
use ApiBundle\Api\Util\RequestUtil;
use AppBundle\Common\ServiceToolkit;

class ClassroomFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'smallPicture', 'middlePicture', 'largePicture'
    );

    protected $publicFields = array(
        'status', 'about', 'price', 'vipLevelId', 'headTeacher', 'teachers', 'assistants',
        'hitNum', 'auditorNum', 'studentNum', 'courseNum', 'threadNum', 'noteNum', 'postNum', 'service', 'recommended',
        'recommendedSeq', 'rating', 'ratingNum', 'maxRate', 'showable', 'buyable', 'expiryMode', 'expiryValue',
        'createdTime', 'updatedTime', 'creator'
    );

    protected function simpleFields(&$data)
    {
        $data['smallPicture'] = RequestUtil::getUriForPath($data['smallPicture']);
        $data['middlePicture'] = RequestUtil::getUriForPath($data['middlePicture']);
        $data['largePicture'] = RequestUtil::getUriForPath($data['largePicture']);
    }

    protected function publicFields(&$data)
    {
        if ($data['expiryMode'] == 'date') {
            Converter::timestampToDate($data['expiryStartDate']);
        }

        $data['service'] = ServiceToolkit::getServicesByCodes($data['service']);

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['creator']);
        $userFilter->filter($data['headTeacher']);
        $userFilter->filters($data['teachers']);
        $userFilter->filters($data['assistants']);
    }
}
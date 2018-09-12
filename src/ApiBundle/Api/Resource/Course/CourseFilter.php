<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\Converter;
use ApiBundle\Api\Util\Money;
use Biz\Course\Util\CourseTitleUtils;
use AppBundle\Common\ServiceToolkit;

class CourseFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'courseSetTitle',
    );

    protected $publicFields = array(
        'subtitle', 'courseSet', 'learnMode', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'summary',
        'goals', 'audiences', 'isDefault', 'maxStudentNum', 'status', 'creator', 'isFree', 'price', 'originPrice',
        'vipLevelId', 'buyable', 'tryLookable', 'tryLookLength', 'watchLimit', 'services', 'ratingNum', 'rating',
        'taskNum', 'compulsoryTaskNum', 'studentNum', 'teachers', 'parentId', 'createdTime', 'updatedTime', 'enableFinish', 'buyExpiryTime', 'access', 'isAudioOn',
    );

    protected function publicFields(&$data)
    {
        $this->learningExpiryDate($data);
        Converter::timestampToDate($data['buyExpiryTime']);

        $data['services'] = AssetHelper::callAppExtensionMethod('transServiceTags', array(ServiceToolkit::getServicesByCodes($data['services'])));

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);

        $courseSetFilter = new CourseSetFilter();
        $courseSetFilter->setMode(Filter::SIMPLE_MODE);
        $courseSetFilter->filter($data['courseSet']);

        /*
         * @TODO 2017-06-29 业务变更、字段变更:publishedTaskNum变更为compulsoryTaskNum,兼容一段时间
         */
        $data['publishedTaskNum'] = $data['compulsoryTaskNum'];
        $data['summary'] = $this->convertAbsoluteUrl($data['summary']);
    }

    protected function simpleFields(&$data)
    {
        $displayedTitle = CourseTitleUtils::getDisplayedTitle($data);
        if (!empty($displayedTitle)) {
            $data['displayedTitle'] = $displayedTitle;
        }
    }

    private function learningExpiryDate(&$data)
    {
        Converter::timestampToDate($data['expiryStartDate']);
        Converter::timestampToDate($data['expiryEndDate']);
        $data['learningExpiryDate'] = array(
            'expiryMode' => $data['expiryMode'],
            'expiryStartDate' => $data['expiryStartDate'],
            'expiryEndDate' => $data['expiryEndDate'],
            'expiryDays' => $data['expiryDays'],
        );

        unset($data['expiryMode']);
        unset($data['expiryStartDate']);
        unset($data['expiryEndDate']);
        unset($data['expiryDays']);

        if ('forever' == $data['learningExpiryDate']['expiryMode'] || 'days' == $data['learningExpiryDate']['expiryMode']) {
            $data['learningExpiryDate']['expired'] = false;
        } else {
            $data['learningExpiryDate']['expired'] = time() > strtotime($data['learningExpiryDate']['expiryEndDate']);
        }

        $data['price2'] = Money::convert($data['price']);
        $data['originPrice2'] = Money::convert($data['originPrice']);
    }
}

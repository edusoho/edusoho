<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Good\GoodSpecsFilter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\Converter;
use ApiBundle\Api\Util\Money;
use AppBundle\Common\ServiceToolkit;
use Biz\Course\Util\CourseTitleUtils;
use Biz\System\Service\SettingService;
use Topxia\Service\Common\ServiceKernel;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;
use VipPlugin\Biz\Marketing\VipRightSupplier\CourseVipRightSupplier;

class CourseFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'courseSetTitle', 'goodsId', 'specsId',
    ];

    protected $publicFields = [
        'id', 'subtitle', 'courseSet', 'learnMode', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'summary',
        'goals', 'audiences', 'isDefault', 'maxStudentNum', 'status', 'creator', 'isFree', 'price', 'originPrice',
        'vipLevelId', 'buyable', 'tryLookable', 'tryLookLength', 'watchLimit', 'services', 'ratingNum', 'rating',
        'taskNum', 'compulsoryTaskNum', 'studentNum', 'teachers', 'parentId', 'createdTime', 'updatedTime', 'enableFinish',
        'buyExpiryTime', 'access', 'isAudioOn', 'hasCertificate', 'goodsId', 'specsId', 'spec', 'hitNum', 'classroom', 'assistants', 'assistant', 'liveStatus', 'drainage',
    ];

    protected function publicFields(&$data)
    {
        $this->learningExpiryDate($data);
        Converter::timestampToDate($data['buyExpiryTime']);

        $data['services'] = AssetHelper::callAppExtensionMethod('transServiceTags', [ServiceToolkit::getServicesByCodes($data['services'])]);
        $data['drainage'] = empty($data['drainage']) ? ['enabled' => 0, 'image' => '', 'text' => ''] : $data['drainage'];

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);
        $userFilter->filter($data['assistant']);
        $userFilter->filters($data['assistants']);

        $courseSetFilter = new CourseSetFilter();
        $courseSetFilter->setMode(Filter::SIMPLE_MODE);
        $courseSetFilter->filter($data['courseSet']);

        if (!empty($data['spec'])) {
            $specsFilter = new GoodSpecsFilter();
            $specsFilter->setMode(Filter::SIMPLE_MODE);
            $specsFilter->filter($data['spec']);
        }

        /*
         * @TODO 2017-06-29 业务变更、字段变更:publishedTaskNum变更为compulsoryTaskNum,兼容一段时间
         */
        $data['publishedTaskNum'] = $data['compulsoryTaskNum'];
        $data['summary'] = $this->convertAbsoluteUrl($data['summary']);

        $vipSetting = $this->getSettingService()->get('vip', []);
        if ($this->isPluginInstalled('Vip') && !empty($vipSetting['enabled'])) {
            $vipRight = $this->getVipRightService()->getVipRightsBySupplierCodeAndUniqueCode(CourseVipRightSupplier::CODE, $data['id']);
            $data['vipLevelId'] = empty($vipRight) ? 0 : $vipRight['vipLevelId'];
        }

        if (!empty($data['classroom'])) {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::SIMPLE_MODE);
            $classroomFilter->filter($data['classroom']);
            if ($this->isPluginInstalled('Vip') && !empty($vipSetting['enabled'])) {
                $vipRight = $this->getVipRightService()->getVipRightsBySupplierCodeAndUniqueCode(ClassroomVipRightSupplier::CODE, $data['classroom']['id']);
                $data['vipLevelId'] = empty($vipRight) ? 0 : $vipRight['vipLevelId'];
            }
        }
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
        $data['learningExpiryDate'] = [
            'expiryMode' => $data['expiryMode'],
            'expiryStartDate' => $data['expiryStartDate'],
            'expiryEndDate' => $data['expiryEndDate'],
            'expiryDays' => $data['expiryDays'],
        ];

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

    /**
     * @return VipRightService
     */
    private function getVipRightService()
    {
        return ServiceKernel::instance()->createService('VipPlugin:Marketing:VipRightService');
    }
}

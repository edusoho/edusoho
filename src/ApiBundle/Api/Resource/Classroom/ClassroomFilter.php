<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Good\GoodSpecsFilter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\Converter;
use ApiBundle\Api\Util\Money;
use AppBundle\Common\ServiceToolkit;
use Biz\System\Service\SettingService;
use Topxia\Service\Common\ServiceKernel;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;

class ClassroomFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'smallPicture', 'middlePicture', 'largePicture', 'price', 'studentNum', 'courseNum', 'about', 'productId', 'goodsId', 'specsId', 'spec', 'learningProgressPercent'
    ];

    protected $publicFields = [
        'id', 'status', 'price', 'vipLevelId', 'headTeacher', 'teachers', 'assistants',
        'hitNum', 'auditorNum', 'studentNum', 'courseNum', 'threadNum', 'noteNum', 'postNum', 'service', 'recommended',
        'recommendedSeq', 'rating', 'ratingNum', 'maxRate', 'showable', 'buyable', 'expiryMode', 'expiryValue',
        'createdTime', 'updatedTime', 'creator', 'access', 'productId', 'goodsId', 'hasCertificate', 'specsId', 'spec',
    ];

    protected function simpleFields(&$data)
    {
        $data['about'] = $this->convertAbsoluteUrl($data['about']);
        $this->transformCover($data);

        $data['price2'] = Money::convert($data['price']);

        if (!empty($data['spec'])) {
            $specsFilter = new GoodSpecsFilter();
            $specsFilter->setMode(Filter::PUBLIC_MODE);
            $specsFilter->filter($data['spec']);
        }
    }

    protected function publicFields(&$data)
    {
        if ('date' == $data['expiryMode']) {
            Converter::timestampToDate($data['expiryStartDate']);
        }

        $data['service'] = AssetHelper::callAppExtensionMethod('transServiceTags', [ServiceToolkit::getServicesByCodes($data['service'])]);

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);
        $userFilter->filters($data['assistants']);
        if (!empty($data['headTeacher'])) {
            $userFilter->setMode(Filter::PUBLIC_MODE);
            $userFilter->filter($data['headTeacher']);
        }

        if (!empty($data['spec'])) {
            $specsFilter = new GoodSpecsFilter();
            $specsFilter->setMode(Filter::SIMPLE_MODE);
            $specsFilter->filter($data['spec']);
        }

        $vipSetting = $this->getSettingService()->get('vip', []);
        if ($this->isPluginInstalled('Vip') && !empty($vipSetting['enabled'])) {
            $vipRight = $this->getVipRightService()->getVipRightsBySupplierCodeAndUniqueCode(ClassroomVipRightSupplier::CODE, $data['id']);
            $data['vipLevelId'] = empty($vipRight) ? 0 : $vipRight['vipLevelId'];
        }
    }

    private function transformCover(&$data)
    {
        $data['smallPicture'] = AssetHelper::getFurl($data['smallPicture'], 'classroom.png');
        $data['middlePicture'] = AssetHelper::getFurl($data['middlePicture'], 'classroom.png');
        $data['largePicture'] = AssetHelper::getFurl($data['largePicture'], 'classroom.png');
        $data['cover'] = [
            'small' => $data['smallPicture'],
            'middle' => $data['middlePicture'],
            'large' => $data['largePicture'],
        ];

        unset($data['smallPicture']);
        unset($data['middlePicture']);
        unset($data['largePicture']);
    }

    /**
     * @return VipRightService
     */
    private function getVipRightService()
    {
        return ServiceKernel::instance()->createService('VipPlugin:Marketing:VipRightService');
    }
}

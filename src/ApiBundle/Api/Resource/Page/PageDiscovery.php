<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Announcement\Service\AnnouncementService;
use Biz\Article\Service\ArticleService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Coupon\Service\CouponBatchService;
use Biz\Course\Service\CourseService;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\H5SettingService;
use Biz\User\UserException;

class PageDiscovery extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $portal)
    {
        if (!in_array($portal, ['h5', 'miniprogram', 'apps'])) {
            throw PageException::ERROR_PORTAL();
        }
        $params = $request->query->all();
        $mode = 'published';
        if (!empty($params['preview'])) {
            $token = $this->getTokenService()->verifyToken('qrcode_url', $params['token']);
            if (empty($token)) {
                throw UserException::PERMISSION_DENIED();
            }
            $mode = 'draft';
        }
        $discoverySettings = $this->getH5SettingService()->getDiscovery($portal, $mode);
        foreach ($discoverySettings as &$discoverySetting) {
            if ('slide_show' == $discoverySetting['type']) {
                array_walk($discoverySetting['data'], function (&$slide) {
                    if (in_array($slide['link']['type'], ['course', 'classroom'])) {
                        $targetId = 'classroom' === $slide['link']['type'] ? $slide['link']['target']['id'] : $slide['link']['target']['courseSetId'];
                        $product = $this->getProductService()->getProductByTargetIdAndType($targetId, $slide['link']['type']);
                        $goods = $this->getGoodsService()->getGoodsByProductId($product['id']);
                        $slide['link']['target']['goodsId'] = $goods['id'];
                    }
                });
            }
            if ('course_list' == $discoverySetting['type']) {
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], ['creator', 'teacherIds']);
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], ['courseSetId'], 'courseSet');
                $discoverySetting['data']['items'] = $this->getCourseService()->appendSpecsInfo($discoverySetting['data']['items']);
                $discoverySetting['data']['source'] = [
                    'category' => $discoverySetting['data']['categoryId'],
                    'courseType' => 'all',
                    'sort' => $discoverySetting['data']['sort'],
                ];
            }
            if ('classroom_list' == $discoverySetting['type']) {
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], ['creator', 'teacherIds', 'assistantIds', 'headTeacherId']);
                $discoverySetting['data']['items'] = $this->getClassroomService()->appendSpecsInfo($discoverySetting['data']['items']);
            }
            if ('coupon' == $discoverySetting['type']) {
                foreach ($discoverySetting['data']['items'] as &$couponBatch) {
                    $couponBatch['target'] = $this->getCouponBatchService()->getTargetByBatchId($couponBatch['id']);
                    $couponBatch['targetDetail'] = $this->getCouponBatchService()->getCouponBatchTargetDetail($couponBatch['id']);
                }
            }

            if ('open_course_list' == $discoverySetting['type']) {
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], ['userId', 'teacherIds']);
            }

            if ('announcement' == $discoverySetting['type']) {
                $announcement = $this->getAnnouncementService()->searchAnnouncements(['startTime' => time(), 'endTime' => time(), 'targetType' => 'global'], ['startTime' => 'DESC'], 0, 1);
                $discoverySetting['data'] = empty($announcement) ? '' : $announcement[0]['content'];
            }

            if('information' == $discoverySetting['type']){
                $information = $this ->getArticleService() -> searchArticles(['status' => 'published'], ['sticky' => 'DESC' ,'publishedTime' => 'DESC'], 0, 3);
                foreach ($information as &$info) {
                    $info['createdTime'] = date('c', $info['createdTime']);
                    $info['updatedTime'] = date('c', $info['updatedTime']);
                    $info['publishedTime'] = date('c', $info['publishedTime']);
                    $info['body'] = AssetHelper::transformImages($info['body']);
                    $info['thumb'] = AssetHelper::transformImagesAddUrl($info['thumb'], '');
                    $info['originalThumb'] = AssetHelper::transformImagesAddUrl($info['originalThumb'], '');
                    $info['picture'] = AssetHelper::transformImagesAddUrl($info['picture'], 'picture');
                }
                $discoverySetting['data'] = empty($information) ? '' : $information;
            }
        }

        return !empty($params['format']) && 'list' == $params['format'] ? array_values($discoverySettings) : $discoverySettings;
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->service('Announcement:AnnouncementService');
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->service('System:H5SettingService');
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->service('Product:ProductService');
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->service('Article:ArticleService');
    }
}

<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Goods\Service\GoodsService;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\CourseVipRightSupplier;

class PageCourse extends AbstractResource
{
    const DEFAULT_DISPLAY_COUNT = 5;

    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\Page\PageCourseFilter", mode="public")
     */
    public function get(ApiRequest $request, $portal, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $user = $this->getCurrentUser();
        $member = null;
        if (!empty($user['id'])) {
            $apiRequest = new ApiRequest('/api/me/course_members/'.$courseId, 'GET', []);
            $member = $this->invokeResource($apiRequest);
        }
        $course['member'] = $member;
        $course['learnedCompulsoryTaskNum'] = empty($member) ? 0 : $member['learnedCompulsoryTaskNum'];

        $this->getOCUtil()->single($course, ['creator', 'teacherIds']);
        $this->getOCUtil()->single($course, ['courseSetId'], 'courseSet');
        $course['access'] = $this->getCourseService()->canJoinCourse($courseId);

        $course['courseItems'] = $this->container->get('api.util.item_helper')->convertToLeadingItemsV2(
            $this->getCourseService()->findCourseItems($courseId),
            $course,
            $request->getHttpRequest()->isSecure(),
            $request->query->get('fetchSubtitlesUrls', 0),
            $request->query->get('onlyPublished', 0),
            $request->query->get('showOptionalNum', 1)
        );

        $course['allowAnonymousPreview'] = $this->getSettingService()->get('course.allowAnonymousPreview', 1);
        $course['courses'] = $this->getCourseService()->findPublishedCoursesByCourseSetId($course['courseSet']['id']);
        $course['courses'] = ArrayToolkit::sortPerArrayValue($course['courses'], 'seq');
        $course['courses'] = $this->getCourseService()->appendSpecsInfo($course['courses']);
        $course['progress'] = $this->getLearningDataAnalysisService()->makeProgress($course['learnedCompulsoryTaskNum'], $course['compulsoryTaskNum']);
        $course['hasCertificate'] = $this->getCourseService()->hasCertificate($course['id']);
        $course = $this->getCourseService()->appendSpecInfo($course);

        $goods = $this->getGoodsService()->getGoods($course['goodsId']);
        $course['hitNum'] = empty($goods['hitNum']) ? 0 : $goods['hitNum'];

        if ($this->isPluginInstalled('vip')) {
            if (version_compare($this->getPluginVersion('Vip'), '1.8.6', '>=')) {
                $vipRights = $this->getVipRightService()->findVipRightsBySupplierCodeAndUniqueCode(CourseVipRightSupplier::CODE, $course['id']);
                if (!empty($vipRights)) {
                    $course['vipLevel'] = $this->getVipLevel($vipRights[0]['vipLevelId']);
                }
            } else if ($course['vipLevelId'] > 0) {
                $course['vipLevel'] = $this->getVipLevel($course['vipLevelId']);
            }
        }

        $course['reviews'] = $this->searchCourseReviews($course);
        $course['myReview'] = $this->getMyReview($course, $user);

        return $course;
    }

    protected function getVipLevel($levelId)
    {
        $apiRequest = new ApiRequest('/api/plugins/vip/vip_levels/'.$levelId, 'GET', []);

        return $this->invokeResource($apiRequest);
    }

    protected function searchCourseReviews($course)
    {
        if (0 == $course['parentId']) {
            $targetType = 'goods';
            $targetId = $course['goodsId'];
        } else {
            $targetType = 'course';
            $targetId = $course['id'];
        }
        $result = $this->invokeResource(new ApiRequest(
            '/api/reviews',
            'GET',
            [
                'targetType' => $targetType,
                'targetId' => $targetId,
                'parentId' => 0,
                'offset' => 0,
                'limit' => self::DEFAULT_DISPLAY_COUNT,
                'orderBys' => ['updatedTime' => 'DESC'],
                'needPosts' => true,
            ]
        ));

        return $result['data'];
    }

    protected function getMyReview($course, $user)
    {
        if (empty($user['id'])) {
            return null;
        }

        if (0 == $course['parentId']) {
            $targetType = 'goods';
            $targetId = $course['goodsId'];
        } else {
            $targetType = 'course';
            $targetId = $course['id'];
        }
        $result = $this->invokeResource(new ApiRequest(
            '/api/reviews',
            'GET',
            [
                'targetType' => $targetType,
                'targetId' => $targetId,
                'userId' => $user['id'],
                'parentId' => 0,
                'offset' => 0,
                'limit' => self::DEFAULT_DISPLAY_COUNT,
                'orderBys' => ['updatedTime' => 'DESC'],
                'needPosts' => true,
            ]
        ));

        return empty($result['data']) ? null : reset($result['data']);
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    private function getLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }

    /**
     * @return VipRightService
     */
    private function getVipRightService()
    {
        return $this->service('VipPlugin:Marketing:VipRightService');
    }
}

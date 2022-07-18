<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Course\Service\CourseService;
use Biz\Goods\Service\GoodsService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;
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

        if ($course['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            empty($classroom) || $course['classroom'] = $this->getClassroomService()->appendSpecInfo($classroom);
        }

        if ($this->isPluginInstalled('vip')) {
            if (!empty($course['classroom'])) {
                $vipRight = $this->getVipRightService()->getVipRightsBySupplierCodeAndUniqueCode(ClassroomVipRightSupplier::CODE, $course['classroom']['id']);
            } else {
                $vipRight = $this->getVipRightService()->getVipRightsBySupplierCodeAndUniqueCode(CourseVipRightSupplier::CODE, $course['id']);
            }
            empty($vipRight) || $course['vipLevel'] = $this->getVipLevel($vipRight['vipLevelId']);
        }

        $course['reviews'] = $this->searchCourseReviews($course);
        $course['myReview'] = $this->getMyReview($course, $user);

        $course['assistant'] = [];
        if (!empty($user['id'])) {
            $assistantStudent = $this->getAssistantStudentService()->getByStudentIdAndCourseId($user['id'], $courseId);
            if (!empty($assistantStudent)) {
                $course['assistantId'] = $assistantStudent['assistantId'];
                $this->getOCUtil()->single($course, ['assistantId']);
                $course['assistant'] = $this->getAssistantScrmQrCode($course['assistant']);
            }
        }

        return $course;
    }

    protected function getAssistantScrmQrCode($assistant)
    {
        if (empty($assistant['scrmStaffId'])) {
            return $assistant;
        }

        $scrmBindQrCode = $this->generateScrmQrCode($assistant);
        if (!empty($scrmBindQrCode)) {
            $assistant['weChatQrCode'] = $scrmBindQrCode;
        }

        return $assistant;
    }

    protected function generateScrmQrCode($assistant)
    {
        $scrmBind = $this->getSCRMService()->isScrmBind();
        if (empty($scrmBind)) {
            return '';
        }

        $user = $this->setScrmData();
        if (!empty($user['scrmUuid'])) {
            return $this->getSCRMService()->getAssistantQrCode($assistant);
        }

        $url = $this->getScrmStudentBindUrl($assistant);
        if (empty($url)) {
            return '';
        }

        $token = $this->getTokenService()->makeToken(
            'qrcode',
            [
                'userId' => $user['id'],
                'data' => [
                    'url' => $url,
                ],
                'times' => 1,
                'duration' => 3600,
            ]
        );
        $url = $this->generateUrl('common_parse_qrcode', ['token' => $token['token']], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->generateUrl('common_qrcode', ['text' => $url], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    protected function setScrmData()
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $user = $this->getSCRMService()->setUserSCRMData($user);

        return $user;
    }

    protected function getScrmStudentBindUrl($assistant)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());

        $bindUrl = $this->getSCRMService()->getWechatOauthLoginUrl($user, $this->generateUrl('scrm_user_bind_result', ['uuid' => $user['uuid'], 'assistantUuid' => $assistant['uuid']], UrlGeneratorInterface::ABSOLUTE_URL));

        return $bindUrl;
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

    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->service('Assistant:AssistantStudentService');
    }

    /**
     * @return \Biz\SCRM\Service\SCRMService
     */
    protected function getSCRMService()
    {
        return $this->service('SCRM:SCRMService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}

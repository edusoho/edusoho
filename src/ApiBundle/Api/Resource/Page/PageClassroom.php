<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;

class PageClassroom extends AbstractResource
{
    const DEFAULT_DISPLAY_COUNT = 5;

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (empty($classroom)) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }
        $user = $this->getCurrentUser();
        $member = null;
        if (!empty($user['id'])) {
            $apiRequest = new ApiRequest('/api/me/classroom_members/'.$classroomId, 'GET', []);
            $member = $this->invokeResource($apiRequest);
        }
        $classroom['member'] = $member;

        $this->getOCUtil()->single($classroom, ['creator', 'teacherIds', 'assistantIds', 'headTeacherId']);
        if (!empty($classroom['headTeacher'])) {
            $this->mergeProfile($classroom['headTeacher']);
        }

        $classroom['access'] = $this->getClassroomService()->canJoinClassroom($classroomId);
        $classroom['courses'] = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        $classroom = $this->getClassroomService()->appendSpecInfo($classroom);

        $this->getOCUtil()->multiple($classroom['courses'], ['courseSetId'], 'courseSet');
        $this->getOCUtil()->multiple($classroom['courses'], ['creator', 'teacherIds']);

        $classroom['myReview'] = $this->getMyReview($classroom, $user);

        $reviewResult = $this->invokeResource(new ApiRequest(
            '/api/review',
            'GET',
            [
                'targetType' => 'goods',
                'targetId' => $classroom['goodsId'],
                'parentId' => 0,
                'offset' => 0,
                'limit' => self::DEFAULT_DISPLAY_COUNT,
            ]
        ));

        $classroom['reviews'] = $reviewResult['data'];

        if ($this->isPluginInstalled('vip')) {
            if (version_compare($this->getPluginVersion('Vip'), '1.8.6', '>=')) {
                $vipRights = $this->getVipRightService()->findVipRightsBySupplierCodeAndUniqueCode(ClassroomVipRightSupplier::CODE, $classroom['id']);
                if (!empty($vipRights)) {
                    $classroom['vipLevel'] = $this->getVipLevel($vipRights[0]['vipLevelId']);
                    $classroom['vipLevelId'] = $vipRights[0]['vipLevelId']; //新版本classroom已删除该字段，兼容需加上
                }
            } else if ($classroom['vipLevelId'] > 0) {
                $classroom['vipLevel'] = $this->getVipLevel($classroom['vipLevelId']);
            }
        }

        return $classroom;
    }

    protected function getVipLevel($levelId)
    {
        $apiRequest = new ApiRequest('/api/plugins/vip/vip_levels/'.$levelId, 'GET', []);

        return $this->invokeResource($apiRequest);
    }

    private function getMyReview($classroom, $user)
    {
        if (empty($user['id'])) {
            return null;
        }
        $myReviewResult = $this->invokeResource(new ApiRequest(
            '/api/review',
            'GET',
            [
                'targetType' => 'goods',
                'targetId' => $classroom['goodsId'],
                'userId' => $user['id'],
                'parentId' => 0,
                'offset' => 0,
                'limit' => self::DEFAULT_DISPLAY_COUNT,
            ]
        ));

        return empty($myReviewResult['data']) ? null : reset($myReviewResult['data']);
    }

    private function mergeProfile(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return VipRightService
     */
    private function getVipRightService()
    {
        return $this->service('VipPlugin:Marketing:VipRightService');
    }
}

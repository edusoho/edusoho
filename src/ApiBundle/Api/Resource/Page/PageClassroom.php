<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;
use VipPlugin\Biz\Vip\Service\VipService;

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
            $vipRight = $this->getVipRightService()->getVipRightsBySupplierCodeAndUniqueCode(ClassroomVipRightSupplier::CODE, $classroom['id']);
            if (!empty($vipRight)) {
                $classroom['vipLevel'] = $this->getVipLevel($vipRight['vipLevelId']);
                $classroom['vipLevelId'] = $vipRight['vipLevelId']; //新版本classroom已删除该字段，兼容需加上
            }
        }

        $vipSetting = $this->getSettingService()->get('vip', []);
        $classroom['vipDeadline'] = false;
        if ($this->isPluginInstalled('Vip') && !empty($vipSetting['enabled']) && 'vip_join' == $member['joinedChannel'] && in_array('student', $member['role'])) {
            $vipMember = $this->getVipService()->getMemberByUserId($member['userId']);
            $vipRight = $this->getVipRightService()->getVipRightBySupplierCodeAndUniqueCode('classroom', $classroomId);
            if (!empty($vipMember) && !empty($vipRight)) {
                $classroom['vipDeadline'] = true;
                $classroom['expiryValue'] = ($vipMember['deadline'] < $classroom['expiryValue']) || empty($classroom['expiryValue']) ? $vipMember['deadline'] : $classroom['expiryValue'];
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
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return VipRightService
     */
    private function getVipRightService()
    {
        return $this->service('VipPlugin:Marketing:VipRightService');
    }
}

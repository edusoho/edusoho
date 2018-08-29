<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Common\TimeMachine;

class PageDiscovery extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $portal)
    {
        if (!in_array($portal, array('h5', 'miniprogram'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $params = $request->query->all();
        $settingName = "{$portal}_published_discovery";
        if (!empty($params['preview'])) {
            $token = $this->getTokenService()->verifyToken('qrcode_url', $token);
            if (empty($token)) {
                throw new \Exception('Error Processing Request', 1);
            }
            $user = $this->getUserService()->getUser($token['userId']);
            if (!in_array('ROLE_SUPER_ADMIN', $user['roles']) && !in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
                throw new \Exception('Error Processing Request', 1);
            }
            $settingName = "{$portal}_draft_discovery";
        }
        $discoverySettings = $this->getSettingService()->get($settingName);
        foreach ($discoverySettings as &$discoverySetting) {
            if ('course_list' == $discoverySetting['type'] && 'condition' == $discoverySetting['data']['sourceType']) {
                if (!empty($discoverySetting['data']['lastDays'])) {
                    $timeRange = TimeMachine::getTimeRangeByDays($discoverySetting['data']['lastDays']);
                    $conditions['otherStartTime'] = $timeRange['startTime'];
                    $conditions['otherEndTime'] = $timeRange['endTime'];
                }

                $conditions = array('parentId' => 0, 'status' => 'published', 'courseSetStatus' => 'published', 'excludeTypes' => array('reservation'));
                $conditions['categoryId'] = $discoverySetting['data']['categoryId'];
                $sort = $this->getSortByStr($discoverySetting['data']['sort']);
                $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
                $courses = $this->getCourseService()->searchBySort($conditions, $sort, 0, $limit);
                $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));
                $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');
                $discoverySetting['data']['items'] = $courses;
            }
        }

        return $discoverySettings;
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}

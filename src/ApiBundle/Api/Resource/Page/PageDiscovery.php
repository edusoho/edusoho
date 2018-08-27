<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
                    $timeRange = $this->getTimeZoneByLastDays($discoverySetting['data']['lastDays']);
                    $conditions['startTime'] = $timeRange['startTime'];
                    $conditions['endTime'] = $timeRange['endTime'];
                }

                $conditions = array('parentId' => 0, 'status' => 'published', 'courseSetStatus' => 'published', 'excludeTypes' => array('reservation'));
                $conditions['categoryId'] = $discoverySetting['data']['categoryId'];
                $sort = $this->getSortByStr($discoverySetting['data']['sort']);
                $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
                $discoverySetting['data']['items'] = $this->getCourseByConditions($conditions, $sort, 0, $limit);
            }
        }

        return $discoverySettings;
    }

    public function getCourseByConditions($conditions, $sort, $start, $limit)
    {
        $courses = array();
        if (array_key_exists('studentNum', $sort)) {
            $courses = $this->getCourseService()->searchByStudentNumAndTimeZone($conditions, $start, $limit);
        }

        if (array_key_exists('createdTime', $sort)) {
            unset($conditions['startTime']);
            unset($conditions['endTime']);
            $courses = $this->getCourseService()->searchWithJoinTableConditions($conditions, $sort, $start, $limit);
        }

        if (array_key_exists('rating', $sort)) {
            $courses = $this->getCourseService()->searchByRatingAndTimeZone($conditions, $start, $limit);
        }
        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));
        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        return $courses;
    }

    protected function getTimeZoneByLastDays($lastDays)
    {
        if (!is_numeric($lastDays) || $lastDays <= 0) {
            throw new BadRequestHttpException('LastDays is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        return array('startTime' => strtotime(date('Y-m-d', time() - $lastDays * 24 * 60 * 60)), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600)));
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

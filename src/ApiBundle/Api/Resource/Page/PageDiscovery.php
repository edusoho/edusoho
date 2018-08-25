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
        $settingName = "{$portal}-published-discovery";
        if (!empty($params['preview'])) {
            $token = $this->getTokenService()->verifyToken('qrcode_url', $token);
            if (empty($token)) {
                throw new \Exception('Error Processing Request', 1);
            }
            $user = $this->getUserService()->getUser($token['userId']);
            if (!in_array('ROLE_SUPER_ADMIN', $user['roles']) && !in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
                throw new \Exception('Error Processing Request', 1);
            }
            $settingName = "{$portal}-draft-discovery";
        }
        $settings = $this->getSettingService()->get($settingName);
        foreach ($settings as &$setting) {
            if ('course_list' == $setting['type'] && 'condition' == $setting['data']['sourceType']) {
                $setting['data']['items'] = $this->searchCourseByConditions($setting['data']);
            }
        }
    }

    protected function searchCourseByConditions($params)
    {
        $conditions = array('parentId' => 0, 'status' => 'published', 'courseSetStatus' => 'published', 'excludeTypes' => array('reservation'));
        $sort = $this->getSortByStr($params['sort']);
        $limit = empty($params['limit']) ? 4 : $params['limit'];
        $conditions['categoryId'] = $params['categoryId'];
        $conditions['startTime'] = $params['startTime'];
        $conditions['endTime'] = $params['endTime'];
        if ('createdTime' == $sort[0]) {
            $courses = $this->getCourseService()->searchWithJoinTableConditions(
                $conditions,
                $sort,
                0,
                $params['limit']
            );
        }

        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));
        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        return $courses;
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

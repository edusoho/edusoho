<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Annotation\Access;

class PageSetting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal, $type)
    {
        $mode = $request->query->get('mode', 'published');

        if (!in_array($mode, array('draft', 'published'))) {
            throw new BadRequestHttpException('Mode is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $type = 'course' == $type ? 'courseCondition' : $type;
        if (!in_array($type, array('courseCondition', 'discovery'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        if (!in_array($portal, array('h5', 'miniprogram'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $method = 'get'.ucfirst($type);

        return $this->$method($portal, $mode);
    }

    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request, $portal)
    {
        $mode = $request->query->get('mode');
        if (!in_array($mode, array('draft', 'published'))) {
            throw new BadRequestHttpException('Mode is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $type = $request->query->get('type');
        if (!in_array($type, array('courseCondition', 'discovery'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        if (!in_array($portal, array('h5', 'miniprogram'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $content = $request->request->all();
        $method = 'add'.ucfirst($type);

        return $this->$method($portal, $mode, $content);
    }

    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function remove(ApiRequest $request, $portal, $type)
    {
        $mode = $request->query->get('mode');
        if ('draft' != $mode) {
            throw new BadRequestHttpException('Mode is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        if (!in_array($type, array('courseCondition', 'discovery'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        if (!in_array($portal, array('h5', 'miniprogram'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }
        $method = 'remove'.ucfirst($type);

        return $this->$method($portal, $mode);
    }

    protected function removeDiscovery($portal, $mode = 'draft')
    {
        return $this->getSettingService()->delete("{$portal}_{$mode}_discovery");
    }

    protected function removeCourseCondition($portal, $mode = 'draft')
    {
        return $this->getSettingService()->delete("{$portal}_{$mode}_courseCondition");
    }

    protected function addDiscovery($portal, $mode = 'draft', $content = array())
    {
        $this->getSettingService()->set("{$portal}_{$mode}_discovery", $content);

        return $this->getDiscovery($portal, $mode);
    }

    protected function addCourseCondition($portal, $mode = 'draft', $content = array())
    {
        $this->getSettingService()->set("{$portal}_{$mode}_courseCondition", $content);

        return $this->getCourseCondition($portal, $mode);
    }

    protected function getDiscovery($portal, $mode = 'published')
    {
        $user = $this->getCurrentUser();
        if ('draft' == $mode && !$user->isAdmin()) {
            throw new AccessDeniedHttpException();
        }

        $discoverySettings = $this->getSettingService()->get("{$portal}_{$mode}_discovery", array());
        if (empty($discoverySettings)) {
            $discoverySettings = $this->getSettingService()->get("{$portal}_published_discovery", array());
        }
        foreach ($discoverySettings as &$discoverySetting) {
            if ('course_list' == $discoverySetting['type'] && 'condition' == $discoverySetting['data']['sourceType']) {
                $timeRange = $this->getTimeZoneByLastDays($discoverySetting['data']['lastDays']);
                // $conditions = array('parentId' => 0, 'status' => 'published', 'courseSetStatus' => 'published', 'excludeTypes' => array('reservation'));
                $conditions['categoryId'] = $discoverySetting['data']['categoryId'];
                $conditions['startTime'] = $timeRange['startTime'];
                $conditions['endTime'] = $timeRange['endTime'];
                $sort = $this->getSortByStr($discoverySetting['data']['sort']);
                $limit = empty($discoverySetting['data']['limit']) ? 4 : $discoverySetting['data']['limit'];
                $discoverySetting['data']['items'] = $this->getCourseByConditions($conditions, $sort, 0, $limit);
            }
        }

        return $discoverySettings;
    }

    public function getCourseByConditions($conditions, $sort, $start, $limit)
    {
        if (array_key_exists('studentNum', $sort)) {
            $courses = $this->getCourseService()->searchByStudentNumAndTimeZone($conditions, $start, $limit);
        }

        if (array_key_exists('createdTime', $sort)) {
            $courses = $this->getCourseService()->searchWithJoinTableConditions($conditions, $sort, $start, $limit);
        }

        if (array_key_exists('rating', $sort)) {
            $courses = $this->getCourseService()->searchByRatingAndTimeZone($conditions, $sort, $start, $limit);
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

    protected function getCourseCondition($portal, $mode = 'published')
    {
        $group = $this->getCategoryService()->getGroupByCode('course');

        return array(
            'title' => '所有课程',
            array(
                'type' => 'category',
                'moduleType' => 'tree',
                'text' => '分类',
                'data' => $this->getCategoryService()->findCategoriesByGroupIdAndParentId($group['id'], 0),
            ),
            array(
                'type' => 'courseType',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => array(
                    array(
                        'type' => 'normal',
                        'text' => '课程',
                    ),
                    array(
                        'type' => 'live',
                        'text' => '直播',
                    ),
                ),
            ),
            array(
                'type' => 'sort',
                'moduleType' => 'normal',
                'text' => '课程类型',
                'data' => array(
                    array(
                        'type' => 'recommendedSeq',
                        'text' => '推荐',
                    ),
                    array(
                        'type' => '-studentNum',
                        'text' => '热门',
                    ),
                    array(
                        'type' => '-createdTime',
                        'text' => '最新',
                    ),
                ),
            ),
        );
    }

    protected function getCategoryService()
    {
        return $this->service('Taxonomy:CategoryService');
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}

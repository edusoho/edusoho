<?php
namespace Topxia\Service\Courseware\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Courseware\CoursewareService;
use Topxia\Common\ArrayToolkit;

class CoursewareServiceImpl extends BaseService implements CoursewareService
{
    public function getCourseware($id)
    {

    }

    public function searchCoursewares(array $conditions, $orderBy, $start, $limit)
    {
        return $this->getCoursewareDao()->searchCoursewares($conditions,$orderBy,$start,$limit);
    }

    public function searchCoursewaresCount($conditions)
    {
        $count = $this->getCoursewareDao()->searchCoursewaresCount($conditions);
        return $count;
    }

    public function createCourseware($courseware)
    {
        if (empty($courseware)) {
            $this->createServiceException("课件内容为空，创建课件失败！");
        }

        $courseware = $this->filterCoursewareFields($courseware);
        
        $courseware = $this->getCoursewareDao()->addCourseware($courseware);

        $this->getLogService()->info('courseware', 'create', "创建课件《({$courseware['title']})》({$courseware['id']})");
        
        return $courseware;
    }

    public function updateCourseware($id,$courseware)
    {

    }

    public function deleteCourseware($id)
    {

    }

    private function getCoursewareDao()
    {
        return $this->createDao('Courseware.CoursewareDao');
    }

    private function filterCoursewareFields($courseware)
    {
        $courseware = ArrayToolkit::parts($courseware,array('mainKnowledgeId','releatedKnowledgeIds','tagIds','source','title','image','categoryId','url'));
        $courseware['type'] = 'video';
        $courseware['userId'] = $this->getCurrentUser()->id;
        $courseware['createdTime'] = time();
        return $courseware;
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
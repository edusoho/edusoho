<?php 

namespace Topxia\Service\Courseware\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Courseware\CoursewareService;
use Topxia\Common\ArrayToolkit;

class CoursewareServiceImpl extends BaseService implements CoursewareService
{
    public function getCourseware($id)
    {
        return $this->getCoursewareDao()->getCourseware($id);
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
        $courseware = $this->filterCoursewareFields($courseware);
        return $this->getCoursewareDao()->updateCourseware($id,$courseware);
    }

    public function deleteCourseware($id)
    {
        $checkCourseware = $this->getCourseware($id);

        if(empty($checkCourseware)){
            throw $this->createServiceException("课件不存在，操作失败。");
        }

        $res = $this->getCoursewareDao()->deleteCourseware($id);
        $this->getLogService()->info('Courseware', 'delete', "课件#{$id}永久删除");
        return true;
    }

    public function deleteCoursewaresByIds($ids)
    {
        if(count($ids) == 1){
            $this->deleteCourseware($ids[0]);
        }else{
            foreach ($ids as $id) {
                $this->deleteCourseware($id);
            }
        }
        return true;
    }

    private function getCoursewareDao()
    {
        return $this->createDao('Courseware.CoursewareDao');
    }

    private function filterCoursewareFields($courseware)
    {
        $courseware = ArrayToolkit::parts($courseware,array('knowledgeIds','mainKnowledgeId','relatedKnowledgeIds','tagIds','source','title','image','categoryId','url'));
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
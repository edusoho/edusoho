<?php

namespace ApiBundle\Api\Resource\UploadFile;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MaterialService;

class UploadFileUsage extends AbstractResource
{
    /**
     * @Access(permissions="admin_v2_cloud_resource")
     */
    public function search(ApiRequest $request, $fileId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $courseSetTitle = $request->query->get('courseSetTitle');
        if (!empty($courseSetTitle)) {
            $courseSets = $this->getCourseSetService()->searchCourseSets(['title' => $courseSetTitle], [], 0, PHP_INT_MAX, ['id']);
            if (empty($courseSets)) {
                return $this->makePagingObject([], 0, $offset, $limit);
            }
        }
        $conditions = ['fileId' => $fileId, 'type' => 'course', 'excludeLessonId' => 0, 'copyId' => 0];
        if (!empty($courseSets)) {
            $conditions['courseSetIds'] = array_column($courseSets, 'id');
        }
        $usages = $this->getCourseMaterialService()->searchMaterialCountGroupByCourseSetId($conditions, $offset, $limit);
        if (!empty($usages)) {
            $materialConditions = $conditions;
            $materialConditions['courseSetIds'] = array_column($usages, 'courseSetId');
            $courseMaterials = $this->getCourseMaterialService()->searchMaterials($materialConditions, [], 0, PHP_INT_MAX);
            $courseMaterials = ArrayToolkit::group($courseMaterials, 'courseSetId');
            $copyCourseMaterials = $this->getCourseMaterialService()->searchMaterials(['copyIds' => array_column($courseMaterials, 'id')], [], 0, PHP_INT_MAX);
            $copyCourseMaterials = ArrayToolkit::group($copyCourseMaterials, 'copyId');
            $courseSets = $this->getCourseSetService()->searchCourseSets(['ids' => array_column($usages, 'courseSetId')], [], 0, PHP_INT_MAX, ['id', 'title', 'defaultCourseId']);
            $courseSets = array_column($courseSets, null, 'id');
            foreach ($usages as &$usage) {
                $usage['courseSetTitle'] = $courseSets[$usage['courseSetId']]['title'] ?? '';
                $usage['defaultCourseId'] = $courseSets[$usage['courseSetId']]['defaultCourseId'] ?? 0;
                $copyCount = 0;
                foreach ($courseMaterials[$usage['courseSetId']] as $courseMaterial) {
                    $copyCount += empty($copyCourseMaterials[$courseMaterial['id']]) ? 0 : count($copyCourseMaterials[$courseMaterial['id']]);
                }
                $usage['usedCount'] += $copyCount;
            }
        }
        $total = $this->getCourseMaterialService()->countDistinctCourseSet($conditions);

        return $this->makePagingObject($usages, $total, $offset, $limit);
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return MaterialService
     */
    private function getCourseMaterialService()
    {
        return $this->service('Course:MaterialService');
    }
}

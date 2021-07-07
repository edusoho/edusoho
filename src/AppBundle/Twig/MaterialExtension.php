<?php

namespace AppBundle\Twig;

use Biz\Course\Service\MaterialService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MaterialExtension extends \Twig_Extension
{
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container, $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('convert_materials', [$this, 'convertMaterials']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('find_materials_by_activity_id_and_source', [$this, 'findMaterialsByActivityIdAndSource']),
        ];
    }

    public function findMaterialsByActivityIdAndSource($activityId, $source)
    {
        if (empty($activityId)) {
            return [];
        }

        $conditions = [
            'lessonId' => $activityId,
            'source' => $source,
        ];

        $activity = $this->getActivityService()->getActivity($activityId, true);

        if (isset($activity['ext']['fileIds']) && $activity['mediaType'] === 'live') {
            $conditions['fileIds'] = $activity['ext']['fileIds'] ?: [-1];
        }

        return $this->getMaterialService()->searchMaterials(
            $conditions,
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );
    }

    public function convertMaterials($materials)
    {
        $newMaterials = [];
        foreach ($materials as $material) {
            $id = empty($material['fileId']) ? $material['link'] : $material['fileId'];
            $newMaterials[$id] = ['id' => $material['fileId'], 'size' => $material['fileSize'], 'name' => $material['title'], 'link' => $material['link']];
        }

        return $newMaterials;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->biz->service('Course:MaterialService');
    }

    public function getName()
    {
        return 'web_material_twig';
    }
}

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
        return array(
            new \Twig_SimpleFilter('convert_materials', array($this, 'convertMaterials')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('find_materials_by_activity_id_and_source', array($this, 'findMaterialsByActivityIdAndSource')),
        );
    }

    public function findMaterialsByActivityIdAndSource($activityId, $source)
    {
        if (empty($activityId)) {
            return array();
        }

        return $this->getMaterialService()->findMaterialsByLessonIdAndSource($activityId, $source);
    }

    public function convertMaterials($materials)
    {
        $newMaterials = array();
        foreach ($materials as $material) {
            $id = empty($material['fileId']) ? $material['link'] : $material['fileId'];
            $newMaterials[$id] = array('id' => $material['fileId'], 'size' => $material['fileSize'], 'name' => $material['title'], 'link' => $material['link']);
        }

        return $newMaterials;
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

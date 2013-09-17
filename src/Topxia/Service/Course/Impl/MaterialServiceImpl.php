<?php
namespace Topxia\Service\Course\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\MaterialService;
use Topxia\Common\ArrayToolkit;

class MaterialServiceImpl extends BaseService implements MaterialService
{

	public function uploadMaterial($material)
	{
		if (!ArrayToolkit::requireds($material, array('courseId', 'file'))) {
			throw $this->createServiceException('参数缺失，上传失败！');
		}

		$course = $this->getCourseService()->getCourse($material['courseId']);
		if (empty($course)) {
			throw $this->createServiceException('课程不存在，上传资料失败！');
		}
		$file = $this->getFileService()->uploadFile('course_private', $material['file']);
		$fields = array(
			'courseId' => $material['courseId'],
			'lessonId' => empty($material['lessonId']) ? 0 : $material['lessonId'],
			'title' => empty($material['title']) ? '' : $material['title'],
			'description'  => empty($material['description']) ? '' : $material['description'],
			'fileUri' => $file['uri'],
			'fileMime' => $file['mime'],
			'fileSize' => $file['size'],
			'userId' => $this->getCurrentUser()->id,
			'createdTime' => time(),
		);

		return $this->getMaterialDao()->addMaterial($fields);
	}

	public function deleteMaterial($courseId, $materialId)
	{
		$material = $this->getMaterialDao()->getMaterial($materialId);
		if (empty($material) or $material['courseId'] != $courseId) {
			throw $this->createNotFoundException('课程资料不存在，删除失败。');
		}
		$this->getMaterialDao()->deleteMaterial($materialId);
	}

	public function getMaterial($courseId, $materialId)
	{
		$material = $this->getMaterialDao()->getMaterial($materialId);
		if (empty($material) or $material['courseId'] != $courseId) {
			return null;
		}
		return $material;
	}

	public function findCourseMaterials($courseId, $start, $limit)
	{
		return $this->getMaterialDao()->findMaterialsByCourseId($courseId, $start, $limit);
	}

    public function findLessonMaterials($lessonId, $start, $limit)
    {
        return $this->getMaterialDao()->findMaterialsByLessonId($lessonId, $start, $limit);
    }

	public function getMaterialCount($courseId)
	{
		return $this->getMaterialDao()->getMaterialCountByCourseId($courseId);
	}

    private function getMaterialDao()
    {
    	return $this->createDao('Course.CourseMaterialDao');
    }


    private function getCourseService()
    {
    	return $this->createService('Course.CourseService');
    }

    private function getFileService()
    {
    	return $this->createService('Content.FileService');
    }

}
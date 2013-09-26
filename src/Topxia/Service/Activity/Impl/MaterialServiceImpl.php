<?php
namespace Topxia\Service\Activity\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Activity\MaterialService;
use Topxia\Common\ArrayToolkit;

class MaterialServiceImpl extends BaseService implements MaterialService
{

	public function uploadMaterial($material)
	{
		if (!ArrayToolkit::requireds($material, array('activityId', 'file'))) {
			throw $this->createServiceException('参数缺失，上传失败！');
		}

		$course = $this->getActivityService()->getActivity($material['activityId']);
		if (empty($course)) {
			throw $this->createServiceException('课程不存在，上传资料失败！');
		}

		$file = $this->getFileService()->uploadFile('course_private', $material['file']);

		$fields = array(
			'activityId' => $material['activityId'],
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
		if (empty($material) or $material['activityId'] != $courseId) {
			throw $this->createNotFoundException('课程资料不存在，删除失败。');
		}
		$this->getMaterialDao()->deleteMaterial($materialId);
	}

	public function getMaterial($courseId, $materialId)
	{
		$material = $this->getMaterialDao()->getMaterial($materialId);
		if (empty($material) or $material['activityId'] != $courseId) {
			return null;
		}
		return $material;
	}

	public function findActivityMaterials($courseId, $start, $limit)
	{
		return $this->getMaterialDao()->findMaterialsByActivityId($courseId, $start, $limit);
	}


	public function getMaterialCount($courseId)
	{
		return $this->getMaterialDao()->getMaterialCountByActivityId($courseId);
	}

    private function getMaterialDao()
    {
    	return $this->createDao('Activity.ActivityMaterialDao');
    }


    private function getActivityService()
    {
    	return $this->createService('Activity.ActivityService');
    }

    private function getFileService()
    {
    	return $this->createService('Content.FileService');
    }

}
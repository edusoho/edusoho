<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class OpenCourseFileManageController extends BaseController
{
    public function indexAction()
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $type = $request->query->get('type');
        $type = 'opencourselesson';

        $conditions = array(
            'targetType' => $type,
            'targetId'   => $course['id']
        );

        if (array_key_exists('targetId', $conditions) && !empty($conditions['targetId'])) {
            $course = $this->getCourseService()->getCourse($conditions['targetId']);

            if ($course['parentId'] > 0 && $course['locked'] == 1) {
                $conditions['targetId'] = $course['parentId'];
            }
        }

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            'latestCreated',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($files as $key => $file) {
            $files[$key]['metas2'] = json_decode($file['metas2'], true) ?: array();

            $files[$key]['convertParams'] = json_decode($file['convertParams']) ?: array();

            $useNum = $this->getCourseService()->searchLessonCount(array('mediaId' => $file['id']));

            $manageFilesUseNum = $this->getMaterialService()->getMaterialCountByFileId($file['id']);

            if ($files[$key]['targetType'] == 'coursematerial') {
                $files[$key]['useNum'] = $manageFilesUseNum;
            } else {
                $files[$key]['useNum'] = $useNum;
            }
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'updatedUserId'));

        $storageSetting = $this->getSettingService()->get("storage");
        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'type'           => $type,
            'course'         => $course,
            'courseLessons'  => $files,
            'users'          => ArrayToolkit::index($users, 'id'),
            'paginator'      => $paginator,
            'now'            => time(),
            'storageSetting' => $storageSetting
        ));
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}

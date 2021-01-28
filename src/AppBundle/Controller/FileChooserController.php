<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\MediaParser\ParserProxy;

/**
 * Class MediaProccessController
 * 用来处理活动中文件选取(上传，从资料库选择，从课程文件选择，导入网络文件)逻辑.
 */
class FileChooserController extends BaseController
{
    public function materialChooseAction(Request $request)
    {
        $currentUser = $this->getUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }
        $conditions = $request->query->all();
        $conditions = $this->filterMaterialConditions($conditions, $currentUser);
        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );
        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));
        $createdUsers = ArrayToolkit::index($createdUsers, 'id');

        return $this->render(
            'file-chooser/widget/choose-table.html.twig',
            array(
                'files' => $files,
                'batch' => $request->query->get('batch', 0),
                'createdUsers' => $createdUsers,
                'paginator' => $paginator,
            )
        );
    }

    public function findMySharingContactsAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $mySharingContacts = $this->getUploadFileService()->findMySharingContacts($user['id']);

        return $this->createJsonResponse($mySharingContacts);
    }

    public function courseFileChooseAction(Request $request, $courseId)
    {
        $currentUser = $this->getUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $query = $request->query->all();
        $courseMaterials = $this->findCourseMaterials($request, $courseId);

        $conditions = array();
        $conditions['page'] = $request->query->get('page', 1);
        $conditions['ids'] = $courseMaterials ? ArrayToolkit::column($courseMaterials, 'fileId') : array(-1);

        $batchCreate = $request->query->get('batch', 0);
        if ($batchCreate) {
            //计划批量添加课时只支持以下文件类型
            $conditions['types'] = array('document', 'video', 'audio', 'ppt', 'flash');
        } else {
            $conditions['type'] = (empty($query['type']) || 'all' == $query['type']) ? null : $query['type'];
        }
        $conditions['filenameLike'] = empty($query['keyword']) ? null : $query['keyword'];

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));
        $createdUsers = ArrayToolkit::index($createdUsers, 'id');

        return $this->render(
            'file-chooser/widget/choose-table.html.twig',
            array(
                'files' => $files,
                'batch' => $batchCreate,
                'createdUsers' => $createdUsers,
                'paginator' => $paginator,
            )
        );
    }

    public function importAction(Request $request, $courseId)
    {
        $url = $request->query->get('url');

        $proxy = new ParserProxy();
        $item = $proxy->parseItem($url);

        return $this->createJsonResponse($item);
    }

    protected function filterMaterialConditions($conditions, $currentUser)
    {
        $conditions['status'] = 'ok';
        $conditions['currentUserId'] = $currentUser['id'];

        $conditions['noTargetType'] = 'attachment';
        if (!empty($conditions['keyword'])) {
            $conditions['filename'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }

        if (isset($conditions['tagId']) && empty($conditions['tagId'])) {
            unset($conditions['tagId']);
        }

        if (!empty($conditions['batch'])) {
            $conditions['types'] = array('document', 'video', 'audio', 'ppt', 'flash');
        } else {
            $conditions['type'] = (empty($conditions['type']) || ('all' == $conditions['type'])) ? null : $conditions['type'];
        }

        return $conditions;
    }

    protected function findCourseMaterials($request, $courseId)
    {
        $courseType = $request->query->get('courseType', 'course');

        $conditions = array('type' => $courseType);
        if ('openCourse' == $courseType) {
            $conditions['courseId'] = $courseId;
        } else {
            $course = $this->getCourseService()->getCourse($courseId);
            $conditions['courseSetId'] = $course['courseSetId'];
        }

        //FIXME 同一个courseId下文件可能存在重复，所以需考虑去重，但没法直接根据groupbyFileId去重（sql_mode）
        $courseMaterials = $this->getMaterialService()->searchMaterials(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $courseMaterials;
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}

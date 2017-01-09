<?php
/**
 * User: Edusoho V8
 * Date: 31/10/2016
 * Time: 11:42
 */

namespace AppBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Biz\File\Service\UploadFileService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Component\MediaParser\ParserProxy;

/**
 * Class MediaProccessController
 * 用来处理活动中文件选取(上传，从资料库选择，从课程文件选择，导入网络文件)逻辑
 * @package AppBundle\Controller
 */
class FileChooserController extends BaseController
{
    public function materialChooseAction(Request $request)
    {
        $currentUser = $this->getUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('您无权访问此页面'));
        }
        $conditions = $request->query->all();
        $conditions = $this->filterMaterialConditions($conditions, $currentUser);
        $paginator  = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            10
        );
        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));
        $createdUsers = ArrayToolkit::index($createdUsers, 'id');

        return $this->render('file-chooser/widget/choose-table.html.twig', array(
            'files'        => $files,
            'createdUsers' => $createdUsers,
            'paginator'    => $paginator
        ));
    }

    public function findMySharingContactsAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('您无权访问此页面'));
        }

        $mySharingContacts = $this->getUploadFileService()->findMySharingContacts($user['id']);
        return $this->createJsonResponse($mySharingContacts);
    }

    public function courseFileChooseAction(Request $request, $courseId)
    {
        $currentUser = $this->getUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $query           = $request->query->all();
        $courseMaterials = $this->findCourseMaterials($request, $courseId);

        $conditions         = array();
        $conditions['ids']  = $courseMaterials ? ArrayToolkit::column($courseMaterials, 'fileId') : array(-1);
        $conditions['type'] = (empty($query['type']) || $query['type'] == 'all') ? null : $query['type'];

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            10
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));
        $createdUsers = ArrayToolkit::index($createdUsers, 'id');

        return $this->render('file-chooser/widget/choose-table.html.twig', array(
            'files'        => $files,
            'createdUsers' => $createdUsers,
            'paginator'    => $paginator
        ));
    }

    public function importAction(Request $request)
    {
        $url = $request->query->get('url');

        $proxy = new ParserProxy();
        $item  = $proxy->parseItem($url);

        return $this->createJsonResponse($item);
    }

    protected function filterMaterialConditions($conditions, $currentUser)
    {
        $conditions['status']        = 'ok';
        $conditions['currentUserId'] = $currentUser['id'];

        $conditions['noTargetType'] = 'attachment';
        if (!empty($conditions['keyword'])) {
            $conditions['filename'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }
        $conditions['type'] = (empty($conditions['type']) || ($conditions['type'] == 'all')) ? null : $conditions['type'];
        return $conditions;
    }

    protected function findCourseMaterials($request, $courseId)
    {
        $query      = $request->query->all();
        $conditions = array(
            'type'     => empty($query['courseType']) ? null : $query['courseType'],
            'courseId' => $courseId
        );

        //FIXME 同一个courseId下文件可能存在重复，所以需考虑去重，但没法直接根据groupbyFileId去重（sql_mode）
        // $courseMaterials = $this->getMaterialService()->searchMaterialsGroupByFileId(
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

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}

<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\CloudClientFactory;

class CourseMaterialController extends CourseBaseController
{
    public function indexAction(Request $request, $id)
    {
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $id);
        if ($response) {
            return $response;
        }

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->getMaterialCount($id),
            20
        );

        $materials = $this->getMaterialService()->findCourseMaterials(
            $id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $lessons = ArrayToolkit::index($lessons, 'id');

        return $this->render("TopxiaWebBundle:CourseMaterial:index.html.twig", array(
            'course' => $course,
            'member' => $member,
            'lessons'=>$lessons,
            'materials' => $materials,
            'paginator' => $paginator,
        ));
    }

    public function downloadAction(Request $request, $courseId, $materialId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('course_materials',array('id' => $courseId)));
        }

        if ($member && $member['levelId'] > 0) {
            if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
                return $this->redirect($this->generateUrl('course_show',array('id' => $id)));
            }
        }

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);
        if (empty($material)) {
            throw $this->createNotFoundException();
        }

        $file = $this->getUploadFileService()->getFile($material['fileId']);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['storage'] == 'cloud') {
            $factory = new CloudClientFactory();
            $client = $factory->createClient();
            $client->download($client->getBucket(), $file['hashId'], 3600, $file['filename']);
        } else {
            return $this->createPrivateFileDownloadResponse($request, $file);
        }
    }

    public function deleteAction(Request $request, $id, $materialId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $this->getCourseService()->deleteCourseMaterial($id, $materialId);
        return $this->createJsonResponse(true);
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function createPrivateFileDownloadResponse(Request $request, $file)
    {

        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();

        $file['filename'] = urlencode($file['filename']);
        if (preg_match("/MSIE/i", $request->headers->get('User-Agent'))) {
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$file['filename'].'"');
        } else {
            $response->headers->set('Content-Disposition', "attachment; filename*=UTF-8''".$file['filename']);
        }

        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);
        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }
}
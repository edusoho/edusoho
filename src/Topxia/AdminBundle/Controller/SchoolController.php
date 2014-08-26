<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;


class SchoolController extends BaseController
{
	public function schoolSettingAction(Request $request) 
	{
		$school = $this->getSettingService()->get('school', array());

		if ($request->getMethod() == 'POST') {
            $school = $request->request->all();
            $this->getSettingService()->set('school', $school);
            $this->getLogService()->info('school', 'update_settings', "更新学校设置", $school);
            $this->setFlashMessage('success', '学校信息设置已保存！');
        }
      
		return $this->render('TopxiaAdminBundle:School:school-setting.html.twig', array(
            'school' => $school
        ));
	}

	public function classSettingAction(Request $request) 
	{
        $conditions = $request->query->All();
            
            $paginator = new Paginator(
            $this->get('request'),
            $this->getClassesService()->searchClassCount($conditions),
            5);

        $classes = $this->getClassesService()->searchClasses(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );  

		return $this->render('TopxiaAdminBundle:School:class-setting.html.twig',array(
            'classes' => $classes
        ));
	}

    public function classCreateAction(Request $request)
    {

        return $this->render('TopxiaAdminBundle:School:class-create-modal.html.twig');
    }

	public function homePageUploadAction(Request $request)
	{
		 $file = $request->files->get('homePage');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'school-homepage.' . $file->getClientOriginalExtension();
        
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/school";
        $file = $file->move($directory, $filename);

        $school = $this->getSettingService()->get('school', array());

        $school['homePage'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/school/{$filename}";
        $school['homePage'] = ltrim($school['homePage'], '/');

        $this->getSettingService()->set('school', $school);

        $this->getLogService()->info('school', 'update_settings', "更新学校首页图片", array('homePage' => $school['homePage']));

        $response = array(
            'path' => $school['homePage'],
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($school['homePage']),
        );

        return new Response(json_encode($response));
	}
	protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
}
<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Topxia\Common\SimpleValidator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ImporterController extends BaseController
{   
	public function checkStudentsAction(Request $request)
	{
		return $this->render('TopxiaAdminBundle:Importer:check-students.html.twig', array(
        ));
	}

	public function importStudentsAction()
	{

	}
}

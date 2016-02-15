<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\Importer\ImporterProcessorFactory;

class ImporterController extends BaseController
{
	public function ValidateExcelInfoAction(Request $request, $target)
    {
        $processor = $this->getImporterProcessor($target['type']);
        $targetObject = $processor->tryManage($target['id']);

        $data = array();
        $data['excel_example'] = $processor->getExcelExample();
        $data['excel_validate_url'] = $processor->getExcelInfoValidateUrl();
        $data['excel_import_url'] = $processor->getExcelInfoImportUrl();

        if ($request->getMethod() == 'POST') {

            $file = $request->files->get('excel');

            $errorMessage = $processor->validateExcelFile($file);
            if (!empty($errorMessage)) {
                $this->setFlashMessage('danger', $errorMessage);
                return $this->render('TopxiaWebBundle:Importer:import.step1.html.twig', array(
                    'targetObject' => $targetObject,
                    'data' => $data
                ));
            }

            
            $repeatInfo = $processor->checkRepeatData();
       
            if($repeatInfo){
                return $this->render('TopxiaWebBundle:Importer:import.step2.html.twig', array(
                    'errorInfo' => $repeatInfo,
                    'targetObject' => $targetObject,
                    'data' => $data
                ));

            }

            $userData = $processor->getUserData();

            return $this->render('TopxiaWebBundle:Importer:import.step2.html.twig', array(
                'userCount' => $userData['userCount'],
                'errorInfo' => $userData['errorInfo'],
                'checkInfo' => $userData['checkInfo'],
                'allUserData' => $userData['allUserData'],
                'data' => $data,
                'targetObject' => $targetObject,
            ));

        }

        return $this->render('TopxiaWebBundle:Importer:import.step1.html.twig', array(
            'data' => $data,
            'targetObject' => $targetObject,
        ));
    }

    public function importExcelDataAction(Request $request, $target)
    {   
        $processor = $this->getImporterProcessor($target['type']);
        $targetObject = $processor->tryManage($target['id']);

        $userData = $request->request->get("data");
        $userData = json_decode($userData,true);

        $currentUser = $this->getCurrentUser();
        $userUrl = $this->generateUrl('user_show', array('id'=>$currentUser['id']), true);
        
        $validateRout = $processor->getExcelInfoValidateUrl();
        $count = $processor->excelDataImporting($targetObject, $userData, $userUrl);
        
        return $this->render('TopxiaWebBundle:Importer:import.step3.html.twig', 
            array(
                'targetObject' => $targetObject,
                'count' => $count,
                'validateRout' => $validateRout,
            )
        );
    }


    protected function getImporterProcessor($targetType)
    {
        return ImporterProcessorFactory::create($targetType);
    }

}

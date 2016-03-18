<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\Importer\ImporterProcessorFactory;

class ImporterController extends BaseController
{
    public function ValidateExcelInfoAction(Request $request, $target)
    {
        $processor    = $this->getImporterProcessor($target['type']);
        $targetObject = $processor->tryManage($target['id']);

        $data                       = array();
        $data['excel_example']      = $processor->getExcelExample();
        $data['excel_validate_url'] = $processor->getExcelInfoValidateUrl();
        $data['excel_import_url']   = $processor->getExcelInfoImportUrl();

        if ($request->getMethod() == 'POST') {
            $file = $request->files->get('excel');

            $errorMessage = $processor->validateExcelFile($file);

            if (!empty($errorMessage)) {
                $this->setFlashMessage('danger', $errorMessage);
                return $this->render('TopxiaWebBundle:Importer:import.step1.html.twig', array(
                    'targetObject' => $targetObject,
                    'data'         => $data
                ));
            }

            $repeatInfo = $processor->checkRepeatData();

            if ($repeatInfo) {
                return $this->render('TopxiaWebBundle:Importer:import.step2.html.twig', array(
                    'errorInfo'    => $repeatInfo,
                    'targetObject' => $targetObject,
                    'data'         => $data
                ));
            }

            $userData = $processor->getUserData();

            if (empty($userData['errorInfo'])) {
                $passedRepeatInfo = $processor->checkPassedRepeatData();

                if ($passedRepeatInfo) {
                    return $this->render('TopxiaWebBundle:Importer:import.step2.html.twig', array(
                        'errorInfo'    => $passedRepeatInfo,
                        'targetObject' => $targetObject,
                        'data'         => $data
                    ));
                }
            }

            $allUserData = json_decode($userData['allUserData'], true);
            $allUserData = array_chunk($allUserData, count($allUserData) / 100 + 1);
            $progress    = array();

            foreach ($allUserData as $index => $userDataInfo) {
                $progress[] = count($userDataInfo);
            }

            return $this->render('TopxiaWebBundle:Importer:import.step2.html.twig', array(
                'userCount'    => $userData['userCount'],
                'errorInfo'    => $userData['errorInfo'],
                'checkInfo'    => $userData['checkInfo'],
                'allUserData'  => json_encode($allUserData),
                'progress'     => json_encode($progress),
                'data'         => $data,
                'targetObject' => $targetObject

            ));
        }

        return $this->render('TopxiaWebBundle:Importer:import.step1.html.twig', array(
            'data'         => $data,
            'targetObject' => $targetObject
        ));
    }

    public function importExcelDataAction(Request $request, $targetId, $targetType)
    {
        $processor    = $this->getImporterProcessor($targetType);
        $targetObject = $processor->tryManage($targetId);

        $userData = $request->request->get("data");
        $userData = json_decode($userData, true);

        $currentUser = $this->getCurrentUser();
        $userUrl     = $this->generateUrl('user_show', array('id' => $currentUser['id']), true);

        $validateRout = $processor->getExcelInfoValidateUrl();

        $count = $processor->excelDataImporting($targetObject, $userData, $userUrl);

        return $this->createJsonResponse(array(
            'count'        => $count,
            'validateRout' => $validateRout
        ));
    }

    public function importModalAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Importer:userimport.modal.html.twig');
    }

    protected function getImporterProcessor($targetType)
    {
        return ImporterProcessorFactory::create($targetType);
    }
}

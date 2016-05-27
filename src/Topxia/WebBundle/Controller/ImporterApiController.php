<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Importer\CheckerFactory;
use Topxia\Service\Importer\ImporterFactory;

class ImporterApiController extends BaseController
{
    public function checkAction(Request $request, $type)
    {
        $checker = CheckerFactory::create($type);
        $checkResult = $checker->check($request);
        return $this->createJsonResponse($checkResult);
    }

    public function importAction(Request $request)
    {
        $importerType = $request->request->get('type');
        $importer = ImporterFactory::create($importerType);
        return $this->createJsonResponse($importer->import($request->request->all()));
    }

    public function templateAction(Request $request)
    {
        $type = $request->query->get('type');

        $mapper = array(
            'user' => 'userimporterbundle/controller/user-importer/template/step1.html'
        );

        $templatePath = '';

        if(array_key_exists($type, $mapper)){
            $templatePath = $mapper[$type];
        }

        return $this->createJsonResponse($templatePath);
    }
}

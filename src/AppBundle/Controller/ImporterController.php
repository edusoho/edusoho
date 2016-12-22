<?php

namespace AppBundle\Controller;

use Biz\Importer\ImporterFactory;
use Symfony\Component\HttpFoundation\Request;


class ImporterController extends BaseController
{
    public function checkAction(Request $request, $type)
    {
        $importer = ImporterFactory::create($type);
        $importer->tryImport($request);
        $checkResult = $importer->check($request);
        return $this->createJsonResponse($checkResult);
    }

    public function importAction(Request $request, $type)
    {
        $importer = ImporterFactory::create($type);
        $importer->tryImport($request);
        $importerResult = $importer->import($request);
        return $this->createJsonResponse($importerResult);
    }

    public function templateAction(Request $request, $type)
    {
        $importer = ImporterFactory::create($type);
        $importer->tryImport($request);
        $template = $importer->getTemplate($request);
        return $template;
    }

    public function importModalAction(Request $request)
    {
        return $this->render('importer/userimport.modal.html.twig');
    }

}

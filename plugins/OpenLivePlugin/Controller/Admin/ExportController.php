<?php

namespace OpenLivePlugin\Controller\Admin;

use OpenLivePlugin\Biz\OpenLiveManage\Service\OpenLiveManageService;
use OpenLivePlugin\Common\Office\CsvHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

abstract class ExportController extends BaseController
{
    abstract public function tryExportAction(Request $request, $id);

    abstract public function preExportAction(Request $request, $id, $fileName);

    abstract protected function canExport($liveId);

    public function exportAction(Request $request, $name)
    {
        $biz = $this->getBiz();
        $fileName = $request->query->get('fileName');

        $exportPath = $biz['topxia.upload.private_directory'].'/'.basename($fileName);
        if (!file_exists($exportPath)) {
            return  $this->createJsonResponse(array('success' => 0, 'message' => 'empty file'));
        }
        $officeHelp = new CsvHelper();

        return $officeHelp->write($name, $exportPath);
    }

    protected function addContent($data, $start, $filePath)
    {
        if ($start == 0) {
            array_unshift($data, $this->transTitles());
        }
        $partPath = $this->updateFilePaths($filePath, $start);
        file_put_contents($partPath, serialize($data), FILE_APPEND);
    }

    protected function updateFilePaths($path, $page)
    {
        $content = file_exists($path) ? file_get_contents($path) : '';
        $content = unserialize($content);
        $partPath = $path.$page;
        $content[] = $partPath;
        file_put_contents($path, serialize($content));

        return $partPath;
    }

    protected function exportFileRootPath()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $rootPath = $biz['topxia.upload.private_directory'].'/';
        if (!$filesystem->exists($rootPath)) {
            $filesystem->mkdir($rootPath);
        }

        return  $rootPath;
    }

    abstract protected function transTitles();

    /**
     * @return OpenLiveManageService
     */
    protected function getOpenLiveManageService()
    {
        return $this->createService('OpenLivePlugin:OpenLiveManage:OpenLiveManageService');
    }
}

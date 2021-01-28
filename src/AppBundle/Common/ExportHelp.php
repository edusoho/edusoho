<?php

namespace AppBundle\Common;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExportHelp
{
    public static function getMagicExportSetting($request)
    {
        $magic = self::getMagic();
        $start = $request->query->get('start', 0);
        if (empty($magic['export_limit'])) {
            $magic['export_limit'] = 1000;
        }

        if (empty($magic['export_allow_count'])) {
            $magic['export_allow_count'] = 10000;
        }

        $limit = ($magic['export_limit'] > $magic['export_allow_count']) ? $magic['export_allow_count'] : $magic['export_limit'];

        return array($start, $limit, $magic['export_allow_count']);
    }

    public static function addFileTitle($request, $contentName, $content)
    {
        $fileName = $request->query->get('fileName', self::genereateExportCsvFileName($contentName));
        $filePath = self::getFilePath($fileName);
        file_put_contents($filePath, $content."\r\n", FILE_APPEND);

        return $fileName;
    }

    public static function getNextMethod($count, $sumCount)
    {
        if ($count >= $sumCount) {
            return 'export';
        }

        return 'getData';
    }

    public static function saveToTempFile($request, $content, $file)
    {
        if (empty($file)) {
            $file = $request->query->get('fileName');
        }
        $filePath = self::getFilePath($file);

        file_put_contents($filePath, $content."\r\n", FILE_APPEND);

        return $file;
    }

    public static function genereateExportCsvFileName($contentName = '')
    {
        $user = ServiceKernel::instance()->getCurrentUser();
        $fileName = md5($contentName.$user->getId().TimeMachine::time());

        return $fileName.RandMachine::rand();
    }

    public static function exportCsv($request, $fileName)
    {
        $filePath = self::getFilePath($request->query->get('fileName'));
        if (empty($filePath) || !file_exists($filePath)) {
            return new JsonResponse('empty file', 200);
        }
        $str = file_get_contents($filePath);
        FileToolkit::remove($filePath);

        $str = chr(239).chr(187).chr(191).$str;

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    public static function getMagic()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService')->get('magic');
    }

    public static function getFilePath($fileName)
    {
        $rootPath = ServiceKernel::instance()->getParameter('topxia.upload.private_directory');

        return $rootPath.'/'.basename($fileName);
    }
}

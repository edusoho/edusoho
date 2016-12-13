<?php

namespace Topxia\Common;

use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\ServiceKernel;

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

        $limit = ($magic['export_limit']>$magic['export_allow_count']) ? $magic['export_allow_count']:$magic['export_limit'];

        return array($start, $limit, $magic['export_allow_count']);
    }

    public static function addFileTitle($request, $contentName, $content)
    {
        $file = $request->query->get('fileName', self::genereateExportCsvFileName($contentName));
        file_put_contents($file, $content."\r\n", FILE_APPEND);

        return $file;
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
        
        file_put_contents($file, $content."\r\n", FILE_APPEND);

        return $file;
    }

    public static function genereateExportCsvFileName($contentName = '')
    {
        $rootPath = ServiceKernel::instance()->getParameter('topxia.upload.private_directory');
        $user = ServiceKernel::instance()->getCurrentUser();
        return $rootPath."/export_content_".$contentName.'_'.$user['id'].time().".txt";
    }

    public static function exportCsv($request, $fileName)
    {
        $file = $request->query->get('fileName');

        $str = file_get_contents($file);
        if (!empty($file)) {
            FileToolkit::remove($file);
        }
        
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
        return ServiceKernel::instance()->createService('System.SettingService')->get('magic');
    }
}
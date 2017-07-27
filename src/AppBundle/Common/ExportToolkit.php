<?php

namespace AppBundle\Common;

use Symfony\Component\HttpFoundation\Response;

class ExportToolkit
{
    public static function csv($fileName, $filePath)
    {
        $fileName = sprintf($fileName.'-(%s).csv', date('Y-n-d'));

        $str = file_get_contents($filePath);
        $str = chr(239).chr(187).chr(191).$str;

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    public static function excel($fileName, $filePath)
    {

    }
}

<?php

namespace AppBundle\Component\Office;

class CsvHelp implements OfficeHelpInterface
{
    public function export($fileName, $filePath)
    {
        $contant = unserialize(file_get_contents($filePath));
        $contant = $this->handleContent($contant);

        $fileName = sprintf($fileName.'-(%s).csv', date('Y-n-d'));

        $contant = chr(239).chr(187).chr(191).$contant;

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $response->headers->set('Content-length', strlen($contant));
        $response->setContent($contant);

        return $response;
    }

    function handleContent($contant)
    {
        $data = '';
        foreach ($contant as $value){
            $data .= implode(',' ,$value)."\r\n";
        }

        return $data;
    }
}

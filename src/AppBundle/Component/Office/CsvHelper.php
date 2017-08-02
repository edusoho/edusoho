<?php

namespace AppBundle\Component\Office;

use Symfony\Component\HttpFoundation\Response;

class CsvHelper extends BaseHelper
{
    public function write($fileName, $filePath)
    {
        $contant = $this->read($filePath);
        $contant = $this->handleContent($contant);

        $this->delete($filePath);
        $fileName = sprintf($fileName.'-(%s).csv', date('Y-n-d'));

        $contant = chr(239).chr(187).chr(191).$contant;

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $response->headers->set('Content-length', strlen($contant));
        $response->setContent($contant);

        return $response;
    }

    private function handleContent($contant)
    {
        $data = '';
        foreach ($contant as $item) {
            foreach ($item as $values) {
                array_walk($values, function (&$value) {
                    //CSV会将字段里的两个双引号""显示成一个
                    $value = '"'.str_replace('"', '""', $value).'"';
                });
                $data .= implode(',', $values)."\r\n";
            }
        }

        return $data;
    }
}

<?php

namespace AppBundle\Component\Office;

use Symfony\Component\HttpFoundation\Response;

class CsvHelper extends BaseHelper
{
    public function write($name, $filePath)
    {
        $content = $this->read($filePath);
        $content = $this->handleContent($content);

        $this->delete($filePath);

        $fileName = sprintf($name.'-(%s).csv', date('Y-n-d'));
        $content = chr(239).chr(187).chr(191).$content;
        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $response->headers->set('Content-length', strlen($content));
        $response->setContent($content);

        return $response;
    }

    private function handleContent($content)
    {
        $data = '';
        foreach ($content as $item) {
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

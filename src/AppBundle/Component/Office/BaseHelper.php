<?php

namespace AppBundle\Component\Office;

class BaseHelper
{
     public function read($filePath)
     {
        $result = array();
        $data = unserialize(file_get_contents($filePath));
        foreach ($data as $item) {
            $result[] = unserialize(file_get_contents($item));
        }
        return $result;
     }

     public function delete($filePath)
     {
         $data = unserialize(file_get_contents($filePath));
         foreach ($data as $item) {
             FileToolkit::remove($item);
         }

         FileToolkit::remove($filePath);
     }
}

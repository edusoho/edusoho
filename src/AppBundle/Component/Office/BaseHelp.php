<?php

namespace AppBundle\Component\Office;

class BaseHelp
{
     protected function read($filePath)
     {
        $result = array();
        $data = unserialize(file_get_contents($filePath));
        foreach ($data as $item) {
            $result[] = unserialize(file_get_contents($item));
        }
        return $result;
     }

     protected function delete($filePath)
     {
         $data = unserialize(file_get_contents($filePath));
         foreach ($data as $item) {
             FileToolkit::remove($item);
         }

         FileToolkit::remove($filePath);
     }
}

<?php

namespace ApiBundle\Api\Resource\UploadFile;

use ApiBundle\Api\Resource\Filter;

class UploadFileFilter extends Filter
{
    protected $publicFields = [
        'id', 'filename', 'fileSize', 'length', 'createdTime',
    ];

    protected function publicFields(&$data)
    {
        $data['fileSize'] = $this->fileSizeFilter($data['fileSize']);
    }

    private function fileSizeFilter($size)
    {
        $unitExps = ['B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3];
        foreach ($unitExps as $unit => $exp) {
            $divisor = pow(1024, $exp);
            $currentUnit = $unit;
            $currentValue = $size / $divisor;

            if ($currentValue < 1024) {
                break;
            }
        }

        return sprintf('%.2f', $currentValue) . $currentUnit;
    }
}

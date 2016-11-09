<?php
/**
 * User: Edusoho V8
 * Date: 07/11/2016
 * Time: 09:34
 */

namespace Biz\File;


interface UploadFileService
{
    public function searchFiles($conditions);

    public function searchFileCount($conditions);
}
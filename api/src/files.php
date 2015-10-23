<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Topxia\WebBundle\Util\UploadToken;

$api = $app['controllers_factory'];


/*
## 文件上传

    GET /files/

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| type | string | 否 | 文件类型，不传则默认为图片 |
| file | file | 是 | 文件 |

** 响应 **

```
{
    'id': {file-id},
    'userId': {file-upload-user}
    'uri': {file-uri},
    'size': {file-size},
    'createdTime': {file-created-time}
}
```
*/

// $api->post('/', function (Request $request) {
//     $type = $request->request->get('type', 'image');
//     $maker = new UploadToken();
//     $token = $maker->make('default',$type);

//     $token = $maker->parse($token);
//     if (empty($token)) {
//         throw new \RuntimeException("上传授权码已过期！");
//     }

//     $groupCode = empty($groupCode) ? 'default' : $token['group'];
//     $file = $request->files->get('file');
//     $record = ServiceKernel::instance()->createService('Content.FileService')->uploadFile($groupCode, $file);
//     return filter($record, 'file');
// });
return $api;
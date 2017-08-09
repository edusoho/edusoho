<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use Topxia\Api\Util\UserUtil;
use Silex\Application;

$api = $app['controllers_factory'];

/*
## 发送私信

    POST /messages/

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| nickname | string | 是 | 接收者昵称 |
| content | string | 是 | 私信内容 |
| type | string | 否 | 私信类型,默认为text |

`type`的值有:

    'text','image','video','audio'

** 响应 **

```
{
    'success':true
}
```
*/

$api->post('/', function (Request $request) {
    $nickname = $request->request->get('nickname');
    $content = $request->request->get('content');
    $type = $request->request->get('type','text');
    $sender = getCurrentUser();
    $receiver = ServiceKernel::instance()->createService('User:UserService')->getUserByNickname($nickname);
    if(empty($receiver)){
        throw $this->createNotFoundException("抱歉，该收信人尚未注册!");
    }
    $message = ServiceKernel::instance()->createService('User:MessageService')->sendMessage($sender['id'], $receiver['id'], $content, $type);
    return array(
        'success' => empty($message) ? 'flase' : 'true',
        'id' => empty($message) ? 0 : $message['id'],
    );
});
return $api;
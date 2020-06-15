<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class GoodComponent extends AbstractResource
{
    public function get(ApiRequest $request, $id, $component)
    {
        return (object) ['teachers' => $this->getMockedComponents($component)];
    }

    public function search(ApiRequest $request, $id)
    {
        $componentTypes = $request->query->get('componentTypes', []);
        $components = [];
        foreach ($componentTypes as $componentType) {
            $components[$componentType] = $this->getMockedComponents($componentType);
        }

        return $components;
    }

    protected function getMockedComponents($component)
    {
        $mockedComponents = [
            'teachers' => [
                [
                    'id' => 1,
                    'avatar' => [
                        'small' => 'http://try6.edusoho.cn/files/user/2020/06-15/1741586dd258161303.jpg',
                        'medium' => 'http://try6.edusoho.cn/files/user/2020/06-15/1741586dd258161303.jpg',
                        'large' => 'http://try6.edusoho.cn/files/user/2020/06-15/1741586dd258161303.jpg',
                    ],
                    'name' => '马老师',
                    'title' => '国家一级教师',
                ],
                [
                    'id' => 2,
                    'avatar' => [
                        'small' => 'http://try6.edusoho.cn/files/user/2020/06-15/1741586dd258161303.jpg',
                        'medium' => 'http://try6.edusoho.cn/files/user/2020/06-15/1741586dd258161303.jpg',
                        'large' => 'http://try6.edusoho.cn/files/user/2020/06-15/1741586dd258161303.jpg',
                    ],
                    'name' => '李老师',
                    'title' => '国家中级教师',
                ],
            ],
            'mpQrcode' => [
            ],
        ];

        return $mockedComponents[$component];
    }
}

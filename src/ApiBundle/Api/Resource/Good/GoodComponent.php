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
                'title' => '关注公众号',
                'imageUrl' => 'https://cdn2.jianshu.io/assets/web/download-index-side-qrcode-4130a7a6521701c4cb520ee6997d5fdb.png',
                'mpName' => '小兵果屋',
                'content' => '关注公众号，随时随地学习最新知识',
            ],
            'reviews' => [
                [
                    'id' => 1,
                    'userId' => 10,
                    'user' => [
                        'id' => 10,
                        'nickname' => '小兵张嘎',
                    ],
                    'content' => '不错的课程',
                    'rating' => '4',
                    'targetName' => '默认学习计划',
                    'targetId' => 1,
                    'createdTime' => date('c'),
                ],
                [
                    'id' => 2,
                    'userId' => 11,
                    'user' => [
                        'id' => 11,
                        'nickname' => '小兵张嘎',
                    ],
                    'content' => '很棒的课程',
                    'rating' => '5',
                    'targetId' => 1,
                    'targetName' => '默认学习计划',
                    'createdTime' => date('c'),
                ],
            ],
            'recommendGoods' => [
                [
                    'id' => 1,
                    'dataDescription' => '富文本商品描述，有挂件，多计划，计划无优惠，永久有效，承诺服务',
                    'title' => '课程商品标题',
                    'subtitle' => '课程商品副标题',
                    'image' => 'http://try6.edusoho.cn/files/course/2020/04-14/18332003d725680219.jpeg',
                ],
                [
                    'id' => 2,
                    'dataDescription' => '富文本商品描述，有挂件，多计划，计划无优惠，永久有效，承诺服务',
                    'title' => '课程商品标题',
                    'subtitle' => '课程商品副标题',
                    'image' => 'http://try6.edusoho.cn/files/course/2020/04-14/18332003d725680219.jpeg',
                ],
            ],
            'classroomCourses' => [
            ],
            'courseTasks' => [
            ],
        ];

        return empty($mockedComponents[$component]) ? [] : $mockedComponents[$component];
    }
}

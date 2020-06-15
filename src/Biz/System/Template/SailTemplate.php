<?php

namespace Biz\System\Template;

use Biz\Content\Service\BlockService;

class SailTemplate extends Template
{
    public function getTemplate()
    {
        $host = $this->getHost();

        $result = [
            'search-0' => [
                'type' => 'search',
                'moduleType' => 'search-0',
            ],
            'slide-1' => [
                'type' => 'slide_show',
                'moduleType' => 'slide-1',
                'data' => [
                    [
                        'title' => '',
                        'image' => [
                            'id' => 0,
                            'uri' => $host.'/static-dist/app/img/admin/h5/slide@2x.png',
                            'size' => '',
                            'createdTime' => 0,
                        ],
                        'link' => [
                            'type' => 'url',
                            'target' => null,
                            'url' => '',
                        ],
                    ],
                ],
                'tips' => [
                    'banner中放限时免费、限时低价、限时活动的广告，强调突出限时活动，更容易吸引用户点击参与。',
                    'banner图设计时，重点突出能吸引用户点击的卖点。比如课程特色，价格等要素，能有效吸引客户点击。',
                ],
            ],
        ];

        return array_merge($result, [
            'graphic_navigation-2' => [
                'type' => 'graphic_navigation',
                'moduleType' => 'graphic_navigation-2',
                'data' => [
                    [
                        'title' => '免费专区',
                        'image' => ['url' => $host.'/static-dist/app/img/admin/h5/free@2x.png', 'uri' => ''],
                        'link' => ['target' => '', 'type' => '', 'url' => ''],
                    ],
                    [
                        'title' => '网校运营',
                        'image' => ['url' => $host.'/static-dist/app/img/admin/h5/operation@2x.png', 'uri' => ''],
                        'link' => ['target' => '', 'type' => '', 'url' => ''],
                    ],
                    [
                        'title' => '使用帮助',
                        'image' => ['url' => $host.'/static-dist/app/img/admin/h5/help@2x.png', 'uri' => ''],
                        'link' => ['target' => '', 'type' => '', 'url' => ''],
                    ],
                    [
                        'title' => '名师专场',
                        'image' => ['url' => $host.'/static-dist/app/img/admin/h5/teacher@2x.png', 'uri' => ''],
                        'link' => ['target' => '', 'type' => '', 'url' => ''],
                    ],
                ],
                'tips' => [
                    '在导航栏中放上“免费专区”，更容易促进进入网校的用户试听课程哦。',
                ],
            ],
            'courseList-3' => [
                'type' => 'course_list',
                'moduleType' => 'courseList-3',
                'data' => [
                    'title' => '本周最受欢迎TOP5',
                    'sourceType' => 'condition',
                    'categoryId' => 0,
                    'sort' => '-studentNum',
                    'lastDays' => 0,
                    'limit' => 5,
                    'displayStyle' => 'row',
                    'items' => [],
                ],
                'tips' => [
                    '首页中可以放多个课程列表。建议将首个课程列表命名为本周最受欢迎TOP5。列表中课程选择建议包含：免费课程、低价课程、观看好评最高的课程、名师课程、爆款课程等。',
                    '免费课程选择：免费课程用于吸引用户体验和试看，选择好评度最高的且适用于核心用户的1个或2个免费课程，这类课程更易获得核心用户试看及好评，为后面低价转化做铺垫。',
                    '低价课程选择：低价课程是达到用户转化的一个策略。建议选择系列课等内容较丰富的课程，价格设低。通过课程丰富度与价格的反差刺激，促进客户进行转化。',
                    '爆款课程选择：选择购买人数最多课程，购买人数最多的课程说明课程受众面较广，可作为爆款课程推广。',
                    '名师课程选择：首页推荐价格较高的名师课程，通过名师效应，可有效增强用户信任感。同时高价课程与免费和低价课程形成反差，更容易促成低价课程成交。',
                ],
            ],
            'courseList-4' => [
                'type' => 'course_list',
                'moduleType' => 'courseList-4',
                'data' => [
                    'title' => '爆款好课 不容错过',
                    'sourceType' => 'condition',
                    'categoryId' => 0,
                    'sort' => 'recommendedSeq',
                    'lastDays' => 0,
                    'limit' => 4,
                    'displayStyle' => 'distichous',
                    'items' => [],
                ],
                'tips' => [
                    '通过在详情页-简介中添加联系方式（微信号或二维码），引导用户加入社群，在社群内运营提升用户购课率。',
                ],
            ],
            'poster-5' => [
                'type' => 'poster',
                'moduleType' => 'poster-5',
                'data' => [
                    'image' => [
                        'uri' => $host.'/static-dist/app/img/admin/h5/poster@2x.png',
                    ],
                    'link' => [],
                ],
                'tips' => [
                    '广告图中增加网校名称、logo、slogan等内容，可有效提升用户对平台信任度，更好促进课程试看和转化。',
                ],
            ],
            'classroom_list-6' => [
                'type' => 'classroom_list',
                'moduleType' => 'classroom_list-6',
                'data' => [
                    'title' => '专项技能班',
                    'sourceType' => 'condition',
                    'categoryId' => 0,
                    'displayStyle' => 'distichous',
                    'sort' => 'recommendedSeq',
                    'lastDays' => 0,
                    'limit' => 4,
                    'items' => [],
                ],
            ],
            'courseList-7' => [
                'type' => 'course_list',
                'moduleType' => 'courseList-7',
                'data' => [
                    'title' => '名师专栏',
                    'sourceType' => 'condition',
                    'categoryId' => 0,
                    'sort' => '-studentNum',
                    'lastDays' => 0,
                    'limit' => 4,
                    'displayStyle' => 'row',
                    'items' => [],
                ],
            ],
        ]);
    }

    /**
     * @return BlockService
     */
    private function getBlockService()
    {
        return $this->biz->service('Content:BlockService');
    }
}

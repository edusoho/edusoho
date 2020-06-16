<?php

namespace AppBundle\Controller\Goods;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class GoodsController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        return $this->render('goods/show.html.twig', array_merge($this->mockData($id), ['goods' => $this->mockData($id)]));
    }

    public function mockData($id)
    {
        $mockData = [
            1 => [
                'dataDescription' => '富文本商品描述，有挂件，多计划，计划无优惠，永久有效，承诺服务',
                'title' => '课程商品标题',
                'subtitle' => '课程商品副标题',
                'image' => 'http://try6.edusoho.cn/files/course/2020/04-14/18332003d725680219.jpeg',
                'description' => '<p>EduSoho可以添加本地视频，也可以导入优酷、土豆、网易公开课的视频。</p>
                <p>本地视频支持MP4格式，如果开通<span style="font-size:16px;"><em><u><a href="http://open.edusoho.com/show/cloud/video"><span style="color:#33cc99;"><strong>EduSoho教育云视频</strong></span></a></u></em></span>，可以上传MP4、AVI、FLV、WMA、MOV视频，省空间省流量且四重加密技术保护视频安全：</p>
                <p>第一重加密：观看身份验证，谁买谁观看，防盗播；</p>              
                <p>第二重加密：视频水印、隐形视频指纹，一秒识别翻录者；</p>                
                <p>第三重加密：先进的播放地址加密，100%无法被常用工具下载；</p>                
                <p>第四重加密：源文件级别加密，视频即使被非法下载也无法播放。</p>',
                'hasExtension' => true,
                'extensions' => [
                    'mpQrCode',
                    'teachers',
                    'recommendGoods',
                ],
                'specs' => [
                    1 => [
                        'title' => '计划一',
                        'subtitle' => '计划一规格副标题',
                        'price' => '100.00',
                        'expiryMode' => 'forever',
                    ],
                    2 => [
                        'title' => '计划二进阶学习',
                        'subtitle' => '计划二规格副标题',
                        'price' => '150.00',
                        'expiryMode' => 'forever',
                        'services' => [
                            'homeworkReview' => [
                                'code' => 'homeworkReview',
                                'shortName' => 'site.services.homeworkReview.shortName',
                                'fullName' => 'site.services.homeworkReview.fullName',
                                'summary' => 'site.services.homeworkReview.summary',
                                'active' => 0,
                            ],
                            'testpaperReview' => [
                                'code' => 'testpaperReview',
                                'shortName' => 'site.services.testpaperReview.shortName',
                                'fullName' => 'site.services.testpaperReview.fullName',
                                'summary' => 'site.services.testpaperReview.summary',
                                'active' => 0,
                            ],
                            'teacherAnswer' => [
                                'code' => 'teacherAnswer',
                                'shortName' => 'site.services.teacherAnswer.shortName',
                                'fullName' => 'site.services.teacherAnswer.fullName',
                                'summary' => 'site.services.teacherAnswer.summary',
                                'active' => 0,
                            ],
                            'liveAnswer' => [
                                'code' => 'liveAnswer',
                                'shortName' => 'site.services.liveAnswer.shortName',
                                'fullName' => 'site.services.liveAnswer.fullName',
                                'summary' => 'site.services.liveAnswer.summary',
                                'active' => 0,
                            ],
                        ],
                    ],
                ],
            ],
            2 => [
                'title' => '课程商品标题',
                'subtitle' => '课程商品副标题',
                'image' => 'http://try6.edusoho.cn/files/course/2020/04-14/18332003d725680219.jpeg',
                'description' => '<p>EduSoho可以添加本地视频，也可以导入优酷、土豆、网易公开课的视频。</p>
                <p>本地视频支持MP4格式，如果开通<span style="font-size:16px;"><em><u><a href="http://open.edusoho.com/show/cloud/video"><span style="color:#33cc99;"><strong>EduSoho教育云视频</strong></span></a></u></em></span>，可以上传MP4、AVI、FLV、WMA、MOV视频，省空间省流量且四重加密技术保护视频安全：</p>
                <p>第一重加密：观看身份验证，谁买谁观看，防盗播；</p>              
                <p>第二重加密：视频水印、隐形视频指纹，一秒识别翻录者；</p>                
                <p>第三重加密：先进的播放地址加密，100%无法被常用工具下载；</p>                
                <p>第四重加密：源文件级别加密，视频即使被非法下载也无法播放。</p>',
                'hasExtension' => false,
                'specs' => [
                    1 => [
                        'title' => '计划一',
                        'subtitle' => '计划一规格副标题',
                        'price' => '1000.00',
                        'expiryMode' => 'forever',
                        'services' => [
                            'homeworkReview' => [
                                'code' => 'homeworkReview',
                                'shortName' => 'site.services.homeworkReview.shortName',
                                'fullName' => 'site.services.homeworkReview.fullName',
                                'summary' => 'site.services.homeworkReview.summary',
                                'active' => 0,
                            ],
                            'testpaperReview' => [
                                'code' => 'testpaperReview',
                                'shortName' => 'site.services.testpaperReview.shortName',
                                'fullName' => 'site.services.testpaperReview.fullName',
                                'summary' => 'site.services.testpaperReview.summary',
                                'active' => 0,
                            ],
                            'teacherAnswer' => [
                                'code' => 'teacherAnswer',
                                'shortName' => 'site.services.teacherAnswer.shortName',
                                'fullName' => 'site.services.teacherAnswer.fullName',
                                'summary' => 'site.services.teacherAnswer.summary',
                                'active' => 0,
                            ],
                            'liveAnswer' => [
                                'code' => 'liveAnswer',
                                'shortName' => 'site.services.liveAnswer.shortName',
                                'fullName' => 'site.services.liveAnswer.fullName',
                                'summary' => 'site.services.liveAnswer.summary',
                                'active' => 0,
                            ],
                        ],
                    ],
                    2 => [
                        'title' => '计划二进阶学习',
                        'subtitle' => '计划二规格副标题',
                        'price' => '1500.00',
                        'expiryMode' => 'forever',
                    ],
                ],
            ],
            3 => [
                'dataDescription' => '富文本商品描述，有优惠，课程测试',
            ],
        ];

        return empty($mockData[$id]) ? [] : $mockData[$id];
    }
}

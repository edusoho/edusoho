<?php

namespace Tests\Unit\Search\Adapter;

use Biz\BaseTestCase;
use Biz\Search\Adapter\SearchAdapterFactory;

class TeacherSearchAdapterTest extends BaseTestCase
{
    public function testAdapt()
    {
        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUserProfilesByIds',
                    'withParams' => array(array(2, 3)),
                    'returnValue' => array(
                        array(
                            'id' => 2,
                            'idcard' => '123',
                        ),
                        array(
                            'id' => 3,
                            'idcard' => '222',
                        ),
                    ),
                ),
                array(
                    'functionName' => 'findUsersByIds',
                    'withParams' => array(array(2, 3)),
                    'returnValue' => array(
                        array(
                            'id' => 2,
                            'truename' => 'truename1',
                            'largeAvatar' => 'largeAvatar1',
                        ),
                        array(
                            'id' => 3,
                            'truename' => 'truename2',
                            'largeAvatar' => 'largeAvatar2',
                        ),
                    ),
                ),
                array(
                    'functionName' => 'filterFollowingIds',
                    'withParams' => array(1, array(2, 3)),
                    'returnValue' => array(2),
                ),
            )
        );
        $result = SearchAdapterFactory::create('teacher')->adapt(array(
            array(
                'userId' => 2,
            ),
            array(
                'userId' => 3,
            ),
        ));

        $this->assertArrayEquals(
            array(
                array(
                    'id' => 2,
                    'userId' => 2,
                    'profile' => array(
                        'id' => 2,
                        'idcard' => '123',
                    ),
                ),
                array(
                    'id' => 3,
                    'userId' => 3,
                    'profile' => array(
                        'id' => 3,
                        'idcard' => '222',
                    ),
                ),
            ),
            $result
        );
        $userService->shouldHaveReceived('findUserProfilesByIds')->times(1);
        $userService->shouldHaveReceived('findUsersByIds')->times(1);
        $userService->shouldHaveReceived('filterFollowingIds')->times(1);
    }

    public function testAdaptWithEmptyArticle()
    {
        $this->mockBiz(
            'Article:ArticleService',
            array(
                array(
                    'functionName' => 'getArticle',
                    'returnValue' => array(),
                    'withParams' => array(111),
                ),
            )
        );
        $result = SearchAdapterFactory::create('article')->adapt(array(array(
            'articleId' => 111,
            'content' => 'test',
            'category' => 'category',
            'updatedTime' => 500000,
        )));

        $this->assertEquals(500000, $result[0]['publishedTime']);
    }
}

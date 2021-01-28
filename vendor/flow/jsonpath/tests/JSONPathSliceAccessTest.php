<?php

namespace Flow\JSONPath\Test;


use Flow\JSONPath\JSONPath;

class JSONPathSliceAccessTest extends \PHPUnit_Framework_TestCase
{
    public function sliceDataProvider()
    {
        return [
            // path, data, expected
            [
                '$.data[1:3]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo1',
                    'foo2',
                    'foo3',
                ]
            ],
            [
                '$.data[4:]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo4',
                    'foo5',
                ]
            ],
            [
                '$.data[:2]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo0',
                    'foo1',
                    'foo2',
                ]
            ],
            [
                '$.data[:]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo0',
                    'foo1',
                    'foo2',
                    'foo3',
                    'foo4',
                    'foo5',
                ]
            ],
            [
                '$.data[-1]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo5',
                ]
            ],
            [
                '$.data[-2:]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo4',
                    'foo5',
                ]
            ],
            [
                '$.data[:-2]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo0',
                    'foo1',
                    'foo2',
                    'foo3',
                ]
            ],
            [
                '$.data[::2]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo0',
                    'foo2',
                    'foo4'
                ]
            ],
            [
                '$.data[2::2]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo2',
                    'foo4'
                ]
            ],
            [
            '$.data[:-2:2]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo0',
                    'foo2'
                ]
            ],
            [
                '$.data[1:5:2]',
                [
                    'data' => [
                        'foo0',
                        'foo1',
                        'foo2',
                        'foo3',
                        'foo4',
                        'foo5',
                    ]
                ],
                [
                    'foo1',
                    'foo3',
                    'foo5',
                ]
            ]
        ];
    }

    /** @dataProvider sliceDataProvider */
    public function testSlice($path, $data, $expected)
    {
        $jsonPath = new JSONPath($data);
        $result = $jsonPath->find($path)->data();
        $this->assertEquals($expected, $result);
    }
}

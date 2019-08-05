<?php

namespace ExamParser\Tests\Writer;

use ExamParser\Tests\BaseTestCase;
use ExamParser\Writer\WriteDocx;

class WriterDocxTest extends BaseTestCase
{
    public function testWrite()
    {
        $questions = array(
            array(
                'type' => 'single_choice',
                'seq' => '1、',
                'num' => '1、',
                'stem' => array(
                    array(
                        'element' => 'text',
                        'content' => '科目汇总表的汇总范围是（）。',
                    )
                ),
                'options' => array(
                    array(
                        array(
                            'element' => 'text',
                            'content' => 'A.全部账户的借方余额',
                        )
                    ),
                    array(
                        array(
                            'element' => 'text',
                            'content' => 'B.全部账户的贷方余额',
                        )
                    ),
                    array(
                        array(
                            'element' => 'text',
                            'content' => 'C.全部账户的借、贷方发生额',
                        )
                    ),
                    array(
                        array(
                            'element' => 'text',
                            'content' => 'D.全部账户的借、贷方余额',
                        )
                    ),
                ),
                'answer' => 'C',
                'analysis' => null,
            ),
            array(
                'type' => 'choice',
                'seq' => '2、',
                'num' => '2、',
                'stem' => array(
                    array(
                        'element' => 'text',
                        'content' => '科目汇总表的汇总范围是（）。',
                    )
                ),
                'options' => array(
                    array(
                        array(
                            'element' => 'text',
                            'content' => 'A.全部账户的借方余额',
                        )
                    ),
                    array(
                        array(
                            'element' => 'text',
                            'content' => 'B.全部账户的贷方余额',
                        )
                    ),
                    array(
                        array(
                            'element' => 'text',
                            'content' => 'C.全部账户的借、贷方发生额',
                        )
                    ),
                    array(
                        array(
                            'element' => 'text',
                            'content' => 'D.全部账户的借、贷方余额',
                        )
                    ),
                ),
                'answer' => 'ACD',
                'analysis' => array(
                    array(
                        'element' => 'text',
                        'content' => '资产是过去的交易事项形成，企业拥有或者控制的预期会给企业带来经济利益的资源。故选ACD。',
                    )
                ),
            ),
            array(
                'type' => 'determine',
                'seq' => '3、',
                'num' => '3、',
                'stem' => array(
                    array(
                        'element' => 'text',
                        'content' => '经上级有关部门批准的经济业务，应将批准文件作为原始凭证附件。'
                    )
                ),
                'answer' => '正确',
            ),
            array(
                'type' => 'fill',
                'seq' => '4、',
                'num' => '4、',
                'stem' => array(
                    array(
                        'element' => 'text',
                        'content' => '唐代诗人李白，字[[太白]]，号[[青莲居士|谪仙人]]，人称诗仙。',
                    )
                ),
                'answer' => array('太白', '青莲居士|谪仙人'),
            ),
            array(
                'type' => 'material',
                'seq' => '5、',
                'num' => '5、',
                'stem' => array(
                    array(
                        'element' => 'text',
                        'content' => '甲公司的所得税税率为25%，请计算：',
                    )
                ),
                'subs' => array(
                    array(
                        'type' => 'essay',
                        'seq' => '（1）',
                        'num' => '（1）',
                        'stem' => array(
                            array(
                                'element' => 'text',
                                'content' => '甲公司的长期股权投资权益法核算下的账面价值与计算基础是否有差异。',
                            )
                        ),
                        'answer' => array(
                            array(
                                'element' => 'text',
                                'content' => '题目二的答案',
                            )
                        )
                    ),
                    array(
                        'type' => 'essay',
                        'seq' => '（2）',
                        'num' => '（2）',
                        'stem' => array(
                            array(
                                'element' => 'text',
                                'content' => '甲公司的长期股权投资权益法核算下的账面价值与计算基础是否有差异。',
                            )
                        ),
                        'answer' => array(
                            array(
                                'element' => 'text',
                                'content' => '题目二的答案',
                            )
                        )
                    ),
                ),
            ),
        );

        $writeDocx = new WriteDocx('/tmp/export.docx');
        $writeDocx->write($questions);
        $this->assertTrue(is_file('/tmp/export.docx'));
    }
}

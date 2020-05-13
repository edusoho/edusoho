<template>
  <div id="app" class="test-vue">
    <div>{{ message }}</div>
    <item-manage :showCKEditorData="showCKEditorData" type="material"></item-manage>
    <!-- <item-engine
     mode="do"
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      @getAnswerData="getAnswerData"
      @checkResult="checkResult"
      @saveAnswerData="saveAnswerData"></item-engine>
    <item-review
      v-if="scene == 'review'"
      :assessment="assessment"
      :answerReport="answerReport"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      @getReviewData="getReviewData"
      @getReviewDataAagin="getReviewDataAagin"
    ></item-review> -->
  </div>
</template>


<script>


export default {
  data() {
    return {
      scene: 'engine',
      message: '信dsfdsfs息',
      showCKEditorData: {
        publicPath: "http://t5.edusoho.cn/static-dist/libs/es-ckeditor/ckeditor.js",
      },
        answerReport: {
        id: "19",
        assessment_id: 37,
        answer_record_id: 1,
        total_score: 100,
        score: 100,
        right_rate: 50.1,
        review_time: 1585052794,
        review_user_id: 1,
        created_time: 1585052794,
        updated_time: 1585052794,
        section_reports: [
          {
            section_id: 19,
            section_name: "问答题",
            total_score: 100,
            score: 100,
            question_count: 3,
            right_question_num: 1,
            wrong_question_num: 1,
            reviewing_question_num: 1,
            no_answer_question_num: 1,
            item_reports: [
              {
                item_id: 18,
                total_score: 3,
                score: 3,
                question_count: 3,
                question_reports: [
                  {
                    id: "18",
                    question_id: 1,
                    total_score: 1,
                    score: 1,
                    comment: "<p>这是评语啦啦啦啦啦啦啦</p>",
                    status: "wrong",
                    response: ["D"]
                  }
                ]
              },
              {
                item_id: 22,
                total_score: 3,
                score: 3,
                question_count: 3,
                question_reports: [
                  {
                    id: "22",
                    question_id: 1,
                    total_score: 1,
                    score: 1,
                    comment: "这是评语",
                    status: "wrong",
                    response: ["C", "D"]
                  }
                ]
              },
              {
                item_id: 19,
                total_score: 3,
                score: 3,
                question_count: 3,
                question_reports: [
                  {
                    id: "19",
                    question_id: 1,
                    total_score: 1,
                    score: 1,
                    comment: "<p>这是评语啦啦啦啦啦啦啦</p>",
                    status: "wrong",
                    response: ["C"]
                  }
                ]
              },
              {
                item_id: 21,
                total_score: 3,
                score: 3,
                question_count: 3,
                question_reports: [
                  {
                    id: "24",
                    question_id: 1,
                    total_score: 1,
                    score: 21,
                    comment: "这是评语",
                    status: "right",
                    response: ["T"]
                  }
                ]
              },
              {
                item_id: 23,
                total_score: 3,
                score: 3,
                question_count: 3,
                question_reports: [
                  {
                    id: "25",
                    question_id: 1,
                    total_score: 1,
                    score: 21,
                    comment: "这是评语",
                    status: "wrong",
                    response: ["厉害了", "真的开口|哈哈哈"]
                  }
                ]
              },
              {
                item_id: 23,
                total_score: 3,
                score: 3,
                question_count: 3,
                question_reports: [
                  {
                    id: "33",
                    question_id: 1,
                    total_score: 1,
                    score: 21,
                    comment: "这是评语",
                    status: "no_answer",
                    response: ["ddd"]
                  }
                ]
              },
              {
                item_id: 23,
                total_score: 3,
                score: 3,
                question_count: 3,
                question_reports: [
                  {
                    id: "27",
                    question_id: 1,
                    total_score: 1,
                    score: 21,
                    comment: "这是评语",
                    status: "no_answer",
                    response: []
                  },
                  {
                    id: "29",
                    question_id: 1,
                    total_score: 1,
                    score: 21,
                    comment: "这是评语",
                    status: "right",
                    response: ["A", "C"]
                  },
                  {
                    id: "30",
                    question_id: 1,
                    total_score: 1,
                    score: 21,
                    comment: "这是评语",
                    status: "wrong",
                    response: ["F"]
                  },
                  {
                    id: "31",
                    question_id: 1,
                    total_score: 1,
                    score: 21,
                    comment: "这是评语",
                    status: "wrong",
                    response: ["快快快", "坎坎坷坷扩扩扩扩"]
                  },
                  {
                    id: "38",
                    question_id: 1,
                    total_score: 1,
                    score: 21,
                    comment: "这是评语",
                    status: "right",
                    response: ["看看反馈库房收款的开发快速打开"]
                  }
                ]
              }
            ]
          },
          {
            section_id: 19,
            section_name: "问答题",
            total_score: 100,
            score: 100,
            question_count: 3,
            right_question_num: 1,
            wrong_question_num: 1,
            reviewing_question_num: 1,
            no_answer_question_num: 1,
            item_reports: [
              {
                item_id: 18,
                total_score: 3,
                score: 3,
                question_count: 3,
                question_reports: [
                  {
                    id: "18",
                    question_id: 1,
                    total_score: 1,
                    score: 1,
                    comment: "<p>这是评语啦啦啦啦啦啦啦</p>",
                    status: "right",
                    response: ["D"]
                  },
                  {
                    id: "19",
                    question_id: 1,
                    total_score: 1,
                    score: 1,
                    comment: "<p>这是评语啦啦啦啦啦啦啦</p>",
                    status: "right",
                    response: [
                      "<p>开发福克斯打分看飞快的时刻看开发的时刻发多少</p>"
                    ]
                  }
                ]
              }
            ]
          }
        ]
      },
      assessment: {
        id: "37",
        bank_id: "1",
        name: "期末试卷",
        description: "测试一年学习水平",
        total_score: "10.0",
        status: "draft",
        item_count: "5",
        question_count: "5",
        created_user_id: "1",
        updated_user_id: "1",
        created_time: "1584945876",
        updated_time: "1584945876",
        sections: [
          {
            id: "19",
            assessment_id: "37",
            name: "test",
            seq: "1",
            description: "test",
            item_count: "5",
            total_score: "5.0",
            question_count: "5",
            score_rule: "",
            created_time: "1584945876",
            updated_time: "1584945876",
            items: [
              {
                id: "23",
                bank_id: "1",
                type: "single_choice",
                material: "科目汇总表的汇总范围是（）。",
                analysis: "<p>这是解析</p>",
                category_id: "1",
                difficulty: "normal",
                question_num: "1",
                created_user_id: "1",
                updated_user_id: "1",
                updated_time: "1584945876",
                created_time: "1584945876",
                isDelete: 0,
                seq: "1",
                score: "5.0",
                score_rule: [
                  {
                    question_id: "23",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  }
                ],
                section_id: "19",
                questions: [
                  {
                    id: "23",
                    item_id: "23",
                    stem: "科目汇总表的汇总范围是（）。",
                    seq: "1",
                    score: 2,
                    answer_mode: "single_choice",
                    response_points: [
                      {
                        radio: {
                          val: "A",
                          text: "全部账户的借方余额"
                        }
                      },
                      {
                        radio: {
                          val: "B",
                          text: "全部账户的贷方余额"
                        }
                      },
                      {
                        radio: {
                          val: "C",
                          text: "全部账户的借、贷方发生额"
                        }
                      },
                      {
                        radio: {
                          val: "D",
                          text: "全部账户的借、贷方余额"
                        }
                      }
                    ],
                    answer: ["A"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  }
                ]
              },
              {
                id: "24",
                bank_id: "1",
                type: "uncertain_choice",
                material: "资产应具备的基本特征有（）。",
                analysis: "<p>这是解析</p>",
                category_id: "1",
                difficulty: "normal",
                question_num: "1",
                created_user_id: "1",
                updated_user_id: "1",
                updated_time: "1584945876",
                created_time: "1584945876",
                isDelete: 0,
                seq: "2",
                score: "5.0",
                score_rule: [
                  {
                    question_id: "24",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "part_roght",
                        score: 1
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  }
                ],
                section_id: "19",
                questions: [
                  {
                    id: "24",
                    item_id: "24",
                    stem: "资产应具备的基本特征有（）。",
                    seq: "1",
                    score: 2,
                    answer_mode: "uncertain_choice",
                    response_points: [
                      {
                        checkbox: {
                          val: "A",
                          text: "资产是由企业过去的交易或事项形成"
                        }
                      },
                      {
                        checkbox: {
                          val: "B",
                          text: "必须是投资者投入的"
                        }
                      },
                      {
                        checkbox: {
                          val: "C",
                          text: "资产是由企业拥有和控制的"
                        }
                      },
                      {
                        checkbox: {
                          val: "D",
                          text: "资产预期能为企业带来经济利益"
                        }
                      }
                    ],
                    answer: ["A", "B"],
                    analysis: "<p></p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  }
                ]
              },
              {
                id: "25",
                bank_id: "1",
                type: "choice",
                material: "资产应具备的基本特征有（）。",
                analysis: "<p>这是解析</p>",
                category_id: "1",
                difficulty: "normal",
                question_num: "1",
                created_user_id: "1",
                updated_user_id: "1",
                updated_time: "1584945876",
                created_time: "1584945876",
                isDelete: 0,
                seq: "3",
                score: "5.0",
                score_rule: [
                  {
                    question_id: "25",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  }
                ],
                section_id: "19",
                questions: [
                  {
                    id: "25",
                    item_id: "25",
                    stem: "资产应具备的基本特征有（）。",
                    seq: "1",
                    score: 2,
                    answer_mode: "choice",
                    response_points: [
                      {
                        checkbox: {
                          val: "A",
                          text: "资产是由企业过去的交易或事项形成"
                        }
                      },
                      {
                        checkbox: {
                          val: "B",
                          text: "必须是投资者投入的"
                        }
                      },
                      {
                        checkbox: {
                          val: "C",
                          text: "资产是由企业拥有和控制的"
                        }
                      },
                      {
                        checkbox: {
                          val: "D",
                          text: "资产预期能为企业带来经济利益"
                        }
                      }
                    ],
                    answer: ["B", "D"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  }
                ]
              },
              {
                id: "26",
                bank_id: "1",
                type: "determine",
                material:
                  "经上级有关部门批准的经济业务，应将批准文件作为原始凭证附件。",
                analysis: "<p>这是解析</p>",
                category_id: "1",
                difficulty: "normal",
                question_num: "1",
                created_user_id: "1",
                updated_user_id: "1",
                updated_time: "1584945876",
                created_time: "1584945876",
                isDelete: 0,
                seq: "4",
                score: "5.0",
                score_rule: [
                  {
                    question_id: "26",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  }
                ],
                section_id: "19",
                questions: [
                  {
                    id: "26",
                    item_id: "26",
                    stem:
                      "经上级有关部门批准的经济业务，应将批准文件作为原始凭证附件。",
                    seq: "1",
                    score: 2,
                    answer_mode: "true_false",
                    response_points: [
                      {
                        radio: {
                          val: "T",
                          text: "正确"
                        }
                      },
                      {
                        radio: {
                          val: "F",
                          text: "错误"
                        }
                      }
                    ],
                    answer: ["T"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  }
                ]
              },
              {
                id: "27",
                bank_id: "1",
                type: "fill",
                material: "诗仙[[ ]], [[ ddd]]",
                analysis: "<p>这是解析</p>",
                category_id: "1",
                difficulty: "normal",
                question_num: "1",
                created_user_id: "1",
                updated_user_id: "1",
                updated_time: "1584945876",
                created_time: "1584945876",
                isDelete: 0,
                seq: "5",
                score: "5.0",
                score_rule: [
                  {
                    question_id: "27",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  }
                ],
                section_id: "19",
                questions: [
                  {
                    id: "27",
                    item_id: "27",
                    stem: "诗仙[[]], 号[[]]",
                    seq: "1",
                    score: 2,
                    answer_mode: "text",
                    response_points: [
                      {
                        text: []
                      },
                      {
                        text: []
                      }
                    ],
                    answer: ["李白", "青莲居士|诗仙"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  }
                ]
              },
              {
                id: "28",
                bank_id: "1",
                type: "essay",
                material:
                  "<p>分别计算甲公司2X15年12月3日所得税负债或资产的账面余额。</p>",
                analysis: "<p>这是解析</p>",
                category_id: "1",
                difficulty: "normal",
                question_num: "1",
                created_user_id: "1",
                updated_user_id: "1",
                updated_time: "1584945876",
                created_time: "1584945876",
                isDelete: 0,
                seq: "6",
                score: "5.0",
                score_rule: [
                  {
                    question_id: "28",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  }
                ],
                section_id: "19",
                questions: [
                  {
                    id: "28",
                    item_id: "28",
                    stem:
                      "<p>分别计算甲公司2X15年12月3日所得税负债或资产的账面余额。</p>",
                    seq: "1",
                    score: 2,
                    answer_mode: "rich_text",
                    response_points: [],
                    answer: ["<p>100要坎坎坷坷扩扩啦啦啦啦</p>"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  }
                ]
              },
              {
                id: "29",
                bank_id: "1",
                type: "material",
                material: "甲公司的所得税税率为25%，请计算：",
                analysis: "<p>这是解析</p>",
                category_id: "1",
                difficulty: "normal",
                question_num: "5",
                created_user_id: "1",
                updated_user_id: "1",
                updated_time: "1584945876",
                created_time: "1584945876",
                isDelete: 0,
                seq: "7",
                score: "5.0",
                score_rule: [
                  {
                    question_id: "29",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "30",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "31",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "31",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "32",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "33",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  }
                ],
                section_id: "19",
                questions: [
                  {
                    id: "29",
                    item_id: "29",
                    stem: "<p>这是题干</p>",
                    seq: "1",
                    score: 2,
                    answer_mode: "single_choice",
                    response_points: [
                      {
                        radio: {
                          val: "A",
                          text: "全部账户的借方余额"
                        }
                      },
                      {
                        radio: {
                          val: "B",
                          text: "全部账户的贷方余额"
                        }
                      },
                      {
                        radio: {
                          val: "C",
                          text: "全部账户的借、贷方发生额"
                        }
                      },
                      {
                        radio: {
                          val: "D",
                          text: "全部账户的借、贷方余额"
                        }
                      }
                    ],
                    answer: ["B"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  },
                  {
                    id: "30",
                    item_id: "29",
                    stem: "<p>这是题干</p>",
                    seq: "1",
                    score: 0,
                    answer_mode: "choice",
                    response_points: [
                      {
                        checkbox: {
                          val: "A",
                          text: "资产是由企业过去的交易或事项形成"
                        }
                      },
                      {
                        checkbox: {
                          val: "B",
                          text: "必须是投资者投入的"
                        }
                      },
                      {
                        checkbox: {
                          val: "C",
                          text: "资产是由企业拥有和控制的"
                        }
                      },
                      {
                        checkbox: {
                          val: "D",
                          text: "资产预期能为企业带来经济利益"
                        }
                      }
                    ],
                    answer: ["A", "C"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  },
                  {
                    id: "31",
                    item_id: "29",
                    stem: "<p>这是题干</p>",
                    seq: "1",
                    score: 0,
                    answer_mode: "true_false",
                    response_points: [
                      {
                        radio: {
                          val: "T",
                          text: "正确"
                        }
                      },
                      {
                        radio: {
                          val: "F",
                          text: "错误"
                        }
                      }
                    ],
                    answer: ["F"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  },
                  {
                    id: "32",
                    item_id: "29",
                    stem: "诗仙[[]], 号[[]]",
                    seq: "1",
                    score: 0,
                    answer_mode: "text",
                    response_points: [
                      {
                        text: []
                      },
                      {
                        text: []
                      }
                    ],
                    answer: ["李白", "青莲居士|诗仙"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  },
                  {
                    id: "33",
                    item_id: "29",
                    stem: "<p>这是题干</p>",
                    seq: "1",
                    score: 0,
                    answer_mode: "rich_text",
                    response_points: [
                      {
                        rich_text: []
                      }
                    ],
                    answer: ["<p>这是答案</p>"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  }
                ]
              },
            //   {
            //     section_id: "19",
            //     isDelete: 1
            //   }
            ]
          },
          {
            id: "20",
            assessment_id: "37",
            name: "第二类",
            seq: "1",
            description: "kk",
            item_count: "5",
            total_score: "5.0",
            question_count: "5",
            score_rule: "",
            created_time: "1584945876",
            updated_time: "1584945876",
            items: [
              {
                id: "29",
                bank_id: "1",
                type: "material",
                material: "甲公司的所得税税率为25%，请计算：",
                analysis: "<p>这是解析</p>",
                category_id: "1",
                difficulty: "normal",
                question_num: "5",
                created_user_id: "1",
                updated_user_id: "1",
                updated_time: "1584945876",
                created_time: "1584945876",
                isDelete: 0,
                seq: "7",
                score: "5.0",
                score_rule: [
                  {
                    question_id: "29",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "30",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "31",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "31",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "32",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  },
                  {
                    question_id: "33",
                    rule: [
                      {
                        name: "all_right",
                        score: 2
                      },
                      {
                        name: "no_answer",
                        score: 0
                      },
                      {
                        name: "wrong",
                        score: 0
                      }
                    ]
                  }
                ],
                section_id: "19",
                questions: [
                  {
                    id: "30",
                    item_id: "29",
                    stem: "<p>这是题干</p>",
                    seq: "1",
                    score: 0,
                    answer_mode: "choice",
                    response_points: [
                      {
                        checkbox: {
                          val: "A",
                          text: "资产是由企业过去的交易或事项形成"
                        }
                      },
                      {
                        checkbox: {
                          val: "B",
                          text: "必须是投资者投入的"
                        }
                      },
                      {
                        checkbox: {
                          val: "C",
                          text: "资产是由企业拥有和控制的"
                        }
                      },
                      {
                        checkbox: {
                          val: "D",
                          text: "资产预期能为企业带来经济利益"
                        }
                      }
                    ],
                    answer: ["A", "C"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  },
                  {
                    id: "32",
                    item_id: "29",
                    stem: "诗仙[[]], 号[[]]",
                    seq: "1",
                    score: 0,
                    answer_mode: "text",
                    response_points: [
                      {
                        text: []
                      },
                      {
                        text: []
                      }
                    ],
                    answer: ["李白", "青莲居士|诗仙"],
                    analysis: "<p>这是解析</p>",
                    created_user_id: "1",
                    updated_user_id: "1",
                    updated_time: "1584945876",
                    created_time: "1584945876"
                  }
                  // {
                  //   id: "33",
                  //   item_id: "29",
                  //   stem: "<p>这是题干</p>",
                  //   seq: "1",
                  //   score: 0,
                  //   answer_mode: "rich_text",
                  //   response_points: [
                  //     {
                  //       rich_text: []
                  //     }
                  //   ],
                  //   answer: ["<p>这是答案</p>"],
                  //   analysis: "<p>这是解析</p>",
                  //   created_user_id: "1",
                  //   updated_user_id: "1",
                  //   updated_time: "1584945876",
                  //   created_time: "1584945876"
                  // }
                ]
              }
            ]
          }
        ]
      },
      answerScene: {
        id: 1,
        name: "场次名称",
        limited_time: 30,
        do_times: 1,
        redo_interval: 0,
        need_score: 1,
        pass_score: 10.0,
        start_time: 1583313958,
        manual_marking: 1,
        created_user_id: 1,
        updated_user_id: 1,
        created_time: 1583313958,
        updated_time: 1583313958
      },
      answerRecord: {
        id: 1,
        user_id: 1,
        begin_time: 1583313958,
        end_time: 1583313989,
        status: "reviewing",
        answer_report_id: 1,
        used_time: 100,
        checked_time: 1583313958,
        checked_user_id: 1,
        answer_scene_id: 1,
        assessment_id: 1
      },
      assessmentResponse: {
        assessment_id: 1,
        answer_record_id: 1,
        used_time: 3600,
        section_responses: [
          {
            section_id: 1,
            item_responses: [
              {
                item_id: 1,
                question_responses: [
                  {
                    question_id: 1,
                    response: ["A", "B"]
                  },
                  {
                    question_id: 2,
                    response: ["李白", "青莲居士"]
                  }
                ]
              }
            ]
          }
        ]
      }
    };
  },
  methods: {
    getAnswerData(data) {
      console.log("答题数据");
      console.log(data);
    },
    saveAnswerData(data) {
      console.log("保存答题数据");
      console.log(data);
    },
    getReviewData(data) {
      console.log("获取es批阅结果");
      console.log(data);
    },
    getReviewDataAagin(data) {
      console.log("获取并跳转");
      console.log(data);
    },
    doAgainEvent() {
      console.log("do Again");
    },
    checkResult() {
      console.log("超时提交提醒");
    }
  }
};
</script>
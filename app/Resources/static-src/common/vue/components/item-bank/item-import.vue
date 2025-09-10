<template>
  <div class="ibs-item-import" id="item-bank-sdk-import">
    <div
      class="ai-problem-analysis-tips"
      v-if="analysisTipClose"
      @click="closeTip"
    >
      <div class="ai-problem-analysis-tips-left">
        <img src="/static-dist/app/img/question-bank/ai-analysis-tips-info.png" alt="" />
        <span>{{ t("itemEngine.aiAnalysisTips") }}</span>
      </div>
      <div class="ai-problem-analysis-tips-right">
        <img src="/static-dist/app/img/question-bank/ai-analysis-tips-close.png" alt="" />
      </div>
    </div>
    <a-row class="ibs-item-import-content">
      <a-col :span="6" class="ibs-import-left">
        <import-data
          :totalItem="items.length"
          :totalTypes="allTypes"
          :allScore="allScore"
          :importType="importType"
        ></import-data>
        <import-list
          :items="items"
          :totalTypes="allTypes"
          :typeIndex="typeIndex"
          :category="category"
          :errorList="errorList"
          :repeatList="repeatList"
          @setCategory="setCategory"
          @setDifficult="setDifficult"
          @setScore="setScore"
        ></import-list>
      </a-col>

      <a-col
        :span="17"
        :offset="1"
        class="ibs-import-right"
        v-if="items.length > 0"
      >
        <div
          class="ibs-item-import-testpapername"
          v-if="importType === 'testpaper'"
        >
          <a-input
            :placeholder="t('itemImport.SetTitle_tip')"
            v-model="fileName"
          />
        </div>

        <div
          v-for="(item, itemIndex) in items"
          :key="itemIndex"
          :id="`ibs-import-${itemIndex}`"
        >
          <div class="ibs-import-item">
            <a-row>
              <a-col :span="2" class="ibs-text-right ibs-mt16">
                <span>{{ itemIndex + 1 }}、</span>
              </a-col>
              <a-col :span="22">
                <material-title
                  v-show="item.type === 'material'"
                  :material="item.material"
                  :item="items"
                ></material-title>
                <!-- ------------题目区域------------ -->
                <div
                  class="ibs-pl16 ibs-pr16"
                  v-for="(question, questionIndex) in item.questions"
                  :key="questionIndex + question.answer"
                >
                  <a-row>
                    <a-col
                      :span="2"
                      class="ibs-text-right ibs-mt16"
                      v-show="item.type === 'material'"
                    >
                      <span>（{{ questionIndex + 1 }}） </span>
                    </a-col>
                    <a-col :span="item.type === 'material' ? 22 : 24">
                      <judge-type
                        v-if="question.answer_mode === 'true_false'"
                        :question="question"
                        :item="item"
                        :mode="mode"
                        :showScoreAndSeq="false"
                        :seq="`${itemIndex}-${questionIndex}`"
                        @changeAnalysis="changeAnalysis"
                      >
                      </judge-type>
                      <single-choice
                        v-if="question.answer_mode === 'single_choice'"
                        :question="question"
                        :item="item"
                        :mode="mode"
                        :showScoreAndSeq="false"
                        :seq="`${itemIndex}-${questionIndex}`"
                        @changeAnalysis="changeAnalysis"
                      ></single-choice>
                      <choice
                        v-if="
                          question.answer_mode === 'choice' ||
                            question.answer_mode === 'uncertain_choice'
                        "
                        :question="question"
                        :item="item"
                        :mode="mode"
                        :showScoreAndSeq="false"
                        :seq="`${itemIndex}-${questionIndex}`"
                        @changeAnalysis="changeAnalysis"
                      ></choice>
                      <essay
                        v-if="question.answer_mode === 'rich_text'"
                        :question="question"
                        :item="item"
                        :mode="mode"
                        :showScoreAndSeq="false"
                        :seq="`${itemIndex}-${questionIndex}`"
                        @changeAnalysis="changeAnalysis"
                      ></essay>
                      <fill
                        v-if="question.answer_mode === 'text'"
                        :question="question"
                        :item="item"
                        :mode="mode"
                        :showScoreAndSeq="false"
                        :seq="`${itemIndex}-${questionIndex}`"
                        @changeAnalysis="changeAnalysis"
                      ></fill>
                    </a-col>
                  </a-row>
                </div>
                <!-- ------------题目区域------------ -->
                <div class="ibs-import-material-analysis">
                  <material-analysis
                    v-show="item.type === 'material' && item.analysis"
                    :analysis="item.analysis"
                    :attachments="item.attachments"
                  ></material-analysis>
                </div>

                <!-- ------------编辑区域------------ -->
                <a-row class="ibs-mt16">
                  <a-col :span="24">
                    <item-footer
                      :categoryName="item.category_name"
                      :difficulty="item.difficulty"
                      :score="getScore(item)"
                    ></item-footer>
                    <div class="ibs-sub-operation ibs-import-edit">
                      <a-button
                        type="link"
                        class="ibs-mr8 ibs-pl0"
                        @click="showEditModal(item, itemIndex)"
                      >
                        <i class="ib-icon ib-icon-setting"></i
                        >{{ t("Edit") }}</a-button
                      >
                      <a-button
                        type="link"
                        class="ibs-mr8"
                        @click="deleteItem(itemIndex)"
                        ><i class="ib-icon ib-icon-delete"></i>{{ t("Delete") }}
                      </a-button>
                      <a-button
                        type="link"
                        class="ibs-mr8"
                        @click="createItem(item, itemIndex)"
                        ><i class="ib-icon ib-icon-add"></i
                        >{{ t("itemImport.AfterAddItem") }}
                      </a-button>
                    </div>
                  </a-col>
                </a-row>
              </a-col>
            </a-row>

            <!-- ------------编辑区域------------ -->
          </div>
        </div>
        <div class="ibs-item-import__footer ibs-mb16" ref="ibsImportBtn">
          <a-button
            type="primary"
            class="ibs-item-import-btn"
            @click="finishImport"
            :disabled="errorList.length > 0"
            :loading="loading"
            >{{ t("itemImport.FinishImport") }}</a-button
          >
        </div>
      </a-col>
    </a-row>

    <div class="ibs-item-import__footer--fix" v-show="showFixBtn">
      <a-button
        type="primary"
        class="ibs-item-import-btn"
        @click="finishImport"
        :disabled="errorList.length > 0"
        :loading="loading"
        >{{ t("itemImport.FinishImport") }}</a-button
      >
    </div>

    <!-- 创建和新增题目 -->
    <a-modal
      :title="modelData.title"
      v-model="visible"
      @ok="handleOk"
      @cancel="handleCancel"
      width="800px"
      :cancelText="t('cancel')"
      :bodyStyle="{ textAlign: 'left' }"
      :destroyOnClose="true"
      :footer="null"
      :getContainer="getContainer"
    >
      <item-manage
        v-if="modelData.mode === 'edit'"
        :showAttachment="showAttachment"
        :showCKEditorData="showCKEditorData"
        @deleteAttachment="deleteAttachment"
        :deleteAttachmentCallback="deleteAttachmentCallback"
        :previewAttachmentCallback="previewAttachmentCallback"
        @previewAttachment="previewAttachment"
        :mode="modelData.mode"
        cdnHost="service-cdn.qiqiuyun.net"
        :uploadSDKInitData="uploadSDKInitData"
        :category="modelData.category"
        :subject="modelData.subject"
        :type="modelData.type"
        :showModelBtn="true"
        :errorList="errorList"
        :aiAnalysisEnable="aiAnalysisEnable"
        @renderFormula="renderFormula"
        @getData="getEditData"
        @changeEditor="changeEditor"
        @getInitRepeatQuestion="getInitRepeatQuestion"
      />
      <item-manage
        v-if="modelData.mode === 'create'"
        :showAttachment="showAttachment"
        :showCKEditorData="showCKEditorData"
        cdnHost="service-cdn.qiqiuyun.net"
        :uploadSDKInitData="uploadSDKInitData"
        @deleteAttachment="deleteAttachment"
        :deleteAttachmentCallback="deleteAttachmentCallback"
        :previewAttachmentCallback="previewAttachmentCallback"
        @previewAttachment="previewAttachment"
        :mode="modelData.mode"
        :category="modelData.category"
        :type="modelData.type"
        :showModelBtn="true"
        :errorList="errorList"
        :aiAnalysisEnable="aiAnalysisEnable"
        @renderFormula="renderFormula"
        @getData="getCreateData"
        @changeEditor="changeEditor"
        @getInitRepeatQuestion="getInitRepeatQuestion"
      />
    </a-modal>

    <!-- 新增题目类型选择 -->
    <a-modal
      :title="t('itemImport.Add_new_item')"
      v-model="modelTypeVisible"
      :destroyOnClose="true"
      :footer="null"
      :getContainer="getContainer"
    >
      <a-row>
        <a-col :span="4" class="model-type-text">{{
          t("itemImport.Choose_type")
        }}</a-col>
        <a-col :span="20">
          <a-row>
            <a-col :span="8" class="ibs-mb24">
              <a-button
                type="primary"
                ghost
                @click="showCreateModal('single_choice')"
                >{{ t("single_choice") }}</a-button
              >
            </a-col>
            <a-col :span="8" class="ibs-mb24">
              <a-button
                type="primary"
                ghost
                @click="showCreateModal('choice')"
                >{{ t("choice") }}</a-button
              >
            </a-col>
            <a-col :span="8" class="ibs-mb24">
              <a-button
                type="primary"
                ghost
                @click="showCreateModal('uncertain_choice')"
                >{{ t("uncertain_choice_1") }}</a-button
              >
            </a-col>
            <a-col :span="8" class="ibs-mb24">
              <a-button
                type="primary"
                ghost
                @click="showCreateModal('determine')"
                >{{ t("determine") }}</a-button
              >
            </a-col>
            <a-col :span="8" class="ibs-mb24">
              <a-button type="primary" ghost @click="showCreateModal('fill')">{{
                t("fill")
              }}</a-button>
            </a-col>
            <a-col :span="8" class="ibs-mb24">
              <a-button
                type="primary"
                ghost
                @click="showCreateModal('essay')"
                >{{ t("essay") }}</a-button
              >
            </a-col>
            <a-col :span="8" class="ibs-mb24">
              <a-button
                type="primary"
                ghost
                @click="showCreateModal('material')"
                >{{ t("material") }}</a-button
              >
            </a-col>
          </a-row>
        </a-col>
      </a-row>
    </a-modal>

    <div id="math-editor-iframe-container" class="hidden"></div>
  </div>
</template>

<script>
import judgeType from "./item-engine-components/judge";
import singleChoice from "./item-engine-components/single-choice";
import choice from "./item-engine-components/choice";
import essay from "./item-engine-components/essay";
import fill from "./item-engine-components/fill";
import materialTitle from "./item-engine-components/material-title";
import materialAnalysis from "./item-engine-components/material-analysis";
import importData from "./item-import-components/import-data";
import importList from "./item-import-components/import-list";
import itemFooter from "./item-import-components/item-footer";
import itemManage from "./item-manage";
import Locale from "common/vue/mixins/locale";
import {MathEditor} from "@codeages/math-editor";

const data = {
  fileName: "这是试卷",
  items: [
    {
      type: "single_choice",
      material: "科目汇总表的汇总范围是（）。",
      attachments: [],
      analysis: "<p>这是解析</p>",
      category_id: "0",
      category_name: "",
      difficulty: "normal",
      score: "5.0",
      questions: [
        {
          stem: "",
          seq: "1",
          score: 2,
          answer_mode: "single_choice",
          errors: {
            answer: {
              element: "answer",
              index: -1,
              code: 100003,
              message: "缺少正确答案"
            },
            options_2: {
              element: "options",
              index: 2,
              code: 100002,
              message: "缺少选项"
            },
            stem: {
              element: "stem",
              index: -11,
              code: 100001,
              message: "缺少题干"
            }
          },
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
                text: ""
              }
            },
            {
              radio: {
                val: "D",
                text: "全部账户的借、贷方余额"
              }
            }
          ],
          answer: [],
          analysis: "<p>这是解析</p>",
          attachments: []
        }
      ]
    },
    {
      type: "uncertain_choice",
      material: "资产应具备的基本特征有（）。",
      attachments: [],
      analysis: "<p>这是解析</p>",
      difficulty: "normal",
      category_id: "0",
      category_name: "",
      score: "5.0",
      questions: [
        {
          stem: "资产应具备的基本特征有（）。",
          answer_mode: "uncertain_choice",
          score: "5.0",
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
          created_time: "1584945876",
          attachments: []
        }
      ]
    },
    {
      type: "choice",
      material: "资产应具备的基本特征有（）。",
      attachments: [],
      analysis: "<p>这是解析</p>",
      difficulty: "normal",
      category_id: "0",
      category_name: "",
      score: "5.0",
      questions: [
        {
          stem: "",
          score: 2,
          answer_mode: "choice",
          errors: {
            answer: {
              element: "answer",
              index: -1,
              code: 100003,
              message: "缺少正确答案"
            },
            options_1: {
              element: "options",
              index: 1,
              code: 100002,
              message: "缺少选项"
            },
            options_2: {
              element: "options",
              index: 2,
              code: 100002,
              message: "缺少选项"
            },
            stem: {
              element: "stem",
              index: -11,
              code: 100001,
              message: "缺少题干"
            }
          },
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
                text: ""
              }
            },
            {
              checkbox: {
                val: "C",
                text: ""
              }
            },
            {
              checkbox: {
                val: "D",
                text: "资产预期能为企业带来经济利益"
              }
            }
          ],
          answer: [],
          analysis: "<p>这是解析</p>",
          attachments: []
        }
      ]
    },
    {
      type: "determine",
      material: "经上级有关部门批准的经济业务，应将批准文件作为原始凭证附件。",
      attachments: [],
      analysis: "<p>这是解析</p>",
      difficulty: "normal",
      category_id: "0",
      category_name: "",
      score: "5.0",
      questions: [
        {
          stem: "",
          score: 2,
          answer_mode: "true_false",
          errors: {
            stem: {
              element: "stem",
              index: -11,
              code: 100001,
              message: "缺少题干"
            }
          },
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
          attachments: []
        }
      ]
    },
    {
      type: "fill",
      material: "",
      attachments: [],
      analysis: "<p>这是解析</p>",
      difficulty: "normal",
      category_id: "0",
      category_name: "",
      score: "5.0",
      questions: [
        {
          stem:
            "填空题唐代诗人李白，字[[李白]]，号[[谪仙人|青莲居士]]，人称诗仙。",
          score: 2,
          answer_mode: "text",
          errors: {
            answers_0: {
              element: "answers",
              index: 0,
              code: 100002,
              message: "缺少正确答案"
            },
            stem: {
              element: "stem",
              index: -1,
              code: 100001,
              message: "缺少题干"
            }
          },
          response_points: [
            {
              text: []
            },
            {
              text: []
            }
          ],
          answer: ["", "谪仙人|青莲居士"],
          analysis: "<p>这是解析</p>",
          attachments: []
        }
      ]
    },
    {
      type: "essay",
      material:
        "<p>分别计算甲公司2X15年12月3日所得税负债或资产的账面余额。</p>",
      attachments: [],
      analysis: "<p>这是解析</p>",
      difficulty: "normal",
      question_num: "1",
      category_id: "0",
      category_name: "",
      score: "5.0",
      questions: [
        {
          id: "28",
          item_id: "28",
          stem:
            "<p>分别计算甲公司2X15年12月3日所得税负债或资产的账面余额。</p>",
          seq: "1",
          score: 2,
          answer_mode: "rich_text",
          errors: {
            answer: {
              element: "answer",
              index: -1,
              code: 100003,
              message: "缺少正确答案"
            },
            stem: {
              element: "stem",
              index: -11,
              code: 100001,
              message: "缺少题干"
            }
          },
          response_points: [],
          answer: [],
          analysis: "<p>这是解析</p>",
          attachments: []
        }
      ]
    },
    {
      type: "material",
      material: "",
      attachments: [],
      analysis: "<p>这是解析</p>",
      difficulty: "normal",
      category_id: "0",
      category_name: "",
      score: "5.0",
      errors: {
        stem: {
          element: "stem",
          index: -1,
          code: 100003,
          message: "缺少题干"
        },
        hasSubError: true
      },
      questions: [
        {
          stem: "",
          score: 2,
          answer_mode: "single_choice",
          errors: {
            answer: {
              element: "answer",
              index: -1,
              code: 100003,
              message: "缺少正确答案"
            },
            options_0: {
              element: "options",
              index: 0,
              code: 100002,
              message: "缺少选项"
            },
            options_2: {
              element: "options",
              index: 2,
              code: 100002,
              message: "缺少选项"
            },
            stem: {
              element: "stem",
              index: -11,
              code: 100001,
              message: "缺少题干"
            }
          },
          response_points: [
            {
              radio: {
                val: "A",
                text: ""
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
                text: ""
              }
            },
            {
              radio: {
                val: "D",
                text: "全部账户的借、贷方余额"
              }
            }
          ],
          answer: [],
          analysis: "<p>这是解析</p>",
          attachments: []
        },
        {
          stem: "<p>这是题干</p>",
          score: 0,
          answer_mode: "choice",
          errors: {
            options_1: {
              element: "options",
              index: 1,
              code: 100002,
              message: "缺少选项"
            }
          },
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
                text: ""
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
          attachments: []
        },
        {
          stem: "<p>这是题干</p>",
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
          attachments: []
        },
        {
          id: "32",
          item_id: "29",
          stem: "诗仙[[李白]], 号[[谪仙人|青莲居士]]",
          score: 0,
          answer_mode: "text",
          errors: {
            answers_1: {
              element: "answers",
              index: 0,
              code: 100002,
              message: "缺少正确答案"
            }
          },
          response_points: [
            {
              text: []
            },
            {
              text: []
            }
          ],
          answer: ["李白", ""],
          analysis: "<p>这是解析</p>",
          attachments: []
        },
        {
          stem: "",
          score: 0,
          answer_mode: "rich_text",
          errors: {
            answer: {
              element: "answer",
              index: -1,
              code: 100003,
              message: "缺少正确答案"
            },
            stem: {
              element: "stem",
              index: -11,
              code: 100001,
              message: "缺少题干"
            }
          },
          response_points: [
            {
              rich_text: []
            }
          ],
          answer: [],
          analysis: "<p>这是解析</p>",
          attachments: []
        }
      ]
    }
  ]
};
const baseCKEditorData = {
  publicPath: `${process.env.BASE_URL}/es-ckeditor/ckeditor.js`,
  fileSingleSizeLimit: 10,
  filebrowserImageUploadUrl: "",
  filebrowserImageDownloadUrl: "",
  language: "zh-cn",
  jqueryPath: "https://cdn.bootcss.com/jquery/3.4.1/jquery.js"
};

export default {
  name: "item-import",
  mixins: [Locale],
  props: {
    subject: {
      type: Object,
      default() {
        return data;
      }
    },
    showCKEditorData: {
      type: Object,
      default() {
        return baseCKEditorData;
      }
    },
    //题库id
    bank_id: {
      type: String,
      default: ""
    },
    //分类
    category: {
      type: Array,
      default: () => []
    },
    /**
     * 导入类型
     * 题目：item
     * 试卷：testpaper
     */
    importType: {
      type: String,
      default: "item"
    },
    repeatList: {
      type: Array,
      default: () => []
    },
    showAttachment: {
      type: String,
      default: "0"
    },
    cdnHost: {
      type: String,
      default: "service-cdn.qiqiuyun.net"
    },
    uploadSDKInitData: {
      type: Object,
      default: () => {}
    },
    deleteAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    previewAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    downloadAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    isDownload: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    aiAnalysisEnable: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      mode: "import",
      btnoffsetTop: 0,
      showFixBtn: false,
      visible: false,
      modelTypeVisible: false,
      currentIndex: 0,
      items: this.subject.items,
      allScore: 0,
      allTypes: {},
      errorList: [],
      typeIndex: { all: [] },
      modelData: {
        title: this.t("itemImport.AddItem"),
        mode: "create",
        category: [],
        subject: {},
        type: ""
      },
      fileName: this.subject.fileName || "",
      ids: this.subject.items.length,
      analysisTipClose: true,
    };
  },
  components: {
    judgeType,
    singleChoice,
    choice,
    essay,
    fill,
    materialTitle,
    materialAnalysis,
    importData,
    importList,
    itemFooter,
    itemManage
  },
  watch: {
    items: {
      handler: function(val) {
        this.formateItems(val);
      },
      deep: true,
      immediate: true
    }
  },
  provide() {
    return {
      showCKEditorData: this.showCKEditorData,
      showAttachment: Number(this.showAttachment),
      cdnHost: this.cdnHost,
      deleteAttachmentCallback: this.deleteAttachmentCallback,
      previewAttachmentCallback: this.previewAttachmentCallback,
      downloadAttachmentCallback: this.downloadAttachmentCallback,
      isDownload: this.isDownload,
      modeOrigin: "do"
    };
  },
  inject: ["self"],
  created() {
    this.$on("previewFile", this.previewAttachment);
    this.$on("getDeleteFile", this.deleteAttachment);
    this.$on("downloadFile", this.downloadAttachment);
  },
  mounted() {
    const subject = {
      fileName: this.fileName,
      items: this.items
    };
    if (this.errorList.length == 0) {
      this.$emit("getInitRepeatQuestion", subject);
    }
    this.$nextTick(() => {
      this.btnoffsetTop =
        this.$refs.ibsImportBtn.offsetTop -
          document.documentElement.clientHeight || document.body.clientHeight;
      window.addEventListener("scroll", this.changBtnFix);
    });
    this.items.forEach((item, index) => {
      this.$set(this.items[index], "ids", index);
    });
    //监听关闭模态框
    this.$on("closeModal", this.closeModalData);
    this.formatMathAnswer();
  },
  methods: {
    formatMathAnswer() {
      const mathEditor = new MathEditor(
          document.getElementById('math-editor-iframe-container'),
          `/static-dist/libs/math-editor/math-editor.html?${window.app.version}`
      );
      mathEditor.on('ready', async () => {
        for (const [index, item] of this.items.entries()) {
          for (const [questionIndex, question] of item.questions.entries()) {
            if (question.answer_mode === 'text') {
              for (const [answerIndex, answer] of question.answer.entries()) {
                const latexMatch = answer.match(/\$\$([^$]+)\$\$/);
                if (latexMatch) {
                  mathEditor.set(this.formatSpecialMathAnswer(latexMatch[1]));
                  const latex = await mathEditor.get();
                  question.stem = question.stem.replace(`[[$$${latexMatch[1]}$$]]`, `[[$$$${latex}$$$]]`);
                  question.answer[answerIndex] = `$$${latex}$$`;
                  item.questions[questionIndex] = question;
                }
              }
            }
          }
          this.$set(this.items, index, item);
        }
        this.renderFormula();
        mathEditor.close();
      });
      mathEditor.open();
    },
    formatSpecialMathAnswer(latex) {
      latex = latex.replace(/\\mathrm\{([^}]*)\}/g, '$1 ');
      latex = latex.replace('\\bot ', '\\perp ');

      return latex;
    },
    //每次item改动统一计算数据
    formateItems() {
      this.allTypes = {};
      this.allScore = 0;
      this.errorList = [];
      this.typeIndex = { all: [] };
      this.items.forEach((item, index) => {
        this.totalScore(item);
        this.totalTypes(item);
        this.totalError(item, index);
        this.getTypeIndex(item, index);
      });
    },
    //统计总分
    totalScore(item) {
      if (item.type === "material") {
        item.questions.forEach(question => {
          this.allScore += parseFloat(question.score);
        });
      } else {
        this.allScore += parseFloat(item.questions[0].score);
      }
    },
    //统计类型个数
    totalTypes(item) {
      if (!this.allTypes[item.type]) {
        this.$set(this.allTypes, item.type, 1);
      } else {
        this.allTypes[item.type] = this.allTypes[item.type] + 1;
      }
    },
    //统计错误题目数据
    totalError(item, index) {
      if (item.type === "material" && item.errors) {
        this.errorList.push(index);
      } else if (item.questions[0].errors) {
        this.errorList.push(index);
      }
    },
    //获取每个题型的 ids
    getTypeIndex(item) {
      if (!this.typeIndex[item.type]) {
        this.$set(this.typeIndex, item.type, []);
      }
      this.typeIndex[item.type].push(item.ids);
      if (item.type !== "material") this.typeIndex.all.push(item.ids);
    },
    changBtnFix() {
      if (
        this.btnoffsetTop <
        (document.documentElement.clientHeight || document.body.clientHeight)
      ) {
        return;
      }
      const scrollTop =
        window.pageYOffset ||
        document.documentElement.scrollTop ||
        document.body.scrollTop;
      if (scrollTop >= this.btnoffsetTop) {
        //移除固定按钮
        this.showFixBtn = false;
      } else {
        this.showFixBtn = true;
        //固定按钮
      }
    },
    cancel() {
      this.visible = false;
    },
    handleOk() {
      this.visible = false;
    },
    handleCancel() {
      this.self.isWrong = false;
    },
    getContainer() {
      return document.getElementById("item-bank-sdk-import");
    },
    showEditModal(item, itemIndex) {
      this.visible = true;
      this.currentIndex = itemIndex;
      this.modelData.title = this.t("itemImport.EditItem");
      this.modelData.mode = "edit";
      this.modelData.category = this.category;
      this.modelData.subject = item;
      this.modelData.type = item.type;

      if (this.errorList.length == 0) {
        this.$emit("editQuestion", item, this.items);
      }
    },
    showCreateModal(type) {
      this.modelData.title = this.t("itemImport.AddItem");
      this.modelData.mode = "create";
      this.modelData.category = this.category;
      this.modelData.type = type;
      this.modelTypeVisible = false;
      this.visible = true;
    },
    createItem(item, itemIndex) {
      this.currentIndex = itemIndex;
      this.modelTypeVisible = true;
    },
    getEditData(value) {
      this.visible = false;
      value.data.ids = this.items[this.currentIndex].ids;
      value.data.type = this.modelData.type;
      if (value.data.type === "material") {
        this.checkMaterialError(value);
      }
      this.$set(this.items, this.currentIndex, value.data);
      //this.items[this.currentIndex] = value.data;
    },
    checkMaterialError(value) {
      let errors = 0;
      console.log(value);
      value.data.questions.forEach(question => {
        if (question.errors) {
          errors += 1;
        }
      });
      console.log(errors);
      if (!errors) {
        if (value.data.errors) {
          delete value.data.errors;
        }
      } else {
        value.data.errors = {
          hasSubError: true
        };
      }
    },
    getCreateData(value) {
      this.visible = false;
      this.ids += 1;
      value.data.ids = this.ids;
      value.data.type = this.modelData.type;
      this.items.splice(this.currentIndex + 1, 0, value.data);
    },
    getScore(item) {
      if (item.type === "material") {
        return this.t("itemImport.SubItem_all_score");
      } else {
        const cell = this.t("itemImport.Cell_Score");
        return `${item.questions[0].score}${cell}`;
      }
    },
    deleteItem(itemIndex) {
      const that = this;
      this.$confirm({
        title: this.t("itemImport.Confirm_delete"),
        content: this.t("itemImport.Confirm_delete_tip"),
        okText: this.t("confirm"),
        cancelText: this.t("cancel"),
        // getContainer: this.getContainer,
        class: "ibs-text-left",
        onOk() {
          that.items.splice(itemIndex, 1);
          that.getEditRepeatQuestion({
            fileName: that.fileName,
            items: that.items
          });
          that.forceRemoveModalDom();
          that.$emit("renderFormula");
        },
        onCancel() {
          that.forceRemoveModalDom();
        }
      });
    },
    forceRemoveModalDom() {
      const modal = document.querySelector(".ibs-text-left").parentNode;

      if (modal) {
        modal.remove();
      }

      document.body.style = "";
    },
    //设置分数
    setScore(list, score, otherScore1) {
      //只允许更改非材料题
      this.items.forEach(item => {
        list.forEach(ids => {
          if (item.ids === ids) {
            item.questions[0].score = score;
            if (item.type === 'choice' || item.type === 'uncertain_choice') {
              item.questions[0].otherScore = otherScore1;
              item.questions[0].otherScore1 = otherScore1;
              item.questions[0].scoreType = 'question';
            }
          }
        });
      });
    },
    //设置难度
    setDifficult(list, difficulty) {
      this.items.forEach(item => {
        list.forEach(ids => {
          if (item.ids === ids) {
            item.difficulty = difficulty;
          }
        });
      });
    },
    //设置分类
    setCategory(list, category_id, category_name) {
      this.items.forEach(item => {
        list.forEach(ids => {
          if (item.ids === ids) {
            item.category_id = category_id;
            item.category_name = category_name;
          }
        });
      });
    },
    //完成导入
    finishImport() {
      if (this.errorList.length) {
        const errorList = this.errorList.map(item => {
          return item + 1;
        });
        const message = this.t("itemImport.Order_Error")(errorList.join("、"));
        this.$message.error(message);
        return;
      }
      if (this.importType === "testpaper" && !this.fileName) {
        //只有在试卷导入才要检验
        this.$message.error(this.t("itemImport.Title_tip"));
        return;
      }
      const subject = {
        fileName: this.fileName,
        items: this.items
      };
      this.$emit("getRepeatQuestion", subject);
    },
    closeModalData() {
      this.visible = false;
    },
    deleteAttachment(data) {
      let fileId = data.fileId ? data.fileId : data;
      let flag = data.flag ? data.flag : false;

      if (flag) {
        const currentValue = this.items[this.currentIndex];

        currentValue.questions = currentValue.questions.map(question => {
          question.attachments = question.attachments.filter(
            item => item.id !== fileId
          );

          return question;
        });

        this.items.splice(this.currentIndex, 1, currentValue);
      }

      this.$emit("deleteAttachment", fileId, flag);
    },
    previewAttachment(fileId) {
      this.$emit("previewAttachment", fileId);
    },
    downloadAttachment(fileId) {
      this.$emit("downloadAttachment", fileId);
    },
    changeEditor(data) {
      this.$emit("changeEditor", data, this.items);
    },
    getEditRepeatQuestion(subject) {
      this.$emit("getEditRepeatQuestion", subject);
    },
    getInitRepeatQuestion() {
      const subject = {
        fileName: this.fileName,
        items: this.items
      };
      this.$emit("getEditRepeatQuestion", subject);
    },
    renderFormula() {
      this.$emit("renderFormula");
    },
    closeTip() {
      this.analysisTipClose = false;
    },
    changeAnalysis(seq, analysis) {
      const [itemIndex, questionIndex] = seq.split("-");
      const item = this.items[itemIndex];
      if (item.type !== "material") {
        item.analysis = analysis;
      }
      item.questions[questionIndex].analysis = analysis;
      this.$set(this.items, itemIndex, item);
    }
  }
};
</script>

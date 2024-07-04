<template>
  <div class="test-create">
    <div class="test-create-title">
      <div class="test-create-title-left">
        <div class="test-create-title-left-back" @click="backConfirm">
          <span class="test-create-title-left-back-img">
            <img src="/static-dist/app/img/question-bank/back-image.png" alt=""/>
          </span>
          <span class="test-create-title-left-back-text">返回</span>
        </div>
        <i></i>
        <span class="test-create-title-left-type">随机卷</span>
      </div>
      <div class="test-create-title-right">
        <span class="test-create-title-right-item">
          <span class="test-create-title-right-text">试卷</span>
          <span class="test-create-title-right-number">0</span>
        </span>
        <i></i>
        <span class="test-create-title-right-item">
          <span class="test-create-title-right-text">总分</span>
          <span class="test-create-title-right-number">0.0</span>
        </span>
      </div>
    </div>

    <div class="test-create-content">
      <a-form id="test-create-form" :form="form" class="test-paper-save-form">
        <div class="test-paper-save-form-item">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-required">*</span>
            <span class="test-paper-save-form-item-label-text">试卷名称</span>
          </div>
          <a-form-item>
            <a-input
              placeholder="请输入试卷名称"
              v-decorator="[
                'testname',
                { rules: [{ required: true, message: '请输入试卷名称' }] },
              ]"
            />
            <span class="max-num">0/50</span>
          </a-form-item>
        </div>

        <div class="test-paper-save-form-item">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-text">试卷说明</span>
          </div>
          <a-form-item>
            <a-input
              placeholder="请输入试卷说明"
              @focus="isShow = true"
              v-show="!isShow"
            />
            <span class="max-num">0/500</span>
            <div v-show="isShow">
              <a-textarea
                :data-image-download-url="showCKEditorData.publicPath"
                :name="`test-paper-explain`"
              />
            </div>
          </a-form-item>
        </div>

        <div class="test-paper-save-form-item">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-required">*</span>
            <span class="test-paper-save-form-item-label-text">试卷份数</span>
          </div>
          <div class="test-paper-number">
            <a-form-item>
              <a-input-number
                id="inputNumber"
                v-model="testNum"
                :min="1"
                :max="200"
                v-decorator="[
                  'testnumber',
                  { rules: [{ required: true, message: '请至少设置 1 份试卷' }] },
                ]"
              />
            </a-form-item>
            <span class="test-paper-number-text">≤200</span>
            <a-tooltip overlayClassName="test-paper-number-tips">
              <template slot="title">
                为了确保每位学生都能获得丰富多样的学习体验，并考虑到系统处理效率及资源分配的最优状态，我们精心设定了试卷生成的灵活性与合理性平衡点。目前，系统支持您创建最多200张独特的随机试卷
              </template>
              <img
                class="test-paper-number-tips-icon"
                src="/static-dist/app/img/question-bank/test-num-tips.png"
                alt=""
              />
            </a-tooltip>
          </div>
        </div>

        <div class="extraction-method-content">
          <div class="extraction-method-content-setting">
            <div class="test-paper-save-form-item">
              <div class="test-paper-save-form-item-label">
                <span class="test-paper-save-form-item-label-text">抽题方式</span>
              </div>
              <a-radio-group name="type" default-value="questionType" @change="onRadioChange">
                <a-radio value="questionType">按题型抽题</a-radio>
                <a-radio value="questionTypeCategory">按题型+分类抽题</a-radio>
              </a-radio-group>
            </div>
            <a-dropdown :trigger="['click']" placement="bottomRight" @visibleChange="onMenuVisibleChange"
                        v-show="chooseQuestionBy === 'questionType'">
              <div class="question-type-display-setting">
                <img
                  src="/static-dist/app/img/question-bank/question-type-show-image.png"
                  alt=""
                />
                <span>题型展示设置</span>
              </div>
              <a-menu slot="overlay" class="question-type-setting-menu">
                <draggable v-model="questionAllTypes" handle=".question-type-setting-menu-item-label-icon"
                           drag-class="question-type-setting-menu-item-drag">
                  <transition-group>
                    <a-menu-item v-for="questionType in questionAllTypes" :key="questionType.type"
                                 class="question-type-setting-menu-item">
                      <span class="question-type-setting-menu-item-label">
                        <img
                          class="question-type-setting-menu-item-label-icon"
                          src="/static-dist/app/img/question-bank/question-type-drag.png"
                          alt=""
                        />
                        <span class="question-type-setting-menu-item-label-text">{{ questionType.name }}</span>
                      </span>
                      <a-switch v-model:checked="questionType.checked"
                                class="question-type-setting-menu-item-switch"></a-switch>
                    </a-menu-item>
                  </transition-group>
                </draggable>
              </a-menu>
            </a-dropdown>
          </div>

          <div class="question-type-display" v-show="chooseQuestionBy === 'questionType'">
            <div class="question-type-display-header">
              <div class="question-type-display-header-top">题型设置</div>
              <div class="question-type-display-header-normal">题目数量</div>
              <div class="question-type-display-header-normal">每题分值</div>
              <div class="question-type-display-header-bottom">
                <span class="question-type-display-header-bottom-title">合计</span>
                <span class="question-type-display-header-bottom-description">（题数/总分）</span>
              </div>
            </div>
            <div v-for="type in questionDisplayTypes">
              <div class="question-type-display-header-top">{{ type.name }}</div>
              <div class="question-type-display-cell-number">
                <span class="question-type-display-cell-number-edit">
                  <input type="number" value="0"/>
                </span>
                <span class="question-type-display-cell-number-total">/{{ questionCounts[type.type].total }}</span>
              </div>
              <div class="question-type-display-cell-score">
                <input type="number" value="2"/>
              </div>
              <div class="question-type-display-cell-sum">0 / 0.0</div>
            </div>
          </div>

          <div class="question-category-choose" v-show="chooseQuestionBy === 'questionTypeCategory'">
            <div class="question-category-choose-btn" @click="displayChooseCategoryDrawer">
              <img
                class="question-category-choose-btn-icon"
                src="/static-dist/app/img/question-bank/question-category-choose.png"
                alt=""
              />
              <span class="question-category-choose-btn-text">选择分类</span>
            </div>
            <!--            <a-drawer title="选择分类" :visible="drawerVisible">-->

            <!--            </a-drawer>-->
          </div>
        </div>

        <div class="test-paper-save-form-item">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-text">难度调节</span>
          </div>
          <a-switch checked-children="开启" un-checked-children="关闭" @change="onSwitchChange"/>
        </div>

        <div class="test-paper-difficulty" v-show="difficultyVisible">
          <div class="test-paper-save-form-item">
            <div class="test-paper-save-form-item-label">
              <span class="test-paper-save-form-item-label-text">试卷难度</span>
            </div>
            <a-slider range :default-value="[30, 70]" :tooltipVisible="false" @change="onSliderChange"
                      class="test-paper-difficulty-slider"/>
          </div>
          <div class="test-paper-difficulty-content">
            <div class="test-paper-difficulty-scale">
              <span v-for="(difficulty, key) in difficultyScales"
                    class="test-paper-difficulty-scale-text">{{ difficulty.text }} {{ difficulty.scale }}%</span>
            </div>
            <div class="test-paper-difficulty-count-ratio">
              <div class="test-paper-difficulty-count-ratio-item">
                <span class="test-paper-difficulty-count-ratio-item-text">{{ difficultyScales.simple.text }}</span>
                <span>
                  <span class="test-paper-difficulty-count-choose">{{ difficultyScales.simple.chooseCount }}</span>
                  <span class="test-paper-difficulty-count-total">/{{ difficultyScales.simple.totalCount }}</span>
                </span>
              </div>
              <i></i>
              <div class="test-paper-difficulty-count-ratio-item">
                <span class="test-paper-difficulty-count-ratio-item-text">{{ difficultyScales.normal.text }}</span>
                <span>
                  <span class="test-paper-difficulty-count-choose">{{ difficultyScales.normal.chooseCount }}</span>
                  <span class="test-paper-difficulty-count-total">/{{ difficultyScales.normal.totalCount }}</span>
                </span>
              </div>
              <i></i>
              <div class="test-paper-difficulty-count-ratio-item">
                <span class="test-paper-difficulty-count-ratio-item-text">{{ difficultyScales.difficulty.text }}</span>
                <span>
                  <span class="test-paper-difficulty-count-choose">{{ difficultyScales.difficulty.chooseCount }}</span>
                  <span class="test-paper-difficulty-count-total">/{{ difficultyScales.difficulty.totalCount }}</span>
                </span>
              </div>
            </div>
            <span class="test-paper-difficulty-tips">如果某个难度的题目数不够，将会随机选择题目来补充</span>
          </div>
        </div>
      </a-form>
    </div>

    <div class="test-paper-save-footer">
      <button class="test-paper-save" @click="saveTestPaper()">保存</button>
      <button class="test-create-cancel">取消</button>
    </div>
  </div>
</template>

<script>
import { apiClient } from 'common/vue/service/api-client';
import loadScript from "load-script";
import Draggable from 'vuedraggable';

export default {
  components: {
    Draggable
  },
  data() {
    return {
      isShow: false,
      explainEditor: "",
      showCKEditorData: {
        filebrowserImageDownloadUrl:
          "/editor/download?token=Mnxjb3Vyc2V8aW1hZ2V8MTY3NzY2OTYyN3w5ZjM2NmRjNjg1ZjUyMzJkZGI0ZjM3MDQ1NzMzODhiNA",
        filebrowserImageUploadUrl:
          "/editor/upload?token=Mnxjb3Vyc2V8aW1hZ2V8MTY3NzY2OTYyN3w5ZjM2NmRjNjg1ZjUyMzJkZGI0ZjM3MDQ1NzMzODhiNA",
        jqueryPath:
          "/static-dist/libs/jquery/dist/jquery.min.js?version=23.1.6",
        language: "zh-cn",
        publicPath: "/static-dist/libs/es-ckeditor/ckeditor.js?version=23.1.6"
      },
      testNum: 1,
      chooseQuestionBy: 'questionType',
      drawerVisible: false,
      difficultyVisible: false,
      questionDisplayTypes: [],
      questionAllTypes: [
        {
          type: "single_choice",
          name: "单选题",
          checked: true,
        },
        {
          type: "choice",
          name: "多选题",
          checked: true,
        },
        {
          type: "essay",
          name: "问答题",
          checked: true,
        },
        {
          type: "uncertain_choice",
          name: "不定项",
          checked: true,
        },
        {
          type: "determine",
          name: "判断题",
          checked: true,
        },
        {
          type: "fill",
          name: "填空题",
          checked: true,
        },
        {
          type: "material",
          name: "材料题",
          checked: true,
        },
      ],
      questionCounts: {
        single_choice: {
          choose: 0,
          total: 0,
        },
        choice: {
          choose: 0,
          total: 0,
        },
        essay: {
          choose: 0,
          total: 0,
        },
        uncertain_choice: {
          choose: 0,
          total: 0,
        },
        determine: {
          choose: 0,
          total: 0,
        },
        fill: {
          choose: 0,
          total: 0,
        },
        material: {
          choose: 0,
          total: 0,
        },
        sum: {
          choose: 0,
          total: 0,
        },
      },
      difficultyScales: {
        simple: {
          text: '简单',
          scale: 30,
          totalCount: 5,
          chooseCount: 5,
        },
        normal: {
          text: '一般',
          scale: 40,
          totalCount: 20,
          chooseCount: 14,
        },
        difficulty: {
          text: '困难',
          scale: 30,
          totalCount: 10,
          chooseCount: 10,
        },
      },
      form: this.$form.createForm(this, {name: "save-test-paper"}),
    };
  },
  mounted() {
    this.getQuestionCounts();
    this.renderQuestionTypeTable();
    this.$nextTick(() => {
      loadScript(this.showCKEditorData.jqueryPath, err => {
        if (err) {
          console.log(err);
        }
        loadScript(this.showCKEditorData.publicPath, err => {
          if (err) {
            console.log(err);
          }
          this.TestPaperExplain();
        });
      });
    });
  },
  methods: {
    TestPaperExplain() {
      this.explainEditor = window.CKEDITOR.replace(`test-paper-explain`, {
        toolbar: "Minimal",
        fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
        filebrowserImageUploadUrl: this.showCKEditorData.filebrowserImageUploadUrl,
        filebrowserImageDownloadUrl: this.showCKEditorData.filebrowserImageDownloadUrl,
        language: this.showCKEditorData.language
      });
      this.explainEditor.on("blur", () => {
        this.isShow = false
      })
    },
    getQuestionCounts() {
      apiClient.get('/api/item/questionType/count', {
        params: {
          bank_id: document.getElementById('itemBankId').value,
        }
      }).then(res => {
        res.forEach((item) => {
          this.questionCounts[item.type].total = item.itemNum;
          this.questionCounts.sum.total += item.itemNum;
        });
      });
    },
    renderQuestionTypeTable() {
      let displayTypes = [];
      for (const type of this.questionAllTypes) {
        if (type.checked) {
          displayTypes.push(type);
        }
      }
      this.questionDisplayTypes = displayTypes;
    },
    backConfirm() {
      this.$confirm({
        title: '是否要保存更改？',
        okText: '保存',
        cancelText: '不保存',
        centered: true,
        onOk: () => {
          this.$router.push({
            name: 'list',
          });
          this.$message.success('创建成功');
        },
        onCancel: () => {
          this.$router.push({
            name: 'list',
          });
        }
      });
    },
    onMenuVisibleChange(visible) {
      if (!visible) {
        this.renderQuestionTypeTable();
      }
    },
    onRadioChange(event) {
      this.chooseQuestionBy = event.target.value;
    },
    onSwitchChange(checked, event) {
      this.difficultyVisible = checked;
    },
    onSliderChange(value) {
      this.difficultyScales.simple.scale = value[0];
      this.difficultyScales.normal.scale = value[1] - value[0];
      this.difficultyScales.difficulty.scale = 100 - value[1];
    },
    displayChooseCategoryDrawer() {
      this.drawerVisible = true;
    },
    saveTestPaper() {
      this.form.validateFields(err => {
        if (!err) {
          console.info('success');
        }
      });
    },
  },
}
</script>

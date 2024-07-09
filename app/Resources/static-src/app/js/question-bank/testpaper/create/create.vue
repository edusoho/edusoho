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
              @change="handleChangeName"
              v-decorator="[
                'testname',
                { initialValue: testpaperFormState.name, rules: [{ required: true, message: '请输入试卷名称' }] },
              ]"
            />
            <span class="max-num">{{testpaperFormState.name ? testpaperFormState.name.length : 0}}/50</span>
          </a-form-item>
        </div>

        <div class="test-paper-save-form-item">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-text">试卷说明</span>
          </div>
          <a-form-item>
            <div>
              <a-textarea
                v-model="testpaperFormState.description"
                :data-image-download-url="showCKEditorData.publicPath"
                :name="`test-paper-explain`"
              />
            </div>
            <span class="max-num">{{testpaperFormState.description ? testpaperFormState.description.length : 0}}/500</span>
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
                :min="1"
                :max="200"
                @change="handleChangeNum"
                v-decorator="[
                  'testnumber',
                  { initialValue: testpaperFormState.num, rules: [{ required: true, message: '请至少设置 1 份试卷' }, ] },
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
              <a-radio-group v-model="testpaperFormState.generateType" name="type" @change="onRadioChange">
                <a-radio value="questionType">按题型抽题</a-radio>
                <a-radio value="questionTypeCategory">按题型+分类抽题</a-radio>
              </a-radio-group>
            </div>
            <question-type-display-set-menu v-if="chooseQuestionBy === 'questionType'" :default-question-all-types="questionAllTypes"
                                            @updateDisplayQuestionType="handleUpdateDisplayQuestionType"/>
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
            <div class="question-category-choose-btn" @click="onDrawerDisplay">
              <img
                class="question-category-choose-btn-icon"
                src="/static-dist/app/img/question-bank/question-category-choose.png"
                alt=""
              />
              <span class="question-category-choose-btn-text">选择分类</span>
            </div>
            <a-drawer
              title="选择分类"
              width="960"
              :visible="drawerVisible"
              :maskClosable="false"
              @close="onDrawerClose"
              class="question-category-choose-drawer">
              <div class="question-category-choose-container-body">
                <div class="question-category-choose-all">
                  <div class="question-category-choose-all-header">
                    <a-input placeholder="搜索分类">
                      <img
                        slot="prefix"
                        class="question-category-choose-all-header-search-icon"
                        src="/static-dist/app/img/question-bank/question-category-search-icon.png"
                        alt=""
                      />
                    </a-input>
                    <a-checkbox :indeterminate="checkAllBoxIndeterminate" :checked="allCategoriesChecked"
                                @change="onCheckAllChange">
                      <span class="question-category-choose-all-header-check-all-text">选择全部分类</span>
                    </a-checkbox>
                  </div>
                  <div class="question-category-choose-all-body">
                    <div v-for="category in questionCategories" class="question-category-choose-all-body-category"
                         @mouseover="showCheckOperation(category.id)" @mouseleave="hideCheckOperation(category.id)">
                      <div :class="`question-category-choose-all-body-category-depth-${category.depth}`">
                        <a-checkbox :value="category.id" :checked="checkedQuestionCategoryIds[category.id]"
                                    @change="onCheckBoxChange">
                          <span class="question-category-choose-all-body-category-name">{{ category.name }}</span>
                        </a-checkbox>
                      </div>
                      <div>
                        <span class="question-category-choose-all-body-category-operation"
                              v-show="checkChildrenVisible(category.id)"
                              @click="checkedSelfAndChildren(category.id)">全选</span>
                        <span class="question-category-choose-all-body-category-operation"
                              v-show="unCheckChildrenVisible(category.id)"
                              @click="unCheckedSelfAndChildren(category.id)">取消全选</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="question-category-choose-selected">
                  <div class="question-category-choose-selected-header">
                    <span class="question-category-choose-selected-header-selected">已选</span>
                    <a-popconfirm
                      title="确定要清空全部吗？"
                      placement="bottomRight"
                      ok-text="确定"
                      cancel-text="取消"
                      @confirm="unCheckedAllCategories"
                    >
                      <span class="question-category-choose-selected-header-clear">清空</span>
                    </a-popconfirm>
                  </div>
                  <div class="question-category-choose-selected-body">
                    <div v-for="category in questionCategories" v-show="checkedQuestionCategoryIds[category.id]"
                         class="question-category-choose-selected-body-item">
                      <div class="question-category-choose-selected-body-item-text">
                        <div class="question-category-choose-selected-body-item-text-name">{{ category.name }}</div>
                        <span class="question-category-choose-selected-body-item-text-level">{{ category.level }}</span>
                      </div>
                      <img
                        class="question-category-choose-selected-body-item-remove"
                        src="/static-dist/app/img/question-bank/remove-selected-question-category-icon.png"
                        alt=""
                        @click="unCheckedCategory(category.id)"
                      />
                    </div>
                  </div>
                </div>
              </div>
              <div class="question-category-choose-container-footer">
                <div class="question-category-choose-container-footer-btn-group">
                  <button class="question-category-choose-container-footer-btn-cancel" @click="onDrawerClose">
                    <span class="question-category-choose-container-footer-btn-text">取消</span>
                  </button>
                  <button class="question-category-choose-container-footer-btn-save" @click="saveCheckedCategories">
                    <span class="question-category-choose-container-footer-btn-text">保存</span>
                  </button>
                </div>
              </div>
            </a-drawer>
            <question-type-category-display
              v-show="selectedQuestionCategories.length > 0"
              :question-display-types="questionDisplayTypes"
              :default-question-all-types="questionAllTypes"
              :categories="selectedQuestionCategories"
              :bankId="bankId"
              @updateCategories="handleUpdateCategories"
              @updateDisplayQuestionType="handleUpdateDisplayQuestionType"
            />
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
import {apiClient} from 'common/vue/service/api-client';
import loadScript from "load-script";
import Draggable from 'vuedraggable';
import QuestionTypeCategoryDisplay from './QuestionTypeCategoryDisplay.vue';
import QuestionTypeDisplaySetMenu from '../component/QuestionTypeDisplaySetMenu.vue';
import {Testpaper} from 'common/vue/service';

export default {
  components: {
    QuestionTypeDisplaySetMenu,
    QuestionTypeCategoryDisplay,
    Draggable
  },
  props: {
    itemBankId: null,
  },
  data() {
    return {
      description: undefined,
      bankId: document.getElementById('itemBankId').value,
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
      chooseQuestionBy: 'questionType',
      drawerVisible: false,
      difficultyVisible: false,
      checkChildrenOperationVisible: {},
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
      questionCategories: [],
      questionCategoriesTree: {},
      checkedQuestionCategoryIds: {},
      selectedQuestionCategories: [],
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
      categories: [],
      testpaperFormState: {
        name: '',
        description: '',
        type: 'random',
        questionBankId: null,
        mode: "rand",
        num: 1,
        generateType: "questionType",
        questionCategoryCounts: [],
        scores: {},
        scoreType: {
          choice: "question",
          uncertain_choice: "question",
          fill: "question"
        },
        choiceScore: {
          choice: 0,
          uncertain_choice: 0,
          fill: 0,
        },
        questionCount: 0,
        percentages: {
          simple: 30,
          normal: 30,
          difficulty: 40,
        },
        wrongQuestionRate: "0"
      },
      fetching: false,
    };
  },
  computed: {
    checkChildrenVisible() {
      return id => {
        if (!this.checkChildrenOperationVisible[id] || this.questionCategoriesTree[id].children.length === 0) {
          return false;
        }
        return this.hasChildUnchecked(id);
      };
    },
    unCheckChildrenVisible() {
      return id => {
        if (!this.checkChildrenOperationVisible[id] || this.questionCategoriesTree[id].children.length === 0) {
          return false;
        }
        return !this.hasChildUnchecked(id);
      };
    },
    checkAllBoxIndeterminate() {
      let checked = 0;
      for (const category of this.questionCategories) {
        if (this.checkedQuestionCategoryIds[category.id]) {
          checked++;
        }
      }
      return checked > 0 && checked < this.questionCategories.length;
    },
    allCategoriesChecked() {
      for (const category of this.questionCategories) {
        if (!this.checkedQuestionCategoryIds[category.id]) {
          return false;
        }
      }
      return this.questionCategories.length > 0;
    },
  },
  mounted() {
    this.fetchQuestionCounts();
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
    fetchQuestionCounts() {
      apiClient.get('/api/item/questionType/count', {
        params: {
          bank_id: this.bankId,
        }
      }).then(res => {
        res.forEach(item => {
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
    fetchQuestionCategories() {
      apiClient.get(`/api/item_bank/${this.bankId}/item_category_transform/treeList`).then(res => {
        this.questionCategories = [];
        this.questionCategoriesTree = {};
        res.forEach(category => {
          this.questionCategories.push({
            id: category.id,
            name: category.name,
            depth: category.depth,
            level: this.getCategoryLevelText(category.depth)
          });
          this.questionCategoriesTree[category.id] = {
            children: []
          };
          if (this.questionCategoriesTree[category.parent_id]) {
            this.questionCategoriesTree[category.parent_id].children.push(category.id);
          }
        });
      });
    },
    getCategoryLevelText(depth) {
      return {
        1: '一级分类',
        2: '二级分类',
        3: '三级分类',
      }[depth];
    },
    backConfirm() {
      this.$confirm({
        title: '是否要保存更改？',
        icon: 'exclamation-circle',
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
    handleUpdateDisplayQuestionType(questionDisplayTypes) {
      this.questionDisplayTypes = questionDisplayTypes;
    },
    onRadioChange(event) {
      this.chooseQuestionBy = event.target.value;
    },
    onCheckBoxChange(event) {
      this.$set(this.checkedQuestionCategoryIds, event.target.value, event.target.checked);
    },
    onCheckAllChange(event) {
      this.questionCategories.forEach(category => {
        this.$set(this.checkedQuestionCategoryIds, category.id, event.target.checked);
      });
    },
    onSwitchChange(checked, event) {
      this.difficultyVisible = checked;
    },
    onSliderChange(value) {
      this.difficultyScales.simple.scale = value[0];
      this.difficultyScales.normal.scale = value[1] - value[0];
      this.difficultyScales.difficulty.scale = 100 - value[1];
    },
    onDrawerDisplay() {
      this.fetchQuestionCategories();
      this.drawerVisible = true;
    },
    onDrawerClose() {
      this.$confirm({
        title: '确定放弃此次操作吗？',
        content: '当前操作尚未保存',
        icon: 'exclamation-circle',
        okText: '确定',
        cancelText: '取消',
        centered: true,
        onOk: () => {
          this.checkedQuestionCategoryIds = {};
          this.drawerVisible = false;
        },
      });
    },
    showCheckOperation(id) {
      this.$set(this.checkChildrenOperationVisible, id, true);
    },
    hideCheckOperation(id) {
      this.$set(this.checkChildrenOperationVisible, id, false);
    },
    hasChildUnchecked(id) {
      if (!this.checkedQuestionCategoryIds[id]) {
        return true;
      }
      if (this.questionCategoriesTree[id].children) {
        for (const childId of this.questionCategoriesTree[id].children) {
          if (this.hasChildUnchecked(childId)) {
            return true;
          }
        }
      }
      return false;
    },
    checkedSelfAndChildren(id) {
      this.$set(this.checkedQuestionCategoryIds, id, true);
      if (this.questionCategoriesTree[id].children) {
        this.questionCategoriesTree[id].children.forEach(childId => {
          this.checkedSelfAndChildren(childId);
        });
      }
    },
    unCheckedSelfAndChildren(id) {
      this.unCheckedCategory(id);
      if (this.questionCategoriesTree[id].children) {
        this.questionCategoriesTree[id].children.forEach(childId => {
          this.unCheckedSelfAndChildren(childId);
        });
      }
    },
    unCheckedAllCategories() {
      this.questionCategories.forEach(category => {
        this.unCheckedCategory(category.id);
      });
    },
    unCheckedCategory(id) {
      this.$set(this.checkedQuestionCategoryIds, id, false);
    },
    saveCheckedCategories() {
      this.selectedQuestionCategories = [];
      this.questionCategories.forEach(category => {
        if (this.checkedQuestionCategoryIds[category.id]) {
          this.selectedQuestionCategories.push(category);
        }
      });
      this.drawerVisible = false;
      this.$message.success('保存成功');
    },
    handleUpdateCategories(categories) {
      this.categories = categories;
    },
    saveTestPaper() {
      if (this.fetching) {
        return;
      }
      this.form.validateFields(async (err) => {
        if (!err) {

          this.fetching = true;
          let questionNum = 0;
          this.testpaperFormState.questionCategoryCounts = [];
          this.testpaperFormState.questionBankId = this.itemBankId;
          this.testpaperFormState.num = `${this.testpaperFormState.num}`;

          for (const category of this.categories) {

            const section = {};
            for (const questionType of category.questionTypes) {
              section[questionType.type] = {count: questionType.addNum, name: this.questionAllTypes.find(type => type.type === questionType.type).name};
              questionNum += questionType.addNum;
            }

            this.testpaperFormState.questionCategoryCounts.push({
              categoryId: category.id,
              sections: section
            })
          }

          this.testpaperFormState.questionCount = questionNum;
          const scored = {};

          for (const questionType of this.questionAllTypes) {
            scored[questionType.type] = questionType.score;
          }

          this.testpaperFormState.scores = scored;

          this.testpaperFormState.percentages = {
            simple: `${this.difficultyScales.simple.scale}`,
            normal: `${this.difficultyScales.normal.scale}`,
            difficulty: `${this.difficultyScales.difficulty.scale}`,
          }
          try {
            await Testpaper.create(this.testpaperFormState);
            this.$router.push({
              name: 'list',
            });
            this.$message.success('创建成功');
          } catch (err) {
            this.$message.error("创建失败", err)
          } finally {
            this.fetching = false;
          }
        }
      });
    },
    handleChangeNum(value) {
      this.testpaperFormState.num = Number.parseInt(value) || 1;
      this.form.setFieldsValue({
        testnumber: this.testpaperFormState.num,
      });
    },
    handleChangeName(value) {
      this.testpaperFormState.name = value.target.value;
      this.form.setFieldsValue({
        testname: value,
      });
    },
  },
}
</script>

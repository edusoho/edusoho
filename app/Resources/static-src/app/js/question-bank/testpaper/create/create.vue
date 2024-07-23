<template>
  <div class="test-create">
    <test-paper-save-header
      :type="testPaperFormState.type"
      :paper-question-count="paperQuestionCount"
      :paper-score="paperScore"
      @back-confirm="backConfirm"
    ></test-paper-save-header>

    <div class="test-create-content">
      <a-alert
        v-if="isPersonalTestPaper()"
        message="个性卷 —— 个性化纠错练习，提升学员对知识的全面掌握，错题，新题优先作答，错误率较高的知识点反个性习，提升掌握度！"
        type="success"
        closable
        show-icon
      >
        <template slot="icon">
          <img src="/static-dist/app/img/question-bank/testpaperAiIcon.png" alt=""/>
        </template>
      </a-alert>

      <a-form id="test-create-form" :form="form" class="test-paper-save-form">
        <div class="test-paper-save-form-item test-paper-save-form-item-align-flex-start">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-required">*</span>
            <span class="test-paper-save-form-item-label-text">试卷名称</span>
          </div>
          <a-form-item>
            <a-input
              placeholder="请输入试卷名称"
              :maxLength="50"
              @change="handleChangeName"
              v-decorator="[
                'name',
                { initialValue: testPaperFormState.name, rules: [{ required: true, message: '请输入试卷名称' }] },
              ]"
            />
            <span class="max-num">{{ testPaperFormState.name ? testPaperFormState.name.length : 0 }}/50</span>
          </a-form-item>
        </div>

        <div class="test-paper-save-form-item test-paper-save-form-item-align-flex-start">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-text">试卷说明</span>
          </div>
          <a-form-item>
            <a-input
              v-decorator="[
                'description',
                { initialValue: '', rules: [{ required: false, max: 500, message: '超出 500  个字符长度限制' }, ] },
              ]"
              placeholder="请输入试卷说明"
              @focus="onDescriptionInputFocus"
              v-show="!descriptionEditorVisible"
            />
            <span class="max-num" v-show="!descriptionEditorVisible">0/500</span>
            <div v-show="descriptionEditorVisible">
              <a-textarea :name="'test-paper-description'"/>
            </div>
          </a-form-item>
        </div>

        <div v-if="!isPersonalTestPaper()"
             class="test-paper-save-form-item test-paper-save-form-item-align-flex-start">
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
                  'num',
                  { initialValue: testPaperFormState.num, rules: [{ required: true, message: '请至少设置 1 份试卷' }, ] },
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
              <a-radio-group v-model="testPaperFormState.generateType">
                <a-radio value="questionType">按题型抽题</a-radio>
                <a-radio v-if="!isPersonalTestPaper()" value="questionTypeCategory">按题型+分类抽题</a-radio>
              </a-radio-group>
            </div>
            <question-type-display-set-menu
              v-if="testPaperFormState.generateType === 'questionType'"
              :question-type-display-settings="questionTypeDisplaySettings"
              :setting-key="testPaperFormState.generateType"
              @updateQuestionTypeDisplaySetting="onQuestionTypeDisplaySettingUpdate"
            />
          </div>

          <div class="question-type-display" v-show="testPaperFormState.generateType === 'questionType'">
            <div class="question-type-display-header">
              <div class="question-type-display-header-top">题型设置</div>
              <div class="question-type-display-header-normal" :class="{'row-editing': editingRow === 'number'}">
                题目数量
              </div>
              <div class="question-type-display-header-normal" :class="{'row-editing': editingRow === 'score'}">
                每题分值
              </div>
              <div class="question-type-display-header-bottom">
                <span class="question-type-display-header-bottom-title">合计</span>
                <span class="question-type-display-header-bottom-description">（题数/总分）</span>
              </div>
            </div>
            <div class="question-type-display-col" v-for="type in questionTypeDisplaySettings.questionType" v-show="type.checked">
              <div class="question-type-display-header-top">
                <span class="question-type-display-header-top-text">{{ type.name }}</span>
              </div>
              <div class="question-type-display-cell-number" :class="{
                'row-editing': editingRow === 'number',
                'question-type-display-cell-number-disable': questionCounts[type.type].total === 0,
                }">
                <span class="question-type-display-cell-number-edit" v-show="questionCounts[type.type].total > 0">
                  <input type="number" v-model="questionCounts[type.type].choose" @focus="editingRow = 'number'" @blur="onInputCountBlur(type.type)"/>
                </span>
                <span class="question-type-display-cell-number-total"
                      v-show="questionCounts[type.type].total === 0">{{ questionCounts[type.type].total }}</span>
                <span class="question-type-display-cell-number-total">/{{ questionCounts[type.type].total }}</span>
              </div>
              <div class="question-type-display-cell-score" :class="{'row-editing': editingRow === 'score'}">
                <input type="number" v-model="scores.questionType[type.type]" @focus="editingRow = 'score'" @blur="onInputScoreBlur(type.type)"/>
              </div>
              <div class="question-type-display-cell-sum">{{ questionCounts[type.type].choose }} / {{ sumScore(type.type) }}</div>
            </div>
          </div>

          <div class="question-category-choose" v-show="testPaperFormState.generateType === 'questionTypeCategory'">
            <question-category-select-drawer
              :bankId="bankId"
              :selected-categories="selectedQuestionCategories"
              @save-selected-categories="onQuestionCategoriesSelected"
            />

            <question-type-category-display
              v-show="selectedQuestionCategories.length > 0"
              :question-type-display-settings="questionTypeDisplaySettings"
              :question-type-display-setting-key="testPaperFormState.generateType"
              :categories="selectedQuestionCategories"
              :scores="scores.questionTypeCategory"
              :question-counts="questionCounts"
              :bank-id="bankId"
              @updateCategories="onQuestionCategoriesSelected"
              @updateQuestionTypeDisplaySetting="onQuestionTypeDisplaySettingUpdate"
              @updateQuestionConfigs="onQuestionConfigsUpdate"
            />
          </div>
        </div>

        <div v-if="isPersonalTestPaper()" class="test-paper-save-form-item">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-text">错题比例</span>
          </div>
          <div class="test-paper-number">
            <a-form-item>
              <a-input-number
                id="inputNumber"
                :min="0"
                :max="100"
                @change="handleChangeWrongRate"
                v-decorator="[
                  'wrongQuestionRate',
                  { initialValue: testPaperFormState.wrongQuestionRate, rules: [{ required: true, message: '请填写错题比例' }, ] },
                ]"
              />
            </a-form-item>
            <span class="test-paper-number-text">%</span>
          </div>
        </div>

        <div v-if="!isPersonalTestPaper()" class="test-paper-save-form-item">
          <div class="test-paper-save-form-item-label">
            <span class="test-paper-save-form-item-label-text">难度调节</span>
          </div>
          <a-switch v-model="difficultyVisible" checked-children="开启" un-checked-children="关闭"/>
        </div>

        <div v-if="!isPersonalTestPaper()" class="test-paper-difficulty" v-show="difficultyVisible">
          <div class="test-paper-save-form-item">
            <div class="test-paper-save-form-item-label">
              <span class="test-paper-save-form-item-label-text">试卷难度</span>
            </div>
            <a-slider
              range
              :default-value="[difficultyScales.simple.scale, difficultyScales.normal.scale + difficultyScales.difficulty.scale]"
              :tooltipVisible="false"
              @change="onSliderChange"
              class="test-paper-difficulty-slider"
            />
          </div>
          <div class="test-paper-difficulty-content">
            <div class="test-paper-difficulty-scale">
              <span v-for="(difficulty, key) in difficultyScales"
                    class="test-paper-difficulty-scale-text">{{ difficulty.text }} {{ difficulty.scale }}%</span>
            </div>
<!--            <div class="test-paper-difficulty-count-ratio">-->
<!--              <div class="test-paper-difficulty-count-ratio-item">-->
<!--                <span class="test-paper-difficulty-count-ratio-item-text">{{ difficultyScales.simple.text }}</span>-->
<!--                <span>-->
<!--                  <span class="test-paper-difficulty-count-choose">{{ difficultyScales.simple.chooseCount }}</span>-->
<!--                  <span class="test-paper-difficulty-count-total">/{{ difficultyScales.simple.totalCount }}</span>-->
<!--                </span>-->
<!--              </div>-->
<!--              <i></i>-->
<!--              <div class="test-paper-difficulty-count-ratio-item">-->
<!--                <span class="test-paper-difficulty-count-ratio-item-text">{{ difficultyScales.normal.text }}</span>-->
<!--                <span>-->
<!--                  <span class="test-paper-difficulty-count-choose">{{ difficultyScales.normal.chooseCount }}</span>-->
<!--                  <span class="test-paper-difficulty-count-total">/{{ difficultyScales.normal.totalCount }}</span>-->
<!--                </span>-->
<!--              </div>-->
<!--              <i></i>-->
<!--              <div class="test-paper-difficulty-count-ratio-item">-->
<!--                <span class="test-paper-difficulty-count-ratio-item-text">{{ difficultyScales.difficulty.text }}</span>-->
<!--                <span>-->
<!--                  <span class="test-paper-difficulty-count-choose">{{ difficultyScales.difficulty.chooseCount }}</span>-->
<!--                  <span class="test-paper-difficulty-count-total">/{{ difficultyScales.difficulty.totalCount }}</span>-->
<!--                </span>-->
<!--              </div>-->
<!--            </div>-->
            <span class="test-paper-difficulty-tips">如果某个难度的题目数不够，将会随机选择题目来补充</span>
          </div>
        </div>
      </a-form>
    </div>

    <div class="test-paper-save-footer">
      <button class="test-paper-save" @click="saveTestPaper">{{ $route.name === 'create' ? '创建' : '保存' }}</button>
      <button class="test-create-cancel" @click="backConfirm">取消</button>
    </div>
  </div>
</template>

<script>
import {apiClient} from 'common/vue/service/api-client';
import loadScript from 'load-script';
import TestPaperSaveHeader from './components/Header';
import QuestionTypeDisplaySetMenu from './components/QuestionTypeDisplaySetMenu.vue';
import QuestionCategorySelectDrawer from './components/QuestionCategorySelectDrawer';
import QuestionTypeCategoryDisplay from './components/QuestionTypeCategoryDisplay.vue';
import {Testpaper} from 'common/vue/service';

export default {
  components: {
    TestPaperSaveHeader,
    QuestionTypeDisplaySetMenu,
    QuestionCategorySelectDrawer,
    QuestionTypeCategoryDisplay,
  },
  props: {
    itemBankId: null,
    id: null,
  },
  data() {
    return {
      bankId: document.getElementById('itemBankId').value,
      descriptionEditorVisible: false,
      descriptionEditor: undefined,
      CKEditorConfig: {
        publicPath: document.getElementById('ckeditor_path').value,
        jqueryPath: document.getElementById('jquery_path').value,
        filebrowserImageUploadUrl: document.getElementById('ckeditor_image_upload_url').value,
        filebrowserImageDownloadUrl: document.getElementById('ckeditor_image_download_url').value,
        language: document.documentElement.lang === 'zh_CN' ? 'zh-cn' : document.documentElement.lang
      },
      difficultyVisible: false,
      questionTypeDisplaySettings: {},
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
      },
      scores: {
        questionType: {
          single_choice: 2,
          choice: 2,
          essay: 2,
          uncertain_choice: 2,
          determine: 2,
          fill: 2,
          material: 2,
        },
        questionTypeCategory: {
          single_choice: 2,
          choice: 2,
          essay: 2,
          uncertain_choice: 2,
          determine: 2,
          fill: 2,
          material: 2,
        },
      },
      selectedQuestionCategories: [],
      difficultyScales: {
        simple: {
          text: '简单',
          scale: 30,
          totalCount: 0,
          chooseCount: 0,
        },
        normal: {
          text: '一般',
          scale: 40,
          totalCount: 0,
          chooseCount: 0,
        },
        difficulty: {
          text: '困难',
          scale: 30,
          totalCount: 0,
          chooseCount: 0,
        },
      },
      form: this.$form.createForm(this, {name: 'save-test-paper'}),
      testPaperFormState: {
        name: '',
        description: '',
        type: 'random',
        itemBankId: null,
        num: 20,
        generateType: 'questionType',
        questionCategoryCounts: [],
        scores: {},
        percentages: {
          simple: '',
          normal: '',
          difficulty: '',
        },
        wrongQuestionRate: '0'
      },
      fetching: false,
      editingRow: null,
    };
  },
  computed: {
    sumScore() {
      return type => {
        return (this.questionCounts[type].choose * this.scores.questionType[type]).toFixed(1);
      }
    },
    paperQuestionCount() {
      return this.sumChooseQuestionCount();
    },
    paperScore() {
      return this.sumChooseQuestionScore();
    }
  },
  mounted() {
    this.fetchLastQuestionTypeDisplaySettings();
    this.fetchQuestionCounts();
    this.$nextTick(() => {
      loadScript(this.CKEditorConfig.jqueryPath, err => {
        if (err) {
          console.log(err);
        }
        loadScript(this.CKEditorConfig.publicPath, err => {
          if (err) {
            console.log(err);
          }
        });
      });
    });

    const routeName = this.$route.name;
    if (routeName === 'create') {
      const type = this.$route.query.type;
      this.testPaperFormState.type = type || 'random';

      const name = this.$route.query.name;
      this.testPaperFormState.name = name || '';
    }

    document.addEventListener('click', this.handleRouterSkip);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.handleRouterSkip);
  },
  methods: {
    confirmLeave(href) {
      this.$confirm({
        title: '确定要离开当前页面？',
        content: '离开后所有已编辑更改的数据将消失！',
        icon: 'exclamation-circle',
        okText: '离开',
        cancelText: '取消',
        centered: true,
        onOk: () => {
          window.location.href = href;
        },
      });
    },
    handleRouterSkip(event) {
      const target = event.target;
      if (target.tagName === 'A' && target.getAttribute('href') && target.getAttribute('data-is-link')) {
        const href = target.getAttribute('href');

        event.preventDefault();

        this.confirmLeave(href);
      }
    },
    initDescriptionEditor() {
      if (this.descriptionEditor) {
        this.descriptionEditor.destroy();
      }
      this.descriptionEditor = CKEDITOR.replace('test-paper-description', {
        toolbar: 'Minimal',
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: this.CKEditorConfig.filebrowserImageUploadUrl,
        filebrowserImageDownloadUrl: this.CKEditorConfig.filebrowserImageDownloadUrl,
        language: this.CKEditorConfig.language,
        startupFocus: true,
      });
      this.descriptionEditor.setData(this.testPaperFormState.description);
      this.descriptionEditor.on('blur', () => {
        const data = this.descriptionEditor.getData();
        this.testPaperFormState.description = data;
        this.form.setFieldsValue({
          description: this.testPaperFormState.description,
        });

        if (data === '') {
          this.descriptionEditorVisible = false;
          return;
        }

        this.form.validateFields(['description'], async () => {
        });
      });
    },
    fetchLastQuestionTypeDisplaySettings() {
      this.questionTypeDisplaySettings = {
        questionType: this.getDefaultQuestionTypeDisplaySetting(),
        questionTypeCategory: this.getDefaultQuestionTypeDisplaySetting(),
      };
    },
    fetchQuestionCounts() {
      apiClient.get('/api/item/questionType/count', {
        params: {
          bank_id: this.bankId,
        }
      }).then(res => {
        res.forEach(item => {
          this.questionCounts[item.type].total = item.itemNum;
        });
      });
      apiClient.get('/api/item/difficulty/count', {
        params: {
          bank_id: this.bankId,
        }
      }).then(res => {
        res.forEach(item => {
          this.difficultyScales[item.difficulty].totalCount = item.itemNum;
        });
      });
    },
    getDefaultQuestionTypeDisplaySetting(checked = true) {
      return [
        {
          type: 'single_choice',
          name: '单选题',
          checked: checked,
        },
        {
          type: 'choice',
          name: '多选题',
          checked: checked,
        },
        {
          type: 'essay',
          name: '问答题',
          checked: checked,
        },
        {
          type: 'uncertain_choice',
          name: '不定项',
          checked: checked,
        },
        {
          type: 'determine',
          name: '判断题',
          checked: checked,
        },
        {
          type: 'fill',
          name: '填空题',
          checked: checked,
        },
        {
          type: 'material',
          name: '材料题',
          checked: checked,
        },
      ];
    },
    backConfirm() {
      this.$confirm({
        title: '是否要保存更改？',
        icon: 'exclamation-circle',
        okText: '保存',
        cancelText: '不保存',
        centered: true,
        onOk: () => {
          this.saveTestPaper();
        },
        onCancel: () => {
          this.$router.push({
            name: 'list',
          });
        }
      });
    },
    onQuestionTypeDisplaySettingUpdate(settingKey, displaySetting) {
      this.$set(this.questionTypeDisplaySettings, settingKey, displaySetting);
    },
    onQuestionConfigsUpdate(questionConfigs) {
      Object.keys(questionConfigs).forEach(type => {
        this.scores.questionTypeCategory[type] = questionConfigs[type].score;
        this.questionCounts[type].categoryCounts = questionConfigs[type].count.choose;
      });
    },
    onDescriptionInputFocus() {
      this.initDescriptionEditor();
      this.descriptionEditorVisible = true;
    },
    onSliderChange(value) {
      this.difficultyScales.simple.scale = value[0];
      this.difficultyScales.normal.scale = value[1] - value[0];
      this.difficultyScales.difficulty.scale = 100 - value[1];
    },
    onQuestionCategoriesSelected(categories) {
      this.selectedQuestionCategories = categories;
    },
    isPersonalTestPaper() {
      return this.testPaperFormState.type === 'aiPersonality';
    },
    saveTestPaper() {
      if (this.fetching) {
        return;
      }
      this.form.validateFields(async (err) => {
        if (!err) {
          if (this.sumChooseQuestionCount() === 0) {
            this.$message.error('请至少选择 1 道题目');
            return;
          }

          this.fetching = true;
          this.testPaperFormState.questionCategoryCounts = [];
          this.testPaperFormState.itemBankId = this.itemBankId;
          this.testPaperFormState.num = `${this.testPaperFormState.type === 'aiPersonality' ? 0 : this.testPaperFormState.num}`;

          if (this.testPaperFormState.generateType === 'questionType') {
            const counts = {};
            this.questionTypeDisplaySettings.questionType.forEach(type => {
              if (type.checked) {
                counts[type.type] = this.questionCounts[type.type].choose;
              }
            });
            this.testPaperFormState.questionCategoryCounts.push({
              categoryId: '',
              counts: counts,
            });
          } else {
            this.selectedQuestionCategories.forEach(category => {
              const counts = {};
              this.questionTypeDisplaySettings.questionTypeCategory.forEach(type => {
                if (type.checked) {
                  counts[type.type] = this.questionCounts[type.type].categoryCounts[category.id];
                }
              });
              this.testPaperFormState.questionCategoryCounts.push({
                categoryId: category.id,
                counts: counts,
              });
            });
          }
          this.testPaperFormState.scores = this.scores[this.testPaperFormState.generateType];

          this.testPaperFormState.percentages = {
            simple: '',
            normal: '',
            difficulty: '',
          };
          if (this.difficultyVisible) {
            this.testPaperFormState.percentages = {
              simple: `${this.difficultyScales.simple.scale}`,
              normal: `${this.difficultyScales.normal.scale}`,
              difficulty: `${this.difficultyScales.difficulty.scale}`,
            };
          }
          const isCreate = this.$route.name === 'create';
          try {
            if (isCreate) {
              await Testpaper.create(this.testPaperFormState);
            } else {
              this.testPaperFormState.id = this.id;
              await Testpaper.update(this.testPaperFormState);
            }
            await this.$router.push({
              name: 'list',
              query: {tab: this.isPersonalTestPaper() ? 'ai_personality' : 'all'}
            });
            if (isCreate) {
              this.$message.success('创建成功');
            } else {
              this.$message.success('保存成功');
            }
          } catch (err) {
            if (isCreate) {
              this.$message.error('创建失败');
            } else {
              this.$message.error('保存失败');
            }
          } finally {
            this.fetching = false;
          }
        }
      });
    },
    sumChooseQuestionCount() {
      let count = 0;
      if (!this.questionTypeDisplaySettings[this.testPaperFormState.generateType]) {
        return count;
      }
      this.questionTypeDisplaySettings[this.testPaperFormState.generateType].forEach(type => {
        if (!type.checked) {
          return;
        }
        if (this.testPaperFormState.generateType === 'questionType') {
          count += Number(this.questionCounts[type.type].choose);
        } else if (this.questionCounts[type.type].categoryCounts) {
          this.selectedQuestionCategories.forEach(category => {
            if (this.questionCounts[type.type].categoryCounts[category.id]) {
              count += Number(this.questionCounts[type.type].categoryCounts[category.id]);
            }
          });
        }
      });

      return count;
    },
    sumChooseQuestionScore() {
      let score = 0;
      if (!this.questionTypeDisplaySettings[this.testPaperFormState.generateType]) {
        return score.toFixed(1);
      }
      this.questionTypeDisplaySettings[this.testPaperFormState.generateType].forEach(type => {
        if (!type.checked) {
          return;
        }
        if (this.testPaperFormState.generateType === 'questionType') {
          score += Number(this.questionCounts[type.type].choose) * this.scores.questionType[type.type];
        } else if (this.questionCounts[type.type].categoryCounts) {
          this.selectedQuestionCategories.forEach(category => {
            if (this.questionCounts[type.type].categoryCounts[category.id]) {
              score += Number(this.questionCounts[type.type].categoryCounts[category.id]) * this.scores.questionTypeCategory[type.type];
            }
          });
        }
      });

      return score.toFixed(1);
    },
    handleChangeNum(value) {
      this.testPaperFormState.num = Number.parseInt(value) || 1;
      this.$nextTick(() => {
        this.form.setFieldsValue({
          num: this.testPaperFormState.num,
        });
      });
    },
    handleChangeWrongRate(value) {
      this.testPaperFormState.wrongQuestionRate = Number.parseInt(value) || 0;
      this.$nextTick(() => {
        this.form.setFieldsValue({
          wrongQuestionRate: this.testPaperFormState.wrongQuestionRate,
        });
      });
    },
    handleChangeName(value) {
      this.testPaperFormState.name = value.target.value;
      this.form.setFieldsValue({
        name: value,
      });
    },
    onInputCountBlur(type) {
      this.editingRow = null;
      if (this.questionCounts[type].choose === '') {
        this.questionCounts[type].choose = 0;
      }
      this.questionCounts[type].choose = Number.parseInt(this.questionCounts[type].choose);
      if (this.questionCounts[type].choose > this.questionCounts[type].total) {
        this.questionCounts[type].choose = this.questionCounts[type].total;
      }
    },
    onInputScoreBlur(type) {
      this.editingRow = null;
      if (this.scores.questionType[type] === '') {
        this.scores.questionType[type] = 2;
      } else if (!Number.isInteger(Number(this.scores.questionType[type]))) {
        this.scores.questionType[type] = Number(this.scores.questionType[type]).toFixed(1);
      }
    },
  },
  async beforeMount() {
    if (this.$route.name === 'update') {
      const paper = await Testpaper.get(this.id);

      this.testPaperFormState.name = paper.name;
      this.testPaperFormState.type = paper.type;
      this.testPaperFormState.description = paper.description;
      this.testPaperFormState.num = paper.assessmentGenerateRule.num;
      this.testPaperFormState.itemBankId = paper.bank_id;
      this.testPaperFormState.generateType = paper.assessmentGenerateRule.type;
      this.testPaperFormState.wrongQuestionRate = paper.assessmentGenerateRule.wrong_question_rate;
      const displayTypes = Object.keys(paper.assessmentGenerateRule.question_setting.questionCategoryCounts[0].counts);
      let questionTypeDisplaySetting = this.getDefaultQuestionTypeDisplaySetting(false);
      questionTypeDisplaySetting.forEach(type => {
        if (-1 !== displayTypes.indexOf(type.type)) {
          type.checked = true;
        }
      });
      this.questionTypeDisplaySettings[this.testPaperFormState.generateType] = questionTypeDisplaySetting;
      displayTypes.forEach(type => {
        this.scores[this.testPaperFormState.generateType][type] = paper.assessmentGenerateRule.question_setting.scores[type];
      });
      if (this.testPaperFormState.generateType === 'questionType') {
        displayTypes.forEach(type => {
          this.questionCounts[type].choose = paper.assessmentGenerateRule.question_setting.questionCategoryCounts[0].counts[type];
        });
      } else {
        const categories = await apiClient.get(`/api/item_bank/${this.bankId}/item_category_transform/map`);
        paper.assessmentGenerateRule.question_setting.questionCategoryCounts.forEach(questionCategoryCount => {
          displayTypes.forEach(type => {
            this.questionCounts[type].categoryCounts = this.questionCounts[type].categoryCounts || {};
            this.questionCounts[type].categoryCounts[questionCategoryCount.categoryId] = questionCategoryCount.counts[type];
          });
          this.selectedQuestionCategories.push({
            id: categories[questionCategoryCount.categoryId].id,
            name: categories[questionCategoryCount.categoryId].name,
            level: {
              1: '一级分类',
              2: '二级分类',
              3: '三级分类',
            }[categories[questionCategoryCount.categoryId].depth],
          });
        });
      }

      if (this.testPaperFormState.description) {
        this.onDescriptionInputFocus();
      }

      const difficulty = paper.assessmentGenerateRule.difficulty;
      if (difficulty.simple && difficulty.normal && difficulty.difficulty) {
        this.difficultyVisible = true;
        this.difficultyScales.simple.scale = Number.parseInt(`${difficulty.simple}`);
        this.difficultyScales.normal.scale = Number.parseInt(`${difficulty.normal}`);
        this.difficultyScales.difficulty.scale = Number.parseInt(`${difficulty.difficulty}`);
      }
    }
  }
};
</script>

<script>
import _ from 'lodash';
import {apiClient} from 'common/vue/service/api-client';
import QuestionTypeDisplaySetMenu from './QuestionTypeDisplaySetMenu.vue';

export default {
  name: 'QuestionTypeCategoryEditDrawer',
  props: {
    drawerVisible: false,
    defaultQuestionTypeDisplaySettings: undefined,
    questionTypeDisplaySettingKey: undefined,
    defaultCategories: undefined,
    bankId: undefined,
  },
  components: {
    QuestionTypeDisplaySetMenu
  },
  data() {
    return {
      editingRow: null,
      questionTypeDisplaySettings: {},
      questionTypeDisplaySetting: [],
      categories: [],
      questionConfigs: {
        single_choice: {
          count: {
            choose: {},
            total: {},
          },
          score: 2
        },
        choice: {
          count: {
            choose: {},
            total: {},
          },
          score: 2
        },
        essay: {
          count: {
            choose: {},
            total: {},
          },
          score: 2
        },
        uncertain_choice: {
          count: {
            choose: {},
            total: {},
          },
          score: 2
        },
        determine: {
          count: {
            choose: {},
            total: {},
          },
          score: 2
        },
        fill: {
          count: {
            choose: {},
            total: {},
          },
          score: 2
        },
        material: {
          count: {
            choose: {},
            total: {},
          },
          score: 2
        },
      },
    }
  },
  computed: {
    totalCount() {
      return (categoryId, type) => {
        if (!this.questionConfigs[type].count.total[categoryId]) {
          return 0;
        }
        return this.questionConfigs[type].count.total[categoryId];
      }
    },
  },
  methods: {
    fetchQuestionCounts() {
      this.questionTypeDisplaySetting.forEach(type => {
        this.defaultCategories.forEach(category => {
          this.questionConfigs[type.type].count.choose[category.id] = 0;
        });
        const typeConfig = this.questionConfigs[type.type];
        this.questionConfigs[type.type] = {};
        this.$set(this.questionConfigs, type.type, typeConfig);
      });
      let categoryIds = [];
      this.defaultCategories.forEach(category => {
        categoryIds.push(category.id);
      });
      apiClient.get('/api/item/categoryIdAndType/count', {
        params: {
          bank_id: this.bankId,
          category_ids: categoryIds,
        }
      }).then(res => {
        res.forEach(item => {
          this.questionConfigs[item.type].count.total[item.category_id] = item.itemNum;
          const typeConfig = this.questionConfigs[item.type];
          this.questionConfigs[item.type] = {};
          this.$set(this.questionConfigs, item.type, typeConfig);
        });
      });
    },
    close() {
      this.$emit('close');
    },
    save() {
      let totalQuestionNum = 0;
      this.questionTypeDisplaySetting.forEach(type => {
        this.categories.forEach(category => {
          totalQuestionNum += this.questionConfigs[type.type].count.choose[category.id];
        });
      });
      if (totalQuestionNum === 0) {
        this.$message.error('请至少选择 1 道题目');
        return;
      }

      this.$emit('save', this.categories, this.questionTypeDisplaySetting, this.questionConfigs);
      this.$message.success(Translator.trans('site.save_success_hint'));
      this.close();
    },
    closeConfirm() {
      this.$confirm({
        title: '确定放弃此次操作吗？',
        content: '当前操作尚未保存',
        centered: true,
        okText: '确定',
        cancelText: '取消',
        onOk: this.close
      });
    },
    removeCategory(id) {
      this.categories = this.categories.filter(category => category.id !== id);
    },
    sumNum(type) {
      let sumCount = 0;
      this.categories.forEach(category => {
        sumCount += this.questionConfigs[type].count.choose[category.id] ? Number(this.questionConfigs[type].count.choose[category.id]) : 0;
      });

      return sumCount;
    },
    sumScore(type) {
      return (this.sumNum(type) * this.questionConfigs[type].score).toFixed(1);
    },
    onQuestionTypeDisplaySettingUpdate(settingKey, displaySetting) {
      this.questionTypeDisplaySettings[settingKey] = displaySetting;
      this.questionTypeDisplaySetting = displaySetting;
    }
  },
  watch: {
    drawerVisible: function (val) {
      if (val) {
        document.body.style.overflowY = 'hidden';
        this.questionTypeDisplaySettings = _.cloneDeep(this.defaultQuestionTypeDisplaySettings);
        this.questionTypeDisplaySetting = this.defaultQuestionTypeDisplaySettings[this.questionTypeDisplaySettingKey];
        this.categories = _.cloneDeep(this.defaultCategories);
        this.fetchQuestionCounts();
      } else {
        document.body.style.overflowY = 'auto';
      }
    },
  },
}
</script>

<template>
  <a-drawer
    :get-container="'.test-create'"
    width="100vw"
    wrap-class-name="drawer-container"
    :visible="drawerVisible"
    :closable="false"
  >
    <template #title>
      <div class="drawer-header">
        <div @click="closeConfirm" class="drawer-header-return">< {{ 'importer.import_back_btn'|trans }}</div>
        <div class="separator"></div>
        <span>按题型+分类抽题</span>
      </div>
    </template>
    <div class="drawer-body">
      <question-type-display-set-menu
        :question-type-display-settings="questionTypeDisplaySettings"
        :setting-key="questionTypeDisplaySettingKey"
        @updateQuestionTypeDisplaySetting="onQuestionTypeDisplaySettingUpdate"
      />
      <div class="question-type-category-display">
        <div class="question-type-category-display-header">
          <div class="question-type-category-display-header-top">分类</div>
          <div v-for="(category, index) in categories"
               class="question-type-category-display-header-normal"
               :class="{'row-editing': editingRow === index}">
            <div class="question-type-category-display-header-normal-level">{{ category.level }}</div>
            <span class="category-name">{{ category.name }}</span>
          </div>
          <div class="question-type-category-display-header-score" :class="{'row-editing': editingRow === categories.length}">
            <span class="question-type-category-display-header-top-content">按题型设置分值</span>
          </div>
          <div class="question-type-category-display-header-bottom">
            <span class="question-type-category-display-header-bottom-title">合计</span>
            <span class="question-type-category-display-header-bottom-description">（题数/总分）</span>
          </div>
        </div>
        <div v-for="type in questionTypeDisplaySetting" v-show="type.checked" class="question-type-category-display-header-type">
          <div class="question-type-category-display-header-top">
            <div class="question-type-category-display-header-top-content">{{ type.name }}</div>
          </div>
          <div v-for="(category, index) in categories"
               class="question-type-category-display-cell"
               :class="{
                 'row-editing': editingRow === index,
                 'question-type-category-display-cell-active': totalCount(category.id, type.type) > 0,
                 'question-type-category-display-cell-inactive': totalCount(category.id, type.type) === 0
               }">
            <input v-if="totalCount(category.id, type.type) > 0" type="number" min="0" :max="totalCount(category.id, type.type)"
                   v-model="questionConfigs[type.type].count.choose[category.id]"
                   @focus="editingRow = index"
                   @blur="editingRow = null"
                   class="question-type-category-display-cell-number"/>
            <span class="question-type-category-display-cell-number-total" v-if="totalCount(category.id, type.type) === 0">0</span>
            <span class="question-type-category-display-cell-number-total">/{{ totalCount(category.id, type.type) }}</span>
          </div>
          <div class="question-type-category-display-cell question-type-category-display-cell-active"
               :class="{'row-editing': editingRow === categories.length}">
            <input type="number" min="0" v-model="questionConfigs[type.type].score"
                   @focus="editingRow = categories.length"
                   @blur="editingRow = null"
                   class="question-type-category-display-cell-number"/>
          </div>
          <div class="question-type-category-display-cell-sum">{{ `${sumNum(type.type)} / ${sumScore(type.type)}` }}</div>
        </div>
        <div class="question-type-category-display-header-action">
          <div class="question-type-category-display-header-top">操作</div>
          <div v-for="(category, index) in categories" class="question-type-category-display-cell"
               :class="{'row-editing': editingRow === index}">
            <a-popconfirm title="确定要删除当前分类吗？" ok-text="移除" cancel-text="取消" @confirm="removeCategory(category.id)">
              <a>移除</a>
            </a-popconfirm>
          </div>
          <div class="question-type-category-display-cell"
               :class="{'row-editing': editingRow === categories.length}">
            <div class="question-type-category-display-cell-number"></div>
          </div>
          <div class="question-type-category-display-cell-sum"></div>
        </div>
      </div>
    </div>
    <div class="drawer-bottom">
      <a-button @click="closeConfirm">{{ 'site.cancel'|trans }}</a-button>
      <a-button type="primary" @click="save">{{ 'site.btn.save'|trans }}</a-button>
    </div>
  </a-drawer>
</template>

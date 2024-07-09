<script>

import QuestionTypeDisplaySetMenu from '../component/QuestionTypeDisplaySetMenu.vue';

export default {
  name: 'QuestionTypeCategoryEditDrawer',
  props: {
    drawerVisible: false,
    // categories: undefined,
    // questionDisplayTypes: undefined,
    questionAllTypes: undefined,
    questionConfigs: undefined,
    defaultCategories: undefined,
    defaultQuestionDisplayTypes: undefined,
  },
  components: {
    QuestionTypeDisplaySetMenu
  },
  data() {
    return {
      editingCell: null,
      editingRow: null,
      categories: this.clone(this.defaultCategories),
      questionDisplayTypes: this.clone(this.defaultQuestionDisplayTypes),
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
    close() {
      this.$emit('closeDrawer');
    },
    save() {
      let totalQuestionNum = 0;
      this.questionDisplayTypes.forEach(type => {
        this.categories.forEach(category => {
          totalQuestionNum += this.questionConfigs[type.type].count.choose[category.id];
        });
      });

      if (totalQuestionNum === 0) {
        this.$message.error('请至少选择 1 道题目');
        return;
      }

      this.$emit('saveDrawer', this.categories, this.questionDisplayTypes);
      this.$emit('updateDisplayQuestionType', this.questionDisplayTypes);
      this.$message.success(Translator.trans('site.save_success_hint'));
      this.close();
    },
    closeDrawer() {
      this.$confirm({
        title: '确定放弃此次操作吗？',
        content: '当前操作尚未保存',
        okText: '确定',
        cancelText: '取消',
        onOk: this.close
      });
    },
    handleUpdateDisplayQuestionType(questionDisplayTypes) {
      this.$emit('updateDisplayQuestionType', questionDisplayTypes);
      this.questionDisplayTypes = questionDisplayTypes;
    },
    handleFinishInputNum(type) {
      type.addNum = type.addNum ? type.addNum > type.totalNum ? type.totalNum : type.addNum : 0;
      this.editingCell = null;
      this.editingRow = null;
    },
    removeCategory(removedCategory) {
      this.$emit('updateCategories', this.categories.filter(category => category.id !== removedCategory.id));
    },
    getQuestionNum(type) {
      let sumCount = 0;
      this.categories.forEach(category => {
        sumCount += this.questionConfigs[type].count.choose[category.id];
      });

      return sumCount;
    },
    getTotalScore(type) {
      return (this.getQuestionNum(type) * this.questionConfigs[type].score).toFixed(1);
    },
    clone(type) {
      if (type) {
        return JSON.parse(JSON.stringify(type));
      } else {
        return type;
      }
    }
  },
  watch: {
    drawerVisible: function (val) {
      if (val) {
        document.body.style.overflowY = 'hidden';
      } else {
        document.body.style.overflowY = 'auto';
        this.categories = this.clone(this.defaultCategories);
        this.questionDisplayTypes = this.clone(this.defaultQuestionDisplayTypes);
      }
    },
    editingCell: async function (val) {
      const cell = document.getElementById(val);
      if (!cell) {
        return;
      }
      this.editingRow = Number.parseInt(cell.dataset.row);
      document.activeElement.blur();
      this.$nextTick(function () {
        const input = document.getElementById(`${val}-input`);
        input && input.focus();
      })
    },
    defaultCategories: function (val) {
      if (!!val) {
        this.categories = this.clone(val);
      }
    },
    defaultQuestionDisplayTypes: function (val) {
      if (!!val) {
        this.questionDisplayTypes = this.clone(val);
      }
    },
    questionConfigs(val) {
      console.log(val);
    }
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
        <div @click="closeDrawer" class="drawer-header-return">< {{ 'importer.import_back_btn'|trans }}</div>
        <div class="separator"></div>
        <span>按题型+分类抽题</span>
      </div>
    </template>
    <div class="drawer-body">
      <question-type-display-set-menu :default-question-all-types="questionAllTypes"
                                      @updateDisplayQuestionType="handleUpdateDisplayQuestionType"/>
      <div class="question-type-category-display">
        <div class="question-type-category-display-header">
          <div class="question-type-category-display-header-top">分类</div>
          <div v-if="categories && categories.length > 0" class="question-type-category-display-header-normal"
               :class="{'row-editing': editingRow === index + 1}" v-for="(category, index) in categories">
            <div class="question-type-category-display-header-normal-level">{{ category.level }}</div>
            <span class="category-name">{{ category.name }}</span>
          </div>
          <div class="question-type-category-display-header-score" :class="{'row-editing': editingRow === categories.length + 1}">
            <span class="question-type-category-display-header-top-content">按题型设置分值</span>
          </div>
          <div class="question-type-category-display-header-bottom">
            <span class="question-type-category-display-header-bottom-title">合计</span>
            <span class="question-type-category-display-header-bottom-description">（题数/总分）</span>
          </div>
        </div>
        <div v-if="questionDisplayTypes && questionDisplayTypes.length > 0" v-for="type in questionDisplayTypes"
             class="question-type-category-display-header-type">
          <div class="question-type-category-display-header-top">
            <div class="question-type-category-display-header-top-content">{{ type.name }}</div>
          </div>
          <div :id="`${category.id}-${type.type}-num`" :data-row="index + 1" v-for="(category, index) in categories"
               @click="totalCount(category.id, type.type) > 0 && (editingCell = `${category.id}-${type.type}-num`)"
               class="question-type-category-display-cell"
               :class="{'row-editing': editingRow === index + 1, 'question-type-category-display-cell-active': totalCount(category.id, type.type) > 0, 'question-type-category-display-cell-inactive': totalCount(category.id, type.type) === 0}">
            <input :id="`${category.id}-${type.type}-num-input`"
                   v-if="editingCell === `${category.id}-${type.type}-num`" type="number" min="0" :max="5"
                   v-model="questionConfigs[type.type].count.choose[category.id]"
                   @blur="" class="question-type-category-display-cell-number"/>
            <span class="question-type-category-display-cell-number-total">/{{ totalCount(category.id, type.type) }}</span>
          </div>
          <div :id="`${type.type}-score`" :data-row="categories.length + 1"
               class="question-type-category-display-cell question-type-category-display-cell-active"
               @click="editingCell = `${type.type}-score`"
               :class="{'row-editing': editingRow === categories.length + 1}">
            <input :id="`${type.type}-score-input`" v-if="editingCell === `${type.type}-score`" type="number" min="0"
                   v-model="questionConfigs[type.type].score"
                   @blur="type.score = type.score ? type.score : 0; editingCell = null; editingRow = null"
                   class="question-type-category-display-cell-number"/>
            <span v-else class="question-type-category-display-cell-number">{{ questionConfigs[type.type].score }}</span>
          </div>
          <div class="question-type-category-display-cell-sum">{{ `${getQuestionNum(type.type)} / ${getTotalScore(type.type)}` }}</div>
        </div>
        <div class="question-type-category-display-header-action">
          <div class="question-type-category-display-header-top">操作</div>
          <div v-for="(category, index) in categories" class="question-type-category-display-cell"
               :class="{'row-editing': editingRow === index + 1}">
            <a-popconfirm title="确定要删除当前分类吗？" ok-text="移除" cancel-text="取消" @confirm="removeCategory(category)">
              <a>移除</a>
            </a-popconfirm>
          </div>
          <div class="question-type-category-display-cell"
               :class="{'row-editing': editingRow === categories.length + 1}">
            <div class="question-type-category-display-cell-number"></div>
          </div>
          <div class="question-type-category-display-cell-sum"></div>
        </div>
      </div>
    </div>
    <div class="drawer-bottom">
      <a-button @click="closeDrawer">{{ 'site.cancel'|trans }}</a-button>
      <a-button type="primary" @click="save">{{ 'site.btn.save'|trans }}</a-button>
    </div>
  </a-drawer>
</template>

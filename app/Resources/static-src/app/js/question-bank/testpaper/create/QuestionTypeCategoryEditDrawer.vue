<script>

import QuestionTypeDisplaySetMenu from '../component/QuestionTypeDisplaySetMenu.vue';

export default {
  name: 'QuestionTypeCategoryEditDrawer',
  props: {
    drawerVisible: false,
    categories: undefined,
    questionDisplayTypes: undefined,
    questionAllTypes: undefined,
  },
  components: {
    QuestionTypeDisplaySetMenu

  },
  data() {
    return {
      editingCell: null,
      editingRow: null,
    }
  },
  methods: {
    closeDrawer() {
      this.$emit('closeDrawer');
    },
    handleUpdateDisplayQuestionType(questionAllTypes, questionDisplayTypes) {
      this.$emit('updateDisplayQuestionType',questionAllTypes, questionDisplayTypes);
    },
    handleFinishInputNum(type) {
      type.addNum = type.addNum ? type.addNum > type.totalNum ? type.totalNum : type.addNum : 0;
      this.editingCell = null;
      this.editingRow = null;
    },
    removeCategory(removedCategory) {
      this.$emit('updateCategories', this.categories.filter(category => category.id !== removedCategory.id));
    }
  },
  watch: {
    drawerVisible: function (val) {
      if (val) {
        document.body.style.overflowY = 'hidden';
      } else {
        document.body.style.overflowY = 'auto';
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
    @close="closeDrawer"
    :closable="false"
  >
    <template #title>
        <div class="drawer-header">
          <div @click="closeDrawer" class="drawer-header-return">< {{'importer.import_back_btn'|trans}}</div>
          <div class="separator"></div>
          <span>按题型+分类抽题</span>
        </div>
    </template>
    <div class="drawer-body">
      <question-type-display-set-menu :default-question-all-types="questionAllTypes" @updateDisplayQuestionType="handleUpdateDisplayQuestionType"/>
      <div class="question-type-category-display">
        <div class="question-type-category-display-header">
          <div class="question-type-category-display-header-top">分类</div>
          <div v-if="categories && categories.length > 0" class="question-type-category-display-header-normal" :class="{'row-editing': editingRow === index + 1}" v-for="(category, index) in categories">
            <a-tag>{{ category.level }}</a-tag>
            <span class="category-name">{{ category.name }}</span>
          </div>
          <div class="question-type-category-display-header-score" :class="{'row-editing': editingRow === categories.length + 1}">
            <span class="question-type-category-display-header-top-content">按题型设置分值 {{ editingRow }}{{ categories.length + 1 }}</span>
          </div>
          <div class="question-type-category-display-header-bottom">
            <span class="question-type-category-display-header-bottom-title">合计</span>
            <span class="question-type-category-display-header-bottom-description">（题数/总分）</span>
          </div>
        </div>
        <div v-if="questionDisplayTypes && questionDisplayTypes.length > 0" v-for="type in questionDisplayTypes" class="question-type-category-display-header-type">
          <div class="question-type-category-display-header-top">
            <div class="question-type-category-display-header-top-content">{{ type.name }}</div>
          </div>
          <div :id="`${category.id}-${type.type}-num`" :data-row="index + 1" v-for="(category, index) in categories" @click="category.questionTypes.find(questionType => questionType.type === type.type).totalNum > 0 && (editingCell = `${category.id}-${type.type}-num`)" class="question-type-category-display-cell" :class="{'row-editing': editingRow === index + 1, 'question-type-category-display-cell-active': category.questionTypes.find(questionType => questionType.type === type.type).totalNum > 0, 'question-type-category-display-cell-inactive': category.questionTypes.find(questionType => questionType.type === type.type).totalNum === 0}">
            <input :id="`${category.id}-${type.type}-num-input`" v-if="editingCell === `${category.id}-${type.type}-num`" type="number" min="0" :max="category.questionTypes.find(questionType => questionType.type === type.type).totalNum" v-model="category.questionTypes.find(questionType => questionType.type === type.type).addNum" @blur="handleFinishInputNum(category.questionTypes.find(questionType => questionType.type === type.type))" class="question-type-category-display-cell-number" />
            <span v-else class="question-type-category-display-cell-number">{{ category.questionTypes.find(questionType => questionType.type === type.type).addNum }}</span>
            <span class="question-type-category-display-cell-number-total">/{{ category.questionTypes.find(questionType => questionType.type === type.type).totalNum }}</span>
          </div>
          <div :id="`${type.type}-score`" :data-row="categories.length + 1" class="question-type-category-display-cell question-type-category-display-cell-active" @click="editingCell = `${type.type}-score`" :class="{'row-editing': editingRow === categories.length + 1}">
            <input :id="`${type.type}-score-input`" v-if="editingCell === `${type.type}-score`" type="number" min="0" v-model="type.score" @blur="type.score = type.score ? type.score : 0; editingCell = null; editingRow = null" class="question-type-category-display-cell-number" />
            <span v-else class="question-type-category-display-cell-number">{{ type.score }}</span>
          </div>
          <div class="question-type-category-display-cell-sum">0 / 0.0</div>
        </div>
        <div class="question-type-category-display-header-action">
          <div class="question-type-category-display-header-top">操作</div>
          <div v-for="(category, index) in categories" class="question-type-category-display-cell" :class="{'row-editing': editingRow === index + 1}">
            <a href="javascript:" @click="removeCategory(category)">移除</a>
          </div>
          <div class="question-type-category-display-cell" :class="{'row-editing': editingRow === categories.length + 1}">
            <div class="question-type-category-display-cell-number"></div>
          </div>
          <div class="question-type-category-display-cell-sum"></div>
        </div>
      </div>
    </div>
    <div class="drawer-bottom">
      <a-button @click="closeDrawer">{{ 'site.cancel'|trans }}</a-button>
      <a-button type="primary">{{ 'site.btn.save'|trans }}</a-button>
    </div>
  </a-drawer>
</template>

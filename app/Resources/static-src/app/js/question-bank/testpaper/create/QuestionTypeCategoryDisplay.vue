<script>

import QuestionTypeCategoryEditDrawer from './QuestionTypeCategoryEditDrawer.vue';

export default {
  name: 'QuestionTypeCategoryDisplay',
  components: {QuestionTypeCategoryEditDrawer},
  props: {
    defaultQuestionAllTypes: undefined,
    questionDisplayTypes: undefined,
  },
  data() {
    return {
      categories: [
        {
          id: '1',
          level: '一级分类',
          name: '一建《机电》分章练习',
          questionTypes: [
            {
              type: 'single_choice',
              addNum: 1,
              totalNum: 5
            },
            {
              type: 'choice',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'essay',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'uncertain_choice',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'determine',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'fill',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'material',
              addNum: 0,
              totalNum: 5
            },
          ]
        },
        {
          id: '2',
          level: '二级分类',
          name: '1H410000机电工程技术',
          questionTypes: [
            {
              type: 'single_choice',
              addNum: 1,
              totalNum: 5
            }, {
              type: 'choice',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'essay',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'uncertain_choice',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'determine',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'fill',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'material',
              addNum: 0,
              totalNum: 5
            },
          ]
        },
        {
          id: '3',
          level: '三级分类',
          name: '1H410000机电工程常用材料及工程设备材料',
          questionTypes: [
            {
              type: 'single_choice',
              addNum: 1,
              totalNum: 5
            }, {
              type: 'choice',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'essay',
              addNum: 0,
              totalNum: 0
            },
            {
              type: 'uncertain_choice',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'determine',
              addNum: 0,
              totalNum: 0
            },
            {
              type: 'fill',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'material',
              addNum: 0,
              totalNum: 5
            },
          ]
        },
        {
          id: '4',
          level: '三级分类',
          name: '1H410000机电工程常用材料及工程设备材料',
          questionTypes: [
            {
              type: 'single_choice',
              addNum: 1,
              totalNum: 5
            }, {
              type: 'choice',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'essay',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'uncertain_choice',
              addNum: 0,
              totalNum: 0
            },
            {
              type: 'determine',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'fill',
              addNum: 0,
              totalNum: 5
            },
            {
              type: 'material',
              addNum: 0,
              totalNum: 0
            },
          ]
        },
      ],
      editMaskVisible: false,
      drawerVisible: false,
    };
  },
  methods: {
    showEditMask() {
      this.editMaskVisible = true;
    },
    hideEditMask() {
      this.editMaskVisible = false;
    },
    handleUpdateDisplayQuestionType(questionAllTypes, questionDisplayTypes) {
      this.$emit('updateDisplayQuestionType',questionAllTypes, questionDisplayTypes);
    },
    showAddNum(category) {
      const isShowNum = category.addNum > 0 && category.totalNum > 0 || category.totalNum === 0;
      if (isShowNum) {
        return category.addNum;
      } else {
        return '';
      }
    },
    getQuestionNum(type) {
      let addNum = 0;
      if (this.categories && this.categories.length > 0) {
        for (const category of this.categories) {
          const num = Number.parseInt(category.questionTypes.find(questionType => questionType.type === type.type).addNum);
          addNum += isNaN(num) ? 0 : num;
        }
      }

      return addNum;
    },
    getTotalScore(type) {
      const questionNum = this.getQuestionNum(type);
      return (questionNum * this.questionDisplayTypes.find(questionType => questionType.type === type.type).score).toFixed(1);
    },
    handleSaveDrawer(categories, questionDisplayTypes) {
      this.categories = categories;
      this.$emit('updateCategories', categories);
      this.handleUpdateDisplayQuestionType(this.defaultQuestionAllTypes, questionDisplayTypes);
    }
  },
  mounted() {
    this.$emit('updateCategories', this.categories);
  }
};

</script>
<template>
  <div class="question-type-category-display" @mouseover="showEditMask" @mouseleave="hideEditMask">
    <div class="question-type-category-display-header">
      <div class="question-type-category-display-header-top">分类</div>
      <div v-if="categories && categories.length > 0" class="question-type-category-display-header-normal"
           v-for="category in categories">
        <a-tag>{{ category.level }}</a-tag>
        <span class="category-name">{{ category.name }}</span>
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
      <div v-for="category in categories" class="question-type-category-display-cell" :class="{'question-type-category-display-cell-inactive': showAddNum(category.questionTypes.find(questionType => questionType.type === type.type)) === 0}">
        <span class="question-type-category-display-cell-number">{{ showAddNum(category.questionTypes.find(questionType => questionType.type === type.type)) }}</span>
      </div>
      <div class="question-type-category-display-cell-sum">{{ `${getQuestionNum(type)} / ${getTotalScore(type)}` }}</div>
    </div>
    <div v-show="editMaskVisible" class="edit-mask-container">
      <a-button @click="drawerVisible = true">编辑</a-button>
    </div>
    <question-type-category-edit-drawer
      :drawer-visible="drawerVisible"
      @closeDrawer="drawerVisible = false"
      :default-categories="categories"
      :default-question-display-types="questionDisplayTypes"
      :default-question-all-types="defaultQuestionAllTypes"
      @updateDisplayQuestionType="handleUpdateDisplayQuestionType"
      @updateCategories="(newCategories) => categories = newCategories"
      @saveDrawer="handleSaveDrawer"
    />
  </div>
</template>

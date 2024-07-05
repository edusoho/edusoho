<script>

export default {
  name: 'QuestionTypeCategoryDisplay',
  components: {},
  props: {
    questionDisplayTypes: undefined,
  },
  data() {
    return {
      categories: [
        {
          level: '一级分类',
          name: '一建《机电》分章练习',
          questionTypes: [
            {
              type: 'single_choice',
              name: '单选题',
              num: 1,
            }, {
              type: 'choice',
              name: '多选题',
              num: 0,
            },
            {
              type: 'essay',
              name: '问答题',
              num: 0,
            },
            {
              type: 'uncertain_choice',
              name: '不定项',
              num: 0,
            },
            {
              type: 'determine',
              name: '判断题',
              num: 0,
            },
            {
              type: 'fill',
              name: '填空题',
              num: 0,
            },
            {
              type: 'material',
              name: '材料题',
              num: 0,
            },
          ]
        },
        {
          level: '二级分类',
          name: '1H410000机电工程技术',
          questionTypes: [
            {
              type: 'single_choice',
              name: '单选题',
              num: 1,
            }, {
              type: 'choice',
              name: '多选题',
              num: 0,
            },
            {
              type: 'essay',
              name: '问答题',
              num: 0,
            },
            {
              type: 'uncertain_choice',
              name: '不定项',
              num: 0,
            },
            {
              type: 'determine',
              name: '判断题',
              num: 0,
            },
            {
              type: 'fill',
              name: '填空题',
              num: 0,
            },
            {
              type: 'material',
              name: '材料题',
              num: 0,
            },
          ]
        },
        {
          level: '三级分类',
          name: '1H410000机电工程常用材料及工程设备材料',
          questionTypes: [
            {
              type: 'single_choice',
              name: '单选题',
              num: 1,
            }, {
              type: 'choice',
              name: '多选题',
              num: 0,
            },
            {
              type: 'essay',
              name: '问答题',
              num: 0,
            },
            {
              type: 'uncertain_choice',
              name: '不定项',
              num: 0,
            },
            {
              type: 'determine',
              name: '判断题',
              num: 0,
            },
            {
              type: 'fill',
              name: '填空题',
              num: 0,
            },
            {
              type: 'material',
              name: '材料题',
              num: 0,
            },
          ]
        },
        {
          level: '三级分类',
          name: '1H410000机电工程常用材料及工程设备材料',
          questionTypes: [
            {
              type: 'single_choice',
              name: '单选题',
              num: 1,
            }, {
              type: 'choice',
              name: '多选题',
              num: 0,
            },
            {
              type: 'essay',
              name: '问答题',
              num: 0,
            },
            {
              type: 'uncertain_choice',
              name: '不定项',
              num: 0,
            },
            {
              type: 'determine',
              name: '判断题',
              num: 0,
            },
            {
              type: 'fill',
              name: '填空题',
              num: 0,
            },
            {
              type: 'material',
              name: '材料题',
              num: 0,
            },
          ]
        },
      ],
      editMaskVisible: false,
    };
  },
  methods: {
    showEditMask() {
      this.editMaskVisible = true;
    },
    hideEditMask() {
      this.editMaskVisible = false;
    }
  },
  created() {
  }
};

</script>
<template>
  <div class="question-type-category-display" @mouseover="showEditMask" @mouseleave="hideEditMask">
    <div class="question-type-category-display-header">
      <div class="question-type-category-display-header-top">分类</div>
      <div v-if="categories && categories.length > 0" class="question-type-category-display-header-normal" v-for="category in categories">
        <a-tag>{{ category.level }}</a-tag>
        <span class="category-name">{{ category.name }}</span>
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
      <div v-for="category in categories" class="question-type-category-display-cell-number">
        <span class="question-type-category-display-cell-number-total">{{ category.questionTypes.find(questionType => questionType.type === type.type).num }}</span>
      </div>
      <div class="question-type-category-display-cell-sum">0 / 0.0</div>
    </div>
    <div v-show="editMaskVisible" class="edit-mask-container">
      <a-button>编辑</a-button>
    </div>
  </div>
</template>

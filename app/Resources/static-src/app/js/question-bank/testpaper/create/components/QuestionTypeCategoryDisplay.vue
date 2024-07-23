<script>
import QuestionTypeCategoryEditDrawer from './QuestionTypeCategoryEditDrawer.vue';

export default {
  name: 'QuestionTypeCategoryDisplay',
  components: {QuestionTypeCategoryEditDrawer},
  props: {
    questionTypeDisplaySettings: undefined,
    questionTypeDisplaySettingKey: undefined,
    categories: undefined,
    scores: undefined,
    questionCounts: undefined,
    bankId: undefined,
  },
  data() {
    return {
      editMaskVisible: false,
      drawerVisible: false,
      countVisible: false,
      questionTotalCounts: {
        single_choice: {},
        choice: {},
        essay: {},
        uncertain_choice: {},
        determine: {},
        fill: {},
        material: {},
      }
    };
  },
  computed: {
    totalCount() {
      return (categoryId, type) => {
        if (!this.questionTotalCounts[type][categoryId]) {
          return 0;
        }
        return this.questionTotalCounts[type][categoryId];
      }
    },
    chooseCount() {
      return (categoryId, type) => {
        if (!this.countVisible) {
          return '';
        }
        if (!this.questionCounts[type]?.categoryCounts[categoryId]) {
          return 0;
        }
        return this.questionCounts[type].categoryCounts[categoryId];
      }
    },
    sumCount() {
      return type => {
        if (!this.countVisible) {
          return 0;
        }
        let sumCount = 0;
        this.categories.forEach(category => {
          if (this.questionCounts[type].categoryCounts[category.id]) {
            sumCount += Number(this.questionCounts[type].categoryCounts[category.id]);
          }
        });

        return sumCount;
      }
    },
    sumScore() {
      return type => {
        if (!this.countVisible) {
          return (0).toFixed(1);
        }
        let sumCount = 0;
        this.categories.forEach(category => {
          if (this.questionCounts[type].categoryCounts[category.id]) {
            sumCount += Number(this.questionCounts[type].categoryCounts[category.id]);
          }
        });

        return (sumCount * this.scores[type]).toFixed(1);
      }
    }
  },
  methods: {
    onEditDrawerSave(categories, questionTypeDisplaySetting, questionCounts, scores) {
      this.$emit('updateCategories', categories);
      this.$emit('updateQuestionTypeDisplaySetting', this.questionTypeDisplaySettingKey, questionTypeDisplaySetting);
      this.$emit('updateQuestionConfigs', questionCounts, scores);
      Object.keys(questionCounts).forEach(type => {
        this.questionTotalCounts[type] = questionCounts[type].total;
      });
      this.countVisible = true;
    }
  },
}

</script>
<template>
  <div class="question-type-category-display" @mouseover="editMaskVisible = true" @mouseleave="editMaskVisible = false">
    <div class="question-type-category-display-header">
      <div class="question-type-category-display-header-top">分类</div>
      <div v-for="category in categories" class="question-type-category-display-header-normal">
        <div class="question-type-category-display-header-normal-level">{{ category.level }}</div>
        <span class="category-name">{{ category.name }}</span>
      </div>
      <div class="question-type-category-display-header-bottom">
        <span class="question-type-category-display-header-bottom-title">合计</span>
        <span class="question-type-category-display-header-bottom-description">（题数/总分）</span>
      </div>
    </div>
    <div v-for="type in questionTypeDisplaySettings[questionTypeDisplaySettingKey]" v-show="type.checked" class="question-type-category-display-header-type">
      <div class="question-type-category-display-header-top">
        <div class="question-type-category-display-header-top-content">{{ type.name }}</div>
      </div>
      <div v-for="category in categories" class="question-type-category-display-cell" :class="{'question-type-category-display-cell-inactive': countVisible && totalCount(category.id, type.type) === 0}">
        <span class="question-type-category-display-cell-number">{{ chooseCount(category.id, type.type) }}</span>
      </div>
      <div class="question-type-category-display-cell-sum">{{ sumCount(type.type) }}/{{ sumScore(type.type) }}</div>
    </div>
    <div v-show="editMaskVisible" class="edit-mask-container">
      <a-button @click="drawerVisible = true">编辑</a-button>
    </div>
    <question-type-category-edit-drawer
      :drawer-visible="drawerVisible"
      :bank-id="bankId"
      :default-categories="categories"
      :default-question-type-display-settings="questionTypeDisplaySettings"
      :question-type-display-setting-key="questionTypeDisplaySettingKey"
      :default-scores="scores"
      :default-question-counts="questionCounts"
      @close="drawerVisible = false"
      @save="onEditDrawerSave"
    />
  </div>
</template>

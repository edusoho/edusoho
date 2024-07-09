<script>

import QuestionTypeCategoryEditDrawer from './QuestionTypeCategoryEditDrawer.vue';
import {apiClient} from 'common/vue/service/api-client';

export default {
  name: 'QuestionTypeCategoryDisplay',
  components: {QuestionTypeCategoryEditDrawer},
  props: {
    defaultQuestionAllTypes: undefined,
    questionDisplayTypes: undefined,
    categories: undefined,
    bankId: undefined,
  },
  data() {
    return {
      editMaskVisible: false,
      drawerVisible: false,
      questionCounts: {
        single_choice: {},
        choice: {},
        essay: {},
        uncertain_choice: {},
        determine: {},
        fill: {},
        material: {},
      },
    };
  },
  methods: {
    fetchQuestionCounts() {
      let categoryIds = [];
      this.categories.forEach(category => {
        categoryIds.push(category.id);
      });
      apiClient.get('/api/item/categoryIdAndType/count', {
        params: {
          bank_id: this.bankId,
          category_ids: categoryIds,
        }
      }).then(res => {
        res.forEach(item => {
          this.$set(this.questionCounts[item.type], item.category_id, item.itemNum);
        });
      });
    },
    editSettingsForTypeAndCategory() {
      this.fetchQuestionCounts();
      this.drawerVisible = true;
    },
    handleUpdateDisplayQuestionType(questionAllTypes, questionDisplayTypes) {
      this.$emit('updateDisplayQuestionType', questionAllTypes, questionDisplayTypes);
    }
  },
  created() {
  }
};

</script>
<template>
  <div class="question-type-category-display" @mouseover="editMaskVisible = true" @mouseleave="editMaskVisible = false">
    <div class="question-type-category-display-header">
      <div class="question-type-category-display-header-top">分类</div>
      <div v-if="categories && categories.length > 0" class="question-type-category-display-header-normal"
           v-for="category in categories">
        <div class="question-type-category-display-header-normal-level">{{ category.level }}</div>
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
      <div v-for="category in categories" class="question-type-category-display-cell">
        <span></span>
      </div>
      <div class="question-type-category-display-cell-sum">0 / 0.0</div>
    </div>
    <div v-show="editMaskVisible" class="edit-mask-container">
      <a-button @click="editSettingsForTypeAndCategory">编辑</a-button>
    </div>
    <question-type-category-edit-drawer
      :drawer-visible="drawerVisible"
      @closeDrawer="drawerVisible = false"
      :categories="categories"
      :question-display-types="questionDisplayTypes"
      :question-all-types="defaultQuestionAllTypes"
      :question-counts="questionCounts"
      @updateDisplayQuestionType="handleUpdateDisplayQuestionType"
      @updateCategories="(newCategories) => categories = newCategories"
    />
  </div>
</template>

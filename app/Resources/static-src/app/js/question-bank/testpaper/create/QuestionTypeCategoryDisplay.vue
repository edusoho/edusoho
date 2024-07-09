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
      countVisible: false,
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
    };
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
    chooseCount() {
      return (categoryId, type) => {
        if (!this.countVisible) {
          return '';
        }
        if (!this.questionConfigs[type].count.choose[categoryId]) {
          return 0;
        }
        return this.questionConfigs[type].count.choose[categoryId];
      }
    },
    sumCount() {
      return type => {
        let sumCount = 0;
        this.categories.forEach(category => {
          if (this.questionConfigs[type].count.choose[category.id]) {
            sumCount += this.questionConfigs[type].count.choose[category.id];
          }
        });

        return sumCount;
      }
    },
    sumScore() {
      return type => {
        let sumCount = 0;
        this.categories.forEach(category => {
          if (this.questionConfigs[type].count.choose[category.id]) {
            sumCount += this.questionConfigs[type].count.choose[category.id];
          }
        });

        return (sumCount * this.questionConfigs[type].score).toFixed(1);
      }
    }
  },
  methods: {
    fetchQuestionCounts() {
      let categoryIds = [];
      this.categories.forEach(category => {
        this.questionDisplayTypes.forEach(type => {
          this.questionConfigs[type.type].count.total[category.id] = 0;
          this.questionConfigs[type.type].count.choose[category.id] = 0;
        });
        categoryIds.push(category.id);
      });
      apiClient.get('/api/item/categoryIdAndType/count', {
        params: {
          bank_id: this.bankId,
          category_ids: categoryIds,
        }
      }).then(res => {
        res.forEach(item => {
          let type = this.questionConfigs[item.type];
          type.count.total[item.category_id] = item.itemNum;
          this.questionConfigs[item.type] = {};
          this.$set(this.questionConfigs, item.type, type);
        });
      });
    },
    editSettingsForTypeAndCategory() {
      this.fetchQuestionCounts();
      this.drawerVisible = true;
    },
    handleUpdateDisplayQuestionType(questionAllTypes, questionDisplayTypes) {
      this.$emit('updateDisplayQuestionType', questionAllTypes, questionDisplayTypes);
    },
    handleSaveDrawer(categories, questionDisplayTypes) {
      this.countVisible = true;
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
      <div v-for="category in categories" class="question-type-category-display-cell" :class="{'question-type-category-display-cell-inactive': countVisible && totalCount(category.id, type.type) === 0}">
        <span class="question-type-category-display-cell-number">{{ chooseCount(category.id, type.type) }}</span>
      </div>
      <div class="question-type-category-display-cell-sum">{{ sumCount(type.type) }}/{{ sumScore(type.type) }}</div>
    </div>
    <div v-show="editMaskVisible" class="edit-mask-container">
      <a-button @click="editSettingsForTypeAndCategory">编辑</a-button>
    </div>
    <question-type-category-edit-drawer
      :drawer-visible="drawerVisible"
      @closeDrawer="drawerVisible = false"
      :question-configs="questionConfigs"
      :default-categories="categories"
      :default-question-display-types="questionDisplayTypes"
      @updateDisplayQuestionType="handleUpdateDisplayQuestionType"
      @saveDrawer="handleSaveDrawer"
    />
  </div>
</template>

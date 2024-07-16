<script>
import QuestionTypeCategoryEditDrawer from './QuestionTypeCategoryEditDrawer.vue';

export default {
  name: 'QuestionTypeCategoryDisplay',
  components: {QuestionTypeCategoryEditDrawer},
  props: {
    questionTypeDisplaySettings: undefined,
    questionTypeDisplaySettingKey: undefined,
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
            sumCount += Number(this.questionConfigs[type].count.choose[category.id]);
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
            sumCount += Number(this.questionConfigs[type].count.choose[category.id]);
          }
        });

        return (sumCount * this.questionConfigs[type].score).toFixed(1);
      }
    }
  },
  methods: {
    onEditDrawerSave(categories, questionTypeDisplaySetting, questionConfigs) {
      this.countVisible = true;
      this.$emit('updateCategories', categories);
      this.$emit('updateQuestionTypeDisplaySetting', this.questionTypeDisplaySettingKey, questionTypeDisplaySetting);
      this.questionConfigs = questionConfigs;
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
      @close="drawerVisible = false"
      @save="onEditDrawerSave"
    />
  </div>
</template>

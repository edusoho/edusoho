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
    }
  },
  methods: {
    closeDrawer() {
      this.$emit('closeDrawer');
    },
    handleUpdateDisplayQuestionType(questionAllTypes, questionDisplayTypes) {
      this.$emit('updateDisplayQuestionType',questionAllTypes, questionDisplayTypes);
    }
  },
  watch: {
    drawerVisible: function (val) {
      if (val) {
        document.body.style.overflowY = 'hidden';
      } else {
        document.body.style.overflowY = 'auto';
      }
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
          <div v-if="categories && categories.length > 0" class="question-type-category-display-header-normal" v-for="category in categories">
            <a-tag>{{ category.level }}</a-tag>
            <span class="category-name">{{ category.name }}</span>
          </div>
          <div class="question-type-category-display-header-score">
            <span class="question-type-category-display-header-top-content">按题型设置分值</span>
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
          <div v-for="category in categories" class="question-type-category-display-cell" :class="{'question-type-category-display-cell-inactive': category.questionTypes.find(questionType => questionType.type === type.type).totalNum === 0}">
            <span class="question-type-category-display-cell-number">{{ category.questionTypes.find(questionType => questionType.type === type.type).addNum }}</span>
            <span class="question-type-category-display-cell-number-total">/{{ category.questionTypes.find(questionType => questionType.type === type.type).totalNum }}</span>
          </div>
          <div class="question-type-category-display-cell">
            <span class="question-type-category-display-cell-number">{{ type.score }}</span>
          </div>
          <div class="question-type-category-display-cell-sum">0 / 0.0</div>
        </div>
        <div class="question-type-category-display-header-action">
          <div class="question-type-category-display-header-top">操作</div>
          <div v-for="category in categories" class="question-type-category-display-cell">
            <a href="javascript:">移除</a>
          </div>
          <div class="question-type-category-display-cell">
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

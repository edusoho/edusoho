<script>

import TestpaperTypeTag from '../TestpaperTypeTag.vue';
import {Testpaper} from 'common/vue/service';
import QuestionTypePreviewDisplay from '../create/components/QuestionTypePreviewDisplay.vue'

export default {
  components: {QuestionTypePreviewDisplay, TestpaperTypeTag},
  props: {
    itemBankId: null,
    id: null,
  },
  data() {
    return {
      paper: null,
      questionTypes: ['single_choice', 'choice', 'essay', 'uncertain_choice', 'determine', 'fill', 'material']
    }
  },
  async beforeMount() {
    this.paper = await Testpaper.get(this.id);
    console.log(this.paper);
  },
  methods: {
    async back() {
      await this.$router.back();
    }
  }
};
</script>
<template>
  <div v-if="paper" class="test-create test-preview">
    <div class="test-create-title">
      <div class="test-create-title-left">
        <div class="test-create-title-left-back" @click="back">
          <span class="test-create-title-left-back-img">
            <img src="/static-dist/app/img/question-bank/back-image.png" alt=""/>
          </span>
          <span class="test-create-title-left-back-text">返回</span>
        </div>
        <i></i>
        <testpaper-type-tag v-if="paper.type" :type="paper.type"/>
        <span v-if="paper.name">{{ paper.name }}</span>
      </div>
      <div class="test-create-title-right">
        <span class="test-create-title-right-item">
          <span class="test-create-title-right-text">试题</span>
          <span class="test-create-title-right-number">{{ paper['question_count'] }}</span>
        </span>
        <i></i>
        <span class="test-create-title-right-item">
          <span class="test-create-title-right-text">总分</span>
          <span class="test-create-title-right-number">{{ paper['total_score'] }}</span>
        </span>
      </div>
    </div>
    <div class="test-preview-content">
      <span class="test-preview-content-title">基本信息</span>
      <div class="test-preview-content-basic">
        <img class="test-preview-content-basic-img" src="/static-dist/app/img/question-bank/create-ai-paper.png" alt=""/>
        <div class="test-preview-content-basic-information-list">
          <div class="test-preview-content-basic-information-item">
            <span class="test-preview-content-basic-information-item-label">试卷名称：</span>
            <span class="test-preview-content-basic-information-item-value">{{ paper.name }}</span>
          </div>
          <div class="test-preview-content-basic-information-item">
            <span class="test-preview-content-basic-information-item-label">试卷说明：</span>
            <span v-if="paper.description.length === 0" class="test-preview-content-basic-information-item-value">无</span>
          </div>
          <div class="test-preview-content-basic-information-item">
            <span class="test-preview-content-basic-information-item-label">错题比例：</span>
            <span class="test-preview-content-basic-information-item-value">{{ paper.assessmentGenerateRule['wrong_question_rate'] }}%</span>
          </div>
          <div class="test-preview-content-basic-information-item">
            <span class="test-preview-content-basic-information-item-label">试题：</span>
            <span class="test-preview-content-basic-information-item-value">{{ paper['question_count'] }}</span>
          </div>
          <div class="test-preview-content-basic-information-item">
            <span class="test-preview-content-basic-information-item-label">总分：</span>
            <span class="test-preview-content-basic-information-item-value">{{ paper['total_score'] }}</span>
          </div>
        </div>
        <div class="test-preview-content-basic-operation">
          <a-button type="primary">进入编辑</a-button>
        </div>
      </div>
      <div class="test-preview-content-title-display">
        <span class="test-preview-content-title">抽题统计</span>
        <span class="test-preview-content-title-explanation">（按题型抽题）</span>
      </div>
      <div class="test-preview-content-question-type-display">
        <question-type-preview-display v-for="(type) in questionTypes"
                                       :type="type"
                                       :score="paper.assessmentGenerateRule['question_setting'].scores[type] || 2"
                                       :num="paper.assessmentGenerateRule['question_setting'].questionCategoryCounts[0].sections[type].count || 5"
        />
      </div>
    </div>
  </div>
</template>

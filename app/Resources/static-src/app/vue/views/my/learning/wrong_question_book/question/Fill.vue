<template>
  <div class="question-layout">
    <order :question="question" />

    <div class="mb16" v-html="formateQuestionStem" />

     <a-divider style="margin: 16px 0;" />

    <div class="clearfix result mb16">
      <div class="pull-left result-label">答题结果：</div>
      <div class="pull-left result-content">{{ rightAnswer }}</div>
    </div>

    <analysis :analysis="question.analysis" />

    <slot name="situation" />
  </div>
</template>

<script>
import _ from 'lodash';
import Order from './components/Order.vue';
import Analysis from './components/Analysis.vue';

export default {
  name: 'Fill',

  props: {
    question: {
      type: Object,
      required: true
    },

    report: {
      type: Object,
      required: true
    }
  },

  components: {
    Order,
    Analysis
  },

  computed: {
    rightAnswer() {
      const { answer } = this.question;
      return `${_.join(answer, '、')}`;
    },

    formateQuestionStem() {
      const text = this.question.stem;
      const reg = /\[\[\]\]/g;
      if (!text.match(reg)) {
        return text;
      }
      let index = 1;
      return text.replace(reg, function() {
        return `<span class="stem-fill-blank ph16">(${index++})</span>`;
      });
    }
  },

  methods: {
    getAnswerClass(value) {
      const { answer } = this.question; // 正确答案
      const { response } = this.report; // 用户选择的答案

      if (_.includes(_.difference(answer, response), value)) return 'right-answer'; // 用户未选的正确答案

      if (_.includes(_.difference(response, answer), value)) return 'choose-answer--wrong'; // 用户选择的错误答案

      if (_.includes(_.intersection(answer, response), value)) return 'choose-answer--right'; // 用户选中的正确答案
    }
  }
}
</script>

<style lang="less" scoped>
@import './common.less';

/deep/ .stem-fill-blank {
  padding-bottom: 2px;
  line-height: 20px;
  border-bottom: 1px solid #999;
  color: #aaa;
}
</style>

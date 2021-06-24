<template>
  <div class="question-layout">
    <order :question="question" />

    <div class="mb16" v-html="question.stem" />

    <div class="prevent-click">
      <a-radio-group :default-value="report.response[0]">
        <a-radio
          :class="['choose-answer', getAnswerClass(item.radio.val)]"
          v-for="(item, index) in question.response_points"
          :key="index"
          :value="item.radio.val"
        >
          <div class="choose-answer-content">
            <span>{{ item.radio.val }}.</span>
            <span class="choose-answer-text" v-html="item.radio.text" />
          </div>
        </a-radio>
      </a-radio-group>
    </div>

     <a-divider style="margin: 16px 0;" />

    <div class="clearfix result mb16">
      <div class="pull-left result-label">正确答案：</div>
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
  name: 'SingleChoice',

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

  data() {
    return {
      value: 1
    }
  },

  computed: {
    rightAnswer() {
      const { answer } = this.question;
      return `${_.join(answer, '、')}`;
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

.result {
  margin-bottom: 16px;

  .result-label {
    color: #333;
  }

  .result-content {
    width: calc(100% - 72px);
  }
}
</style>

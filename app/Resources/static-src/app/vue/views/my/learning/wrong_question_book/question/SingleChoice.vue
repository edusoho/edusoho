<template>
  <div class="question-layout">
    <order />

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
      <div class="pull-left result-content">正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C</div>
    </div>

    <analysis />

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

  methods: {
    getAnswerClass(value) {
      const { answer } = this.question; // 正确答案
      const { response } = this.report; // 用户选择的答案

      // 用户未选的正确答案
      if (_.includes(_.difference(answer, response), value)) return 'right-answer';

      // 用户选择的错误答案
      if (_.includes(_.difference(response, answer), value)) return 'choose-answer--wrong'; // check wrong answer

      // 用户选中的正确答案
      if (_.includes(_.intersection(answer, response), value)) return 'choose-answer--right'; // select correct answer
    }
  }
}
</script>

<style lang="less" scoped>
.question-layout {
  position: relative;
  padding-left: 54px;

  /deep/ .choose-answer {
    display: block;
    position: relative;
    font-weight: 400;

    .ant-radio {
      position: absolute;
      top: 2px;
      vertical-align: super;

      & + span {
        display: inline-block;
        margin-left: 16px;
      }
    }

    .choose-answer-content {
      display: table;
      white-space: normal;

      .choose-answer-text {
        display: table-cell;

        p {
          margin: 0;
        }
      }
    }

    &--right {
      color: #46c37b;
    }

    &--right .ant-radio-checked .ant-radio-inner {
      border-color: #46c37b;

      &::after {
        background-color: #46c37b;
      }
    }

    &--wrong {
      color: #ff5c3b;

      .ant-radio-checked .ant-radio-inner {
        border-color: #ff5c3b;

        &::after {
          background-color: #ff5c3b;
        }
      }
    }
  }

  .right-answer {
    color: #46c37b;
  }
}

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

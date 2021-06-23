<template>
  <div class="question-layout">
    <order />

    <div class="mb16" v-html="question.stem" />

    <div class="prevent-click">
      <a-radio-group :default-value="question.answer[0]">
        <a-radio
          class="choose-item"
          v-for="(item, index) in question.response_points"
          :key="index"
          :value="item.radio.val"
        >
          <div :class="['choose-answer', { 'choose-answer--right': question.answer[0] == item.radio.val }]">
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
  }
}
</script>

<style lang="less" scoped>
.question-layout {
  position: relative;
  padding-left: 54px;

  /deep/ .choose-item {
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

    .choose-answer {
      display: table;
      white-space: normal;

      &-text {
        display: table-cell;

        p {
          margin: 0;
        }
      }

      &--right {
        color: #46c37b;
      }
    }
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

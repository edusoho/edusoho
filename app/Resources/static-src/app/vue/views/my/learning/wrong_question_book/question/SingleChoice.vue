<template>
  <div class="question-layout">
    <order />

    <div class="title">这是题目?</div>

    <div class="prevent-click">
      <a-radio-group :default-value="value">
        <a-radio :style="radioStyle" :value="1">
          Option A
        </a-radio>
        <a-radio :style="radioStyle" :value="2">
          Option B
        </a-radio>
        <a-radio :style="radioStyle" :value="3">
          Option C
        </a-radio>
      </a-radio-group>
    </div>

     <a-divider style="margin: 16px 0;" />

    <div class="clearfix result">
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

  components: {
    Order,
    Analysis
  },

  data() {
    return {
      value: 1,
      radioStyle: {
        display: 'block',
        height: '30px',
        lineHeight: '30px',
        fontWeight: '400'
      }
    }
  }
}
</script>

<style lang="less" scoped>
.question-layout {
  position: relative;
  padding-left: 54px;
}

.prevent-click {
  pointer-events: none;
}

.title {
  margin-bottom: 16px;
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

@media (max-width: 767px) {

  .title,
  .result {
    margin-bottom: 8px;
  }
}
</style>

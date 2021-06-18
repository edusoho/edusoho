<template>
  <Layout>
    <order />

    <div class="title">这是题目?</div>

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

    <a-divider style="margin: 16px 0;" />

    <div class="clearfix result">
      <div class="pull-left result-label">正确答案：</div>
      <div class="pull-left result-content">正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C</div>
    </div>

    <analysis />

    <template #situation>
      <slot name="situation" />
    </template>
  </Layout>
</template>

<script>
import Layout from './Layout.vue';
import Order from './Order.vue';
import Analysis from './Analysis.vue';

export default {
  name: 'SingleChoice',

  components: {
    Layout,
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

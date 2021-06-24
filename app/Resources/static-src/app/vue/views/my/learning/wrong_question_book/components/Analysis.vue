<template>
  <div class="clearfix analysis">
    <div class="pull-left analysis-label">解析：</div>
    <div class="pull-left analysis-content" v-html="analysis"></div>
  </div>
</template>

<script>
export default {
  props: {
    analysis: {
      type: String,
      required: true
    }
  }
}
</script>

<style lang="less" scoped>
.analysis {
  padding: 16px 26px 16px 0;
  margin-top: 16px;
  background: rgba(70, 195, 123, 0.05);
  border-radius: 4px;

  .analysis-label {
    text-indent: 2em;
    color: #333;
  }

  .analysis-content {
    width: calc(100% - 72px);
    color: #666;
  }

  /deep/ p {
    margin: 0;
  }
}

@media (max-width: 767px) {
  .analysis {
    padding: 8px 8px 8px 0;
    margin-top: 8px;
  }
}
</style>

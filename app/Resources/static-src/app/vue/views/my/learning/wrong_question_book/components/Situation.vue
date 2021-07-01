<template>
  <div class="clearfix situation">
    <div class="pull-left source">来源：{{ source }}</div>

    <div class="pull-right clearfix situation-right">
      <div class="pull-left frequency">
        做错频次：
        <span class="frequency-error">{{ question.wrong_times }}</span>次
      </div>
      <div class="pull-left time">错题时间：{{ $dateFormat(question.updated_time, 'YYYY-MM-DD HH:mm:ss') }}</div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    question: {
      type: Object,
      required: true
    }
  },

  computed: {
    source() {
      const { mainSource, secondarySource } = this.question.questions[0].source;
      return `${mainSource} - ${secondarySource}`;
    }
  }
}
</script>

<style lang="less" scoped>
.situation {
  color: #666;

  .source {
    color: #46c37b;
  }

  .frequency {
    margin-right: 40px;

    .frequency-error {
      color: #ff5c3b;
    }
  }

  .source,
  .frequency,
  .time {
    margin-top: 16px;
  }
}

@media (max-width: 767px) {
  .situation {

    .frequency {
      margin-right: 0;
    }

    .source,
    .situation-right,
    .frequency,
    .time {
      float: none !important;
    }

    .source,
    .frequency,
    .time {
      margin-top: 8px;
    }
  }
}
</style>


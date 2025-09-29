<template>
  <div class="clearfix situation">
    <div class="pull-left source">{{ 'components.wrong_question.question_element.situation.source' | trans }}：{{ source }}</div>

    <div class="pull-right clearfix situation-right">
      <div class="pull-left frequency">
        {{ 'components.wrong_question.question_element.situation.mistakes_frequency' | trans }}：
        <span class="frequency-error">{{ question.wrong_times }}</span>{{ 'components.wrong_question.question_element.situation.times' | trans }}
      </div>
      <div class="pull-left time">{{ 'components.wrong_question.question_element.situation.incorrect_questions' | trans }}：{{ $dateFormat(question.submit_time, 'YYYY-MM-DD HH:mm:ss') }}</div>
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
      return this.question.sources.join('、');
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


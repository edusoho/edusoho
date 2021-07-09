<template>
  <div class="wrong-question-result">
    <e-loading v-if="isLoading" />
    <div class="result-data">
      <div class="result-data__item">
        正确率
        <div class="result-data__bottom data-number-green">
          <span class="data-number">{{ rightRate }}</span
          >%
        </div>
      </div>
      <div class="result-data__item">
        做题用时
        <div class="result-data__bottom data-number-gray">
          <span class="data-number">{{ usedTime }}</span>
          分钟
        </div>
      </div>
    </div>

    <div class="result-tag">
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-green" />
        正确
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-orange" />
        错误
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-gray" />
        未作答
      </div>
    </div>

    <div class="result-panel">
      <ul class="result-list">
        <li
          v-for="(item, index) in reports.item_reports"
          :key="index"
          :class="['result-list__item testpaper-number', getStatusClass(item)]"
        >
          {{ index + 1 }}
        </li>
      </ul>
    </div>

    <div class="result-footer">
      <van-button
        class="result-footer__btn"
        type="primary"
        @click="viewAnalysis()"
        >查看解析</van-button
      >
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import _ from 'lodash';

export default {
  name: 'WrongQuestionResult',
  data() {
    return {
      isLoading: true,
      answerRecord: {},
      reports: {},
    };
  },
  computed: {
    usedTime() {
      const timeInterval = parseInt(this.answerRecord.used_time) || 0;
      return timeInterval <= 60 ? 1 : Math.round(timeInterval / 60);
    },

    rightRate() {
      return parseInt(
        (this.reports.right_question_num / this.reports.question_count) * 100,
      );
    },
  },

  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },

  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';
    next();
  },

  created() {
    this.fetchAnswerRecord();
  },

  methods: {
    fetchAnswerRecord() {
      const query = {
        answerRecordId: Number(this.$route.query.recordId),
      };
      Api.answerRecord({
        query,
      })
        .then(res => {
          this.answerRecord = res.answer_record;
          this.reports = res.answer_report.section_reports[0];
          this.isLoading = false;
        })
        .catch(err => {
          this.isLoading = false;
          this.$toast(err.message);
        });
    },

    getStatusClass(reports) {
      const statusResult = {
        right: 'circle-green',
        wrong: 'circle-orange',
        partRight: 'circle-orange',
        no_answer: 'circle-gray',
      };
      const { response, status } = reports.question_reports[0];

      if (!_.size(response)) {
        return statusResult.no_answer;
      }

      return statusResult[status];
    },

    viewAnalysis() {
      this.$router.push({
        name: 'WrongExercisesAnalysis',
      });
    },
  },
};
</script>

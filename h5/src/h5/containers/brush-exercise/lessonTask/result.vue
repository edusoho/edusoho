<template>
  <div class="item-bank-result" :style="{ height: height + 'px' }">
    <e-loading v-if="isLoading" />
    <div class="notify">
      ※请参考题目解析，对主观题自行估分批阅。
    </div>
    <div class="item-bank-result_content">
      <div v-if="isReadOver" class="exercise-report-status">
        <img src="static/report-success.png" />
        <p class="result-text result-status_fail">正在批阅中</p>
      </div>
      <div v-else class="exercise-report-status">
        <img src="static/report-success.png" />
        <p class="result-text result-status_fail">很遗憾，您未通过本次考试</p>
        <p class="result-comment">教师评语：继续努力，争取下次考试通过</p>
      </div>
      <div class="result-score">
        分数：
        <span class="result-status_fail" v-if="isReadOver">?</span>
        <span v-else>{{ answerReport.score }}分</span>
      </div>
      <div class="result-content">
        <div class="result-content-item result-section-title">
          <span>题型</span>
          <span>答对题</span>
          <span>总分</span>
        </div>
        <div
          class="result-content-item"
          v-for="(report, index) in answerReport.section_reports"
          :key="index"
        >
          <span>{{ report.section_name }}</span>
          <span>
            <i class="color-primary">{{ report.right_question_num }}</i>
            / {{ report.question_count }}
          </span>
          <span class="color-primary">
            <i
              v-if="report.reviewing_question_num > 0 && isReadOver"
              class="result-status_fail"
              >?
            </i>
            <i v-else>{{ report.score }}</i>
          </span>
        </div>
      </div>
      <div class="result-footer">
        <div v-if="isReadOver" class="result-footer__btn" @click="doReview">
          开始批阅
        </div>
        <template v-else>
          <div class="result-footer__btn" @click="doAgain">再次答题</div>
          <div class="result-footer__btn" @click="doAnalysis">查看解析</div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import * as types from '@/store/mutation-types.js';
export default {
  components: {},
  data() {
    return {
      isLoading: true,
      assessment: {},
      answerScene: {},
      answerReport: {},
      answerRecord: {},
      height: 0,
    };
  },
  computed: {
    getStatusImg() {
      const status = this.answerRecord.status;
      switch (status) {
        case 'doing':
          return 'static/report-success.png';
        case 'reviewing':
          return 'static/report-success.png';
        case 'finished':
          return 'static/report-success.png';
        default:
          return '';
      }
    },
    usedTime() {
      return this.answerRecord.used_time / 60;
    },
    isReadOver() {
      return this.answerRecord.status === 'reviewing';
    },
  },
  watch: {},
  created() {
    this.getData();
  },
  mounted() {
    this.$nextTick(() => {
      this.height = this.getheight();
    });
  },
  methods: {
    getheight() {
      const clientHeight = document.body.clientHeight - 46;
      if (document.body.scrollHeight - 46 > clientHeight) {
        return document.body.scrollHeight;
      }
      return clientHeight;
    },
    getData() {
      const query = {
        answerRecordId: Number(this.$route.params.answerRecordId),
      };
      Api.answerRecord({
        query,
      })
        .then(res => {
          this.assessment = res.assessment;
          this.answerScene = res.answer_scene;
          this.answerReport = res.answer_report;
          this.answerRecord = res.answer_record;
          this.$store.commit(types.SET_NAVBAR_TITLE, res.assessment.name);
          this.isLoading = false;
        })
        .catch(err => {
          this.$toast(err.message);
        });
    },
    doReview() {
      const query = {
        type: this.$route.query.type,
        exerciseId: this.$route.query.exerciseId,
        assessmentId: this.$route.query.assessmentId,
        moduleId: this.$route.query.moduleId,
      };
      const answerRecordId = this.$route.params.answerRecordId;
      this.$router.push({ path: `/brushReview/${answerRecordId}`, query });
    },
    doAgain() {
      const type = this.$route.query.type;
      const query = {
        mode: 'start',
        type: this.$route.query.type,
        exerciseId: this.$route.query.exerciseId,
        moduleId: this.$route.query.moduleId,
      };
      if (type === 'chapter') {
        query.categoryId = this.$route.query.categoryId;
      } else {
        query.assessmentId = this.$route.query.assessmentId;
      }
      this.$router.push({ path: '/brushDo', query });
    },
    doAnalysis() {
      const answerRecordId = this.$route.params.answerRecordId;
      this.$router.push({
        path: `/brushReport/${answerRecordId}`,
      });
    },
  },
};
</script>

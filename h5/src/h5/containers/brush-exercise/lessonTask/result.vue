<template>
  <div class="brush-exercise-report item-bank-result">
    <e-loading v-if="isLoading" />
    <div v-if="isReadOver" class="exercise-report-status">
      <img src="static/report-success.png" />
      <p class="result-text result-status_fail">正在批阅中</p>
    </div>

    <div v-else class="exercise-report-status">
      <img src="static/report-success.png" />
      <p class="result-text result-status_fail">很遗憾，您未通过本次考试</p>
      <p class="result-comment">教师评语：继续努力，争取下次考试通过</p>
    </div>
    <!-- <div v-if="answerReport" ref="data" class="result-data">
      <div class="result-data__item">
        本次得分
        <div
          v-if="isReadOver"
          class="result-data__bottom data-number-orange data-medium"
        >
          <span class="data-number">{{ answerReport.score }}</span
          >分
        </div>
        <div v-else class="result-data__bottom data-text-blue">待批阅</div>
      </div>
      <div class="result-data__item">
        正确率
        <div
          v-if="isReadOver"
          class="result-data__bottom data-number-green data-medium"
        >
          <span class="data-number">{{ result.right_rate }}</span
          >%
        </div>
        <div v-else class="result-data__bottom data-text-blue">待批阅</div>
      </div>
      <div class="result-data__item">
        做题用时
        <div class="result-data__bottom data-number-gray data-medium">
          <span class="data-number">{{ usedTime }}</span
          >分钟
        </div>
      </div>
    </div>
    <div ref="tag" class="result-tag">
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
      <div v-show="!isReadOver" class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-brown" />
        待批阅
      </div>
    </div>
    <div class="result-subject">
      <van-panel
        v-for="(section, sectionIndex) in assessment.sections"
        :key="sectionIndex"
        :title="section.name"
        class="result-panel"
      >
        <ul class="result-list" :key="itemIndex">
          <template v-for="(item, itemIndex) in section.items">
            <li
              v-for="(question, questionIndex) in item.questions"
              :class="[
                'result-list__item testpaper-number',
                getStatusColor(sectionIndex, itemIndex, questionIndex),
              ]"
              :key="questionIndex"
            >
              {{ question.seq }}
            </li>
          </template>
        </ul>
      </van-panel>
    </div>-->
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
          <i v-if="report.reviewing_question_num > 0" class="result-status_fail"
            >?
          </i>
          <i v-else>{{ report.score }}</i>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
const color = {
  // 题号标签状态判断
  right: 'green',
  reviewing: 'brown',
  wrong: 'orange',
  part_right: 'orange',
  no_answer: 'gray',
};
export default {
  components: {},
  data() {
    return {
      isLoading: true,
      assessment: {},
      answerScene: {},
      answerReport: {},
      answerRecord: {},
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
  methods: {
    getData() {
      const query = {
        answerRecordId: Number(this.$route.params.answerRecordId),
      };
      Api.answerRecord({
        query,
      }).then(res => {
        this.assessment = res.assessment;
        this.answerScene = res.answer_scene;
        this.answerReport = res.answer_report;
        this.answerRecord = res.answer_record;
        console.log(res);
        this.isLoading = false;
      });
    },
    getStatusColor(s, i, q) {
      const status = this.answerReport.section_reports[s].item_reports[i]
        .question_reports[q].status;
      console.log(status);
      return `circle-${color[status]}`;
    },
  },
};
</script>

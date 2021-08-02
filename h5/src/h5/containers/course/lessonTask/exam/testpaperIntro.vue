<template>
  <div>
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />
    <div class="intro-body">
      <van-panel class="panel intro-panel" :title="$t('courseLearning.testName')">
        <div class="intro-panel__content intro-panel__content--title">
          {{ testpaperTitle }}
        </div>
      </van-panel>
      <van-panel v-if="startTime" class="panel intro-panel" :title="$t('courseLearning.openingTime')">
        <div
          :class="[
            'intro-panel__content',
            result || !disabled ? '' : 'intro-tip',
          ]"
        >
          {{ formateStartTime(startTime) }}
        </div>
      </van-panel>
      <van-panel class="panel intro-panel" :title="$t('courseLearning.examinationDuration')">
        <div
          v-if="limitTime"
          :class="[
            'intro-panel__content',
            result || !disabled ? '' : 'intro-tip',
          ]"
        >
          {{ limitTime }}{{ $t('courseLearning.minutes') }}
        </div>
        <div v-else class="intro-panel__content">{{ $t('courseLearning.noRestrictions') }}</div>
      </van-panel>
      <van-panel class="panel intro-panel" :title="$t('courseLearning.fullScoreOfTestPaper')">
        <div class="intro-panel__content">{{ $t('courseLearning.fullMark', { number: score }) }}</div>
      </van-panel>
      <van-panel class="panel intro-panel" :title="$t('courseLearning.numberOfTopics')">
        <div class="intro-panel__content">
          <van-cell
            :border="false"
            :value="`${sum}${$t('courseLearning.topic')}`"
            class="intro-cell intro-cell--total"
            :title="$t('courseLearning.total')"
          />
          <van-cell
            v-for="item in question_type_seq"
            :border="false"
            :key="item"
            :title="$t(obj[item])"
            :value="`${counts[item]}${$t('courseLearning.topic')}`"
            class="intro-cell"
          />
        </div>
      </van-panel>
    </div>
    <div class="intro-footer">
      <van-button
        v-if="result"
        class="intro-footer__btn"
        type="primary"
        @click="showResult"
        >{{ $t('courseLearning.viewResult') }}</van-button
      >
      <van-button
        v-else
        :disabled="disabled"
        class="intro-footer__btn"
        type="primary"
        @click="startTestpaper()"
        >{{ $t('courseLearning.startTheExam') }}</van-button
      >
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapActions } from 'vuex';
import { Dialog, Toast } from 'vant';
import { formatTime } from '@/utils/date-toolkit.js';
import examMixin from '@/mixins/lessonTask/exam.js';
import report from '@/mixins/course/report';
import OutFocusMask from '@/components/out-focus-mask.vue';

export default {
  name: 'TestpaperIntro',
  mixins: [examMixin, report],
  components: {
    OutFocusMask,
  },
  data() {
    return {
      enable_facein: '', // 是否开启云监考
      testpaper: null, // 考试数据
      testpaperTitle: '', // 考试标题
      info: {}, // 考试类型说明，是否能重考相关信息
      startTime: null, // 考试开始时间
      limitTime: null, // 考试限制时间/分钟
      score: null, // 考试满分
      total: 0, // 考试题目总计数量
      testId: null, // 考试试卷ID
      targetId: null, // 任务ID
      counts: {}, // 考试题型数量对象
      result: null, // 考试结果信息
      question_type_seq: [], // 试卷已有题型
      answerName: null,
      timeName: null,
      answer: null,
      time: null,
      obj: {
        single_choice: 'courseLearning.singleChoice',
        choice: 'courseLearning.choice',
        essay: 'courseLearning.essay',
        uncertain_choice: 'courseLearning.uncertainChoice',
        determine: 'courseLearning.determine',
        fill: 'courseLearning.fill',
        material: 'courseLearning.material',
      },
    };
  },
  computed: {
    sum() {
      let sum = 0;
      for (const i in this.counts) {
        sum = sum + parseInt(this.counts[i]);
      }
      return sum;
    },
    disabled() {
      const nowTime = new Date().getTime();
      return this.startTime > nowTime;
    },
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user,
      selectedPlanId: state => state.course.selectedPlanId,
    }),
  },
  mounted() {
    this.initReport();
    this.getInfo();
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';
    next();
  },
  methods: {
    ...mapActions('course', ['handExamdo']),
    // 初始化上报数据
    initReport() {
      this.initReportData(
        this.selectedPlanId,
        this.$route.query.targetId,
        'testpaper',
      );
    },
    getInfo() {
      this.testId = this.$route.query.testId;
      this.targetId = this.$route.query.targetId;
      Api.testpaperIntro({
        params: {
          targetId: this.targetId,
          targetType: 'task',
        },
        query: {
          testId: this.testId,
        },
      })
        .then(res => {
          this.counts = res.items;
          this.testpaperTitle = res.task.title;
          this.testpaper = res.testpaper;
          this.result = res.testpaperResult;
          this.info = res.task.activity.testpaperInfo;
          this.enable_facein = res.task.enable_facein;

          this.score = this.testpaper.score;
          this.startTime = parseInt(this.info.startTime) * 1000;
          this.limitTime = parseInt(this.info.limitTime);
          this.question_type_seq = this.testpaper.metas.question_type_seq;

          this.canDoing(this.result, this.user.id)
            .then(() => {
              this.startTestpaper();
            })
            .catch(({ answer, endTime }) => {
              this.submitExam(answer, endTime);
            });
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    submitExam(answer, endTime) {
      endTime = endTime || new Date().getTime();
      const datas = {
        answer,
        resultId: this.result.id,
        userId: this.user.id,
        beginTime: Number(this.result.beginTime),
        endTime,
      };
      // 交卷+跳转到结果页
      this.handExamdo(datas)
        .then(res => {
          this.showResult();
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    startTestpaper() {
      if (this.enable_facein === 1) {
        Dialog.alert({
          title: '',
          confirmButtonText: this.$t('courseLearning.iKnow'),
          message:
            '本场考试已开启云监考，暂不支持在移动端答题，请前往PC端进行答题。',
        }).then(() => {});
      } else {
        this.goDoTestpaper();
      }
    },
    goDoTestpaper() {
      this.$router.push({
        name: 'testpaperDo',
        query: {
          testId: this.testId,
          targetId: this.targetId,
          title: this.testpaperTitle,
          action: 'do',
        },
        params: {
          KeepDoing: true,
        },
      });
    },
    showResult() {
      this.$router.push({
        name: 'testpaperResult',
        query: {
          resultId: this.result.id,
          testId: this.testId,
          targetId: this.targetId,
        },
      });
    },
    // 开考时间
    formateStartTime(startTime) {
      startTime = formatTime(new Date(startTime));
      return startTime;
    },
  },
};
</script>

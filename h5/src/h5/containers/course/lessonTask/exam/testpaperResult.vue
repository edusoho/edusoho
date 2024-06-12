<template>
  <div class="testResults">
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />
    <div class="testpaper-result-status">
      <img v-if="result.passedStatus === 'passed'" src="static/images/testpaper/passed-bg.png" />
      <img v-else src="static/images/testpaper/unpassed-bg.png" />
      <div class="trs-content">
        <template v-if="isReadOver">
          <div class="result-score">
            <span class="data-number">{{ result.score }}</span>
            <span class="data-unit">{{ $t('courseLearning.branch') }}</span>
          </div>
          <div class="result-score-tips">
            {{ result.passedStatus === 'passed' ? $t('courseLearning.passedTips') : $t('courseLearning.unpassedTips') }}
          </div>
        </template>
        <div v-else class="text-center">
          <img src="static/images/testpaper/reviewing-icon.png" style="width: 44px;" />
          <div style="margin-top: 4px;">{{ $t('courseLearning.reviewing') }}</div>
        </div>
      </div>

    </div>

    <div v-if="result" ref="data" class="testpaper-result-data">
      <div v-if="isReadOver" class="testpaper-result-data__item">
        <div class="trd-header">{{ $t('courseLearning.correctRate') }}</div>
        <div>
          <span class="data-number">{{ result.rightRate }}</span>
          <span class="data-unit">%</span>
        </div>
      </div>

      <div v-if="result.limitedTime > 0" class="testpaper-result-data__item">
        <div class="trd-header">{{ $t('courseLearning.examinationDuration') }}</div>
        <div>
          <span class="data-number">{{ result.limitedTime }}</span>
          <span class="data-unit">{{ $t('courseLearning.minutes') }}</span>
        </div>
      </div>

      <div class="testpaper-result-data__item">
        <div class="trd-header">{{ $t('courseLearning.examTotalTips') }}</div>
        <div>
          <span class="data-number">{{ usedTime.minutes }}</span>
          <span class="data-unit">{{ $t('courseLearning.branch') }}</span>
          <span class="ml-4 data-number">{{ usedTime.second }}</span>
          <span class="data-unit">{{ $t('courseLearning.second') }}</span>
        </div>
      </div>
    </div>

    <div v-if="result.teacherSay" class="teacher-say">
      {{ $t('courseLearning.teacherSay') }}：{{ result.teacherSay }}
    </div>

    <div class="testpaper-result">
      <div class="testpaper-result__header">
        <div>{{ $t('courseLearning.qustionType') }}</div>
        <div>{{ $t('courseLearning.corretAnswer') }}</div>
        <div>{{ $t('courseLearning.fullScoreOfTestPaper') }}</div>
      </div>
      <div class="testpaper-result__content">
        <div class="trc-item" v-for="keyItem in question_type_seq" :key="keyItem">
          <div class="question-type">{{ $t(obj[keyItem]) }}</div>
          <div class="question-answer">
            <span class="right-number">{{ getRightAnswerData(keyItem).totalNumber }}</span>/{{ subjectList[keyItem].length }}
          </div>
          <div class="question-total">{{ getRightAnswerData(keyItem).totalScore }}</div>
        </div>
      </div>
    </div>

    <div ref="footer" class="result-footer">
      <van-button
        v-if="resultShow"
        :style="{ marginRight: isReadOver && canDoAgain ? '2vw' : 0 }"
        class="result-footer__btn"
        type="primary"
        @click="viewAnalysis()"
        >{{ $t('courseLearning.viewParsed') }}</van-button
      >
      <van-button
        v-if="again && isReadOver && canDoAgain"
        class="result-footer__btn"
        type="primary"
        @click="startTestpaper()"
        >{{ $t('courseLearning.takeTheTestAgain') }}</van-button
      >
      <van-button
        v-if="!again && isReadOver && canDoAgain"
        class="result-footer__btn"
        type="primary"
        disabled
        >
        <div>{{ remainTime }}</div>
        {{ $t('courseLearning.takeTheTestAgain') }}
      </van-button>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapMutations, mapActions } from 'vuex';
import * as types from '@/store/mutation-types';
import examMixin from '@/mixins/lessonTask/exam.js';
import report from '@/mixins/course/report';
import { formatTimeByNumber } from '@/utils/date-toolkit.js';
import { Dialog, Toast } from 'vant';
import OutFocusMask from '@/components/out-focus-mask.vue';

export default {
  name: 'TestpaperResult',
  components: {
    OutFocusMask,
  },
  mixins: [examMixin, report],
  data() {
    return {
      courseId: '',
      enable_facein: '', // 是否开启云监考
      isReadOver: false, // 是否已批阅
      resultId: null, // 考试结果ID
      again: 0, // 是否再考一次
      result: {}, // 返回的考试结果对象
      calHeight: null, // 题目列表高度
      subjectList: {}, // 题目列表对象
      question_type_seq: [], // 考试已有题型
      targetId: null, // 任务ID
      canDoAgain: '', // 是否还可以考试
      redoInterval: null, // 重考间隔
      remainTime: null, // 再次重考剩余时间
      timeMeter: null, // 重考间隔倒计时
      testpaperTitle: null, // 考试题目
      obj: {
        // 题型判断
        single_choice: 'courseLearning.singleChoice',
        choice: 'courseLearning.choice',
        essay: 'courseLearning.essay',
        uncertain_choice: 'courseLearning.uncertainChoice',
        determine: 'courseLearning.determine',
        fill: 'courseLearning.fill',
        material: 'courseLearning.material',
      },
      color: {
        // 题号标签状态判断
        right: 'green',
        none: 'brown',
        wrong: 'orange',
        partRight: 'orange',
        noAnswer: 'gray',
      },
      resultShow: false,
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user,
      selectedPlanId: state => state.course.selectedPlanId,
    }),
    usedTime: function() {
      const usedTime = parseInt(this.result.usedTime) || 0;
      const minutes = Math.floor(usedTime / 60) || 0;
      const second = usedTime - 60 * minutes;

      return { minutes, second }
    }
  },
  watch: {
    hasRemainderDoTimes: function() {
      this.calSubjectHeight();
    },
  },
  mounted() {
    this.initReport();
    this.getTestpaperResult();
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';
    next();
  },
  beforeDestroy() {
    // 清除定时器
    this.clearTime();
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),
    ...mapActions('course', ['handExamdo']),
    getRightAnswerData(type) {
      const currentData = this.subjectList[type] || []
      let totalNumber = 0;
      let totalScore = 0;

      currentData.forEach(item => {
        if (item.status === 'right') {
          totalNumber++;
        }

        totalScore += Number(item.score);
      })

      return { totalNumber, totalScore }
    },
    async getTestpaperResult() {
      await Api.testpaperResult({
        query: {
          resultId: this.$route.query.resultId,
        },
      }).then(res => {
        this.result = res.testpaperResult;
        this.question_type_seq = res.testpaper.metas.question_type_seq;
        this.isReadOver = this.result.status === 'finished';
        this.resultShow = res.resultShow;
        this.getSubjectList(res.items);
        this.calSubjectHeight();
        // 上报学习进度
        this.reprtData({ eventName: 'doing' });
        this.canDoing(this.result, this.user.id)
          .then(() => {
            this.startTestpaper('KeepDoing');
          })
          .catch(({ answer, endTime }) => {
            this.submitExam(answer, endTime);
          });
      });
      this.getInfo();
    },
    // 初始化上报数据
    initReport() {
      this.initReportData(
        this.selectedPlanId,
        this.$route.query.targetId,
        'testpaper',
      );
    },
    judgeTime() {
      const interval = this.redoInterval;
      if (interval == 0) {
        this.again = true;
        return;
      }

      const intervalTimestamp = parseInt(interval) * 60 * 1000;
      const nowTimestamp = new Date().getTime();
      const checkedTime = parseInt(this.result.checkedTime) * 1000;
      let sumTime = checkedTime + intervalTimestamp;
      this.again = nowTimestamp >= sumTime;

      if (!this.again) {
        sumTime = Math.floor((sumTime - nowTimestamp) / 1000)
        this.timeMeter = setInterval(() => {
          this.remainTime = formatTimeByNumber(sumTime--);

          if (this.sumTime <= 0) {
            this.again = true;
            this.clearTime();
          }
        }, 1000);
      }
    },
    getSubjectList(resData) {
      for (const i in resData) {
        const final = [];
        resData[i].forEach(one => {
          if (i === 'material') {
            one.subs.forEach(item => {
              this.getStatus(item, final);
            });
          } else {
            this.getStatus(one, final);
          }
        });
        this.subjectList[i] = final;
      }
    },
    calSubjectHeight() {
      this.$nextTick(() => {
        const dataHeight =
          this.$refs.data.offsetHeight + this.$refs.tag.offsetHeight + 46;
        const allHeight = document.documentElement.clientHeight;
        const footerHeight = this.$refs.footer.offsetHeight || 0;
        const finalHeight = allHeight - dataHeight - footerHeight;
        this.calHeight = `${finalHeight}px`;
      });
    },
    getStatus(data, arr) {
      const obj = {};
      obj.seq = data.seq;
      obj.score = data.testResult.score;
      if (data.testResult) {
        obj.status = data.testResult.status;
      } else {
        obj.status = 'noAnswer';
      }
      arr.push(obj);
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
          this.$router.replace({
            name: 'testpaperResult',
            query: {
              resultId: this.$route.query.resultId,
              testId: this.$route.query.testId,
              targetId: this.$route.query.targetId,
              courseId: this.courseId
            },
          });
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    clearTime() {
      clearInterval(this.timeMeter);
      this.timeMeter = null;
    },
    startTestpaper() {
      if (this.enable_facein === 1) {
        Dialog.alert({
          title: '',
          confirmButtonText: '知道了',
          message:
            '本场考试已开启云监考，暂不支持在移动端答题，请前往PC端进行答题。',
        }).then(() => {});
      } else {
        this.goDoTestpaper();
      }
    },
    goDoTestpaper() {
      this.$router.push({
        name: 'testpaperIntro',
        query: {
          testId: this.result.testId,
          targetId: this.reportData.taskId,
        },
      });
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
      }).then(res => {
        this.courseId = res.testpaperResult.courseId
        const { canDoAgain  } = res.task.activity.testpaperInfo;

        this.testpaperTitle = res.task.title;
        this.setNavbarTitle(res.task.title);
        this.redoInterval = Number(
          res.task.activity.testpaperInfo.redoInterval,
        );
        this.enable_facein = res.task.enable_facein;
        this.canDoAgain = canDoAgain === '1';
        this.judgeTime();
      });
    },
    // 查看解析
    viewAnalysis() {
      this.$router.push({
        name: 'testpaperAnalysis',
        query: {
          resultId: this.$route.query.resultId,
          title: this.testpaperTitle,
          targetId: this.$route.query.targetId,
        },
      });
    },
  },
};
</script>

<style lang="scss">
.testpaper-result {
  margin: 8px 16px;
  border-radius: 8px;
  background-color: #fff;

  .testpaper-result__header {
    display: flex;
    border-bottom: solid 1px #fafafa;

    > div {
      display: flex;
      align-items: center;
      justify-content: center;
      flex: 1;
      height: 40px;
      font-weight: 400;
      font-size: 14px;
      color: rgba(0, 0, 0, 0.35);
    }
  }

  .testpaper-result__content {
    .trc-item {
      display: flex;

      > div {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 48px;
      }

      > div:not(:last-child) {
        border-bottom: solid 1px #fafafa;
      }

      .question-type {
        font-weight: 400;
        font-size: 16px;
        color: rgba(0, 0, 0, 0.55);
      }

      .question-answer {
        font-weight: 400;
        font-size: 16px;
        color: rgba(0, 0, 0, 0.85);

        .right-number {
          color: #00BE63;
        }
      }

      .question-total {
        font-weight: 400;
        font-size: 16px;
        color: rgba(0, 0, 0, 0.85);
      }
    }
  }
}

.teacher-say {
  margin: 8px 16px;
  padding: 16px;
  font-weight: 400;
  font-size: 14px;
  color: #666666;
  background-color: #fff;
  border-radius: 8px;
}

.testpaper-result-status {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  height: 121px;
  margin: 16px 16px 0;
  color: #fff;
  border-radius: 8px 8px 0px 0px;

  > img {
    width: 100%;
    height: 100%;
  }

  .trs-content {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
  }

  .result-score {
    display: flex;
    align-items: center;

    .data-number {
      font-weight: 600;
      font-size: 36px;
    }

    .data-unit {
      margin-left: 4px;
      font-weight: 400;
      font-size: 12px;
    }
  }

  .result-score-tips {
    margin-top: 12px;
    font-weight: 500;
    font-size: 16px;
    color: #fff;
  }

}

.testpaper-result-data {
  position: relative;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 0;
  margin: -7px 16px 0;
  background-color: #fff;
  border-radius: 8px;

  &__item {
    text-align: center;
    padding: 0 20px;

    .trd-header {
      margin-bottom: 8px;
      font-weight: 400;
      font-size: 14px;
      line-height: 22px;
      color: #999;
    }

    .data-number {
      font-weight: 600;
      font-size: 22px;
      line-height: 30px;
      color: #333;
    }

    .data-unit {
      margin-left: 4px;
      font-weight: 400;
      font-size: 14px;
      line-height: 22px;
      color: #333;
    }
  }

  &__item:not(:last-child) {
    position: relative;

    &::after {
      content: '';
      position: absolute;
      right: 0;
      top: 14px;
      width: 1px;
      height: 32px;
      background: #f1f1f1;
    }
  }
}
</style>

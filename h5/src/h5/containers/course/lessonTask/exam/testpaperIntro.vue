<template>
  <div>
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />
    <div v-if="startTime > Date.now()" class="test-start-count-down">
      <div class="tips">{{ $t('courseLearning.leftTime') }}</div>
      <div class="number">{{ countDown.hours }}</div>
      <div class="unit">{{ $t('courseLearning.hourUnit') }}</div>
      <div class="number">{{ countDown.minutes }}</div>
      <div class="unit">{{ $t('courseLearning.minuteUnit') }}</div>
      <div class="number">{{ countDown.seconds }}</div>
      <div class="unit">{{ $t('courseLearning.secondUnit') }}</div>
    </div>
    <div class="intro-body">
			<div v-if="endTime < Date.now()" class="exam-over">
				{{ $t('courseLearning.examOver') }}
			</div>
      <van-panel class="panel intro-panel" :title="$t('courseLearning.testTips')">
        <van-cell class="intro-cell test-name" :border="false" :title="$t('courseLearning.testName')" :value="testpaperTitle" />

        <van-cell v-if="info.doTimes == '1' && startTime" :class="['intro-panel__content', result || !disabled ? '' : 'intro-tip']"
          class="intro-cell" :border="false" :title="$t('courseLearning.openingTime')" :value="formateStartTime(startTime)" />

        <van-cell :class="['intro-panel__content', result || !disabled ? '' : 'intro-tip']"
          class="intro-cell" :border="false" :title="$t('courseLearning.examinationDuration')"
          :value="limitTime ? `${limitTime} ${$t('courseLearning.minutes')}` : $t('courseLearning.unlimited')" />

        <van-cell class="intro-cell" :border="false" :title="$t('courseLearning.fullScoreOfTestPaper')" :value="score + ' ' + $t('courseLearning.branch')" />

        <van-cell class="intro-cell" :border="false" :title="$t('courseLearning.numberOfRemainingTests')" :value="info.doTimes == '0' ? $t('courseLearning.unlimited') : info.remainderDoTimes + ' ' + $t('courseLearning.times')"   />

				<van-cell v-if="info.startTime == null && info.endTime == null" class="intro-cell" :border="false" :title="$t('courseLearning.validityPeriodOfExamination')" :value="$t('courseLearning.unlimited')" />

				<!-- <van-cell v-if="info.startTime == null && info.endTime == null" class="intro-cell" :border="false" :title="$t('courseLearning.validityPeriodOfExamination')" :value="$t('courseLearning.unlimited')" /> -->

				<van-cell v-if="startTime" :class="['intro-panel__content', result || !disabled ? '' : 'intro-tip']"
          class="intro-cell" :border="false" :title="$t('courseLearning.testStartTime')" :value="formateStartTime(startTime)" />

				<van-cell v-if="startTime" :class="['intro-panel__content', result || !disabled ? '' : 'intro-tip']"
          class="intro-cell" :border="false" :title="$t('courseLearning.examDeadline')" :value="endTime ? formateStartTime(endTime) : $t('courseLearning.unlimitedDuration')" />

        <template #footer>
          <div v-if="info.examMode == '0'" class="testpaper-tips">
            {{ $t('courseLearning.noLimitTips') }}
          </div>
          <!-- <div v-if="info.examMode == '1'" class="testpaper-tips">
            {{ info.doTimes == '0' ? $t('courseLearning.noLimitTips1') : $t('courseLearning.oneTips1') }}
          </div> -->
        </template>
      </van-panel>

      <van-panel class="panel intro-panel">
        <template #header>
          <div class="van-cell van-panel__header">
            <span style="font-size:16px;font-weight:500;color:rgba(0,0,0,0.85)">{{ $t('courseLearning.numberOfTopics') }}</span>
            <span style="margin-left:12px;font-size:14px;font-weight:400;color:rgba(0,0,0,0.35)">{{ sum + ' ' + $t('courseLearning.topic') }}</span>
          </div>
        </template>
        <div class="intro-panel__content">
          <van-cell
            v-for="item in question_type_seq"
            :border="false"
            :key="item"
            :title="$t(obj[item])"
            :value="`${counts[item]} ${$t('courseLearning.topic')}`"
            class="intro-cell"
          />
        </div>
      </van-panel>
    </div>
    <div class="intro-footer">
      <template v-if="result">
        <van-button
          v-if="result.status === 'doing'"
          class="intro-footer__btn"
          type="primary"
          @click="startTestpaper(true, true)"
          >
          {{ $t('courseLearning.continueExam') }}
        </van-button>
        <van-button
          v-else-if="canDoAgain"
          class="intro-footer__btn"
          type="primary"
          @click="startTestpaper(true, true)"
        >{{ $t('courseLearning.startTheExam') }}</van-button>
        <van-button
          v-else
          class="intro-footer__btn"
          type="primary"
          @click="showResult"
          >
          {{ $t('courseLearning.ViewDetail') }}
        </van-button>
      </template>
      <template v-else>
				<van-button v-if="endTime < Date.now()"
					class="intro-footer__btn"
					type="primary"
					@click="startTestpaper()"
					>{{ $t('courseLearning.ViewDetail') }}</van-button
				>
				<van-button v-else
					class="intro-footer__btn"
					type="primary"
					@click="startTestpaper(true,true)"
					>{{ $t('courseLearning.startTheExam') }}</van-button
				>
			</template>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapActions } from 'vuex';
import { Dialog, Toast } from 'vant';
import { formatTime, getCountDown } from '@/utils/date-toolkit.js';
import examMixin from '@/mixins/lessonTask/exam.js';
import report from '@/mixins/course/report';
import OutFocusMask from '@/components/out-focus-mask.vue';
import { clearInterval } from 'timers';

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
			endTime: null, // 考试结束时间
      limitTime: null, // 考试限制时间/分钟
      hasRemainderDoTimes: false, // 是否还剩下考试次数
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
      interval: null,
			canDoAgain:'',
			courseId: '',
      countDown: {
        hours: '00',
        minutes: '00',
        seconds: '00'
      },
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
      if (this.info.doTimes == '0') return false;

      return this.startTime > Date.now();
    },
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user,
      selectedPlanId: state => state.course.selectedPlanId,
    }),
  },
	created(){
    this.getInfo();
	},
  mounted() {
    this.initReport();
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },
  beforeRouteLeave(to, from, next) {
    try {
      this.interval && clearInterval(this.interval);
    } catch(e) {}

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
          this.testpaperTitle = res.testpaper.name;
          this.testpaper = res.testpaper;
					this.courseId = res.task.activity.fromCourseId
          this.result = res.testpaperResult;
          this.info = res.task.activity.testpaperInfo;
          this.enable_facein = res.task.enable_facein;
          this.canDoAgain = this.info.canDoAgain === '1';
          this.score = this.testpaper.score;
          this.startTime = parseInt(this.info.startTime) * 1000;
          this.endTime = parseInt(this.info.endTime) * 1000;
          this.limitTime = parseInt(this.info.limitTime);
          this.question_type_seq = this.testpaper.metas.question_type_seq;

          if (this.startTime > Date.now()) {
            this.startCountDown()
          }
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
        courseId: this.courseId
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
    startTestpaper(KeepDoing, reDo) {
			if(this.startTime > Date.now()) {
			 	Toast.fail(this.$t('courseLearning.examNotStart'))
				return
			}

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
          if (res.testpaperResult?.status === 'doing') {
            this.goDoTestpaper(KeepDoing, reDo);
            return;
          }
          this.testId = res.testpaper.id;
          this.canDoAgain = res.task.activity.testpaperInfo.canDoAgain === '1';
					if(this.canDoAgain){
						if (this.enable_facein === 1) {
							Dialog.alert({
								title: '',
								confirmButtonText: this.$t('courseLearning.iKnow'),
								message:
									'本场考试已开启云监考，暂不支持在移动端答题，请前往PC端进行答题。',
							}).then(() => {});
						} else {
							this.goDoTestpaper(KeepDoing, reDo);
						}
					}else {
						Dialog.alert({
							message: this.$t('courseLearning.examOverExam'),
						}).then(() => {
							this.$router.push(`/course/${this.courseId}`)
						});
					}

          if (this.startTime > Date.now()) {
            this.startCountDown()
          }
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    goDoTestpaper(KeepDoing, reDo) {
      this.$router.push({
        name: 'testpaperDo',
        query: {
          testId: this.testId,
          targetId: this.targetId,
          title: this.testpaperTitle,
          action: reDo ? 'redo' : 'do',
          courseId: this.courseId
        },
        params: {
          KeepDoing,
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
    startCountDown() {
      this.interval = setInterval(() => {
        this.countDown = getCountDown(this.startTime - Date.now(), 0)
      }, 1000)
    },
    formateStartTime(startTime) {
      startTime = formatTime(new Date(startTime));
      return startTime;
    },
  },
};
</script>

<style lang="scss">
.test-name {
  .van-cell__title {
    max-width: 70px;
    margin-right: 12px;
  }

  .van-cell__value {
    overflow: hidden;
    -webkit-line-clamp: 1;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
  }
}

.test-start-count-down {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 16px 16px 8px;
  padding: 8px 12px;
  height: 40px;
  background-color: #fff;
  border-radius: 8px;
  .number {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 24px;
    padding: 0 4px;
    font-weight: 500;
    font-size: 14px;
    color: #FF7D00;
    background: rgba(255, 125, 0, 0.04);
    border-radius: 2px;
  }
  .unit {
    padding: 0 6px;
    font-weight: 400;
    font-size: 16px;
    line-height: 24px;
    color: #666666;
  }
}
</style>

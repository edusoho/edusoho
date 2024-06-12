<template>
  <div>
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />
    <div v-if="exercise" class="intro-body">
      <van-cell-group class="intro-panel">
        <van-cell
          class="intro-cell test-name"
          :border="false"
          :title="$t('courseLearning.exerciseName')"
          :value="exercise.name"
        />
      </van-cell-group>
      <van-panel
        class="panel intro-panel"
        :title="$t('courseLearning.numberOfTopics')"
      >
        <template #header>
          <div class="van-cell van-panel__header">
            <span class="font-medium text-16"
              style="color:rgba(0,0,0,0.85)"
              >{{ $t('courseLearning.numberOfTopics') }}</span
            >
            <span class="ml-12 font-normal text-14 leading-6"
              style="color:rgba(0,0,0,0.35)"
              >{{ exercise.itemCount + ' ' + $t('courseLearning.topic') }}</span
            >
          </div>
        </template>
        <van-cell
          v-for="item in question_type_seq"
          :border="false"
          :key="item"
          :title="$t(obj[item])"
          :value="`${counts[item]} ${$t('courseLearning.topic')}`"
          class="intro-cell"
        />
      </van-panel>
    </div>
    <div v-if="exercise" class="intro-footer">
      <van-button
        class="intro-footer__btn"
        type="primary"
        @click="chooseQuestionMode = true"
        >{{ $t('courseLearning.chooseQuestionAnsweringMode') }}</van-button
      >
    </div>
    <!-- 选择模式弹框 -->
    <van-popup
      v-model="chooseQuestionMode"
      position="bottom"
      closeable
      round
      safe-area-inset-bottom
      :style="{ height: '44%' }"
      class="choose-mode-popup"
    >
      <div class="choose-mode-title">
        {{ $t('courseLearning.answerMode') }}
      </div>
      <div class="choose-mode-change-radio">
        <van-radio-group v-model="radio" class="choose-mode-group-radio">
          <van-radio name="0" class="choose-mode-radio">
            {{ $t('courseLearning.testMode') }}
            <template #icon="props">
              <img
                class="img-icon"
                :src="props.checked ? activeIcon : defaultIcon"
              />
              <i v-show="props.checked" class="iconfont icon-check"></i>
            </template>
          </van-radio>
          <van-radio name="1" class="choose-mode-radio">
            {{ $t('courseLearning.answerOneQuestionAtTime') }}
            <template #icon="props">
              <img
                class="img-icon"
                :src="props.checked ? activeQuestions : defaultQuestions"
              />
              <i v-show="props.checked" class="iconfont icon-check"></i>
            </template>
          </van-radio>
        </van-radio-group>
      </div>
      <van-button
        class="choose-mode__btn"
        type="primary"
        @click="startExercise()"
        >{{ $t('courseLearning.startAnsweringQuestions') }}</van-button
      >
    </van-popup>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapActions } from 'vuex';
import { Toast } from 'vant';
import exerciseMixin from '@/mixins/lessonTask/exercise.js';
import report from '@/mixins/course/report';
import OutFocusMask from '@/components/out-focus-mask.vue';

export default {
  name: 'ExerciseIntro',
  mixins: [exerciseMixin, report],
  components: {
    OutFocusMask,
  },
  data() {
    return {
      courseId: null,
      taskId: null,
      exercise: null,
      chooseQuestionMode: false,
      question_type_seq: [], // 试卷已有题型
      counts: {}, // 考试题型数量对象
      radio: '0',
      activeIcon: 'static/images/exercise/active-icon.png',
      defaultIcon: 'static/images/exercise/default-icon.png',
      activeQuestions: 'static/images/exercise/active-on-questions.png',
      defaultQuestions: 'static/images/exercise/default-on-questions.png',
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
    hasResult() {
      const latestExerciseResult = this.exercise.latestExerciseResult;
      return !!latestExerciseResult;
    },
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user,
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
    ...mapActions('course', ['handExercisedo']),
    getInfo() {
      this.courseId = this.$route.query.courseId;
      this.taskId = this.$route.query.taskId;
      Api.getExerciseIntro({
        query: {
          courseId: this.courseId,
          taskId: this.taskId,
        },
      }).then(res => {
        this.exercise = res.exercise;
        this.counts = res.exercise.itemCounts;

        const { lastExerciseResult, latestExerciseResult } = this.exercise;

        if (!latestExerciseResult && lastExerciseResult) {
          this.formatItem();

          if (this.$route.query.answerAgain) return;

          this.$router.replace({
            name: 'exerciseResult',
            query: {
              exerciseId: this.exercise.id,
              exerciseResultId: lastExerciseResult.id,
              courseId: this.courseId,
              taskId: this.taskId,
            },
          });

          return;
        }
        this.formatItem();
        this.interruption();
      })
      .catch(err => {
        Toast.fail(err.message);
      });
    },

    // 遍历出题目类型
    formatItem() {
      for (const i in this.counts) {
        if (this.counts[i] > 0) {
          this.question_type_seq.push(i);
        }
      }
    },

    // 初始化上报数据
    initReport() {
      this.initReportData(
        this.$route.query.courseId,
        this.$route.query.taskId,
        'exercise',
      );
    },
    // 异常中断
    interruption() {
      this.canDoing(this.exercise.latestExerciseResult, this.user.id)
        .then(() => {
          this.startExercise();
        })
        .catch(({ answer }) => {
          this.submitExercise(answer);
        });
    },
    // 跳转到结果页
    showResult() {
      this.$router.push({
        name: 'exerciseResult',
        query: {
          exerciseId: this.exercise.id,
          exerciseResultId: this.exercise.latestExerciseResult.id,
          courseId: this.courseId,
          taskId: this.taskId,
        },
      });
    },
    // 开始作业
    startExercise() {
      this.$router.push({
        name: 'exerciseDo',
        query: {
          targetId: this.taskId,
          exerciseId: this.exercise.id,
          courseId: this.courseId,
          exerciseMode: this.radio,
        },
        params: {
          KeepDoing: true,
        },
      });
    },
    // 交练习
    submitExercise(answer) {
      const datas = {
        answer,
        exerciseId: this.exercise.id,
        userId: this.user.id,
        exerciseResultId: this.exercise.latestExerciseResult.id,
        courseId: this.courseId,
      };
      // 提交练习+跳转到结果页
      this.handExercisedo(datas)
        .then(res => {
          // 上报完成作业课时
          this.reprtData({ eventName: 'finish' });
          this.showResult();
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.test-name {
  .van-cell__title {
    max-width: vw(70);
    margin-right: vw(12);
  }

  .van-cell__value {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
.choose-mode-group-radio {
  ::v-deep .van-radio__icon {
    position: relative;
    margin-bottom: vw(8);
    height: vw(64);
  }

  ::v-deep .van-radio__label {
    margin-left: 0;
  }
  .img-icon {
    display: block;
    width: vw(64);
    height: vw(64);
  }

  .icon-check {
    position: absolute;
    left: vw(25);
    bottom: vw(-10);
    font-size: vw(14);
    color: #00be63;
  }

  .choose-mode-radio {
    display: flex;
    flex-direction: column;

    &:nth-child(1) {
      margin-right: vw(64);
    }
  }
}
</style>

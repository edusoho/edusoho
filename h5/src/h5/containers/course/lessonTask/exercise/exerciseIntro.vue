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
      <van-panel class="panel intro-panel" :title="$t('courseLearning.exerciseName')">
        <div class="intro-panel__content intro-panel__content--title">
          {{ exercise.name }}
        </div>
      </van-panel>
      <van-panel class="panel intro-panel" :title="$t('courseLearning.numberOfTopics')">
        <div class="intro-panel__content">{{ $t('courseLearning.total') }} {{ exercise.itemCount }} {{ $t('courseLearning.topic') }}</div>
      </van-panel>
    </div>
    <div v-if="exercise" class="intro-footer">
      <van-button
        v-if="hasResult"
        class="intro-footer__btn"
        type="primary"
        @click="showResult"
        >{{ $t('courseLearning.viewResult2') }}</van-button
      >
      <van-button
        v-else
        class="intro-footer__btn"
        type="primary"
        @click="startExercise()"
        >{{ $t('courseLearning.startAnsweringQuestions') }}</van-button
      >
    </div>
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
        this.interruption();
      });
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

<style></style>

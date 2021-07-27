<template>
  <div>
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />
    <div v-if="homework" class="intro-body">
      <van-panel class="panel intro-panel" :title="$t('courseLearning.jobName')">
        <div class="intro-panel__content intro-panel__content--title">
          {{ homework.name }}
        </div>
      </van-panel>
      <van-panel class="panel intro-panel" :title="$t('courseLearning.jobDescription')">
        <div class="intro-panel__content" v-html="homework.description" />
      </van-panel>
    </div>
    <div v-if="homework" class="intro-footer">
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
        @click="startHomework()"
        >{{ $t('courseLearning.startAnsweringQuestions') }}</van-button
      >
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapActions } from 'vuex';
import { Toast } from 'vant';

import homeworkMixin from '@/mixins/lessonTask/homework.js';
import report from '@/mixins/course/report';
import OutFocusMask from '@/components/out-focus-mask.vue';

export default {
  name: 'HomeworkIntro',
  mixins: [homeworkMixin, report],
  components: {
    OutFocusMask,
  },
  data() {
    return {
      courseId: null,
      taskId: null,
      homework: null,
    };
  },
  computed: {
    hasResult() {
      const latestHomeworkResult = this.homework.latestHomeworkResult;
      return !!latestHomeworkResult;
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
    ...mapActions('course', ['handHomeworkdo']),
    getInfo() {
      this.courseId = this.$route.query.courseId;
      this.taskId = this.$route.query.taskId;
      Api.getHomeworkIntro({
        query: {
          courseId: this.courseId,
          taskId: this.taskId,
        },
      }).then(res => {
        this.homework = res.homework;

        this.interruption();
      });
    },
    // 初始化上报数据
    initReport() {
      this.initReportData(
        this.$route.query.courseId,
        this.$route.query.taskId,
        'homework',
      );
    },
    // 异常中断
    interruption() {
      this.canDoing(this.homework.latestHomeworkResult, this.user.id)
        .then(() => {
          this.startHomework();
        })
        .catch(({ answer }) => {
          this.submitHomework(answer);
        });
    },
    // 跳转到结果页
    showResult() {
      this.$router.push({
        name: 'homeworkResult',
        query: {
          homeworkId: this.homework.id,
          homeworkResultId: this.homework.latestHomeworkResult.id,
          courseId: this.$route.query.courseId,
          taskId: this.taskId,
        },
      });
    },
    // 开始作业
    startHomework() {
      this.$router.push({
        name: 'homeworkDo',
        query: {
          targetId: this.taskId,
          homeworkId: this.homework.id,
          courseId: this.$route.query.courseId,
        },
        params: {
          KeepDoing: true,
        },
      });
    },
    // 交作业
    submitHomework(answer) {
      const datas = {
        answer,
        homeworkId: this.homework.id,
        userId: this.user.id,
        homeworkResultId: this.homework.latestHomeworkResult.id,
      };
      // 提交作业+跳转到结果页
      this.handHomeworkdo(datas)
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

<template>
  <div>
    <e-loading v-if="isLoading" />
    <div v-else class="ibs-wap-vue">
      <item-engine
        ref="itemEngine"
        :answerRecord="answerRecord"
        :assessmentResponse="assessmentResponse"
        :assessment="assessment"
        :answerScene="answerScene"
        @reachTimeSubmitAnswerData="reachTimeSubmitAnswerData"
        @saveAnswerData="saveAnswerData"
        @getAnswerData="getAnswerData"
        @timeSaveAnswerData="timeSaveAnswerData"
      ></item-engine>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import * as types from '@/store/mutation-types.js';
import { Toast } from 'vant';
const config = {
  assessment: {
    api: 'getAssessmentExerciseRecord',
  },
  chapter: {
    api: 'getChapterExerciseRecord',
  },
};
export default {
  components: {},
  data() {
    return {
      isLoading: true,
      assessment: {},
      answerScene: {},
      answerRecord: {},
      assessmentResponse: {},
      canLeave: false,
    };
  },
  computed: {},
  watch: {},
  created() {
    const mode = this.$route.query.mode;
    mode === 'start' ? this.getStart() : this.getContinue();
  },
  mounted() {},
  // beforeRouteEnter(to, from, next) {
  //   // 通过链接进来
  //   if (from.fullPath === '/') {
  //     backUrl = '/'
  //   } else {
  //     backUrl = ''
  //   }
  //   next()
  // },
  beforeRouteLeave(to, from, next) {
    // 可捕捉离开提醒
    if (this.canLeave) {
      next();
    } else {
      this.$refs.itemEngine.submitPaper(true);
    }
  },
  methods: {
    getContinue() {
      this.isLoading = true;
      const data = { answer_record_id: this.$route.query.answer_record_id };
      Api.continueAnswer({ data })
        .then(res => {
          this.assignData(res);
          this.isLoading = false;
        })
        .catch(err => {
          this.$toast(err.message);
          console.log(err);
        });
    },
    getStart() {
      this.isLoading = true;
      const type = this.$route.query.type;
      const query = { exerciseId: this.$route.query.exerciseId };
      const data = { moduleId: this.$route.query.moduleId };
      if (type === 'assessment') {
        data.assessmentId = this.$route.query.assessmentId;
      } else {
        data.categoryId = this.$route.query.categoryId;
      }
      Api[config[type].api]({ query, data }).then(res => {
        this.isLoading = false;
        this.assignData(res);
      });
    },
    assignData(res) {
      this.$store.commit(types.SET_NAVBAR_TITLE, this.$route.query.title);
      this.assessment = res.assessment;
      this.answerScene = res.answer_scene;
      this.answerRecord = res.answer_record;
      this.assessmentResponse = res.assessment_response;
    },
    reachTimeSubmitAnswerData(data) {
      Api.submitAnswer({ data })
        .then(res => {
          this.canLeave = true;
          this.goResult();
          console.log(res);
        })
        .catch(err => {
          this.$toast(err.message);
        });
    },
    saveAnswerData(data) {
      Toast.loading({
        message: '保存中...',
        forbidClick: true,
      });
      Api.saveAnswer({ data })
        .then(res => {
          Toast.clear();
          this.canLeave = true;
          const exerciseId = this.$route.query.exerciseId;
          this.$router.replace({
            path: `/item_bank_exercise/${exerciseId}`,
          });
        })
        .catch(err => {
          Toast.clear();
          this.$toast(err.message);
        });
    },
    getAnswerData(data) {
      Toast.loading({
        message: '提交中...',
        forbidClick: true,
      });
      Api.submitAnswer({ data })
        .then(res => {
          Toast.clear();
          this.canLeave = true;
          this.goResult();
        })
        .catch(err => {
          Toast.clear();
          this.$toast(err.message);
        });
    },
    timeSaveAnswerData(data) {
      Api.saveAnswer({ data })
        .then(res => {
          console.log(res);
        })
        .catch(err => {
          console.log(err);
        });
    },
    goResult() {
      const query = {
        title: this.$route.query.title,
        type: this.$route.query.type,
        exerciseId: this.$route.query.exerciseId,
        assessmentId: this.$route.query.assessmentId,
        moduleId: this.$route.query.moduleId,
        categoryId: this.$route.query.categoryId,
        backUrl: `/item_bank_exercise/${this.$route.query.exerciseId}`,
      };
      const answerRecordId = this.assessmentResponse.answer_record_id;
      this.$router.replace({
        path: `/brushResult/${answerRecordId}`,
        query,
      });
    },
  },
};
</script>

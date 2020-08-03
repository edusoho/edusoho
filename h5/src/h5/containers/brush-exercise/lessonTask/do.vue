<template>
  <div>
    <e-loading v-if="isLoading" />
    <div v-else class="ibs-wap-vue">
      <item-engine
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
    };
  },
  computed: {},
  watch: {},
  created() {
    const mode = this.$route.query.mode;
    mode === 'start' ? this.getStart() : this.getContinue();
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
        this.assignData(res);
        this.isLoading = false;
      });
    },
    assignData(res) {
      this.$store.commit(types.SET_NAVBAR_TITLE, res.assessment.name);
      this.assessment = res.assessment;
      this.answerScene = res.answer_scene;
      this.answerRecord = res.answer_record;
      this.assessmentResponse = res.assessment_response;
    },
    reachTimeSubmitAnswerData(data) {
      console.log('自动提交', data);
      Api.submitAnswer({ data })
        .then(res => {
          console.log(res);
        })
        .catch(err => {
          console.log(err);
        });
    },
    saveAnswerData(data) {
      console.log('手动保存进度', data);
      Api.saveAnswer({ data })
        .then(res => {
          this.$route.go(-1);
        })
        .catch(err => {
          console.log(err);
        });
    },
    getAnswerData(data) {
      console.log('手动提交', data);
      Api.submitAnswer({ data })
        .then(res => {
          console.log(res);
        })
        .catch(err => {
          console.log(err);
        });
    },
    timeSaveAnswerData(data) {
      console.log('三分钟保存进度', data);
      Api.saveAnswer({ data })
        .then(res => {
          console.log(res);
        })
        .catch(err => {
          console.log(err);
        });
    },
  },
};
</script>

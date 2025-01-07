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
        :exerciseId="exerciseId"
        :exerciseInfo="exerciseInfo"
        @saveAnswerData="saveAnswerData"
        @getAnswerData="getAnswerData"
        @timeSaveAnswerData="timeSaveAnswerData"
      ></item-engine>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';
import * as types from '@/store/mutation-types.js';
import { Dialog, Toast } from 'vant';
import isAuthorized from '@/mixins/isAuthorized';
import itemEngine from '@/src/components/item-engine/src/item-engine.vue';
const config = {
  assessment: {
    api: 'getAssessmentExerciseRecord',
  },
  chapter: {
    api: 'getChapterExerciseRecord',
  },
};

export default {
  mixins: [isAuthorized],
  components: {itemEngine},
  data() {
    return {
      exerciseId: '',
      isLoading: true,
      assessment: {},
      answerScene: {},
      answerRecord: {},
      assessmentResponse: {},
      canLeave: false,
      resources: [{ id: '1' }, { id: '2' }],
      exerciseModes: this.$route.query.exerciseMode,
      status: '',
      reviewedCount: 0,
      recordId: '',
      backUrl: '',
      type: 'lessonTask',
      exerciseInfo: []
    };
  },
  computed: {
    ...mapState({
      storageSetting: state => state.storageSetting
    }),
  },
  watch: {},
  created() {
    const mode = this.$route.query.mode;

    if (mode === 'start' && !localStorage.getItem('exerciseId_'+this.$route.query.exerciseId)) {
      this.getStart()
    } else {
      this.getContinue()
    }
  },
  provide() {
    return {
      getResourceToken: this.getResourceToken,
      settings: this.storageSetting,
      brushDo:this
    }
  },
  mounted() {
    this.exerciseId = this.$route.query.exerciseId
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';
    if (this.canLeave || to.query.isLeave) {
      next();
    } else {
      this.$refs.itemEngine.submitPaper(true)
    }
  },
  methods: {
    getContinue() {
      this.isLoading = true;
      const data = { answer_record_id: this.$route.query.answer_record_id ? this.$route.query.answer_record_id : localStorage.getItem('exerciseId_'+this.$route.query.exerciseId) };
      Api.continueAnswer({ data })
        .then(res => {
          this.recordId = res.answer_record.id
          this.reviewedCount = res.reviewedCount
          this.exerciseModes = res.answer_record.exercise_mode;
          this.status = res.answer_record.status;
          this.assignData(res);
          this.isLoading = false;
          this.exerciseInfo = res.submittedQuestions
        })
        .catch(err => {
          this.handleError(err);
        });
    },
    getResourceToken(globalId) {
      return Api.getItemDetail({
        params: { globalId }
      })
    },
    getStart() {
      this.isLoading = true;
      const type = this.$route.query.type;
      const query = { exerciseId: this.$route.query.exerciseId };
      const data = { moduleId: this.$route.query.moduleId ,exerciseMode: this.exerciseModes};
      if (type === 'assessment') {
        data.assessmentId = this.$route.query.assessmentId;
      } else {
        data.categoryId = this.$route.query.categoryId;
      }
      Api[config[type].api]({ query, data })
        .then(res => {
          this.recordId = res.answer_record.id
          this.exerciseModes = res.answer_record.exercise_mode
          this.status = res.answer_record.status;
          this.isLoading = false;
          this.assignData(res);
          localStorage.setItem('exerciseId_'+this.$route.query.exerciseId,res.answer_record.id)
        })
        .catch(err => {
          this.handleError(err);
        });
    },
    handleError(err) {
      this.canLeave = true;
      this.isLoading = false;
      if (err.code === 4004003) {
        Dialog.alert({
          title: '试卷已关闭',
          confirmButtonText: '确定',
          confirmButtonColor: '#00BE63',
        }).then(() => {
          const query = {
            targetId: this.$route.query.exerciseId,
            moduleId: this.$route.query.moduleId,
            type: 'item_bank_exercise',
          };
          this.$router.push({ path: `/item_bank_exercise/${this.$route.query.exerciseId}`, query });
        });
      } else {
        this.isAuthorized(err);
      }
    },
    assignData(res) {
      this.$store.commit(types.SET_NAVBAR_TITLE, this.$route.query.title);
      this.assessment = res.assessment;
      this.answerScene = res.answer_scene;
      this.answerRecord = res.answer_record;
      this.assessmentResponse = res.assessment_response;
    },
    saveAnswerData(data) {
      Toast.loading({
        message: '保存中...',
        forbidClick: true,
      });
      data.admission_ticket = this.answerRecord.admission_ticket;
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

          if (err.code == 50095211) {
            Dialog.confirm({
              title: '您已退出题库，无法继续学习',
              showCancelButton: false,
              confirmButtonText: '点击刷新'
            }).then(() => this.exitPage())
            return
          }

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

          if (err.code == 50095211) {
            Dialog.confirm({
              title: '您已退出题库，无法继续学习',
              showCancelButton: false,
              confirmButtonText: '点击刷新'
            }).then(() => this.exitPage())
            return
          }

          this.$toast(err.message);
        });
    },
    timeSaveAnswerData(data) {
      data.admission_ticket = this.answerRecord.admission_ticket;
      Api.saveAnswer({ data })
        .then(res => {
          console.log(res);
        })
        .catch(err => {
          if (err.code == 50095211) {
            Dialog.confirm({
              title: '您已退出题库，无法继续学习',
              showCancelButton: false,
              confirmButtonText: '点击刷新'
            }).then(() => this.exitPage())
            return
          }

          this.$toast(err.message);
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
        backUrl: `/item_bank_exercise/${this.$route.query.exerciseId}?categoryId=${this.$route.query.categoryId}`,
      };
      const answerRecordId = this.assessmentResponse.answer_record_id;
      this.$router.replace({
        path: `/brushResult/${answerRecordId}`,
        query,
      });
    },
    exitPage() {
      this.canLeave = true
      this.$router.replace(`/my/courses/learning?active=2`)
    }
  },
  destroyed(){
    localStorage.removeItem('exerciseId_'+this.$route.query.exerciseId)
  },
};
</script>

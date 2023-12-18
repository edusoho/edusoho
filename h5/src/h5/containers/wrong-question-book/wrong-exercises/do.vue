<template>
  <div>
    <e-loading v-if="isLoading" />
    <div v-else class="ibs-wap-vue">
      <item-engine
        ref="itemEngine"
        :wrong="true"
        :answerRecord="answerRecord"
        :assessmentResponse="assessmentResponse"
        :assessment="assessment"
        :answerScene="answerScene"
        :show-save-process-btn="false"
        @getAnswerData="getAnswerData"
      ></item-engine>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import { mapState } from 'vuex';
import { Toast } from 'vant';
import itemEngine from '@/src/components/item-engine/src/item-engine.vue';

export default {
  name: 'WrongQuestionDo',
  components: {
    itemEngine
  },
  data() {
    return {
      poolId: this.$route.query.id,
      isLoading: false,
      assessment: {},
      answerScene: {},
      answerRecord: {},
      assessmentResponse: {},
      status: '',
      reviewedCount: 0,
      recordId: '',
      exerciseModes: this.$route.query.exerciseMode,
      type: 'wrongQuestionBook',
    };
  },

  computed: {
    ...mapState({
      storageSetting: state => state.storageSetting
    }),
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';
    next();
  },
  provide() {
    return {
      getResourceToken: this.getResourceToken,
      settings: this.storageSetting,
      brushDo: this
    }
  },

  created() {
    this.fetchQuestion();
  },

  methods: {
    fetchQuestion() {
      this.isLoading = true;
      const data = _.assign({}, this.$route.query);
      delete data.id;
      Api.getWrongQuestionStartAnswer({
        query: {
          poolId: this.poolId,
        },
        data
      }).then(res => {
        const {
          assessment,
          assessment_response,
          answer_scene,
          answer_record,
        } = res;
        _.assign(this, {
          assessment,
          answerScene: answer_scene,
          answerRecord: answer_record,
          assessmentResponse: assessment_response,
          status: answer_record.status
        });
        this.recordId = answer_record.id
        this.isLoading = false;
      });
    },

    getAnswerData(data) {
      Toast.loading({
        message: this.$t('wrongQuestion.submitting'),
        forbidClick: true,
      });
      Api.submitWrongQuestionAnswer({
        query: {
          poolId: this.poolId,
          recordId: this.answerRecord.id,
        },
        data,
      })
        .then(res => {
          Toast.clear();
          this.goResult();
        })
        .catch(err => {
          Toast.clear();
          this.$toast(err.message);
        });
    },

    goResult() {
      this.$router.replace({
        name: 'WrongExercisesResult',
        query: {
          recordId: this.answerRecord.id,
        },
      });
    },

    getResourceToken(globalId) {
      return Api.getItemDetail({ 
        params: { globalId } 
      })
    },
  },
};
</script>

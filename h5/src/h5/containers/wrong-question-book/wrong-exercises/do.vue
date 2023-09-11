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
    };
  },

  computed: {
    ...mapState({
      storageSetting: state => state.storageSetting
    }),
  },

  provide() {
    return {
      getResourceToken: this.getResourceToken,
      settings: this.storageSetting
    }
  },

  created() {
    this.fetchQuestion();
  },

  methods: {
    fetchQuestion() {
      this.isLoading = true;
      const params = _.assign({}, this.$route.query);
      delete params.id;
      Api.getWrongQuestionStartAnswer({
        query: {
          poolId: this.poolId,
        },
        params,
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
        });
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

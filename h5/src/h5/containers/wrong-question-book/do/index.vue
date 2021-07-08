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
import { Toast } from 'vant';

export default {
  name: 'WrongQuestionDo',

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
        data: params,
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
        message: '提交中...',
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
      this.$router.push({
        name: 'WrongQuestionResult',
        query: {
          recordId: this.answerRecord.id,
        },
      });
    },
  },
};
</script>

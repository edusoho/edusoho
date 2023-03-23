<template>
  <div class="brush-exercise-report">
    <e-loading v-if="isLoading" />
    <template v-else>
      <div class="ibs-wap-vue">
        <item-report
          :answerRecord="answerRecord"
          :answerReport="answerReport"
          :assessment="assessment"
          :answerScene="answerScene"
          :assessmentResponse="assessmentResponse"
        ></item-report>
      </div>
    </template>
  </div>
</template>

<script>
import Api from '@/api';
import * as types from '@/store/mutation-types.js';
export default {
  components: {},
  data() {
    return {
      isLoading: true,
      assessment: {},
      answerScene: {},
      answerReport: {},
      answerRecord: {},
      assessmentResponse: {}
    };
  },
  computed: {},
  watch: {},
  created() {
    this.getData();
  },
  provide() {
    return {
      getResourceToken: this.getResourceToken
    }
  },
  methods: {
    getResourceToken(globalId) {
      return Api.getItemDetail({ 
        params: { globalId } 
      })
    },
    getData() {
      const query = {
        answerRecordId: Number(this.$route.params.answerRecordId),
      };
      Api.answerRecord({
        query,
      })
        .then(res => {
          this.$store.commit(types.SET_NAVBAR_TITLE, this.$route.query.title);
          this.assessment = res.assessment;
          this.answerScene = res.answer_scene;
          this.answerReport = res.answer_report;
          this.answerRecord = res.answer_record;
          this.assessmentResponse = res.assessment_response;
          this.isLoading = false;
        })
        .catch(err => {
          this.isLoading = false;
          this.$toast(err.message);
        });
    },
  },
};
</script>

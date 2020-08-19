<template>
  <div class="item-bank-review ibs-wap-vue">
    <e-loading v-if="isLoading" />
    <item-review
      v-else
      :answerRecord="answerRecord"
      :answerReport="answerReport"
      :assessment="assessment"
      :answerScene="answerScene"
      @getReviewData="getReviewData"
    ></item-review>
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
    };
  },
  computed: {},
  watch: {},
  created() {
    this.getData();
  },
  methods: {
    getData() {
      const query = {
        answerRecordId: Number(this.$route.params.answerRecordId),
      };
      Api.answerRecord({
        query,
      })
        .then(res => {
          this.assessment = res.assessment;
          this.answerScene = res.answer_scene;
          this.answerReport = res.answer_report;
          this.answerRecord = res.answer_record;
          this.$store.commit(types.SET_NAVBAR_TITLE, this.$route.query.title);
          this.isLoading = false;
        })
        .catch(err => {
          this.$toast(err.message);
        });
    },
    getReviewData(data) {
      const query = { exerciseId: this.$route.query.exerciseId };
      Api.pushItemBankReviewReport({ query, data })
        .then(res => {
          this.goResult(res);
        })
        .catch(err => {
          this.$toast(err.message);
        });
    },
    goResult(res) {
      const query = {
        title: this.$route.query.title,
        type: this.$route.query.type,
        exerciseId: this.$route.query.exerciseId,
        assessmentId: this.$route.query.assessmentId,
        moduleId: this.$route.query.moduleId,
        categoryId: this.$route.query.categoryId,
      };
      const answerRecordId = this.$route.params.answerRecordId;
      this.$router.replace({
        path: `/brushResult/${answerRecordId}`,
        query,
      });
    },
  },
};
</script>

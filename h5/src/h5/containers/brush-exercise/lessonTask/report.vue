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
          this.$store.commit(types.SET_NAVBAR_TITLE, this.$route.query.title);
          this.assessment = res.assessment;
          this.answerScene = res.answer_scene;
          this.answerReport = res.answer_report;
          this.answerRecord = res.answer_record;
          this.isLoading = false;
        })
        .catch(err => {
          this.$toast(err.message);
        });
    },
  },
};
</script>

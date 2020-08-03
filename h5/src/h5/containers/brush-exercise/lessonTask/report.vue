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
      }).then(res => {
        this.assessment = res.assessment;
        this.answerScene = res.answer_scene;
        this.answerReport = res.answer_report;
        this.answerRecord = res.answer_record;
        console.log(res);
        this.isLoading = false;
      });
    },
  },
};
</script>

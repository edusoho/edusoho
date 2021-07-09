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
import _ from 'lodash';
import Api from '@/api';

export default {
  name: 'WrongExercisesAnalysis',

  data() {
    return {
      isLoading: false,
      assessment: {},
      answerScene: {},
      answerReport: {},
      answerRecord: {},
    };
  },

  created() {
    this.fetchData();
  },

  methods: {
    fetchData() {
      this.isLoading = true;

      Api.answerRecord({
        query: {
          answerRecordId: Number(179),
        },
      }).then(res => {
        const { assessment, answer_scene, answer_report, answer_record } = res;
        _.assign(this, {
          assessment,
          answerScene: answer_scene,
          answerRecord: answer_record,
          answerReport: answer_report,
        });
        this.isLoading = false;
      });
    },
  },
};
</script>

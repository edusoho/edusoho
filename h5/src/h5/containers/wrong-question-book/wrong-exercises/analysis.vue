<template>
  <div class="brush-exercise-report">
    <e-loading v-if="isLoading" />
    <template v-else>
      <div class="ibs-wap-vue">
        <item-report
          :wrong="true"
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
import _ from 'lodash';
import Api from '@/api';
import { mapState } from 'vuex';
import itemReport from '@/src/components/item-report/src/item-report.vue';

export default {
  name: 'WrongExercisesAnalysis',
  components:{
    itemReport
  },
  data() {
    return {
      isLoading: false,
      assessment: {},
      answerScene: {},
      answerReport: {},
      answerRecord: {},
      assessmentResponse: {}
    };
  },

  created() {
    this.fetchData();
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';
    next();
  },
  computed: {
    ...mapState({
      storageSetting: state => state.storageSetting
    }),
  },

  provide() {
    return {
      getResourceToken: this.getResourceToken,
      settings: this.storageSetting,
      brushDo: this
    }
  },

  methods: {
    fetchData() {
      this.isLoading = true;

      Api.answerRecord({
        query: {
          answerRecordId: this.$route.query.recordId,
        },
      }).then(res => {
        const { assessment, answer_scene, answer_report, answer_record, assessment_response } = res;
        _.assign(this, {
          assessment,
          answerScene: answer_scene,
          answerRecord: answer_record,
          answerReport: answer_report,
          assessmentResponse: assessment_response
        });
        this.isLoading = false;
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

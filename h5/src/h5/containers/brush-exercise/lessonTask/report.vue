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
import { mapState } from 'vuex';
import * as types from '@/store/mutation-types.js';
import itemReport from "@/src/components/item-report/src/item-report.vue";
export default {
  components: {itemReport},
  data() {
    return {
      isLoading: true,
      assessment: {},
      answerScene: {},
      answerReport: {},
      answerRecord: {},
      assessmentResponse: {},
      exerciseModes: '',
      status: ''
    };
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
  watch: {},
  created() {
    this.getData();
  },
  provide() {
    return {
      getResourceToken: this.getResourceToken,
      settings: this.storageSetting,
      brushDo:this
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
          this.exerciseModes = res.answer_record.exercise_mode;
          this.status = res.answer_record.status;
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

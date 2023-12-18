<template>
  <div class="item-bank-review ibs-wap-vue">
    <e-loading v-if="isLoading" />
    <item-review
      v-else
      :answerRecord="answerRecord"
      :answerReport="answerReport"
      :assessment="assessment"
      :answerScene="answerScene"
      :assessmentResponse="assessmentResponse"
      @getReviewData="getReviewData"
    ></item-review>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';
import * as types from '@/store/mutation-types.js';
import { Toast } from 'vant';
import itemReview from '@/src/components/item-review/src/item-review.vue';
export default {
  components: {itemReview},
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
          this.assessment = res.assessment;
          this.answerScene = res.answer_scene;
          this.answerReport = res.answer_report;
          this.answerRecord = res.answer_record;
          this.assessmentResponse = res.assessment_response;
          this.$store.commit(types.SET_NAVBAR_TITLE, this.$route.query.title);
          this.isLoading = false;
        })
        .catch(err => {
          this.isLoading = false;
          this.$toast(err.message);
        });
    },
    getReviewData(data) {
      Toast.loading({
        message: '提交中...',
        forbidClick: true,
      });
      const query = { exerciseId: this.$route.query.exerciseId };
      Api.pushItemBankReviewReport({ query, data })
        .then(res => {
          Toast.clear();
          this.goResult(res);
        })
        .catch(err => {
          Toast.clear();
          this.$toast(err.message);
        });
    },
    goResult(res) {
      this.$router.go(-1);
    },
  },
};
</script>

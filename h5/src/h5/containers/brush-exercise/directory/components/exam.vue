<template>
  <div class="brush-exercise-directory-exam">
    <template v-if="exercise.length">
      <van-list :finished="finished" finished-text="" @load="onLoad">
        <div
          class="directory-exam-warp"
          v-for="(item, index) in exercise"
          :key="index"
        >
          <div class="exam-left">
            <div class="exam-title">{{ item.assessment.name }}</div>
            <div class="exam-score">
              {{ item.assessment.question_count }}题/{{
                item.assessment.total_score
              }}分
            </div>
          </div>
          <div class="exam-right">
            <div :class="[getBtnText(item).class]" @click="clickBtn(item)">
              {{ getBtnText(item).text }}
            </div>
          </div>
        </div>
      </van-list>
    </template>
    <van-loading v-if="isLoading" color="#1989fa" size="24px" vertical
      >加载中...</van-loading
    >
  </div>
</template>

<script>
import getBtnText from '@/utils/itemBank-status.js';
export default {
  components: {},
  data() {
    return {
      loading: false,
    };
  },
  props: {
    exercise: {
      type: Array,
      default() {
        return [];
      },
    },
    isLoading: {
      type: Boolean,
      default: true,
    },
    finished: {
      type: Boolean,
      default: false,
    },
  },
  computed: {},
  watch: {
    isLoading: {
      handler: 'handleLoad',
    },
  },
  created() {},
  methods: {
    onLoad() {
      this.$emit('loadMore');
    },
    handleLoad(e) {
      this.loading = e;
    },
    getBtnText(item) {
      return getBtnText(item.latestAnswerRecord?.status || '');
    },
    startDo(item) {
      const query = {
        mode: 'start',
        type: 'assessment',
        exerciseId: item.exerciseId,
        assessmentId: item.assessment.id,
        moduleId: item.moduleId,
      };
      this.$router.push({ path: '/brushDo', query });
    },
    continueDo(item) {
      const query = {
        mode: 'continue',
        answer_record_id: item.latestAnswerRecord.answerRecordId,
      };
      this.$router.push({ path: '/brushDo', query });
    },
    goResult(item) {
      const answerRecordId = item.latestAnswerRecord.answerRecordId;
      this.$router.push({
        path: `/brushResult/${answerRecordId}`,
      });
    },
    clickBtn(item) {
      const status = item.latestAnswerRecord?.status;
      switch (status) {
        case 'doing':
        case 'paused':
          this.continueDo(item);
          break;
        case 'reviewing':
        case 'finished':
          this.goResult(item);
          break;
        default:
          this.startDo(item);
          break;
      }
    },
  },
};
</script>

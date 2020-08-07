<template>
  <div class="">
    <div class="directory-exercise">
      <div class="directory-exercise-left">{{ section.name }}</div>
      <template v-if="hasQuestion">
        <div class="directory-exercise-center">
          {{ learnNum }}/{{ section.question_num }}题
        </div>
        <div class="directory-exercise-right">
          <div :class="[btnText.class]" @click="clickBtn()">
            {{ btnText.text }}
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import getBtnText from '@/utils/itemBank-status.js';
export default {
  nama: 'exercise-section',
  components: {},
  data() {
    return {};
  },
  props: {
    section: {},
    moduleId: {
      type: String,
      default: '',
    },
    exerciseId: {
      type: Number,
      default: -1,
    },
  },
  computed: {
    learnNum() {
      if (this.section.latestAnswerRecord) {
        return this.section.latestAnswerRecord.doneQuestionNum;
      }
      return 0;
    },
    btnText() {
      return getBtnText(this.section.latestAnswerRecord?.status || '');
    },
    hasQuestion() {
      return this.section.question_num > 0;
    },
  },
  watch: {},
  created() {},
  methods: {
    clickBtn() {
      const status = this.section.latestAnswerRecord?.status;
      switch (status) {
        case 'doing':
        case 'paused':
          this.continueDo(this.section);
          break;
        case 'reviewing':
        case 'finished':
          this.goResult(this.section);
          break;
        default:
          this.startDo(this.section);
          break;
      }
    },
    startDo(item) {
      const query = {
        mode: 'start',
        type: 'chapter',
        exerciseId: this.exerciseId,
        categoryId: item.id,
        moduleId: this.moduleId,
      };
      this.$router.push({ path: '/brushDo', query });
    },
    continueDo(item) {
      const query = {
        mode: 'continue',
        type: 'chapter',
        exerciseId: this.exerciseId,
        categoryId: item.id,
        moduleId: this.moduleId,
        answer_record_id: item.latestAnswerRecord.answerRecordId,
      };
      this.$router.push({ path: '/brushDo', query });
    },
    goResult(item) {
      const query = {
        type: 'chapter',
        exerciseId: this.exerciseId,
        categoryId: item.id,
        moduleId: this.moduleId,
      };
      const answerRecordId = item.latestAnswerRecord.answerRecordId;
      this.$router.push({
        path: `/brushResult/${answerRecordId}`,
        query,
      });
    },
  },
};
</script>

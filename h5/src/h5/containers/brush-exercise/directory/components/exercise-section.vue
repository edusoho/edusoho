<template>
  <div class="">
    <div class="directory-exercise">
      <div class="directory-exercise-left">{{ section.name }}</div>
      <template v-if="hasQuestion">
        <div class="directory-exercise-center">
          {{ learnNum }}/{{ section.question_num }}题
        </div>
        <div class="directory-exercise-right">
          <div :class="[btnText.class]">{{ btnText.text }}</div>
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
  },
  computed: {
    learnNum() {
      if (this.section.latestAnswerRecord) {
        return this.section.latestAnswerRecord.done_question_count;
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
  methods: {},
};
</script>

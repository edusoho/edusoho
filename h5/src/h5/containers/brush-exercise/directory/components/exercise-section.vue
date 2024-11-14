<template>
  <div class="directory-exercise">
    <div class="directory-exercise-left">{{ section.name }}</div>
    <template v-if="hasQuestion">
      <div
        :class="[
          isMember ? 'directory-exercise-center' : 'directory-exercise-end',
        ]"
      >
        {{ learnNum }}{{ allNum }}题
      </div>
      <div class="directory-exercise-right" v-if="isMember">
        <div :class="[btnText.class]" @click="clickBtn()">
          {{ btnText.text }}
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { getBtnText } from '@/utils/itemBank-status.js';
import { mapState } from 'vuex';
import { closedToast } from '@/utils/on-status.js';

export default {
  nama: 'exercise-section',
  components: {},
  data() {
    return {
    };
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
    ...mapState('ItemBank', {
      isMember: state => state.ItemBankExercise.isMember,
      ItemBankExercise: state => state.ItemBankExercise,
    }),
    btnText() {
      return getBtnText(this.section.latestAnswerRecord?.status || '');
    },
    hasQuestion() {
      return this.section.question_num > 0;
    },
    learnNum() {
      if (!this.isMember) {
        return '';
      }
      if (this.section.latestAnswerRecord) {
        return `${this.section.latestAnswerRecord.doneQuestionNum}/`;
      }
      return '0/';
    },
    allNum() {
      if (this.section.latestAnswerRecord) {
        return `${this.section.latestAnswerRecord.questionNum}`;
      }
      return `${this.section.question_num}`;
    },
  },
  watch: {},
  created() {
  },
  methods: {
    clickBtn() {
      const status = this.section.latestAnswerRecord?.status;
      console.log(status)
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
      if (this.ItemBankExercise?.status === 'closed' || this.ItemBankExercise?.canLearn === '0') {
        closedToast('exercise')
        return
      }

      const query = {
        moduleId: this.moduleId,
        categoryId: item.id,
        exerciseId: this.exerciseId,
      };
      this.$router.push({ path: '/brushIntro', query });
    },
    continueDo(item) {
      if (this.ItemBankExercise?.status === 'closed' || this.ItemBankExercise?.canLearn === '0') {
        closedToast('exercise')
        return
      }

      const query = {
        moduleId: this.moduleId,
        categoryId: item.id,
        exerciseId: this.exerciseId,
        answer_record_id: item.latestAnswerRecord.answerRecordId
      };
      this.$router.push({ path: '/brushIntro', query });
    },
    goResult(item) {
      const query = {
        type: 'chapter',
        title: item.name,
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

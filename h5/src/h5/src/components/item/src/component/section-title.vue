<template>
  <div :class="[ brushDo.status === 'doing' ? brushDo.exerciseModes === '0' ? 'ibs-head-top' : 'ibs-one-questions-head' : 'ibs-head-top' ]">
    <div v-if="brushDo.exerciseModes === '1' && brushDo.status === 'doing'" class="w-full">
      <div class="flex justify-between items-end mb-8">
        <span class="text-14 ibs-answer-progress">{{ $t('courseLearning.answerProgress') }} <span class="ibs-current-num">{{ reviewedCount? reviewedCount : brushDo.reviewedCount }} <span class="ibs-total-number"> /{{ all }}</span></span></span>
        <span @click="itemEngine.cardShow = true" class="text-12 ibs-answer-card">{{ $t('courseLearning.answerSheet') }}</span>
      </div>
      <van-progress :percentage="((reviewedCount ? reviewedCount : brushDo.reviewedCount) / all) * 100" stroke-width="8" track-color="#E9E9EB" :show-pivot=false />
    </div>
    <div v-else class="flex justify-between w-full">
      <div class="ibs-head-left">
        {{ subject }}
        <span v-show="showScore" class="ibs-left-color">[{{ score }}分]</span>
      </div>
      <div class="ibs-head-right">
        <span class="ibs-right-color">{{ current }}</span
        >/{{ all }}
      </div>
    </div>
  </div>
</template>
<script>
import answerMode from "@/src/utils/filterAnswerMode";
export default {
  name: "sectionTitle",
  props: {
    all: {
      type: Number,
      default: 0
    },
    current: {
      type: Number,
      default: 0
    },
    itemType: {
      type: String,
      default: ""
    },
    questionsType: {
      type: String,
      default: ""
    },
    score: {
      type: Number,
      default: 0
    },
    showScore: {
      type: Boolean,
      default: true
    },
    reviewedCount: {
      type: Number,
      default: 0
    }
  },
  inject: ['brushDo','itemEngine'],
  computed: {
    subject() {
      return `${answerMode(this.questionsType)}`;
    }
  }
};
</script>

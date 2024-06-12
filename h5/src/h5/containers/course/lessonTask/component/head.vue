<template>
  <div :class="[(exerciseMode === '0') ? 'head-top' : 'one-questions-head']">
    <div v-if="exerciseMode === '1' && canDo" class="w-full">
      <div class="flex justify-between items-end mb-8">
        <span class="text-14 answer-progress">{{ $t('courseLearning.answerProgress') }} <span class="current-num">{{reviewedCount}} <span class="total-number"> /{{totalCount}}</span></span></span>
        <span @click="showCard" class="text-12 answer-card">{{ $t('courseLearning.answerSheet') }}</span>
      </div>
      <van-progress :percentage="(reviewedCount / totalCount) * 100" stroke-width="8" track-color="#E9E9EB" :show-pivot=false />
    </div>
    <div v-else class="flex justify-between w-full">
      <div class="head-left">
        {{ subject }}
        <span v-show="showScore" class="left-color">[{{ score }}分]</span>
      </div>
      <div class="head-right">
        <span class="right-color">{{ current }}</span
        >/{{ all }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HeadTop',
  props: {
    all: {
      type: Number,
      default: 0,
    },
    current: {
      type: Number,
      default: 0,
    },
    subject: {
      type: String,
      default: '',
    },
    score: {
      type: String,
      default: '0',
    },
    showScore: {
      type: Boolean,
      default: true,
    },
    exerciseMode: {
      type: String,
      default: ''
    },
    totalCount: {
      type: Number,
      default: 0
    },
    reviewedCount: {
      type: Number,
      default: 0
    },
    canDo: {
      type: Boolean,
      default: true,
    },
  },
  methods :{
    showCard() {
      this.$parent.$parent.$parent.$parent.cardShow = true
    }
  }
};
</script>

<style></style>

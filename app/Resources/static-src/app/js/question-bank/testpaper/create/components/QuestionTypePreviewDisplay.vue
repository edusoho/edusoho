<script>
export default {
  name: 'QuestionTypePreviewDisplay',
  props: {
    type: null,
    num: undefined,
    score: undefined,
  },
}
</script>
<template>
  <div v-if="type" class="question-type-display">
    <div class="question-type" :class="`question-type-${type}`">
      <div class="question-type-icon">
        <img :src="`/static-dist/app/img/question-bank/${type}-icon.png`" alt=""/>
      </div>
      <div class="question-type-label">{{ `course.question.type.${type}` | trans }}</div>
    </div>
    <div class="question-type-content">
      <div class="question-type-num-and-score">
        <div class="question-type-num">
          <span>题目数量：</span>
          <span class="question-type-num-value">{{ num }}</span>
        </div>
        <div class="question-type-score">
          <span>每题分值：</span>
          <span class="question-type-num-value">{{ score }}</span>
        </div>
      </div>
      <div class="divider"></div>
      <div class="question-type-total">
        <span class="question-type-total-label">合计（题数/总分）:</span>
        <span class="question-type-total-value">{{ `${num} / ${(num * score).toFixed(1)}` }}</span>
      </div>
    </div>
  </div>
</template>

<template>
  <layout :question="question">
    <template #answer>
      <a-radio-group :default-value="question.report.response[0]">
        <a-radio
          :class="['choose-answer', getAnswerClass(item.radio.val)]"
          v-for="(item, index) in question.question.response_points"
          :key="index"
          :value="item.radio.val"
        >
          <div class="choose-answer-content">
            <span>{{ item.radio.val }}.</span>
            <span class="choose-answer-text" v-html="item.radio.text" />
          </div>
        </a-radio>
      </a-radio-group>
    </template>
  </layout>
</template>

<script>
import answerMixin from './answerMixin.js';

export default {
  name: 'SingleChoice',

  mixins: [answerMixin]
}
</script>

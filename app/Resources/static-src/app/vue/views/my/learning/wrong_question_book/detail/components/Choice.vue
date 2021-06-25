<template>
  <layout :question="question">
    <template #answer>
      <a-checkbox-group :default-value="question.report.response">
        <a-checkbox
          :class="['choose-answer', getAnswerClass(item.checkbox.val)]"
          v-for="(item, index) in question.question.response_points"
          :key="index"
          :value="item.checkbox.val"
        >
          <div class="choose-answer-content">
            <span>{{ item.checkbox.val }}.</span>
            <span class="choose-answer-text" v-html="item.checkbox.text" />
          </div>
        </a-checkbox >
      </a-checkbox-group>
    </template>
  </layout>
</template>

<script>
import answerMixin from './answerMixin.js';

export default {
  name: 'Choice',

  mixins: [answerMixin]
}
</script>

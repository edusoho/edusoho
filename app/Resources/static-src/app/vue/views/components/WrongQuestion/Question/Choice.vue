<template>
  <layout :question="question" :order="order">
    <template #answer>
      <a-checkbox-group :default-value="questions.answer">
        <a-checkbox
          v-for="(item, index) in questions.response_points"
          :class="['choose-answer', getAnswerClass(item.checkbox.val)]"
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

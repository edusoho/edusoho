<template>
  <layout :question="question" :order="order">
    <template #answer>
      <a-radio-group :default-value="questions.report.response[0]">
        <a-radio
          v-for="(item, index) in questions.response_points"
          :class="['choose-answer', getAnswerClass(item.radio.val)]"
          :key="index"
          :value="item.radio.val"
        >
          <div class="choose-answer-content">
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
  name: 'Judge',

  mixins: [answerMixin]
}
</script>

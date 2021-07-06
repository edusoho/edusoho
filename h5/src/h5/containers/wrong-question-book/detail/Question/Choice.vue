<template>
  <van-checkbox-group v-model="questions.answer">
    <van-checkbox
      v-for="(answer, index) in questions.response_points"
      :key="index"
      :name="answer.checkbox.val"
      class="question-option"
    >
      <div class="question-option__content" v-html="answer.checkbox.text" />
      <span
        slot="icon"
        :class="[
          'question-option__order',
          'question-option__order--square',
          checkAnswer(answer.checkbox.val),
        ]"
      >
        {{ answer.checkbox.val }}
      </span>
    </van-checkbox>
  </van-checkbox-group>
</template>

<script>
import _ from 'lodash';

export default {
  props: {
    question: {
      type: Object,
      required: true,
    },
  },

  computed: {
    questions() {
      return this.question.questions[0];
    },
  },

  methods: {
    checkAnswer(value) {
      const {
        answer,
        report: { response },
      } = this.questions;

      // 正确答案
      if (_.includes(answer, value)) {
        return 'question-option__order_right';
      }

      // 用户选择的错误答案
      if (_.includes(_.difference(response, answer), value)) {
        return 'question-option__order_wrong';
      }
    },
  },
};
</script>

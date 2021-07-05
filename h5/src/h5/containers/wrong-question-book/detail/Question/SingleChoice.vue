<template>
  <van-radio-group v-model="questions.answer[0]">
    <van-radio
      v-for="(answer, index) in questions.response_points"
      :key="index"
      class="question-option"
      :name="answer.radio.val"
    >
      <div class="question-option__content" v-html="answer.radio.text" />
      <template slot="icon">
        <span
          :class="['question-option__order', checkAnswer(answer.radio.val)]"
        >
          {{ answer.radio.val }}
        </span>
      </template>
    </van-radio>
  </van-radio-group>
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

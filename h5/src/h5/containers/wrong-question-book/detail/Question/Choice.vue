<template>
  <van-checkbox-group v-model="questions.answer">
    <van-checkbox
      v-for="(answer, index) in questions.response_points"
      :key="index"
      :name="answer.checkbox.val"
      :class="['question-option',
        {'van-checkbox__right': RadioRight(answer.checkbox.val)},
        {'van-checkbox__wrong': RadioWrong(answer.checkbox.val)},
      ]"
    >
      <i class="iconfont icon-zhengque1"></i>
      <i class="iconfont icon-cuowu2"></i>
      <div class="question-option__content" v-html="answer.checkbox.text" />
      <span
        slot="icon"
        :class="[
          'question-option__order',
        ]"
      >
        {{ answer.checkbox.val + '.'}}
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
    RadioWrong(radioItem) {
      const answer = this.question.questions[0].answer
      const response = this.question.questions[0].report.response
      if (answer.includes(radioItem)) return false
      if((answer.includes(radioItem)) && response.includes(radioItem) || (!answer.includes(radioItem) && response.includes(radioItem))) {
        return true;
      }
    },
    RadioRight(radioItem) {
      const answer = this.question.questions[0].answer
      const response = this.question.questions[0].report.response
      if(response.length === 0 && answer.includes(radioItem) || answer.includes(radioItem)) {
        return true;
      }
    },
  },
};
</script>

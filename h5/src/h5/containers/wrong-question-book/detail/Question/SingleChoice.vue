<template>
  <van-radio-group v-model="radio">
    <van-radio
      v-for="(answer, index) in questions.response_points"
      :key="index"
      class="question-option"
      :name="answer.radio.val"
    >
      {{ answer.radio.text }}
    </van-radio>
  </van-radio-group>
</template>

<script>
export default {
  props: {
    question: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      radio: '1',
    };
  },

  computed: {
    questions() {
      return this.question.questions[0];
    },
  },
};
</script>

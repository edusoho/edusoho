<template>
  <div class="question">
    <div class="question-head">
      <div class="head-left">{{ getQuestionType }}</div>
      <div class="head-right">
        <span class="right-color">{{ order }}</span>
        /{{ total }}
      </div>
    </div>

    <div class="question-stem"></div>

    <div class="question-making">
      <div class="answer-result">
        你的回答：未作答
      </div>

      <div class="right-answer">
        正确答案：这里是答案
      </div>

      <div class="question-analysis">
        <div class="question-analysis__label">题目解析：</div>
        <div class="question-analysis__result">这里是材料题的解析</div>
      </div>

      <div class="question-situation">
        <div class="situation-top">来源：课程名称-作业课时任务</div>
        <div class="situation-bottom">
          <span>2021-04-15 20:20:00</span>
          <span>做错频次：<span class="frequency">3</span>次</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    total: {
      type: Number,
      required: true,
    },

    order: {
      type: Number,
      required: true,
    },

    question: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      types: {
        single_choice: '单选题',
        fill: '填空题',
        choice: '多选题',
        uncertain_choice: '不定项选择题',
        determine: '判断题',
      },
    };
  },

  computed: {
    getQuestionType() {
      return this.types[this.question.type];
    },
  },
};
</script>

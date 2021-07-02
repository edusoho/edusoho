<template>
  <div class="question">
    <div class="question-head">
      <div class="head-left">{{ currentQuestionComponent.name }}</div>
      <div class="head-right">
        <span class="right-color">{{ order }}</span>
        /{{ total }}
      </div>
    </div>

    <div class="question-stem"></div>

    <component :is="currentQuestionComponent.component" />

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
import Choice from './Choice.vue';
import SingleChoice from './SingleChoice.vue';
import Judge from './Judge.vue';
import Fill from './Fill.vue';

export default {
  components: {
    // eslint-disable-next-line vue/no-unused-components
    Choice,
    // eslint-disable-next-line vue/no-unused-components
    SingleChoice,
    // eslint-disable-next-line vue/no-unused-components
    Judge,
    // eslint-disable-next-line vue/no-unused-components
    Fill,
  },

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
      questionComponents: {
        single_choice: {
          name: '单选题',
          component: 'SingleChoice',
        },
        fill: {
          name: '填空题',
          component: 'Fill',
        },
        choice: {
          name: '多选题',
          component: 'Choice',
        },
        uncertain_choice: {
          name: '不定项选择题',
          component: 'Choice',
        },
        determine: {
          name: '判断题',
          component: 'Judge',
        },
      },
    };
  },

  computed: {
    currentQuestionComponent() {
      return this.questionComponents[this.question.type];
    },
  },
};
</script>

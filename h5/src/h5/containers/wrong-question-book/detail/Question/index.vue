<template>
  <div class="question">
    <div class="question-head">
      <div class="head-left">{{ currentQuestionComponent.name }}</div>
      <div class="head-right">
        <span class="right-color">{{ order }}</span>
        /{{ total }}
      </div>
    </div>

    <div class="question-body">
      <div class="question-stem clearfix">
        <span>{{ order }}、</span>
        <div v-html="formateQuestionStem" />
      </div>

      <div class="question-answer">
        <component
          :is="currentQuestionComponent.component"
          :question="question"
        />
      </div>
    </div>

    <div class="analysis">
      <div class="mt10 analysis-result">
        <div class="analysis-title">做题结果</div>
        <div class="analysis-content">
          <div class="analysis-content__item mt10">
            <div class="analysis-item__title">做题结果</div>
            <div class="analysis-item_right">回答正确</div>
          </div>
          <div class="analysis-content__item mt10">
            <div class="analysis-item__title">正确答案</div>
            <div class="analysis-item_right">回答正确</div>
          </div>
          <div class="analysis-content__item mt10">
            <div class="analysis-item__title">做题解析</div>
            <div class="analysis-item_right">回答正确</div>
          </div>
        </div>
      </div>

      <div class="mt10 analysis-result">
        <div class="analysis-title">做题解析</div>
        <div class="analysis-content mt10">这是判断题的解析</div>
      </div>

      <div class="mt10 analysis-result">
        <div class="question-situation">
          <div class="situation-top">来源：课程名称-作业课时任务</div>
          <div class="situation-bottom">
            <span>2021-04-15 20:20:00</span>
            <span>做错频次：<span class="frequency">3</span>次</span>
          </div>
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
    questions() {
      return this.question.questions[0];
    },

    formateQuestionStem() {
      const text = this.questions.stem;
      const reg = /\[\[\]\]/g;
      if (!text.match(reg)) {
        return text;
      }
      let index = 1;
      return text.replace(reg, function() {
        return `<span class="stem-fill-blank">(${index++})</span>`;
      });
    },

    currentQuestionComponent() {
      return this.questionComponents[this.question.type];
    },
  },
};
</script>

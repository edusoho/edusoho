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
            <div :class="[status.color]">{{ status.text }}</div>
          </div>
          <div class="analysis-content__item mt10">
            <div class="analysis-item__title">正确答案</div>
            <div class="analysis-item_right">{{ rightAnswer }}</div>
          </div>
          <div class="analysis-content__item mt10">
            <div class="analysis-item__title">你的答案</div>
            <div :class="[status.color]">{{ yourAnswer }}</div>
          </div>
        </div>
      </div>

      <div class="mt10 analysis-result">
        <div class="analysis-title">做题解析</div>
        <div
          class="analysis-content mt10"
          v-html="questions.analysis || '无解析'"
        />
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
import _ from 'lodash';
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

    status() {
      const statusResult = {
        right: {
          color: 'analysis-item_right',
          text: '回答正确',
        },
        wrong: {
          color: 'analysis-item_worng',
          text: '回答错误',
        },
        partRight: {
          color: 'analysis-item_worng',
          text: '回答错误',
        },
        noAnswer: {
          color: 'analysis-item_noAnswer',
          text: '未回答',
        },
      };
      const { response, status } = this.questions.report;

      if (!_.size(response)) {
        return statusResult.noAnswer;
      }

      return statusResult[status];
    },

    rightAnswer() {
      let { answer, answer_mode } = this.questions;

      if (answer_mode === 'true_false') {
        answer = _.map(answer, function(item) {
          return item === 'T' ? '正确' : '错误';
        });
      }

      return _.join(answer, '、');
    },

    yourAnswer() {
      let {
        answer,
        answer_mode,
        report: { response },
      } = this.questions;

      if (!_.size(response)) {
        return '未作答';
      }

      if (answer_mode === 'true_false') {
        response = _.map(response, function(item) {
          return item === 'T' ? '正确' : '错误';
        });
      }

      if (answer_mode === 'text') {
        let result = '';
        _.forEach(answer, function(item, index) {
          result += `(${index + 1})：${item}`;
        });
        return result;
      }

      return _.join(response, '、');
    },
  },

  methods: {},
};
</script>

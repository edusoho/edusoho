<template>
  <div class="question">
    <div class="question-head">
      <div class="head-left">{{ $t(currentQuestionComponent.name) }}</div>
      <div class="head-right">
        <span class="right-color">{{ order }}</span>
        /{{ total }}
      </div>
    </div>

    <div class="question-body">
      <div class="question-stem clearfix">
        <div class="pull-left">{{ order }}、</div>
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
        <div class="analysis-title">{{ $t('wrongQuestion.answerResult') }}</div>
        <div class="analysis-content">
          <div class="analysis-content__item mt10">
            <div class="analysis-item__title">{{ $t('wrongQuestion.answerResult') }}</div>
            <div :class="[status.color]">{{ status.text }}</div>
          </div>
          <div class="analysis-content__item mt10">
            <div class="analysis-item__title">{{ $t('wrongQuestion.correctAnswer') }}</div>
            <div
              class="analysis-item_right analysis-content__item--column"
              v-html="rightAnswer"
            ></div>
          </div>

          <div class="analysis-content__item mt10">
            <div class="analysis-item__title">{{ $t('wrongQuestion.yourAnswer') }}</div>
            <div
              class="analysis-item_right analysis-content__item--column"
              :class="[status.color]"
              v-html="yourAnswer"
            ></div>
          </div>
        </div>
      </div>

      <div class="mt10 analysis-result">
        <div class="analysis-title">{{ $t('wrongQuestion.parsing') }}</div>
        <div
          class="analysis-content mt10"
          v-html="questions.analysis || $t('wrongQuestion.noParsing')"
        />
      </div>

      <div class="mt10 analysis-result">
        <div class="question-situation">
          <div class="situation-top">{{ $t('wrongQuestion.source') }}：{{ sourcesStr }}</div>
          <div class="situation-bottom">
            <span>{{
              $moment(question.submit_time * 1000).format('YYYY-MM-DD HH:mm:ss')
            }}</span>
            <span>
              {{ $t('wrongQuestion.frequency') }}：
              <span class="frequency">{{ question.wrong_times }}</span>
              {{ $t('wrongQuestion.times') }}
            </span>
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

export default {
  components: {
    // eslint-disable-next-line vue/no-unused-components
    Choice,
    // eslint-disable-next-line vue/no-unused-components
    SingleChoice,
    // eslint-disable-next-line vue/no-unused-components
    Judge,
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
          name: 'wrongQuestion.singleChoice',
          component: 'SingleChoice',
        },
        text: {
          name: 'wrongQuestion.fill',
          component: '',
        },
        choice: {
          name: 'wrongQuestion.choice',
          component: 'Choice',
        },
        uncertain_choice: {
          name: 'wrongQuestion.uncertainChoice',
          component: 'Choice',
        },
        true_false: {
          name: 'wrongQuestion.determine',
          component: 'Judge',
        },
      },
    };
  },

  computed: {
    questions() {
      return this.question.questions[0];
    },

    sourcesStr() {
      return _.join(this.question.sources, '、');
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
      return this.questionComponents[this.question.questions[0].answer_mode];
    },

    status() {
      const statusResult = {
        right: {
          color: 'analysis-item_right',
          text: this.$t('wrongQuestion.correctAnswer2')
        },
        wrong: {
          color: 'analysis-item_worng',
          text: this.$t('wrongQuestion.wrongAnswer')
        },
        partRight: {
          color: 'analysis-item_worng',
          text: this.$t('wrongQuestion.wrongAnswer')
        },
        no_answer: {
          color: 'analysis-item_noAnswer',
          text: this.$t('wrongQuestion.unanswered')
        },
      };
      const { response, status } = this.questions.report;

      if (!_.size(response)) {
        return statusResult.no_answer;
      }

      return statusResult[status];
    },

    rightAnswer() {
      let { answer, answer_mode } = this.questions;

      if (answer_mode === 'true_false') {
        answer = _.map(answer, function(item) {
          return item === 'T' ? this.$t('wrongQuestion.right') : this.$t('wrongQuestion.wrong');
        });
      }

      if (answer_mode === 'text') {
        let result = '';
        _.forEach(answer, (item, index) => {
          result += `<div style="margin-bottom: 2vw"> (${index +
            1}) ${item} </div>`;
        });
        return result;
      }

      return _.join(answer, '、');
    },

    yourAnswer() {
      let {
        answer_mode,
        report: { response },
      } = this.questions;

      if (!_.size(response)) {
        if (answer_mode === 'text') {
          return `<div class="fill-answer">（1）${this.$t('wrongQuestion.unanswered')}</div>`;
        }
        return this.$t('wrongQuestion.unanswered');
      }

      if (answer_mode === 'true_false') {
        response = _.map(response, function(item) {
          return item === 'T' ? this.$t('wrongQuestion.right2') : this.$t('wrongQuestion.wrong2');
        });
      }

      if (answer_mode === 'text') {
        let result = '';
        _.forEach(response, (item, index) => {
          result += `<div style="margin-bottom: 2vw"> (${index + 1}) ${item ||
            this.$t('wrongQuestion.unanswered')}</div>`;
        });
        return result;
      }

      return _.join(response, '、');
    },
  }
};
</script>

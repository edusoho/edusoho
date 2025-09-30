<template>
  <div class="question">
    <div class="question-head">
      <div class="head-left">{{ $t('wrongQuestion.topicList') }}</div>
      <div class="head-right">
        <span class="right-color">{{ order }}</span>
        /{{ total }}
      </div>
    </div>

    <div class="question-body">
      <span class="ibs-tags">
        {{ $t(currentQuestionComponent.name) }}
      </span>
      <div class="clearfix question-stem">
        <div class="pull-left">{{ order }}、</div>
        <div v-html="formateQuestionStem" @click="handleClickImage($event.currentTarget)" />
        <attachement-preview
          v-for="item in getAttachementByType('stem')"
          :attachment="item"
          :key="item.id" />
      </div>
      <div v-if="question.type !== 'fill'" class="question-answer">
        <component
          :is="currentQuestionComponent.component"
          :question="question"
        />
      </div>
      <div class="questions-analysis">
        <div :class="[{'flex': questions.answer_mode !== 'text'}, {'justify-between': questions.answer_mode !== 'text'}, 'analysis-answer']">
          <div v-if="questions.answer_mode !== 'text'" class="flex items-center">
            <span class="answer">{{ $t('wrongQuestion.correctAnswer') }}：</span>
            <span class="options" style="color: #00B42A;" v-html="rightAnswer"></span>
          </div>
          <div v-if="yourAnswerShow()" :class="[{'flex': questions.answer_mode !== 'text'}, {'items-center': questions.answer_mode !== 'text'}]">
            <div class="answer">{{ $t('wrongQuestion.yourAnswer') }}：</div>
            <div :class="[status ? status.color : '', {options: questions.answer_mode !== 'text'}]" v-html="yourAnswer"></div>
          </div>
          <div v-if="questions.answer_mode === 'text'" class="correct-answer">
            <div class="answer">{{ $t('wrongQuestion.correctAnswer') }}：</div>
            <div :class="[{'options': questions.answer_mode !== 'text'}]" style="color: #00B42A;" v-html="rightAnswer"></div>
          </div>
        </div>
        <attachement-preview
          v-for="item in getAttachementByType('answer')"
          :attachment="item"
          :key="item.id" />
        <div class="analysis-color">
          <span class="float-left">{{ $t('courseLearning.analyze') }}：</span>
          <span class="analysis-content mt10" v-html="questions.analysis || $t('wrongQuestion.noParsing')"></span>
          <div class="analysis-content">
            <attachement-preview
              v-for="item in getAttachementByType('analysis')"
              :attachment="item"
              :key="item.id" />
          </div>
        </div>
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
    <div v-if="!(order === total)" class="question-footer-shardow">
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import Api from '@/api';
import _ from 'lodash';
import Choice from './Choice.vue';
import SingleChoice from './SingleChoice.vue';
import Judge from './Judge.vue';
import attachementPreview from '@/containers/course/lessonTask/component/attachement-preview.vue';
import { ImagePreview } from 'vant'

export default {
  components: {
    // eslint-disable-next-line vue/no-unused-components
    Choice,
    // eslint-disable-next-line vue/no-unused-components
    SingleChoice,
    // eslint-disable-next-line vue/no-unused-components
    Judge,
    attachementPreview
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
      wrong: 'static/images/exercise/wrong.png',
    };
  },

  provide() {
    return {
      getResourceToken: this.getResourceToken,
      settings: this.storageSetting
    }
  },

  computed: {
    ...mapState({
      storageSetting: state => state.storageSetting
    }),
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
      return text.replace(reg, function() {
        return ``;
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
        answer = _.map(answer, (item) => {
          return item === 'T' ? this.$t('wrongQuestion.right') : this.$t('wrongQuestion.wrong');
        });
      }

      if (answer_mode === 'text') {
        let result = '';
        _.forEach(answer, (item, index) => {
          result += `<div>${item}</div>`;
        });
        return result;
      }

      return _.join(answer, '');
    },

    yourAnswer() {
      let {
        answer_mode,
        report: { response },
      } = this.questions;
      const that = this

      if (!_.size(response)) {
        if (answer_mode === 'text') {
          return `<img src="${this.wrong}" alt="" class="fill-status"><span class="wrong-answer"> ${this.$t('wrongQuestion.unanswered')}</span>`;
        }
        return this.$t('wrongQuestion.unanswered');
      }

      if (answer_mode === 'true_false') {
        response = _.map(response, function(item) {
          return item === 'T' ? that.$t('wrongQuestion.right2') : that.$t('wrongQuestion.wrong2');
        });
      }

      if (answer_mode === 'text') {
        let result = '';
        _.forEach(response, (item, index) => {
          result += `<img src="${this.wrong}" alt="" class="fill-status"><span class="wrong-answer"> ${item ||
            this.$t('wrongQuestion.unanswered')}</span>`;
        });
        return result;
      }

      return _.join(response, '');
    },
  },

  methods: {
    getAttachementByType(type) {
      return this.questions.attachments.filter(item => item.module === type) || []
    },
    getResourceToken(globalId) {
      return Api.getItemDetail({
        params: { globalId }
      })
    },
    yourAnswerShow() {
      if(this.question.questions[0].report.response.length > 0 && this.question.questions[0].report.response[0] !== '') {
        return true;
      }
      if(this.questions.answer_mode === 'text') {
        return true;
      }
    },
    handleClickImage (imagesUrl) {
      if (imagesUrl === undefined) return;
      event.stopPropagation();//  阻止冒泡
      const images = [imagesUrl]
      ImagePreview({
        images
      })
    }
  }
};
</script>

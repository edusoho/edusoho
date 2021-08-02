<template>
  <div class="analysis">
    <div class="mt10 analysis-result">
      <div class="analysis-title">{{ $t('courseLearning.answerResult') }}</div>
      <div class="analysis-content">
        <div class="analysis-content__item  mt10">
          <div class="analysis-item__title">{{ $t('courseLearning.answerResult') }}</div>
          <div :class="[statusColor]">{{ status(testResult) }}</div>
        </div>

        <div v-if="subject === 'fill'">
          <div class="analysis-content__item  mt10" v-if="resultShow">
            <div class="analysis-item__title">{{ $t('courseLearning.correctAnswer') }}</div>
            <div class="analysis-item_right analysis-content__item--column">
              <div
                v-for="(item, index) in answer"
                :key="`right${index}`"
                class="fill-answer"
              >
                （{{ index + 1 }}）{{ filterOrder(item, 'standard') }}
              </div>
            </div>
          </div>
          <!-- 因为这里的testResult在部分情况下是没有的，所以这里的遍历使用正确答案来遍历 -->
          <div class="analysis-content__item ">
            <div class="analysis-item__title">{{ $t('courseLearning.yourAnswer') }}</div>
            <div class="analysis-content__item--column">
              <div v-for="(item, index) in answer" :key="index">
                <div v-if="!testResult" class="analysis-item_noAnswer">
                  {{ $t('courseLearning.unanswered') }}
                </div>
                <div v-else :class="[statusColor, 'fill-answer']">
                  （{{ index + 1 }}）{{ filterOrder(testResult.answer[index]) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="subject === 'essay'">
          <div class="analysis-content__item  mt10" v-if="resultShow">
            <div class="analysis-item__title">{{ $t('courseLearning.correctAnswer') }}</div>
            <div class="analysis-item_right" v-html="answer[0]" />
          </div>
          <div class="analysis-content__item  mt10">
            <div class="analysis-item__title">{{ $t('courseLearning.yourAnswer') }}</div>
            <div
              v-if="testResult && testResult.answer.length > 0"
              :class="[statusColor]"
              v-html="testResult.answer[0]"
            />
            <div v-else class="analysis-item_noAnswer">{{ $t('courseLearning.unanswered') }}</div>
          </div>
        </div>

        <div v-if="subject !== 'fill' && subject !== 'essay'">
          <div class="analysis-content__item  mt10" v-if="resultShow">
            <div class="analysis-item__title">{{ $t('courseLearning.correctAnswer') }}</div>
            <div
              class="analysis-item_right"
              v-html="filterOrder(answer, 'standard')"
            />
          </div>
          <div class="analysis-content__item  mt10">
            <div class="analysis-item__title">{{ $t('courseLearning.yourAnswer') }}</div>
            <div v-if="!testResult" class="analysis-item_noAnswer">{{ $t('courseLearning.unanswered') }}</div>
            <div v-else :class="[statusColor]">
              {{ filterOrder(testResult.answer) }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt10 analysis-result" v-if="resultShow">
      <div class="analysis-title">{{ $t('courseLearning.parsing') }}</div>
      <div v-if="analysis" class="analysis-content mt10" v-html="analysis" />
      <div v-else class="analysis-content mt10">{{ $t('courseLearning.noParsing') }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Analysis',
  props: {
    testResult: {
      type: Object,
      default: () => {},
    },
    answer: {
      type: Array,
      default: () => [],
    },
    analysis: {
      type: String,
      default: '',
    },
    subject: {
      type: String,
      default: '',
    },
    isExercise: {
      type: Boolean,
      default: false,
    },
    resultShow: {
      type: Boolean,
      default: true,
    },
  },
  computed: {
    statusColor() {
      if (!this.testResult) {
        return 'analysis-item_noAnswer';
      }
      const status = this.testResult.status;
      switch (status) {
        case 'right':
          return 'analysis-item_right';
        case 'none':
          return this.isExercise
            ? 'analysis-item_subject'
            : 'analysis-item_none';
        case 'wrong':
        case 'partRight':
          return 'analysis-item_worng';
        case 'noAnswer':
          return 'analysis-item_noAnswer';
      }
      return '';
    },
  },
  methods: {
    status: function(testResult) {
      if (!testResult) {
        return this.$t('courseLearning.unanswered');
      }
      const status = testResult.status;
      switch (status) {
        case 'right':
          return this.$t('courseLearning.correctAnswer2');
        case 'none':
          return this.isExercise ? '主观题' : this.$t('courseLearning.toBeReviewed');
        case 'wrong':
        case 'partRight':
          return this.$t('courseLearning.wrongAnswer');
        case 'noAnswer':
          return this.$t('courseLearning.unanswered');
      }
    },
    filterOrder: function(answer = [], mode = 'do') {
      // standard表示标砖答案过滤
      if (this.subject == 'fill') {
        if (mode == 'standard') {
          return answer.length > 0 ? answer.toString() : '无';
        } else {
          return answer.length > 0 ? answer.toString() : this.$t('courseLearning.unanswered');
        }
      } else {
        let arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        if (this.subject == 'determine') {
          arr = ['错', '对'];
        }
        let formateAnswer = null;
        formateAnswer = answer.map(element => {
          return arr[element];
        });
        if (mode == 'standard') {
          return formateAnswer.length > 0 ? formateAnswer.join(' ') : '无';
        }
        return formateAnswer.length > 0 ? formateAnswer.join(' ') : this.$t('courseLearning.unanswered');
      }
    },
  },
};
</script>

<style></style>

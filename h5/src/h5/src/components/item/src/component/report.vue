<template>
  <div class="ibs-analysis">
    <div class="analysis-result">
      <div class="ibs-analysis-title">{{ $t('wrongQuestion.answerResult') }}</div>
      <div class="ibs-analysis-content">
        <div class="ibs-analysis-content__item  ibs-mt10 " v-if="showResult">
          <div class="ibs-analysis-item__title">{{ $t('wrongQuestion.answerResult') }}</div>
          <div :class="[statusColor.className]">{{ statusColor.text }}</div>
        </div>

        <div v-if="answer_mode === 'text'">
          <div class="ibs-analysis-content__item  ibs-mt10">
            <div class="ibs-analysis-item__title">{{ $t('wrongQuestion.correctAnswer') }}</div>
            <div
              class="ibs-analysis-item_right ibs-analysis-content__item--column"
            >
              <div
                v-for="(item, index) in answer"
                :key="`right${index}`"
                class="fill-answer"
              >
                （{{ index + 1 }}）
                <span v-html="item"></span>
              </div>
            </div>
          </div>
          <div class="ibs-analysis-content__item ">
            <div class="ibs-analysis-item__title">{{ $t('wrongQuestion.yourAnswer') }}</div>
            <div class="ibs-analysis-content__item--column">
              <div v-for="(item, index) in answer" :key="index">
                <div :class="[statusColor.className, 'fill-answer']">
                  （{{ index + 1 }}）
                  <span
                    v-html="formateFillResponse(report.response[index])"
                  ></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else>
          <div class="ibs-analysis-content__item  ibs-mt10">
            <div class="ibs-analysis-item__title">{{ $t('wrongQuestion.correctAnswer') }}</div>
            <div class="ibs-analysis-item_right" v-html="formateAnswer()"></div>
          </div>
          <div class="ibs-analysis-content__item  ibs-mt10">
            <div class="ibs-analysis-item__title">{{ $t('wrongQuestion.yourAnswer') }}</div>
            <div :class="[statusColor.className]" v-html="formateResponse()" />
          </div>
        </div>
      </div>

      <attachement-preview
        v-for="item in attachements"
        :attachment="item"
        :key="item.id"
      />
    </div>
  </div>
</template>

<script>
import { filterTF } from "@/src/utils/filters";
import attachementPreview from "./attachement-preview.vue";

export default {
  name: "ibs-report",
  components: {
    attachementPreview
  },
  props: {
    answer: {
      type: Array,
      default: () => []
    },
    report: {
      type: Object,
      default: () => {}
    },
    answer_mode: {
      type: String,
      default: ""
    },
    mode: {
      type: String,
      default: "do"
    },
    showResult: {
      type: Boolean,
      default: true
    },
    attachements: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    statusColor() {
      if (!Object.keys(this.report).length) {
        return "analysis-item_noAnswer";
      }
      const status = this.report.status;
      switch (status) {
        case "right":
          return { className: "ibs-analysis-item_right", text: this.$t('wrongQuestion.correctAnswer2') };
        case "reviewing":
          return { className: "ibs-analysis-item_none", text: this.$t('courseLearning.toBeReviewed') };
        case "wrong":
        case "part_right":
          return { className: "ibs-analysis-item_worng", text: this.$t('wrongQuestion.wrongAnswer') };
        case "no_answer":
          return { className: "ibs-analysis-item_noAnswer", text: this.$t('wrongQuestion.unanswered') };
        default:
          return "";
      }
    }
  },
  methods: {
    formateAnswer() {
      if (this.answer_mode === "true_false") {
        return filterTF(this.answer[0]);
      }
      return this.answer.join(" ");
    },
    formateResponse() {
      if (this.report.status === "noAnswer") {
        return "未回答";
      }
      if (this.answer_mode === "true_false") {
        return filterTF(this.report.response[0]);
      }
      return this.report.response.join(" ");
    },
    formateFillResponse(response) {
      if (this.report.status === "noAnswer") {
        return "未回答";
      }
      return response;
    }
  }
};
</script>

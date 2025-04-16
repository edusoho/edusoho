<template>
  <answer-model
    :answerRecord="answerRecord"
    :question="formatQuestion"
    :questionFavoritesItem="questionFavoritesItem"
    :needScore="needScore"
    :mode="mode"
    :keys="keys"
    :seq="seq"
    :section_responses="section_responses"
    v-bind="$attrs"
    v-on="$listeners"
    @changeTag="changeTag"
    @changeCollect="changeCollect"
    @prepareTeacherAiAnalysis="prepareTeacherAiAnalysis"
  >
    <template v-slot:response_points>
      <div v-if="mode == 'do'">
        <a-input
          class="ibs-mt16"
          v-for="(item, index) in answer"
          :key="index"
          :placeholder="getFillOrder(index + 1)"
          v-model="answer[index]"
          @blur="changeAnswer"
        />
      </div>

      <div
        v-if="mode === 'preview' || mode === 'analysis' || mode === 'import'"
        class="ibs-answer-part"
      >
        <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
        <div class="ibs-content">
          <div
            class="ibs-position-relative"
            :class="{ 'ibs-position-relative': mode == 'analysis' }"
            v-for="(item, index) in question.answer"
            :key="index"
          >
            {{ t("itemEngine.fillText") }}
            {{ `(${index + 1})` }} ：
            <!-- word导入解析错误 -->
            <span
              class="ibs-danger-color"
              v-if="question.errors && question.errors[`answers_${index}`]"
              >{{ t("itemEngine.rightAnswerErrorMessage") }}</span
            >

            <span v-else-if="mode === 'preview' || mode === 'import'">{{ answerFill(item) }}</span>
            <span v-else>
              <span class="ibs-success-color">{{ answerFill(item) }}</span>
              <span class="ibs-analysis-status"
                >{{ countNumList[index].num
                }}{{ t("itemEngine.manCount") }}</span
              >
            </span>
          </div>
        </div>
      </div>

      <div v-if="mode == 'report'" class="ibs-answer-part">
        <span class="ibs-label">{{ t("itemEngine.answerResult") }}</span>
        <div class="ibs-content" style="padding-right: 100px;">
          <div
            class="ibs-mb4"
            v-for="(item, index) in question.answer"
            :key="index"
          >
            {{ t("itemEngine.fillText") }} {{ `(${index + 1})` }} ：<span>{{
              t("itemEngine.rightAnswer")
            }}</span
            ><span class="ibs-success-color">{{ answerFill(item) }}</span>
            <span v-if="reportAnswer.status !== 'right'" class="ibs-ml8"
              >{{ t("itemEngine.memberAnswer") }}
              <span v-if="finalAnswer.length" class="ibs-danger-color">{{
                finalAnswer[index]
              }}</span>
              <span v-else>{{
                t("itemEngine.answerStatus.no_answer")
              }}</span></span
            >
            <a-radio-group
              v-if="isErrorCorrection && !correctionRequired[index]"
              class="ibs-ml24"
              :default-value="0"
              :value="correctionAnwer[index]"
              @change="
                e => {
                  handleChangeErrorCorrection(e.target.value, index);
                }
              "
            >
              <a-radio :value="1">
                {{ t("Right") }}
              </a-radio>
              <a-radio :value="0">
                {{ t("Wrong") }}
              </a-radio>
            </a-radio-group>
          </div>
          <div v-if="isErrorCorrection" class="lib-error-correction">
            <a-button type="link" @click="handleClickSave">
              {{ t("confirm") }}
            </a-button>
            <a-button type="link" @click="isErrorCorrection = false">
              {{ t("cancel") }}
            </a-button>
          </div>
          <a-button
            v-else-if="isErrorCorrectionBtn && !isErrorCorrection"
            class="lib-error-correction"
            type="link"
            @click="isErrorCorrection = true"
          >
            {{ t("errorCorrection") }}
          </a-button>
        </div>
      </div>
    </template>
    <template v-slot:analysis_response_points>
      <div class="ibs-answer ibs-answer-part">
        <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
        <div class="ibs-content">
          <div
            class="ibs-position-relative"
            v-for="(item, index) in question.answer"
            :key="index"
          >
            {{ t("itemEngine.fillText") }}
            {{ `(${index + 1})` }} ：
            <span>
              <span class="ibs-success-color">{{ answerFill(item) }}</span>
            </span>
          </div>
        </div>
      </div>
    </template>
  </answer-model>
</template>

<script>
import answerModel from "./answer-model";
import Locale from "common/vue/mixins/locale";
export default {
  name: "fill-type",
  mixins: [Locale],
  data() {
    return {
      formLayout: "horizontal",
      form: this.$form.createForm(this, { name: "fill-answer-form" }),
      answer: this.getUserAnswer(),
      finalAnswer: [],
      isErrorCorrection: false,
      isErrorCorrectionBtn: false,
      correctionAnwer: [],
      correctionRequired: [],
      formatQuestion: this.question,
    };
  },
  components: { answerModel },
  props: {
    answerRecord: {
      type: Object,
      default() {
        return {};
      }
    },
    needScore: {
      type: Number,
      default() {
        return 1;
      }
    },
    mode: {
      type: String,
      default: "do"
    },
    question: {
      type: Object,
      default() {
        return {};
      }
    },
    keys: {
      type: Array,
      default() {
        return [];
      }
    },
    seq: {
      type: String
    },
    userAnwer: {
      type: Array,
      default() {
        return [];
      }
    },
    reportAnswer: {
      type: Object,
      default() {
        return {};
      }
    },
    questionFavoritesItem: {
      type: Object,
      default() {
        return {};
      }
    },
    analysisQuestionInfo: {
      type: Object,
      default() {
        return {};
      }
    },
    previewType: {
      type: String,
      default: ""
    },
    showErrorCorrection: {
      type: String,
      default: "0"
    },
    section_responses: {
      type: Array,
      default() {
        return [];
      }
    },
    item: {
      type: Object,
      default() {
        return {};
      }
    },
  },
  computed: {
    countNumList() {
      if (this.analysisQuestionInfo.response_points_report) {
        return this.analysisQuestionInfo.response_points_report || [];
      } else {
        return this.answerCountNumList;
      }
    },
    answerCountNumList() {
      const defaultData = this.question.response_points.map(item => {
        return Object.assign(item, { num: 0 });
      });
      return defaultData;
    }
  },
  mounted() {
    if (this.mode === "report") {
      this.getFinalAnswer();
      this.initCorrectionAnwer();
    }
    this.formatQuestion.stem = this.filterFillHtml(this.formatQuestion.stem);
    this.formatQuestion.answer = this.filterFillAnswer(this.formatQuestion.answer);
  },
  methods: {
    filterFillHtml(text) {
      const reg =
        (this.mode === "preview" || this.mode === "import") &&
        this.previewType !== "item"
          ? /\[\[.+?\]\]/g
          : /\[\[\]\]/g;
      if (text && !text.match(reg)) {
        return text;
      }
      let index = 1;
      return (
        text &&
        text.replace(reg, function() {
          return `<span class="ibs-stem-fill-blank">(${index++})</span>`;
        })
      );
    },
    reverseStr(value) {
      let newStr = "";
      for (let i = 0; i < value.length; i++) {
        let s = value.charAt(value.length - 1 - i);
        newStr += s;
      }

      return newStr;
    },
    getFillAnswer(value) {
      let result = [];
      let message = value;

      do {
        let reverseStrs = this.reverseStr(message);

        const start = message.indexOf("[[");
        if (start === -1) break;

        const end = reverseStrs.lastIndexOf("]]");
        if (end === -1) break;

        const content = message.substring(
          start + 2,
          message.length - end - (end % 2 === 1 ? 2 : 1)
        );
        message = message.substring(message.length - end + 1);
        result.push(content);
      } while (message.length > 0);

      return result;
    },
    filterFillAnswer(data) {
      return data.map(item => {
        return item.replace(/\|/g, this.t("Or"));
      });
    },
    changeAnswer() {
      this.$emit("changeAnswer", this.answer, this.keys);
    },
    getUserAnswer() {
      if (this.userAnwer.length < this.question.response_points.length) {
        return new Array(this.question.response_points.length).fill("");
      }
      return this.userAnwer;
    },
    getFinalAnswer() {
      this.finalAnswer = this.filterFillAnswer(this.reportAnswer.response);
    },
    changeTag(data) {
      this.$emit("changeTag", data, this.keys);
    },
    changeCollect(data, collectStatus) {
      this.$emit("changeCollect", data, collectStatus, this.keys);
    },
    getFillOrder(index) {
      return this.t("itemEngine.fillOrder")(index);
    },
    answerFill(str) {
      return str.replace(/\|/g, this.t("Or"));
    },

    initCorrectionAnwer() {
      if (!Number(this.showErrorCorrection)) {
        return;
      }
      const { answer } = this.question;
      const { revise } = this.reportAnswer;
      for (let i = 0; i < answer.length; i++) {
        const tempCorrection = revise[i];
        let value;
        if (tempCorrection) {
          value = 1;
        } else {
          const tempAnswer = answer[i].split("|");
          value = tempAnswer.includes(this.finalAnswer[i]) ? 1 : 0;
        }
        this.correctionRequired.push(value);
        this.correctionAnwer.push(0);
      }
      const filterAnswer = this.correctionRequired.filter(item => item === 0);
      this.isErrorCorrectionBtn = filterAnswer.length > 0;
    },

    handleChangeErrorCorrection(value, index) {
      this.correctionAnwer.splice(index, 1, value);
    },

    handleClickSave() {
      this.$emit("error-correction", {
        item_id: this.question.item_id,
        question_id: this.reportAnswer.question_id,
        answer: this.correctionAnwer
      });
      this.isErrorCorrection = false;
      for (let i = 0; i < this.correctionAnwer.length; i++) {
        const answer = this.correctionAnwer[i];
        if (answer == 1) {
          this.correctionRequired.splice(i, 1, answer);
        }
      }
    },
    prepareTeacherAiAnalysis(gen) {
      const data = {};
      let question = JSON.parse(JSON.stringify(this.question));
      data.stem = question.stem;
      data.answers = this.getFillAnswer(question.stem);
      if (this.item.type === "material") {
        data.type = "material-fill";
        data.material = this.item.material;
      } else {
        data.type = "fill";
      }
      gen(data);
    },
  }
};
</script>

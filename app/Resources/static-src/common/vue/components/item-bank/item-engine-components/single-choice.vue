<template>
  <answer-model
    :answerRecord="answerRecord"
    :question="question"
    :questionFavoritesItem="questionFavoritesItem"
    :mode="mode"
    :needScore="needScore"
    v-bind="$attrs"
    v-on="$listeners"
    :keys="keys"
    :seq="seq"
    :section_responses="section_responses"
    @changeTag="changeTag"
    @prepareTeacherAiAnalysis="prepareTeacherAiAnalysis"
  >
    <template v-slot:response_points>
      <div class="ibs-answer">
        <a-radio-group
          :class="{ 'ibs-prevent-click ibs-width-full': mode !== 'do' }"
          v-model="answer"
          @change="changeAnswer"
        >
          <a-radio
            v-for="(item, index) in question.response_points"
            :class="statusClass(item.radio.val)"
            :value="item.radio.val"
            :key="index"
          >
            <div
              v-if="item.radio.val === question.answer[0] && mode == 'report'"
              class="ibs-table ibs-success-color"
            >
              <span>{{ item.radio.val }}.</span>
              <div
                class="ibs-table-cell ibs-editor-text"
                v-html="item.radio.text"
              ></div>
            </div>

            <!-- word导入解析错误 -->
            <div
              class="ibs-table"
              v-else-if="
                (mode === 'preview' || mode === 'import') &&
                  question.errors &&
                  question.errors[`options_${index}`]
              "
            >
              <span>{{ item.radio.val }}.</span>
              <div class="ibs-table-cell ibs-danger-color">
                {{ t("itemEngine.choiceErrorMessage") }}
              </div>
            </div>

            <div v-else-if="mode === 'analysis'">
              <div class="ibs-table ibs-table--analysis">
                <span>{{ item.radio.val }}.</span>
                <div
                  class="ibs-table-cell ibs-editor-text"
                  v-html="item.radio.text"
                ></div>
              </div>
              <div
                class="ibs-analysis-status ibs-success-color"
                v-if="item.radio.val == question.answer[0]"
              >
                {{ countNumList[index].num }}{{ t("itemEngine.manCount") }}
              </div>
              <div v-else class="ibs-analysis-status">
                {{ countNumList[index].num }}{{ t("itemEngine.manCount") }}
              </div>
            </div>
            <div v-else class="ibs-table">
              <span>{{ item.radio.val }}.</span>
              <div
                class="ibs-table-cell ibs-editor-text"
                v-html="item.radio.text"
              ></div>
            </div>
          </a-radio>
        </a-radio-group>
        <div
          class="ibs-answer ibs-answer-part"
          v-if="mode === 'preview' || mode === 'analysis' || mode === 'import'"
        >
          <!-- word导入解析错误 -->
          <div
            class="ibs-danger-color"
            v-if="question.errors && question.errors.answers"
          >
            {{ t("itemEngine.answerErrorMessage") }}
          </div>

          <template v-else>
            <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
            <div class="ibs-content">
              {{ t("itemEngine.rightAnswerShow") }} {{ question.answer[0] }}
            </div>
          </template>
        </div>

        <div class="ibs-answer ibs-answer-part" v-if="mode == 'report'">
          {{ t("itemEngine.rightAnswer")
          }}<span class="ibs-success-color">{{ question.answer[0] }} </span>，
          <span class="ibs-mr8" v-if="reportAnswer.status !== 'right'"
            >{{ t("itemEngine.memberAnswer")
            }}<span class="ibs-danger-color">{{
              reportAnswer.response[0]
            }}</span></span
          >
          <span>{{ statusText }}</span>
        </div>
      </div>
    </template>
    <template v-slot:analysis_response_points>
      <div class="ibs-answer ibs-answer-part">
        <template>
          <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
          <div class="ibs-content">
            {{ t("itemEngine.rightAnswerShow") }} {{ question.answer[0] }}
          </div>
        </template>
      </div>
    </template>
  </answer-model>
</template>

<script>
import Locale from "common/vue/mixins/locale";
import answerModel from "./answer-model";

export default {
  name: "single-choice",
  inheritAttrs: false,
  mixins: [Locale],
  components: {
    answerModel
  },
  data: function() {
    return {
      answer: this.userAnwer[0],
      statusText: "",
      optionKey: [
        "A",
        "B",
        "C",
        "D",
        "E",
        "F",
        "G",
        "H",
        "I",
        "J",
        "K",
        "L",
        "M",
        "N",
        "O",
        "P",
        "Q",
        "R",
        "S",
        "T"
      ],
      form: this.$form.createForm(this, { name: "base-question-type" }),
    };
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
    // 模式 preview:预览模式 report:答题结果模式 do:做题模式
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
    }
  },
  mounted() {
    this.initAnswer();
    this.statusText = this.getStatus();
  },
  methods: {
    initAnswer() {
      if (
        this.mode === "preview" ||
        this.mode === "analysis" ||
        this.mode === "import"
      ) {
        this.answer = this.question.answer[0];
      } else if (this.mode === "report") {
        this.answer = this.reportAnswer.response[0];
      }
    },
    changeAnswer(e) {
      if (this.mode !== "do") {
        return;
      }
      this.$emit("changeAnswer", e.target.value, this.keys);
    },
    statusClass(item) {
      if (
        this.mode === "report" &&
        this.reportAnswer.status === "wrong" &&
        this.reportAnswer.response[0] === item
      ) {
        return "ibs-choose-item ibs-engine-radio ibs-engine-radio--error";
      } else if (this.question.answer[0] == item && this.mode !== "do") {
        return "ibs-choose-item ibs-engine-radio ibs-engine-radio--success";
      } else {
        return "ibs-choose-item ibs-engine-radio";
      }
    },

    getStatus() {
      const status = {
        right: this.t("itemEngine.answerStatus.right"),
        wrong: this.t("itemEngine.answerStatus.wrong"),
        part_right: this.t("itemEngine.answerStatus.wrong"),
        no_answer: this.t("itemEngine.answerStatus.no_answer"),
        reviewing: this.t("itemEngine.answerStatus.reviewing")
      };
      return status[this.reportAnswer.status];
    },
    changeTag(data) {
      this.$emit("changeTag", data, this.keys);
    },
    prepareTeacherAiAnalysis(gen) {
      const data = {
        stem: this.question.stem,
        answer: this.question.answer.join(),
        options: [],
      };
      this.question.response_points.forEach((item, index) => {
        data.options.push(`${this.optionKey[index]}.${item.radio.text}`);
      });
      if (this.item.type === "material") {
        data.type = "material-choice";
        data.material = this.item.material;
      } else {
        data.type = "choice";
      }
      gen(data);
    }
  }
};
</script>

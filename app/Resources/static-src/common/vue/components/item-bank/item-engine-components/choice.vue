<template>
  <answer-model
    :answerRecord="answerRecord"
    :question="question"
    :questionFavoritesItem="questionFavoritesItem"
    :needScore="needScore"
    :mode="mode"
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
        <a-checkbox-group
          :class="{ 'ibs-prevent-click ibs-width-full': mode !== 'do' }"
          v-model="answer"
          @change="changeAnswer"
        >
          <a-checkbox
            v-for="(item, index) in question.response_points"
            :class="statusClass(item.checkbox.val)"
            :value="item.checkbox.val"
            :key="index"
          >
            <div
              v-if="
                question.answer.includes(item.checkbox.val) && mode == 'report'
              "
              class="ibs-table ibs-success-color"
            >
              <span>{{ item.checkbox.val }}.</span>
              <div
                class="ibs-table-cell ibs-editor-text"
                v-html="item.checkbox.text"
                @click="onImgViewer($event.target)"
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
              <span>{{ item.checkbox.val }}.</span>
              <div class="ibs-table-cell ibs-danger-color">
                {{ t("itemEngine.choiceErrorMessage") }}
              </div>
            </div>

            <div v-else-if="mode === 'analysis'">
              <div class="ibs-table ibs-table--analysis">
                <span>{{ item.checkbox.val }}.</span>
                <div
                  class="ibs-table-cell ibs-editor-text"
                  v-html="item.checkbox.text"
                  @click="onImgViewer($event.target)"
                ></div>
              </div>
              <div
                class="ibs-analysis-status ibs-success-color"
                v-if="question.answer.includes(item.checkbox.val)"
              >
                {{ countNumList[index].num }}{{ t("itemEngine.manCount") }}
              </div>
              <div v-else class="ibs-analysis-status">
                {{ countNumList[index].num }}{{ t("itemEngine.manCount") }}
              </div>
            </div>

            <div v-else class="ibs-table">
              <span>{{ item.checkbox.val }}.</span>
              <div
                class="ibs-table-cell ibs-editor-text"
                v-html="item.checkbox.text"
                @click="onImgViewer($event.target)"
              ></div>
            </div>
          </a-checkbox>
        </a-checkbox-group>
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
              {{ t("itemEngine.rightAnswerShow") }}
              {{ question.answer.join(",") }}
            </div>
          </template>
        </div>

        <div class="ibs-answer ibs-answer-part" v-if="mode == 'report'">
          {{ t("itemEngine.rightAnswer")
          }}<span class="ibs-success-color"
            >{{ question.answer.join(",") }} </span
          >，
          <span class="ibs-mr8" v-show="reportAnswer.status !== 'right'"
            >{{ t("itemEngine.memberAnswer")
            }}<span class="ibs-danger-color">{{
              reportAnswer.response.join(",")
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
            {{ t("itemEngine.rightAnswerShow") }}
            {{ question.answer.join(",") }}
          </div>
        </template>
      </div>
    </template>
  </answer-model>
</template>

<script>
import Locale from "common/vue/mixins/locale";
import answerModel from "./answer-model";
import { onImgViewer } from 'common/viewer';
export default {
  name: "choice",
  inheritAttrs: false,
  mixins: [Locale],
  components: {
    answerModel
  },
  data: function() {
    return {
      answer: this.userAnwer,
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
    // 模式 preview:预览模式 report:答题结果模式 do:做题模式
    mode: {
      type: String,
      default: "do"
    },
    needScore: {
      type: Number,
      default() {
        return 1;
      }
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
        this.answer = this.question.answer;
      } else if (this.mode === "report") {
        this.answer = this.reportAnswer.response;
      }
    },
    changeAnswer(e) {
      if (this.mode !== "do") {
        return;
      }
      this.$emit("changeAnswer", e, this.keys);
    },
    changeTag(data) {
      this.$emit("changeTag", data, this.keys);
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
    statusClass(item) {
      if (
        this.reportAnswer.status === "wrong" &&
        this.reportAnswer.response.includes(item) &&
        this.mode === "report"
      ) {
        return "ibs-choose-item ibs-engine-radio ibs-engine-radio--error";
      } else if (
        this.reportAnswer.status === "part_right" &&
        this.reportAnswer.response.includes(item) &&
        this.mode === "report"
      ) {
        return "ibs-choose-item ibs-engine-radio ibs-engine-radio--success";
      } else if (this.question.answer.includes(item) && this.mode !== "do") {
        return "ibs-choose-item ibs-engine-radio ibs-engine-radio--success";
      } else {
        return "ibs-choose-item ibs-engine-radio";
      }
    },
    prepareTeacherAiAnalysis(gen) {
      const data = {};
      let question = JSON.parse(JSON.stringify(this.question));
      data.stem = question.stem;
      data.answer = question.answer.join();
      data.options = [];
      this.question.response_points.forEach((item, index) => {
        data.options.push(`${this.optionKey[index]}.${item.checkbox.text}`);
      });
      if (this.item.type === "material") {
        data.type = "material-choice";
        data.material = this.item.material;
      } else {
        data.type = "choice";
      }
      gen(data);
    },
    onImgViewer(container) {
      onImgViewer(container)
    },
  }
};
</script>

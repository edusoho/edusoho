<template>
  <answer-model
    :question="question"
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
    @genAiAnalysis="getAiAnalysis"
  >
    <template v-slot:response_points>
      <div class="ibs-answer ibs-answer--judge">
        <!-- 答题结果 -->
        <a-radio-group
          v-if="mode == 'report'"
          :class="{ 'ibs-prevent-click': mode === 'report' }"
          v-model="answer"
        >
          <a-radio
            v-for="(item, index) in question.response_points"
            :class="statusClass(item.radio.val)"
            :value="item.radio.val"
            :key="index"
            ><span
              v-if="item.radio.val === question.answer[0]"
              class="ibs-success-color ibs-judge-choose ibs-ml16"
              >{{ item.radio.text }}</span
            >
            <span v-else class="ibs-judge-choose ibs-ml16">{{
              item.radio.text
            }}</span>
          </a-radio>
        </a-radio-group>

        <!-- 做题以及预览任务 -->
        <a-radio-group
          v-if="mode == 'do'"
          v-model="answer"
          :class="{ 'ibs-prevent-click': mode !== 'do' }"
          @change="changeAnswer"
        >
          <a-radio
            v-for="(item, index) in question.response_points"
            class="ibs-choose-item ibs-engine-radio"
            :value="item.radio.val"
            :key="index"
            ><span class="ibs-ml16">{{ item.radio.text }}</span>
          </a-radio>
        </a-radio-group>

        <!-- 预览题目 -->
        <a-radio-group
          v-if="mode === 'preview' || mode === 'import'"
          v-model="answer"
          class="ibs-prevent-click"
        >
          <a-radio
            v-for="(item, index) in question.response_points"
            :class="statusClass(item.radio.val)"
            :value="item.radio.val"
            :key="index"
            ><span class="ibs-ml16 ibs-judge-choose">{{
              item.radio.text
            }}</span>
          </a-radio>
        </a-radio-group>

        <!-- 答题分布 -->
        <a-radio-group
          v-if="mode == 'analysis'"
          v-model="answer"
          :class="{
            'ibs-prevent-click ibs-width-full': mode !== 'do'
          }"
          @change="changeAnswer"
        >
          <a-radio
            v-for="(item, index) in question.response_points"
            :class="statusClass(item.radio.val)"
            :value="item.radio.val"
            :key="index"
          >
            <span
              class="ibs-ml16 ibs-success-color"
              v-if="item.radio.val === question.answer[0]"
              >{{ item.radio.text }}
              <span class="ibs-analysis-status"
                >{{ countNumList[index].num
                }}{{ t("itemEngine.manCount") }}</span
              ></span
            >
            <span class="ibs-ml16" v-else
              >{{ item.radio.text }}
              <span class="ibs-analysis-status"
                >{{ countNumList[index].num
                }}{{ t("itemEngine.manCount") }}</span
              ></span
            >
          </a-radio>
        </a-radio-group>

        <div
          class="ibs-answer ibs-answer-part"
          v-if="mode === 'preview' || mode === 'analysis' || mode === 'import'"
        >
          <div
            class="ibs-danger-color"
            v-if="question.errors && question.errors.answer"
          >
            {{ t("itemEngine.answerErrorMessage") }}
          </div>
          <div v-else>
            <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
            <div class="ibs-content">
              {{ t("itemEngine.rightAnswerShow") }}
              {{ judgeAnswer(question.answer[0]) }}
            </div>
          </div>
        </div>

        <div class="ibs-answer ibs-answer-part" v-if="mode == 'report'">
          {{ t("itemEngine.rightAnswer")
          }}<span class="ibs-success-color"
            >{{ judgeAnswer(question.answer[0]) }} </span
          >，
          <span class="ibs-mr8" v-if="reportAnswer.status !== 'right'"
            >{{ t("itemEngine.memberAnswer")
            }}<span class="ibs-danger-color">{{
              judgeAnswer(reportAnswer.response[0])
            }}</span></span
          >
          <span>{{ statusText }}</span>
        </div>
      </div>
    </template>
    <template v-slot:analysis_response_points>
      <div class="ibs-answer ibs-answer-part">
        <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
        <div class="ibs-content">
          {{ t("itemEngine.rightAnswerShow") }}
          {{ judgeAnswer(question.answer[0]) }}
        </div>
      </div>
    </template>
  </answer-model>
</template>

<script>
import Locale from "common/vue/mixins/locale";
import answerModel from "./answer-model";
export default {
  name: "judge-type",
  inheritAttrs: false,
  components: {
    answerModel
  },
  mixins: [Locale],
  data: function() {
    return {
      answer: this.userAnwer[0],
      statusText: "",
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
    },
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
        this.reportAnswer.status === "wrong" &&
        this.reportAnswer.response[0] === item
      ) {
        return "ibs-choose-item ibs-engine-radio ibs-engine-radio--error";
      } else if (this.question.answer.includes(item)) {
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
    changeCollect(data, collectStatus) {
      this.$emit("changeCollect", data, collectStatus, this.keys);
    },
    judgeAnswer(str) {
      if (!str) {
        return "";
      }
      return str === "T" ? this.t("Right") : this.t("Wrong");
    },
    getAiAnalysis(disable, enable, complete, finish) {
      let data = {};
      let question = JSON.parse(JSON.stringify(this.question));
      data.stem = question.stem;
      data.answer = question.answer === "T" ? "正确" : "错误";
      if (this.item.type === "material") {
        data.type = "material-determine";
        data.material = this.item.material;
      } else {
        data.type = "determine";
      }
      this.$emit("getAiAnalysis", data, disable, enable, complete, finish);
    }
  }
};
</script>

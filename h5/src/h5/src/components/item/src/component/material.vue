<template>
  <div>
    <div class="ibs-material-title" v-if="item.type === 'material'">
      <div v-if="item.isDelete && item.isDelete === 1">
        此题已删除
      </div>
      <template v-else>
        <div v-html="item.material"></div>
        <div class="ibs-material-analysis" v-if="canShowAnalysis">
          <div class="ibs-material-analysis-title">解析</div>
          <div
            v-if="item.analysis"
            v-html="item.analysis"
            class="ibs-material-analysis-content"
          ></div>
          <div v-else class="ibs-material-analysis-content">无解析</div>
        </div>
        <attachement-preview
          v-for="item in getAttachmentTypeData('material')"
          :attachment="item"
          :key="item.id"
        />
      </template>
    </div>

    <swiper
      :ref="`childSwiper${item.id}`"
      :autoHeight="true"
      :nested="true"
      :resistanceRatio="0"
      class="ibs-question-swiper"
    >
      <template v-for="(question, questionIndex) in item.questions">
        <swiper-slide v-if="getIsWrong(questionIndex)" :key="question.id">
          <div v-if="question.isDelete === 1">此题已删除</div>
          <template v-else>
            <answer-model
              :mode="mode"
              :commonData="getAnswerModeProps(question, questionIndex)"
              @showMaterialAnalysis="showMaterialAnalysis"
            >
              <!-- 答题区 -->
              <singleChoice
                v-if="question.answer_mode === 'single_choice'"
                :itemData="getItemProps(question, questionIndex)"
                @changeAnswer="changeAnswer"
              />

              <judge
                v-if="question.answer_mode === 'true_false'"
                :itemData="getItemProps(question, questionIndex)"
                @changeAnswer="changeAnswer"
              />

              <choice
                v-if="
                  question.answer_mode === 'choice' ||
                    question.answer_mode === 'uncertain_choice'
                "
                :itemData="getItemProps(question, questionIndex)"
                @changeAnswer="changeAnswerArray"
              />

              <essay
                v-if="question.answer_mode === 'rich_text'"
                :itemData="getItemProps(question, questionIndex)"
                @changeAnswer="changeAnswer"
              />

              <fill
                v-if="question.answer_mode === 'text'"
                :itemData="getItemProps(question, questionIndex)"
                @changeAnswer="changeAnswerArray"
              />
            </answer-model>
          </template>
        </swiper-slide>
      </template>
    </swiper>
  </div>
</template>

<script>
// import sectionTitle from "./section-title";
import singleChoice from "./single-choice";
import choice from "./choice";
import judge from "./judge";
import essay from "./essay";
import fill from "./fill";
import answerModel from "./answer-model";
import attachementPreview from "./attachement-preview.vue";

export default {
  name: "ibs-item",
  components: {
    singleChoice,
    judge,
    choice,
    essay,
    fill,
    answerModel,
    attachementPreview
  },
  data() {
    return {
      materialAnalysis: false
    };
  },
  props: {
    item: {
      //题目
      type: Object,
      default: () => {}
    },
    mode: {
      //当前模式
      type: String,
      default: "do"
    },
    itemUserAnswer: {
      //用户答案
      type: Object,
      default: () => {}
    },
    needScore: {
      //是否展示分数
      type: Boolean,
      default: true
    },
    all: {
      //题目总数
      type: Number,
      default: 0
    },
    keys: {
      //当前索引
      type: Array,
      default: () => []
    },
    itemUserReport: {
      //用户答题结果
      type: Object,
      default: () => {}
    },
    wrongMode: {
      //是否为做题模式
      type: Boolean,
      default: false
    },
    doLookAnalysis: {
      //是否做题时可以查看解析
      type: Boolean,
      default: false
    }
  },
  computed: {
    canShowAnalysis() {
      if (this.doLookAnalysis && this.mode === "do") {
        return this.materialAnalysis;
      }
      return this.mode === "report";
    }
  },
  methods: {
    getUserAnwer(q) {
      if (this.mode === "report") {
        return this.itemUserAnswer.question_reports[q].response;
      }
      return this.itemUserAnswer.question_responses[q].response;
    },
    getQuestionReport(q) {
      if (this.mode === "report") {
        return this.itemUserAnswer.question_reports[q];
      }
      return {};
    },
    changeAnswer(value, keys) {
      this.itemUserAnswer.question_responses[keys].response = [value];
      this.$emit("changeAnswer", this.itemUserAnswer, this.keys);
    },
    changeAnswerArray(value, keys) {
      this.itemUserAnswer.question_responses[keys].response = value;
      this.$emit("changeAnswer", this.itemUserAnswer, this.keys);
    },
    getIsWrong(q) {
      if (this.mode !== "report" || !this.wrongMode) {
        return true;
      }
      const status = this.itemUserReport.question_reports[q].status;
      return status !== "right";
    },
    getItemProps(question, questionIndex) {
      return {
        mode: this.mode,
        keys: questionIndex,
        question: question,
        userAnwer: this.getUserAnwer(questionIndex)
      };
    },
    getAnswerModeProps(question, index) {
      const data = {
        questionsType: question.answer_mode,
        score: Number(question.score) || 0,
        current: Number(question.seq),
        showScore: this.needScore,
        all: this.all,
        questionStem: question.stem,
        answer: question.answer,
        report: this.getQuestionReport(index),
        analysis: question.analysis,
        doLookAnalysis: this.doLookAnalysis
      };
      return data;
    },
    showMaterialAnalysis(data) {
      if (this.item.type !== "material") {
        return;
      }
      this.materialAnalysis = data;
    },
    getAttachmentTypeData(type) {
      return this.attachements.filter(item => item.module === type)
    }
  }
};
</script>

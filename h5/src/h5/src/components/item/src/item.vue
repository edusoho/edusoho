<template>
  <div v-if="item">
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
      </template>
    </div>
    <swiper
      :ref="`childSwiper${item.id}`"
      :height="height"
      :nested="true"
      :resistanceRatio="0"
      class="ibs-question-swiper  ibs-paper-item"
      @slideChange="slideChange"
    >
      <template v-for="(question, questionIndex) in item.questions">
        <swiper-slide
          v-if="getShow(questionIndex, question)"
          :key="question.id"
        >
          <div v-if="question.isDelete === 1">此题已删除</div>
          <template v-else>
            <answer-model
              :mode="mode"
              :activeIndex="swiperActiveIndex"
              :commonData="getAnswerModeProps(question, questionIndex)"
              :attachements="
                getItemProps(question, questionIndex).question.attachments
              "
              @showMaterialAnalysis="showMaterialAnalysis"
              v-bind="$attrs"
            >
              <!-- 答题区 -->
              <singleChoice
                v-if="question.answer_mode === 'single_choice'"
                :itemData="getItemProps(question, questionIndex)"
								:commonData="getAnswerModeProps(question, questionIndex)"
                @changeAnswer="changeAnswer"
              />

              <judge
                v-if="question.answer_mode === 'true_false'"
                :itemData="getItemProps(question, questionIndex)"
								:commonData="getAnswerModeProps(question, questionIndex)"
                @changeAnswer="changeAnswer"
              />

              <choice
                v-if="
                  question.answer_mode === 'choice' ||
                    question.answer_mode === 'uncertain_choice'
                "
                :itemData="getItemProps(question, questionIndex)"
								:commonData="getAnswerModeProps(question, questionIndex)"
                @changeAnswer="changeAnswerArray"
              />

              <essay
                v-if="question.answer_mode === 'rich_text'"
                :itemData="getItemProps(question, questionIndex)"
								:commonData="getAnswerModeProps(question, questionIndex)"
                @changeAnswer="changeAnswer"
              />

              <fill
                v-if="question.answer_mode === 'text'"
                :itemData="getItemProps(question, questionIndex)"
								:commonData="getAnswerModeProps(question, questionIndex)"
                @changeAnswer="changeAnswerArray"
              />
            </answer-model>
          </template>
        </swiper-slide>
      </template>
    </swiper>
    <ibs-slide
      :isLast="isLast"
      :isFrist="isFrist"
      @slidePrev="slidePrev"
      @slideNext="slideNext"
    ></ibs-slide>
  </div>
</template>

<script>
// import sectionTitle from "./section-title";
import singleChoice from "./component/single-choice";
import choice from "./component/choice";
import judge from "./component/judge";
import essay from "./component/essay";
import fill from "./component/fill";
import ibsSlide from "./component/slide-btn";
import answerModel from "./component/answer-model";

export default {
  name: "ibs-item",
  components: {
    singleChoice,
    judge,
    choice,
    essay,
    fill,
    answerModel,
    ibsSlide
  },
  data() {
    return {
      height: 0,
      swiperActiveIndex: 0,
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
    },
    current: {
      type: Number,
      default: 0
    },
    itemLength: {
      type: Number,
      default: 0
    }
  },
  computed: {
    canShowAnalysis() {
      if (this.doLookAnalysis && this.mode === "do") {
        return this.materialAnalysis;
      }
      return this.mode === "report";
    },
    isLast() {
      let lastNum = this.item.questions.length - 1;
      if (this.mode === "report" && this.wrongMode) {
        lastNum = this.item.wrongNum - 1;
      }
      if (this.mode === "review") {
        lastNum = this.item.reviewNum - 1;
      }
      if (
        this.current === this.itemLength - 1 &&
        this.swiperActiveIndex === lastNum
      ) {
        return true;
      }
      return false;
    },
    isFrist() {
      if (this.current === 0 && this.swiperActiveIndex === 0) {
        return true;
      }
      return false;
    }
  },
  mounted() {
    this.setSwiperHeight();
  },
  methods: {
    slideChange() {
      const swiperName = `childSwiper${this.item.id}`;
      this.swiperActiveIndex = this.$refs[swiperName].$swiper.activeIndex;
    },
    setSwiperHeight() {
      const offsetTopHeight = document.getElementById("ibs-item-bank")
        .offsetTop;
      const WINDOWHEIGHT = document.documentElement.clientHeight;
      this.height = WINDOWHEIGHT - offsetTopHeight;
    },
    getUserAnwer(q) {
      if (this.mode !== "do") {
        return this.itemUserAnswer.question_reports[q].response;
      }
      return this.itemUserAnswer.question_responses[q].response;
    },
    getQuestionReport(q) {
      if (this.mode !== "do") {
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
    getShow(q, question) {
      if (this.mode === "report" && this.wrongMode) {
        const status = this.itemUserReport.question_reports[q].status;
        return status !== "right";
      }
      if (this.mode === "review") {
        return question.reviewShow;
      }
      return true;
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
        doLookAnalysis: this.doLookAnalysis,
        questionId: question.id
      };
      return data;
    },
    showMaterialAnalysis(data) {
      if (this.item.type !== "material") {
        return;
      }
      this.materialAnalysis = data;
    },
    slidePrev() {
      const swiperName = `childSwiper${this.item.id}`;
      const slide = this.$refs[swiperName].$swiper.slidePrev();
      if (!slide) {
        this.$emit("itemSlidePrev");
      }
    },
    slideNext() {
      const swiperName = `childSwiper${this.item.id}`;
      const slide = this.$refs[swiperName].$swiper.slideNext();
      if (!slide) {
        this.$emit("itemSlideNext");
      }
    }
  }
};
</script>

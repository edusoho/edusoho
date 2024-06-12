<template>
  <div v-if="item" style="height: 100% !important;">
    <swiper
      :ref="`childSwiper${item.id}`"
      :height="height"
      :nested="true"
      :speed="500"
      :resistanceRatio="0"
      :touchable="touchable"
      :show-indicators="false"
      class="ibs-question-swiper  ibs-paper-item"
      @slideChange="slideChange"
    >
      <template v-for="(question, questionIndex) in item.questions">
        <swiper-slide
          v-if="getShow(questionIndex, question) && !wrong"
          :key="question.id"
          :class="mode == 'do' && iscando[Number(question.seq) -1] ? 'ibs-exercise-do' : 'ibs-exercise-analysis'"
        >
          <template>
            <sectionTitle
              :questionsType="getAnswerModeProps(question, questionIndex).questionsType"
              :score="getAnswerModeProps(question, questionIndex).score"
              :current="getAnswerModeProps(question, questionIndex).current"
              :showScore="getAnswerModeProps(question, questionIndex).showScore"
              :all="getAnswerModeProps(question, questionIndex).all"
              :reviewedCount= 'reviewedCount'
            />
            <answer-model
              :mode="mode"
              :activeIndex="swiperActiveIndex"
              :commonData="getAnswerModeProps(question, questionIndex)"
              :attachements="
                getItemProps(question, questionIndex).question.attachments
              "
              :reviewStatus="question_reports[current]"
              @changeReviewList="changeReviewList"
              @showMaterialAnalysis="showMaterialAnalysis"
              v-bind="$attrs"
            >
              <!-- 答题区 -->
              <singleChoice
                v-if="question.answer_mode === 'single_choice'"
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :wrong="wrong"
                :currentItem="item"
                :totalCount="all"
                :showShadow="showShadow"
                :reviewedCount="reviewedCount"
                :disabledData="(mode === 'report' ? false : iscando.length === 0 ? true : mode === 'do' && iscando[Number(question.seq) -1]) && mode !== 'review'"
                :exerciseInfo="exerciseInfo"
                :isAnswerFinished="isAnswerFinished"
                @changeAnswer="changeAnswer"
                @goBrushResult="goBrushResult"
              />
              <judge
                v-if="question.answer_mode === 'true_false'"
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :currentItem="item"
                :wrong="wrong"
                :totalCount="all"
                :showShadow="showShadow"
                :disabledData="(mode === 'report' ? false : iscando.length === 0 ? true : mode === 'do' && iscando[Number(question.seq) -1]) && mode !== 'review'"
                :exerciseInfo="exerciseInfo"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                @changeAnswer="changeAnswer"
                @goBrushResult="goBrushResult"
              />
              <choice
                v-if="
                  question.answer_mode === 'choice' ||
                    question.answer_mode === 'uncertain_choice'
                "
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :currentItem="item"
                :totalCount="all"
                :wrong="wrong"
                :showShadow="showShadow"
                :exerciseInfo="exerciseInfo"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                :disabledData="(mode === 'report' ? false : iscando.length === 0 ? true : mode === 'do' && iscando[Number(question.seq) -1]) && mode !== 'review'"
                @submitSingleAnswer="submitSingleAnswer"
                @changeAnswer="changeAnswerArray"
                @goBrushResult="goBrushResult"
              />

              <essay
                v-if="question.answer_mode === 'rich_text'"
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :currentItem="item"
                :totalCount="all"
                :wrong="wrong"
                :questionStatus="status"
                :showShadow="showShadow"
                :exerciseInfo="exerciseInfo"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                :reviewedQuestion="reviewedQuestion"
                :EssayRadio="EssayRadio"
                :disabledData="(mode === 'report' ? false : iscando.length === 0 ? true : mode === 'do' && iscando[Number(question.seq) -1]) && mode !== 'review'"
                @nextSkipQuestion="nextSkipQuestion"
                @updataIsAnswerFinished="updataIsAnswerFinished"
                @submitSingleAnswer="submitSingleAnswer"
                @changeAnswer="changeAnswer"
                @goBrushResult="goBrushResult"
                @changeEssayRadio="changeEssayRadio"
              />

              <fill
                v-if="question.answer_mode === 'text'"
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :currentItem="item"
                :totalCount="all"
                :wrong="wrong"
                :questionStatus="status"
                :showShadow="showShadow"
                :fillStatus="fillStatus"
                :exerciseInfo="exerciseInfo"
                :disabledData="(mode === 'report' ? false : iscando.length === 0 ? true : mode === 'do' && iscando[Number(question.seq) -1]) && mode !== 'review'"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                @submitSingleAnswer="submitSingleAnswer"
                @changeAnswer="changeAnswerArray"
                @goBrushResult="goBrushResult"
              />
            </answer-model>

          </template>

        </swiper-slide>

        <swiper-slide
          v-if="getShow(questionIndex, question) && wrong"
          :key="question.id"
          :class="mode == 'do' && !choiceIsCando[Number(question.seq) - 1]  ? 'ibs-exercise-do' : 'ibs-exercise-analysis'"
        >
          <template>
            <sectionTitle
              :questionsType="getAnswerModeProps(question, questionIndex).questionsType"
              :score="getAnswerModeProps(question, questionIndex).score"
              :current="getAnswerModeProps(question, questionIndex).current"
              :showScore="getAnswerModeProps(question, questionIndex).showScore"
              :all="getAnswerModeProps(question, questionIndex).all"
              :reviewedCount= 'reviewedCount'
            />
            <answer-model
              :mode="mode"
              :activeIndex="swiperActiveIndex"
              :commonData="getAnswerModeProps(question, questionIndex)"
              :attachements="
                getItemProps(question, questionIndex).question.attachments
              "
              :reviewStatus="question_reports[current]"
              @changeReviewList="changeReviewList"
              @showMaterialAnalysis="showMaterialAnalysis"
              v-bind="$attrs"
            >
              <!-- 答题区 -->
              <singleChoice
                v-if="question.answer_mode === 'single_choice'"
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :wrong="wrong"
                :currentItem="item"
                :totalCount="all"
                :showShadow="showShadow"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                :disabledData="mode === 'report' ? false : choiceIsCando.length === 0 ? true : mode === 'do' && !choiceIsCando[Number(question.seq) - 1]"
                :exerciseInfo="exerciseInfo"
                @changeChoiceCando="changeChoiceCando"
                @changeAnswer="changeAnswer"
                @goBrushResult="goBrushResult"
              />

              <judge
                v-if="question.answer_mode === 'true_false'"
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :currentItem="item"
                :wrong="wrong"
                :totalCount="all"
                :showShadow="showShadow"
                :disabledData="mode === 'report' ? false : choiceIsCando.length === 0 ? true : mode === 'do' && !choiceIsCando[Number(question.seq) - 1]"
                :exerciseInfo="exerciseInfo"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                @changeChoiceCando="changeChoiceCando"
                @changeAnswer="changeAnswer"
                @goBrushResult="goBrushResult"
              />
              <choice
                v-if="
                  question.answer_mode === 'choice' ||
                    question.answer_mode === 'uncertain_choice'
                "
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                @changeChoiceCando="changeChoiceCando"
                :currentItem="item"
                :totalCount="all"
                :wrong="wrong"
                :showShadow="showShadow"
                :exerciseInfo="exerciseInfo"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                :disabledData="mode === 'report' ? false : choiceIsCando.length === 0 ? true : mode === 'do' && !choiceIsCando[Number(question.seq) - 1]"
                @submitSingleAnswer="submitSingleAnswer"
                @changeTouch="changeTouch"
                @changeAnswer="changeAnswerArray"
                @goBrushResult="goBrushResult"
              />

              <essay
                v-if="question.answer_mode === 'rich_text'"
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :currentItem="item"
                :totalCount="all"
                :wrong="wrong"
                :questionStatus="status"
                :showShadow="showShadow"
                :exerciseInfo="exerciseInfo"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                :disabledData="iscando.length === 0 ? true : mode === 'do' && !choiceIsCando[Number(question.seq) - 1]"
                @changeTouch="changeTouch"
                @submitSingleAnswer="submitSingleAnswer"
                @changeAnswer="changeAnswer"
                @goBrushResult="goBrushResult"
              />

              <fill
                v-if="question.answer_mode === 'text'"
                :ref="'submit'+(Number(question.seq) - 1)"
                :mode="mode"
                :itemData="getItemProps(question, questionIndex)"
                :commonData="getAnswerModeProps(question, questionIndex)"
                :attachements="
                  getItemProps(question, questionIndex).question.attachments
                "
                :currentItem="item"
                :totalCount="all"
                :wrong="wrong"
                :showShadow="showShadow"
                :exerciseInfo="exerciseInfo"
                :questionStatus="status"
                :fillStatus="fillStatus"
                :disabledData="iscando.length === 0 ? true : mode === 'do' && !choiceIsCando[Number(question.seq) - 1]"
                :reviewedCount="reviewedCount"
                :isAnswerFinished="isAnswerFinished"
                @changeTouch="changeTouch"
                @submitSingleAnswer="submitSingleAnswer"
                @changeAnswer="changeAnswerArray"
                @goBrushResult="goBrushResult"
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
import singleChoice from "./component/single-choice";
import choice from "./component/choice";
import judge from "./component/judge";
import essay from "./component/essay";
import fill from "./component/fill";
import ibsSlide from "./component/slide-btn";
import answerModel from "./component/answer-model";
import sectionTitle from "./component/section-title.vue";
import _ from 'lodash';
import { Toast } from 'vant';
import Api from '@/api';

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: "ibs-item",
  components: {
    sectionTitle,
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
      materialAnalysis: false,
      isLeave: false,
      status: '',
      reviewList: [],
      touchable: true,
      showShadow: '',
      width: WINDOWWIDTH,
    };
  },
  props: {
    item: {
      // 题目
      type: Object,
      default: () => {}
    },
    mode: {
      // 当前模式
      type: String,
      default: "do"
    },
    itemUserAnswer: {
      // 用户答案
      type: Object,
      default: () => {}
    },
    needScore: {
      // 是否展示分数
      type: Boolean,
      default: true
    },
    all: {
      // 题目总数
      type: Number,
      default: 0
    },
    keys: {
      // 当前索引
      type: Array,
      default: () => []
    },
    iscando: {
      // 当前索引
      type: Array,
      default: () => []
    },
    choiceIsCando: {
      // 当前索引
      type: Array,
      default: () => []
    },
    question_reports: {
      // 当前索引
      type: Array,
      default: () => []
    },
    itemUserReport: {
      // 用户答题结果
      type: Object,
      default: () => {}
    },
    wrongMode: {
      // 是否为做题模式
      type: Boolean,
      default: false
    },
    doLookAnalysis: {
      // 是否做题时可以查看解析
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
    },
    reviewedCount: {
      type: Number,
      default: 0
    },
    exerciseInfo: {
      type: Object,
      default: () => {}
    },
    items: {
      type: Array,
      default: () => []
    },
    wrong: {
      type: Boolean,
      default: false
    },
    isAnswerFinished: {
      type: Number,
      default: 0
    },
    reviewedQuestion: {
      type: Array,
      default: () => []
    },
    fillStatus: {
      type: Array,
      default: () => []
    },
    EssayRadio: {
      type: Array,
      default: () => []
    }
  },
  inject: ["brushDo"],
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
    this.$nextTick(() => {
      if (this.mode === 'do') {
        const allItem = []
        this.items.forEach((item, index) => {
          item.questions.forEach((subItem, subIndex) => {
            allItem.push(subItem)
          })
          this.showShadow = allItem[allItem.length - 1].id
        });
      }
    })
    this.setSwiperHeight();
  },
  methods: {
    changeReviewList(status) {
      this.$emit('changeStatus', status)
    },

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
    changeAnswer(value, keys, data) {
      this.itemUserAnswer.question_responses[keys].response = [value];
      this.$emit("changeAnswer", this.itemUserAnswer, this.keys);

      if(this.brushDo.answerRecord.exercise_mode === '1' && data.type !== 'essay') {
        this.notSwiperChangeTouch(false);
        this.touchable = false;
        this.submitSingleAnswer(value.split(''), data)
      }
    },
    // 单题提交
    submitSingleAnswer: _.debounce(function (response, data, type) {
      Api.submitSingleAnswer({
        query:{
          id: this.brushDo.answerRecord.id
        },
        data:{
          admission_ticket: this.brushDo.answerRecord.admission_ticket,
          assessment_id: this.brushDo.answerRecord.assessment_id,
          exerciseMode: this.brushDo.answerRecord.exercise_mode,
          section_id: this.item.section_id,
          item_id: data.item_id,
          question_id: data.question_id,
          response: response,
        }
      }).then(res=> {
        const idx = data.seq - 1
        this.$refs['submit'+idx][0].refreshChoice(res)
        this.status = res.status
        if (this.wrong) {
          this.changeChoiceCando()
          if (res.status === 'right') {
            setTimeout(() => {
              this.slideNext(true)
            }, 1000);
          }
        } else {
          if (res.status === 'right') {
            setTimeout(() => {
              this.slideNext(true)
            }, 1000);
            this.$emit('changeIsCando', idx, false)
          } else {
            this.$emit('changeIsCando', idx, false)
          }
        }
        this.swiperChangeTouch(true)
        this.touchable = true
        if (type === 'fill') {
          const questionData = {
            status: res.status,
            question_id: data.question_id
          }
          this.$emit('submitedQuestionStatus', questionData)
        }
        this.$emit('changeReviewedCount', res.reviewedCount, res.isAnswerFinished)

      }).catch(err=> {
        this.touchable = true
        this.swiperChangeTouch(true)
        Toast.fail(err.message)
      })
    }, 1000),
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
        questionId: question.id,
        item_id: question.item_id,
        aiAnalysisEnable: question.aiAnalysisEnable,
      };
      return data;
    },
    showMaterialAnalysis(data) {
      if (this.item.type !== "material") {
        return;
      }
      this.materialAnalysis = data;
    },
    changeChoiceCando() {
      this.$emit('changeChoiceCando', this.current, true)
    },
    slidePrev() {
      if (!this.touchable) {
        return
      }
      const swiperName = `childSwiper${this.item.id}`;
      const slide = this.$refs[swiperName].$swiper.slidePrev();
      if (!slide) {
        this.$emit("itemSlidePrev");
      }
    },
    slideNext(flag) {
      if (!this.touchable && !flag) {
        return
      }
      const swiperName = `childSwiper${this.item.id}`;
      const slide = this.$refs[swiperName].$swiper.slideNext();
      if (!slide || flag) {
        this.$emit("itemSlideNext");
      }
    },
    goBrushResult() {
      this.isLeave = true;
      const query = {
        type: 'chapter',
        title: this.$route.query.title,
        exerciseId: this.$route.query.exerciseId,
        categoryId: this.$route.query.categoryId,
        moduleId: this.$route.query.moduleId,
        isLeave: this.isLeave,
      };
      const answerRecordId = this.brushDo.recordId;
      this.$router.replace({
        path: `/brushResult/${answerRecordId}`,
        query,
      });
    },
    nextSkipQuestion() {
      setTimeout(() => {
        this.slideNext(true);
      }, 2000)
    },
    changeTouch() {
      this.touchable = false;
      this.notSwiperChangeTouch(false);
    },
    swiperChangeTouch(val) {
      this.$emit('changeTouch', val)
    },
    notSwiperChangeTouch(val) {
      this.$emit('noChangeTouch', val)
    },
    updataIsAnswerFinished(val, flag, data, questionId) {
      this.$emit('updataIsAnswerFinished', val, flag, data, questionId)
    },
    changeEssayRadio(data) {
      this.$emit('changeEssayRadio', data)
    }
  }
};
</script>

<template>
  <div id="ibs-item-bank" class="ibs-item-bank">
    <template v-if="this.question_reports.length > 0">
      <swiper
        ref="mySwiper"
        :height="height"
        :loop="false"
        :speed="500"
        @slideNextTransitionEnd="slideNextTransitionEnd"
        @slidePrevTransitionEnd="slidePrevTransitionEnd"
      >
        <template v-for="item in renderItmes">
          <swiper-slide
            :key="`paper${item.id}`"
            :ref="`paper${item.id}`"
            :style="{ height: height + 'px' }"
            class="ibs-paper-item"
          >
            <ibs-item
              :ref="`item${item.id}`"
              :item="item"
              :mode="mode"
              :itemUserAnswer="getUserAnwer(item.sectionIndex, item.itemIndex)"
              :needScore="needScore"
							:question_reports="question_reports"
              :all="Number(assessment.question_count)"
              :keys="[item.sectionIndex, item.itemIndex]"
              :itemUserReport="getUserReport(item.sectionIndex, item.itemIndex)"
              :showAnalysis="true"
              :showReport="true"
              :showResult="false"
              :showReview="true"
              :current="current"
              :itemLength="items.length"
              @itemSlideNext="itemSlideNext"
              @itemSlidePrev="itemSlidePrev"
							@changeStatus="changeStatus"
            />
          </swiper-slide>
        </template>
      </swiper>
      <!-- 答题卡 -->
      <card
        v-model="cardShow"
        :mode="mode"
        :sections="assessment.sections"
        :section_responses="section_responses"
        :answer_report="answerReport"
        :question_reports="question_reports"
        :needScore="needScore"
        @slideTo="slideTo"
      ></card>
      <ibs-footer
        :mode="mode"
        @showcard="showcard"
        @submitReview="submitReview"
      />
    </template>
  </div>
</template>
<script>
import ibsItem from "@/src/components/item/src/item.vue";
import card from "@/src/components/common/card";
import ibsFooter from "@/src/components/common/footer";

import itemBankMixins from "@/src/mixins/itemBankMixins.js";
let lastItemId = 0;
let questionIndex = 0;
let itemIndex = 0;
let richTextIndex = 0;
export default {
  name: "item-review",
  mixins: [itemBankMixins],
  components: {
    ibsItem,
    card,
    ibsFooter
  },
  props: {
    mode: {
      type: String,
      default: "review"
    },
    assessmentResponse: {
      type: Object,
      default: () => {}
    },
    answerScene: {
      type: Object,
      default: () => {}
    },
    answerRecord: {
      type: Object,
      default: () => {}
    },
    answerReport: {
      type: Object,
      default: () => {}
    },
    assessment: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      section_responses: [],
      height: 0, //swiper高低
      cardShow: false,
      currentItemIndex: 0, //当前题目索引
      sourceMap: {},
      sections: [],
      question_reports: [],
      items: [],
      current: 0,
      renderItmes: [],
      answerAttachments: {}
    };
  },
  provide() {
    return {
      itemEngine:this
    }
  },
  inject: ["brushDo"],
  computed: {
    needScore() {
      return !!Number(this.answerScene.need_score);
    }
  },
  created() {
    this.$on("changeStatus", this.changeStatus);
    this.$on("changeScore", this.changeScore);
    this.resetDefaultData();
  },
  mounted() {
    this.setSwiperHeight();
    this.getSectionResponses();
  },
  methods: {
    resetDefaultData() {
      lastItemId = 0;
      questionIndex = 0;
      itemIndex = 0;
      richTextIndex = 0;
    },
    //题卡定位
    slideTo(keys) {
      const itemKey = `item${keys.itemId}`;
      this.current = Math.max(
        this.sourceMap[`item_${keys.itemId}`].reviewIndex,
        0
      );
      this.changeRenderItems(this.current);
      this.fastSlide();
      this.$nextTick(() => {
        const childSwiper = this.$refs[itemKey][0].$refs[
          `childSwiper${keys.itemId}`
        ];
        const childSwiperSlide = Math.max(
          this.sourceMap[`question_${keys.questionId}`].reviewIndex
        );
        childSwiper.$swiper.slideTo(childSwiperSlide, 0, false);
      });
    },
    getSectionResponses() {
      this.section_responses = this.answerReport.section_reports;
      this.formateSections();
    },
    getResponseAttachments() {
      if (!this.assessmentResponse.section_responses) return;

      this.assessmentResponse.section_responses.forEach(response => {
        response.item_responses.forEach(item => {
          item.question_responses.forEach(question => {
            this.answerAttachments[question.question_id] = question.attachments;
          });
        });
      });
    },
    //遍历获取答案体结构
    formateSections() {
      this.getResponseAttachments();
      this.assessment.sections.forEach((item, sectionIndex) => {
        const sectionReviewNum = this.answerReport.section_reports[sectionIndex]
          .reviewing_question_num;
        if (Number(sectionReviewNum) > 0) {
          this.formateItems(item.items, sectionIndex);
        }
      });
      this.changeRenderItems(0);
    },
    formateItems(items, sectionIndex) {
      items.forEach((item, ItemIndex) => {
        const itemReviewNum = this.answerReport.section_reports[sectionIndex]
          .item_reports[ItemIndex].reviewing_question_num;
        if (itemReviewNum > 0) {
          item.sectionIndex = sectionIndex;
          item.itemIndex = ItemIndex;
          this.items.push(item);
          this.formateQuestions(item, item.questions, sectionIndex, ItemIndex);
        }
      });
    },
    formateQuestions(item, questions, s, i) {
      let richTextNum = 0;
      questions.forEach((question, q) => {
        if (question.answer_mode === "rich_text") {
          richTextNum += 1;
          this.assessment.sections[s].items[i].questions[q].reviewShow = true;
          this.setReviewList(s, i, q);
          if (lastItemId !== item.id) {
            lastItemId = item.id;
            questionIndex = 0;
            if (!item.reviewIndex) {
              item.reviewIndex = itemIndex;
              itemIndex++;
            }
          }
          if (!question.reviewIndex) {
            question.reviewIndex = questionIndex;
            questionIndex++;
          }
          question.richTextIndex = richTextIndex;
          question.attachments = question.attachments.concat(
            this.answerAttachments[question.id] || []
          );
          richTextIndex++;
          this.setSourceMap(item, question, s, i, q);
        } else {
          this.assessment.sections[s].items[i].questions[q].reviewShow = false;
        }
      });
      if (richTextNum > 0) {
        this.assessment.sections[s].richTextNum = richTextNum;
        this.assessment.sections[s].items[i].richTextNum = richTextNum;
      }
    },
    setReviewList(s, i, q) {
      const id = this.answerReport.section_reports[s].item_reports[i]
        .question_reports[q].id;
      this.question_reports.push({
        id,
        score: null,
        comment: null,
        status: null
      });
    },
    setSourceMap(item, question, s, i, q) {
      item.sectionIndex = s;
      item.itemIndex = i;
      question.sectionIndex = s;
      question.itemIndex = i;
      question.questionIndex = q;
      const itemReport = this.answerReport.section_reports[s].item_reports[i];
      item.reviewNum = itemReport.reviewing_question_num;
      this.sourceMap[`item_${item.id}`] = item;
      this.sourceMap[`question_${question.id}`] = question;
    },
    showcard() {
      this.cardShow = true;
    },
    getUserAnwer(s, i) {
      return this.answerReport.section_reports[s].item_reports[i];
    },
    getUserReport(s, i) {
      return this.answerReport.section_reports[s].item_reports[i];
    },
    changeStatus(e) {
      const index = this.sourceMap[`question_${e.questionId}`].richTextIndex;
      this.question_reports[index].status = e.status;
    },
    changeScore(e) {
      const index = this.sourceMap[`question_${e.questionId}`].richTextIndex;
      this.question_reports[index].score = e.score;
    },
    submitReview() {
      const allReview = this.question_reports.every(item => {
        if (this.needScore) {
          return item.score || item.score === 0;
        } else {
          return item.status;
        }
      });
      if (!allReview) {
        this.$toast({
          message: "还有题目未批阅",
          getContainer: () => {
            return document.querySelector("#ibs-item-bank");
          }
        });
      } else {
        const data = {
          report_id: this.answerReport.id,
          question_reports: this.question_reports
        };
        this.$emit("getReviewData", data);
      }
    }
  }
};
</script>

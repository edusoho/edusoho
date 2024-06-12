<template>
  <div id="ibs-item-bank" class="ibs-item-bank">
    <template v-if="this.section_responses.length > 0">
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
              :wrongMode="wrongMode"
              :itemUserAnswer="getUserAnwer(item.sectionIndex, item.itemIndex)"
              :needScore="needScore"
              :all="Number(assessment.question_count)"
              :keys="[item.sectionIndex, item.itemIndex]"
              :itemUserReport="getUserReport(item.sectionIndex, item.itemIndex)"
              :showAnalysis="true"
              :showReport="true"
              :current="current"
              :wrong="wrong"
              :itemLength="items.length"
              :exerciseInfo="answerRecord"
              @itemSlideNext="itemSlideNext"
              @itemSlidePrev="itemSlidePrev"
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
        :wrongMode="wrongMode"
        @slideTo="slideTo"
      ></card>
      <ibs-footer
        :mode="mode"
        @showcard="showcard"
        @lookWrong="lookWrong"
        :wrongMode="wrongMode"
      />
    </template>
  </div>
</template>
<script>
import { Toast } from "vant";
import ibsItem from "@/src/components/item/src/item.vue";
import card from "@/src/components/common/card";
import ibsFooter from "@/src/components/common/footer";
import itemBankMixins from "@/src/mixins/itemBankMixins.js";
let lastItemId = 0;
let questionIndex = 0;
let itemIndex = 0;

export default {
  name: "item-report",
  mixins: [itemBankMixins],
  components: {
    ibsItem,
    // ibsSlide,
    card,
    ibsFooter
  },
  provide() {
    return {
      itemEngine:this
    }
  },
  inject: ["brushDo"],
  props: {
    mode: {
      type: String,
      default: "report"
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
    },
    assessmentResponse: {
      type: Object,
      default: () => {}
    },
    wrong: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      section_responses: [],
      height: 0, // swiper高低
      cardShow: false,
      wrongMode: false, // 错题模式
      currentItemIndex: 0, // 当前题目索引
      sourceMap: {},
      hasWrong: false,
      current: 0,
      items: [],
      wrongItems: [],
      defaultItems: [],
      renderItmes: [],
      answerAttachments: {}
    };
  },
  computed: {
    needScore() {
      return !!Number(this.answerScene.need_score);
    }
  },
  mounted() {
    this.setSwiperHeight();
    this.getSectionResponses();
  },
  methods: {
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
    resetDefaultData() {
      lastItemId = 0;
      questionIndex = 0;
      itemIndex = 0;
    },
    // 题卡定位
    slideTo(keys) {
      const itemKey = `item${keys.itemId}`;
      let itemSlide = Math.max(keys.itemIndex - 1, 0);
      let childSwiperSlide = Math.max(keys.questionIndex, 0);
      if (this.wrongMode) {
        itemSlide = Math.max(
          this.sourceMap[`item_${keys.itemId}`].wrongIndex,
          0
        );
        childSwiperSlide = Math.max(
          this.sourceMap[`question_${keys.questionId}`].wrongIndex
        );
      }
      this.current = itemSlide;
      this.changeRenderItems(this.current);
      this.fastSlide();
      this.$nextTick(() => {
        const childSwiper = this.$refs[itemKey][0].$refs[
          `childSwiper${keys.itemId}`
        ];

        childSwiper.$swiper.slideTo(childSwiperSlide, 0, false);
      });
    },
    getSectionResponses() {
      this.section_responses = this.answerReport.section_reports;
      this.formateSections();
    },
    // 遍历获取答案体结构
    formateSections() {
      this.getResponseAttachments();
      this.assessment.sections.forEach((item, sectionIndex) => {
        this.formateItems(item.items, sectionIndex);
      });
      this.items = this.defaultItems;
      this.changeRenderItems(0);
    },
    formateItems(items, sectionIndex) {
      items.forEach((item, ItemIndex) => {
        const rightNum = this.answerReport.section_reports[sectionIndex]
          .item_reports[ItemIndex].right_question_num;

        const allNum = this.answerReport.section_reports[sectionIndex]
          .item_reports[ItemIndex].question_count;

        item.sectionIndex = sectionIndex;
        item.itemIndex = ItemIndex;
        this.defaultItems.push(item);
        if (rightNum < allNum) {
          this.wrongItems.push(item);
        }
        this.formateQuestions(item, item.questions, sectionIndex, ItemIndex);
      });
    },
    formateQuestions(item, questions, sectionIndex, itemIndex) {
      questions.forEach((question, questionIndex) => {
        question.attachments = question.attachments.concat(
          this.answerAttachments[question.id] || []
        );
        this.getQuestionWrongIndex(
          item,
          question,
          sectionIndex,
          itemIndex,
          questionIndex
        );
      });
    },
    getQuestionWrongIndex(item, question, s, i, q) {
      const status = this.answerReport.section_reports[s].item_reports[i]
        .question_reports[q].status;
      if (status !== "right") {
        this.hasWrong = true;
        if (lastItemId !== item.id) {
          lastItemId = item.id;
          questionIndex = 0;
          if (!item.wrongIndex) {
            item.wrongIndex = itemIndex;
            itemIndex++;
          }
        }
        if (!question.wrongIndex) {
          question.wrongIndex = questionIndex;
          questionIndex++;
        }
      }
      this.setSourceMap(item, question, s, i, q);
    },
    setSourceMap(item, question, s, i, q) {
      item.sectionIndex = s;
      item.itemIndex = i;
      question.sectionIndex = s;
      question.ItemIndex = i;
      question.questionIndex = q;
      const itemReport = this.answerReport.section_reports[s].item_reports[i];
      item.wrongNum = itemReport.question_count - itemReport.right_question_num;
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
    changeswiper() {
      this.currentItemIndex = this.$refs.mySwiper.$swiper.activeIndex;
    },

    getIsWrong(s, q) {
      if (!this.wrongMode) {
        return true;
      }
      const rightNum = Number(
        this.answerReport.section_reports[s].item_reports[q].right_question_num
      );
      const questionNum = Number(
        this.answerReport.section_reports[s].item_reports[q].question_count
      );
      return rightNum < questionNum;
    },
    lookWrong() {
      if (!this.hasWrong) {
        Toast({
          message: "没有错题",
          getContainer: "#ibs-item-bank"
        });
        return;
      }
      this.wrongMode = !this.wrongMode;
      if (!this.wrongMode) {
        this.$nextTick(() => {
          this.items = this.defaultItems;
          this.current = 0;
          this.changeRenderItems(this.current);
          this.fastSlide();
        });
        return;
      }
      const item = this.items[this.current];
      // 如果当前是错题，停留在当前题
      const itemId = Number(item.id);
      const itemKey = `item${itemId}`;
      const currentItem = this.sourceMap[`item_${itemId}`];

      const s = Number(currentItem.sectionIndex);
      const i = Number(currentItem.itemIndex);
      const childSwiper = this.$refs[itemKey][0].$refs[`childSwiper${itemId}`];

      const q = childSwiper.$swiper.activeIndex;
      const question = this.assessment.sections[s].items[i].questions[q];
      this.items = this.wrongItems;
      if (currentItem.wrongIndex) {
        this.current = currentItem.wrongIndex;
        this.changeRenderItems(this.current);
        this.fastSlide();
        if (question.wrongIndex) {
          // 子级swiper滑动
          this.$nextTick(() => {
            childSwiper.$swiper.slideTo(question.wrongIndex, 0, false);
          });
        }
      } else {
        this.current = 0;
        this.changeRenderItems(this.current);
        this.fastSlide();
      }
    }
  }
};
</script>
